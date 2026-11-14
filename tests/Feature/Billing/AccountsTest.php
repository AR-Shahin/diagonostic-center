<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\CommissionEntry;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\Referrer;
use App\Models\ReferrerRate;
use App\Models\Setting;
use App\Services\Accounts\CashClosingService;
use App\Services\Accounts\CommissionService;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'is_main' => true]);
    $this->path = Department::create(['name_en' => 'Pathology', 'slug' => 'pathology']);
    $this->img = Department::create(['name_en' => 'Imaging', 'slug' => 'imaging']);
    $this->cbc = LabTest::create(['department_id' => $this->path->id, 'code' => 'CBC', 'name_en' => 'CBC', 'slug' => 'cbc', 'price' => 400]);
    $this->ecg = LabTest::create(['department_id' => $this->path->id, 'code' => 'ECG', 'name_en' => 'ECG', 'slug' => 'ecg', 'price' => 300]);
    $this->usg = LabTest::create(['department_id' => $this->img->id, 'code' => 'USG', 'name_en' => 'USG', 'slug' => 'usg', 'price' => 1500]);

    $this->doc = Referrer::create(['code' => 'R-1', 'type' => 'doctor', 'name' => 'Dr. A']);
    $this->other = Referrer::create(['code' => 'R-2', 'type' => 'doctor', 'name' => 'Dr. B']);

    ReferrerRate::create(['type' => 'percent', 'value' => 30]);                                   // default: everything 30%
    ReferrerRate::create(['department_id' => $this->img->id, 'type' => 'percent', 'value' => 15]); // default: imaging 15%
    ReferrerRate::create(['referrer_id' => $this->doc->id, 'lab_test_id' => $this->usg->id, 'type' => 'fixed', 'value' => 300]); // Dr. A: USG ৳300

    $this->staff = Admin::create(['name' => 'Desk', 'email' => 'desk@test.com', 'password' => 'secret', 'max_discount_percent' => 20]);
    $this->boss = Admin::create(['name' => 'Boss', 'email' => 'boss@test.com', 'password' => 'secret', 'max_discount_percent' => 50]);
    $this->bills = app(InvoiceService::class);
    $this->commission = app(CommissionService::class);
});

function referredBill(array $o = []): Invoice
{
    $t = test();

    return $t->bills->create(array_merge([
        'branch_id' => $t->branch->id,
        'patient' => ['name' => 'Karim', 'mobile' => '01712000000', 'gender' => 'male', 'age_years' => 50],
        'referrer_id' => $t->doc->id,
        'items' => [['type' => 'test', 'id' => $t->cbc->id], ['type' => 'test', 'id' => $t->usg->id]],
        'payment_method' => 'cash',
    ], $o), $o['by'] ?? $t->staff);
}

it('picks the most specific rate', function () {
    $rates = $this->commission->ratesFor($this->doc);

    expect((float) $this->commission->pickRate($rates, $this->usg->id, $this->img->id)->value)->toBe(300.0)
        ->and((float) $this->commission->pickRate($rates, $this->cbc->id, $this->path->id)->value)->toBe(30.0)
        ->and((float) $this->commission->pickRate($this->commission->ratesFor($this->other), $this->usg->id, $this->img->id)->value)->toBe(15.0);
});

it('earns nothing until the bill is fully paid, then earns once', function () {
    $invoice = referredBill(['paid_amount' => 1000]);
    expect(CommissionEntry::count())->toBe(0);

    $this->bills->collect($invoice, 900, 'cash', null, $this->staff);
    expect((float) $this->doc->fresh()->unpaidCommission())->toBe(420.0); // CBC 30% of 400 = 120, USG fixed 300

    $this->commission->accrueIfDue($invoice->fresh());
    expect(CommissionEntry::count())->toBe(2);
});

it('can earn on billing instead of on payment', function () {
    Setting::put(['commission_trigger' => 'on_invoice']);
    referredBill(['paid_amount' => 0]);

    expect((float) $this->doc->fresh()->unpaidCommission())->toBe(420.0);
});

it('company bears the discount: commission is on the full price', function () {
    referredBill(['discount_type' => 'percent', 'discount_value' => 10, 'discount_reason' => 'Poor', 'paid_amount' => 1710]);

    expect((float) $this->doc->fresh()->unpaidCommission())->toBe(420.0)
        ->and((float) CommissionEntry::sum('discount_deducted'))->toBe(0.0);
});

it('referrer bears the discount: each test’s discount comes off its commission', function () {
    Setting::put(['discount_bearer' => 'referrer']);
    referredBill(['discount_type' => 'percent', 'discount_value' => 10, 'discount_reason' => 'Poor', 'paid_amount' => 1710]);

    // CBC 120 − 40 = 80; USG 300 − 150 = 150
    expect((float) $this->doc->fresh()->unpaidCommission())->toBe(230.0);
});

it('pays a package test on its share of the package price', function () {
    $package = Package::create(['code' => 'PK', 'name_en' => 'Pack', 'slug' => 'pack', 'price' => 1000]);
    $package->labTests()->attach([$this->cbc->id, $this->ecg->id]);
    referredBill(['referrer_id' => $this->other->id, 'items' => [['type' => 'package', 'id' => $package->id]], 'paid_amount' => 1000]);

    expect((float) $this->other->fresh()->unpaidCommission())->toBe(300.0); // 30% of 1000, split over the two shares
});

it('skips referrers who are not eligible and bills with no referrer', function () {
    $this->doc->update(['commission_enabled' => false]);
    referredBill(['paid_amount' => 1900]);
    referredBill(['referrer_id' => null, 'paid_amount' => 1900]);

    expect(CommissionEntry::count())->toBe(0);
});

it('removes unpaid commission when a bill is cancelled', function () {
    $invoice = referredBill(['paid_amount' => 1900]);
    $this->bills->cancel($invoice, 'Wrong patient', 'cash', $this->staff);

    expect(CommissionEntry::count())->toBe(0);
});

it('pays out, and recovers already-paid commission on a later cancel', function () {
    $invoice = referredBill(['paid_amount' => 1900]);
    $payout = $this->commission->pay($this->doc, now(), 'cash', null, null, $this->boss);
    expect($payout->payout_no)->toStartWith('CP-')->and((float) $payout->amount)->toBe(420.0)
        ->and($this->doc->fresh()->unpaidCommission())->toBe(0.0);

    $this->bills->cancel($invoice, 'Refund', 'cash', $this->staff);
    expect((float) $this->doc->fresh()->unpaidCommission())->toBe(-420.0);

    referredBill(['paid_amount' => 1900]); // next bill earns 420 → net 0, nothing to pay
    expect(fn () => $this->commission->pay($this->doc, now(), 'cash', null, null, $this->boss))->toThrow(ValidationException::class);
});

it('closes the day: opening + cash in − refunds − cash expenses − commission paid', function () {
    referredBill(['paid_amount' => 1900, 'by' => $this->boss]);                            // 1900 cash
    referredBill(['referrer_id' => null, 'paid_amount' => 400, 'payment_method' => 'bkash', 'by' => $this->boss, 'items' => [['type' => 'test', 'id' => $this->cbc->id]]]);
    $cat = ExpenseCategory::create(['name' => 'Tea']);
    Expense::create(['branch_id' => $this->branch->id, 'expense_category_id' => $cat->id, 'expense_date' => today(), 'amount' => 150, 'method' => 'cash', 'created_by' => $this->boss->id]);
    $this->commission->pay($this->doc, now(), 'cash', null, null, $this->boss);           // 420 cash out

    $service = app(CashClosingService::class);
    $s = $service->summary($this->boss, today());
    expect($s['cash_received'])->toBe(1900.0)->and($s['cash_expenses'])->toBe(150.0)
        ->and($s['commission_paid_cash'])->toBe(420.0)->and($s['non_cash'])->toBe(['bkash' => 400.0]);

    $closing = $service->close($this->boss, today(), 500, 1820, 'Short 10');
    expect((float) $closing->expected_cash)->toBe(1830.0)->and((float) $closing->difference)->toBe(-10.0);

    expect(fn () => $service->close($this->boss, today(), 0, 0, null))->toThrow(ValidationException::class)
        ->and(fn () => $service->verify($closing, $this->boss))->toThrow(ValidationException::class);

    $service->verify($closing, $this->staff);
    expect($closing->fresh()->status)->toBe('verified');
});

it('does not close a future day', function () {
    app(CashClosingService::class)->close($this->staff, Carbon::tomorrow(), 0, 0, null);
})->throws(ValidationException::class);
