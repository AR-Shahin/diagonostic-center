<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\InvoiceItem;
use App\Models\LabTest;
use App\Models\SampleType;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use App\Services\Lab\LabService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'is_main' => true]);
    $dept = Department::create(['name_en' => 'Lab', 'slug' => 'lab']);
    $edta = SampleType::create(['name_en' => 'Blood (EDTA)']);
    $serum = SampleType::create(['name_en' => 'Serum']);
    $mg = Unit::create(['name' => 'mg/dL']);
    $gdl = Unit::create(['name' => 'g/dL']);

    $this->cbc = LabTest::create(['department_id' => $dept->id, 'sample_type_id' => $edta->id, 'code' => 'CBC', 'name_en' => 'CBC', 'slug' => 'cbc', 'price' => 400]);
    $hb = $this->cbc->parameters()->create(['code' => 'HB', 'name' => 'Haemoglobin', 'unit_id' => $gdl->id, 'sort' => 1]);
    $hb->referenceRanges()->createMany([['gender' => 'male', 'low' => 13, 'high' => 17], ['gender' => 'female', 'low' => 12, 'high' => 15]]);
    $this->hb = $hb;

    $this->lipid = LabTest::create(['department_id' => $dept->id, 'sample_type_id' => $serum->id, 'code' => 'LIPID', 'name_en' => 'Lipid', 'slug' => 'lipid', 'price' => 1000]);
    $this->p = collect([
        ['TC', null, 200], ['TG', null, 150], ['HDL', null, null], ['LDL', 'TC - HDL - TG / 5', 100],
    ])->mapWithKeys(function ($row, $i) use ($mg) {
        $param = $this->lipid->parameters()->create(['code' => $row[0], 'name' => $row[0], 'unit_id' => $mg->id, 'formula' => $row[1], 'sort' => $i]);
        if ($row[2]) {
            $param->referenceRanges()->create(['gender' => 'any', 'high' => $row[2]]);
        }

        return [$row[0] => $param->id];
    });

    $this->usg = LabTest::create(['department_id' => $dept->id, 'code' => 'USG', 'name_en' => 'USG', 'slug' => 'usg', 'price' => 1500, 'report_type' => 'template']);

    $this->staff = Admin::create(['name' => 'Lab', 'email' => 'lab@test.com', 'password' => 'secret', 'max_discount_percent' => 10]);
    $this->lab = app(LabService::class);
    $this->invoice = app(InvoiceService::class)->create([
        'branch_id' => $this->branch->id,
        'patient' => ['name' => 'Rina Begum', 'mobile' => '01711111111', 'gender' => 'female', 'age_years' => 35],
        'items' => [['type' => 'test', 'id' => $this->cbc->id], ['type' => 'test', 'id' => $this->lipid->id], ['type' => 'test', 'id' => $this->usg->id]],
        'payment_method' => 'cash', 'paid_amount' => 2900,
    ], $this->staff);
});

function labItem(string $code): InvoiceItem
{
    return test()->invoice->labItems()->where('code', $code)->first();
}

it('creates one sample per tube type and none for imaging', function () {
    expect($this->invoice->samples)->toHaveCount(2)
        ->and(labItem('USG')->sample_id)->toBeNull()
        ->and($this->invoice->report_pin)->toHaveLength(6);
});

it('needs the sample before results, then flags by the patient’s sex', function () {
    expect(fn () => $this->lab->saveResult(labItem('CBC'), ['values' => [$this->hb->id => '11.2']], $this->staff))
        ->toThrow(ValidationException::class);

    $this->lab->collect(labItem('CBC')->sample, $this->staff);
    $result = $this->lab->saveResult(labItem('CBC'), ['values' => [$this->hb->id => '11.2']], $this->staff);

    $v = $result->values->first();
    expect($v->flag)->toBe('L')->and($v->reference_text)->toBe('12 - 15')
        ->and(labItem('CBC')->lab_status)->toBe('processing');
});

it('calculates formulas and locks the report once verified', function () {
    $this->lab->collect(labItem('LIPID')->sample, $this->staff);
    $result = $this->lab->saveResult(labItem('LIPID'), ['values' => [$this->p['TC'] => '220', $this->p['TG'] => '150', $this->p['HDL'] => '45']], $this->staff, verify: true);

    $ldl = $result->values->firstWhere('code', 'LDL');
    expect($ldl->value)->toBe('145.00')->and($ldl->flag)->toBe('H')
        ->and(labItem('LIPID')->lab_status)->toBe('ready');

    expect(fn () => $this->lab->saveResult(labItem('LIPID'), ['values' => []], $this->staff))->toThrow(ValidationException::class);

    $this->lab->amend(labItem('LIPID'), 'Wrong HDL', $this->staff);
    expect(labItem('LIPID')->lab_status)->toBe('processing')
        ->and(labItem('LIPID')->result->amendments)->toBe(1);
});

it('cleans report HTML and allows imaging without a sample', function () {
    $result = $this->lab->saveResult(labItem('USG'), ['body' => '<p onclick="x()"><b>Liver</b> normal</p><script>alert(1)</script>'], $this->staff, verify: true);

    expect($result->body)->toBe('<p><b>Liver</b> normal</p>');
});

it('gives a rejected sample a new barcode', function () {
    $old = labItem('CBC')->sample;
    $fresh = $this->lab->reject($old, 'Haemolysed', $this->staff);

    expect($fresh->sample_no)->not->toBe($old->sample_no)
        ->and(labItem('CBC')->sample_id)->toBe($fresh->id)
        ->and($old->fresh()->status)->toBe('rejected');
});

it('holds the report while a due is unpaid, everywhere', function () {
    Setting::put(['block_report_if_due' => 1, 'block_portal_report_if_due' => 1]);
    $this->lab->saveResult(labItem('USG'), ['body' => '<p>Normal</p>'], $this->staff, verify: true);
    expect($this->invoice->fresh()->canReleaseReport())->toBeTrue();

    $dueInvoice = app(InvoiceService::class)->create([
        'branch_id' => $this->branch->id, 'patient_id' => $this->invoice->patient_id,
        'items' => [['type' => 'test', 'id' => $this->usg->id]], 'payment_method' => 'cash', 'paid_amount' => 500,
    ], $this->staff);
    $this->lab->saveResult($dueInvoice->labItems()->first(), ['body' => '<p>Normal</p>'], $this->staff, verify: true);

    expect(fn () => $this->lab->deliver($dueInvoice->fresh(), $this->staff))->toThrow(ValidationException::class);

    $this->post(route('report.lookup'), ['invoice' => $dueInvoice->invoice_no, 'pin' => $dueInvoice->report_pin])->assertRedirect(route('report.show', $dueInvoice));
    $this->get(route('report.show', $dueInvoice))->assertRedirect(route('site.report'));
});

it('opens the report online with invoice number + PIN, never with a wrong PIN', function () {
    $this->lab->saveResult(labItem('USG'), ['body' => '<p>Liver normal in size</p>'], $this->staff, verify: true);

    $this->post(route('report.lookup'), ['invoice' => $this->invoice->invoice_no, 'pin' => '000000'])->assertSessionHasErrors('invoice');
    $this->get(route('report.show', $this->invoice))->assertForbidden();

    $this->post(route('report.lookup'), ['invoice' => strtolower($this->invoice->invoice_no), 'pin' => $this->invoice->report_pin]);
    $this->get(route('report.show', $this->invoice))->assertOk()->assertSee('Liver normal in size');
});

it('shows a QR check page without medical values', function () {
    $this->lab->collect(labItem('CBC')->sample, $this->staff);
    $this->lab->saveResult(labItem('CBC'), ['values' => [$this->hb->id => '11.2']], $this->staff, verify: true);

    $this->get(route('report.verify', $this->invoice->report_token))->assertOk()
        ->assertSee('CBC')->assertDontSee('11.2')->assertDontSee('Rina Begum');
    $this->get(route('report.verify', 'not-a-real-token'))->assertOk()->assertSee('পাওয়া যায়নি', false);
});

it('shows the report in the patient account only to the owner', function () {
    $this->lab->saveResult(labItem('USG'), ['body' => '<p>Normal study</p>'], $this->staff, verify: true);
    $owner = User::where('phone', '01711111111')->first();
    $owner->update(['must_change_password' => false]);
    $other = User::create(['name' => 'X', 'phone' => '01911111111', 'password' => 'secret-123', 'must_change_password' => false]);

    $this->actingAs($owner, 'web')->get(route('portal.report', $this->invoice))->assertOk()->assertSee('Normal study');
    $this->actingAs($other, 'web')->get(route('portal.report', $this->invoice))->assertNotFound();
});

it('rejects samples of a cancelled bill', function () {
    app(InvoiceService::class)->cancel($this->invoice, 'Wrong patient', 'cash', $this->staff);

    expect($this->invoice->samples()->where('status', '!=', 'rejected')->count())->toBe(0);
    expect(fn () => $this->lab->saveResult(labItem('USG'), ['body' => '<p>x</p>'], $this->staff))->toThrow(ValidationException::class);
});
