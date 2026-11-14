<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LabTest;
use App\Models\Referrer;
use App\Models\ReferrerRate;
use App\Services\Billing\InvoiceService;
use App\Services\Reports\ReportService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'is_main' => true]);
    $this->other = Branch::create(['code' => 'B2', 'name_en' => 'Branch 2']);
    $path = Department::create(['name_en' => 'Pathology', 'slug' => 'pathology']);
    $img = Department::create(['name_en' => 'Imaging', 'slug' => 'imaging']);
    $this->cbc = LabTest::create(['department_id' => $path->id, 'code' => 'CBC', 'name_en' => 'CBC', 'slug' => 'cbc', 'price' => 400]);
    $this->usg = LabTest::create(['department_id' => $img->id, 'code' => 'USG', 'name_en' => 'USG', 'slug' => 'usg', 'price' => 1500]);
    $this->doc = Referrer::create(['code' => 'R-1', 'type' => 'doctor', 'name' => 'Dr. A']);
    ReferrerRate::create(['type' => 'percent', 'value' => 10]);
    $this->owner = Admin::create(['name' => 'Owner', 'email' => 'owner@test.com', 'password' => 'secret', 'max_discount_percent' => 50]);
    $bills = app(InvoiceService::class);

    // 1) CBC + USG, 10% off, referred, fully paid  → net 1710, commission 190
    $this->a = $bills->create(['branch_id' => $this->branch->id, 'patient' => ['name' => 'A', 'mobile' => '01711000001', 'gender' => 'male', 'age_years' => 40],
        'referrer_id' => $this->doc->id, 'items' => [['type' => 'test', 'id' => $this->cbc->id], ['type' => 'test', 'id' => $this->usg->id]],
        'discount_type' => 'percent', 'discount_value' => 10, 'discount_reason' => 'Poor', 'payment_method' => 'cash', 'paid_amount' => 1710], $this->owner);
    // 2) CBC at branch 2, 100 paid by bKash → 300 due
    $this->b = $bills->create(['branch_id' => $this->other->id, 'patient' => ['name' => 'B', 'mobile' => '01711000002', 'gender' => 'female', 'age_years' => 30],
        'items' => [['type' => 'test', 'id' => $this->cbc->id]], 'payment_method' => 'bkash', 'paid_amount' => 100], $this->owner);
    // 3) cancelled bill: must not count anywhere
    $c = $bills->create(['branch_id' => $this->branch->id, 'patient' => ['name' => 'C', 'mobile' => '01711000003', 'gender' => 'male', 'age_years' => 20],
        'items' => [['type' => 'test', 'id' => $this->usg->id]], 'payment_method' => 'cash', 'paid_amount' => 0], $this->owner);
    $bills->cancel($c, 'Wrong entry', 'cash', $this->owner);

    $cat = ExpenseCategory::create(['name' => 'Rent']);
    Expense::create(['branch_id' => $this->branch->id, 'expense_category_id' => $cat->id, 'expense_date' => today(), 'amount' => 500, 'method' => 'bank', 'created_by' => $this->owner->id]);

    $this->reports = fn (?int $branch = null) => app(ReportService::class)->for(today(), today(), $branch);
});

it('summary adds up: billed = collected + due, cancelled left out', function () {
    $r = ($this->reports)()->build('summary');

    expect($r['totals']['bills'])->toBe(2)
        ->and((float) $r['totals']['net'])->toBe(2110.0)
        ->and((float) $r['totals']['collected'])->toBe(1810.0)
        ->and((float) $r['totals']['discount'])->toBe(190.0);
});

it('splits income by department after discount', function () {
    $rows = collect(($this->reports)()->build('departments')['rows'])->keyBy('name');

    expect($rows['Pathology']['tests'])->toBe(2)->and($rows['Pathology']['net'])->toBe(760.0)   // 360 + 400
        ->and($rows['Imaging']['net'])->toBe(1350.0);                                                // 1500 − 150
});

it('filters by branch', function () {
    expect((float) ($this->reports)($this->other->id)->build('summary')['totals']['net'])->toBe(400.0);
});

it('shows referrer business and commission', function () {
    $rows = collect(($this->reports)()->build('referrers')['rows'])->keyBy('name');

    expect($rows['Dr. A']['net'])->toBe(1710.0)->and($rows['Dr. A']['commission'])->toBe(190.0)
        ->and($rows['Self (no referrer)']['net'])->toBe(400.0);
});

it('profit = income − commission − expenses', function () {
    $r = ($this->reports)()->build('profit');

    expect($r['totals']['amount'])->toBe(2110.0 - 190.0 - 500.0);
});

it('ages dues as of the end date', function () {
    $this->b->forceFill(['created_at' => now()->subDays(40)])->save();
    $rows = collect(app(ReportService::class)->for(today(), today(), null)->build('dues')['rows'])->keyBy('age');

    expect($rows['31–90 days']['due'])->toBe(300.0)->and($rows['0–7 days']['bills'])->toBe(0);
});

it('counts turnaround against the promised time', function () {
    $items = $this->a->labItems()->get();
    foreach ($items as $i => $item) {
        $item->result()->create(['report_type' => 'template', 'status' => 'verified', 'verified_at' => $i ? $item->delivery_at->copy()->addHour() : $item->created_at->copy()->addHours(2)]);
    }
    $r = ($this->reports)()->build('turnaround');

    expect($r['totals']['done'])->toBe(2)->and($r['cards'][1][1])->toBe('50%');
});

describe('screens', function () {
    beforeEach(function () {
        DatabaseSeeder::rolesAndPermissions();
        $this->owner->assignRole('Super Admin');
    });

    it('opens every report, the dashboard and the Excel export', function () {
        foreach (array_keys(ReportService::TYPES) as $type) {
            $this->actingAs($this->owner, 'admin')->get(route('admin.reports.index', $type))->assertOk();
        }
        $this->actingAs($this->owner, 'admin')->get(route('admin.reports.index', ['summary', 'print' => 1]))->assertOk()->assertSee('Summary');
        $csv = $this->actingAs($this->owner, 'admin')->get(route('admin.reports.export', 'departments'));
        $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        expect($csv->streamedContent())->toStartWith("\xEF\xBB\xBF")->toContain('Pathology');

        $this->actingAs($this->owner, 'admin')->get(route('admin.dashboard'))->assertOk()->assertSee('Lab right now')->assertSee('Billed today');
    });

    it('keeps profit away from staff without permission', function () {
        $accountant = Admin::create(['name' => 'Acc', 'email' => 'acc@test.com', 'password' => 'secret']);
        $accountant->assignRole('Accountant');
        $this->actingAs($accountant, 'admin')->get(route('admin.reports.index', 'summary'))->assertOk()->assertDontSee('Profit &amp; loss');
        $this->actingAs($accountant, 'admin')->get(route('admin.reports.index', 'profit'))->assertForbidden();

        $tech = Admin::create(['name' => 'Tech', 'email' => 'tech@test.com', 'password' => 'secret']);
        $tech->assignRole('Lab Technologist');
        $this->actingAs($tech, 'admin')->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($tech, 'admin')->get(route('admin.dashboard'))->assertOk()->assertDontSee('Billed today')->assertSee('Lab right now');
    });

    it('limits a branch user to their own branch', function () {
        $clerk = Admin::create(['name' => 'Clerk', 'email' => 'clerk@test.com', 'password' => 'secret', 'branch_id' => $this->other->id]);
        $clerk->assignRole('Manager');
        $this->actingAs($clerk, 'admin')->get(route('admin.reports.index', ['summary', 'branch_id' => $this->branch->id]))
            ->assertOk()->assertSee('Branch 2')->assertSee('400.00')->assertDontSee('2,110.00');
    });
});
