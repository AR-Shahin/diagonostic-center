<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\Setting;
use App\Services\Billing\InvoiceService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'is_main' => true]);
    $dept = Department::create(['name_en' => 'Lab', 'slug' => 'lab']);
    $make = fn ($code, $price, $offer = null) => LabTest::create([
        'department_id' => $dept->id, 'code' => $code, 'name_en' => $code, 'slug' => strtolower($code),
        'price' => $price, 'offer_price' => $offer, 'delivery_hours' => 24,
    ]);
    $this->cbc = $make('CBC', 400);
    $this->usg = $make('USG', 1500);
    $this->ecg = $make('ECG', 300);
    $this->hba1c = $make('HBA1C', 1000, 850);
    $this->package = Package::create(['code' => 'PK', 'name_en' => 'Pack', 'slug' => 'pack', 'price' => 1000]);
    $this->package->labTests()->attach([$this->cbc->id, $this->ecg->id]); // separately ৳700, package ৳1000 is only for the split test

    $this->staff = Admin::create(['name' => 'Desk', 'email' => 'desk@test.com', 'password' => 'secret', 'max_discount_percent' => 10]);
    $this->manager = Admin::create(['name' => 'Boss', 'email' => 'boss@test.com', 'password' => 'boss-pass', 'max_discount_percent' => 30]);
    $this->service = app(InvoiceService::class);
    $this->patient = ['name' => 'Rahim', 'mobile' => '01711000000', 'gender' => 'male', 'age_years' => 40];
});

function bill(array $overrides = []): Invoice
{
    $t = test();

    return $t->service->create(array_merge([
        'branch_id' => $t->branch->id,
        'patient' => $t->patient,
        'items' => [['type' => 'test', 'id' => $t->cbc->id], ['type' => 'test', 'id' => $t->usg->id]],
        'payment_method' => 'cash',
    ], $overrides), $overrides['by'] ?? $t->staff);
}

it('totals a bill with a percent discount and spreads the discount over the lines', function () {
    $invoice = bill(['discount_type' => 'percent', 'discount_value' => 10, 'discount_reason' => 'Regular', 'paid_amount' => 1000]);

    expect((float) $invoice->subtotal)->toBe(1900.0)
        ->and((float) $invoice->discount_amount)->toBe(190.0)
        ->and((float) $invoice->net_amount)->toBe(1710.0)
        ->and((float) $invoice->paid_amount)->toBe(1000.0)
        ->and((float) $invoice->due_amount)->toBe(710.0)
        ->and($invoice->status)->toBe('partial')
        ->and((float) $invoice->billedItems()->sum('discount_amount'))->toBe(190.0)
        ->and($invoice->invoice_no)->toStartWith('INV-MAIN-');
});

it('bills the offer price when a test has one', function () {
    $invoice = bill(['items' => [['type' => 'test', 'id' => $this->hba1c->id]]]);

    expect((float) $invoice->net_amount)->toBe(850.0);
});

it('splits a package into its tests and the shares add up to the package price', function () {
    $invoice = bill(['items' => [['type' => 'package', 'id' => $this->package->id]]]);
    $children = $invoice->items()->where('item_type', 'package_test')->get();

    expect($children)->toHaveCount(2)
        ->and((float) $children->sum('price'))->toBe(1000.0)
        ->and((float) $children->firstWhere('lab_test_id', $this->cbc->id)->price)->toBe(571.43);
});

it('rejects a test that is also inside a package on the same bill', function () {
    bill(['items' => [['type' => 'test', 'id' => $this->cbc->id], ['type' => 'package', 'id' => $this->package->id]]]);
})->throws(ValidationException::class);

it('needs a manager password for a discount above the staff limit', function () {
    expect(fn () => bill(['discount_type' => 'percent', 'discount_value' => 20, 'discount_reason' => 'x']))
        ->toThrow(ValidationException::class);

    $invoice = bill(['discount_type' => 'percent', 'discount_value' => 20, 'discount_reason' => 'x',
        'approver_id' => $this->manager->id, 'approver_password' => 'boss-pass']);

    expect($invoice->discount_approved_by)->toBe($this->manager->id);
});

it('does not let a manager approve more than their own limit', function () {
    bill(['discount_type' => 'percent', 'discount_value' => 50, 'discount_reason' => 'x',
        'approver_id' => $this->manager->id, 'approver_password' => 'boss-pass']);
})->throws(ValidationException::class);

it('adds VAT, the home collection charge and rounds to whole taka', function () {
    Setting::put(['vat_percent' => 5, 'home_collection_charge' => 200, 'round_to_whole_taka' => 1]);
    $invoice = bill(['items' => [['type' => 'test', 'id' => $this->hba1c->id]], 'home_collection' => true,
        'discount_type' => 'flat', 'discount_value' => 33, 'discount_reason' => 'x']);

    // 850 − 33 = 817; VAT 40.85; + 200 = 1057.85 → 1058
    expect((float) $invoice->vat_amount)->toBe(40.85)
        ->and((float) $invoice->net_amount)->toBe(1058.0)
        ->and((float) $invoice->round_off)->toBe(0.15);
});

it('collects dues and cancels with a full refund', function () {
    $invoice = bill(['paid_amount' => 500]);
    $this->service->collect($invoice, 1400, 'bkash', 'TX9', $this->staff);
    expect($invoice->fresh()->status)->toBe('paid');

    expect(fn () => $this->service->collect($invoice->fresh(), 1, 'cash', null, $this->staff))->toThrow(ValidationException::class);

    $refund = $this->service->cancel($invoice->fresh(), 'Wrong patient', 'cash', $this->staff);
    $invoice->refresh();
    expect((float) $refund->amount)->toBe(1900.0)
        ->and($invoice->status)->toBe('cancelled')
        ->and((float) $invoice->paid_amount)->toBe(0.0)
        ->and($invoice->labItems()->where('lab_status', '!=', 'cancelled')->count())->toBe(0);
});

it('holds the report while a due is unpaid when the rule is on', function () {
    Setting::put(['block_report_if_due' => 1]);
    $invoice = bill(['paid_amount' => 100]);
    expect($invoice->canReleaseReport())->toBeFalse();

    $this->service->collect($invoice, (float) $invoice->due_amount, 'cash', null, $this->staff);
    expect($invoice->fresh()->canReleaseReport())->toBeTrue();
});

it('keeps branch staff to their own branch', function () {
    $other = Branch::create(['code' => 'BR2', 'name_en' => 'Two']);
    $this->staff->update(['branch_id' => $this->branch->id]);

    bill(['branch_id' => $other->id]);
})->throws(ValidationException::class);
