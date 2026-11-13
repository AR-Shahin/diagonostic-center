<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'is_main' => true]);
    $dept = Department::create(['name_en' => 'Lab', 'slug' => 'lab']);
    $this->cbc = LabTest::create(['department_id' => $dept->id, 'code' => 'CBC', 'name_en' => 'CBC', 'slug' => 'cbc', 'price' => 400]);
    $this->staff = Admin::create(['name' => 'Desk', 'email' => 'desk@test.com', 'password' => 'secret', 'max_discount_percent' => 10]);
    $this->bill = fn (array $patient, bool $family = false) => app(InvoiceService::class)->create([
        'branch_id' => $this->branch->id, 'patient' => $patient, 'as_family_member' => $family,
        'items' => [['type' => 'test', 'id' => $this->cbc->id]], 'payment_method' => 'cash',
    ], $this->staff);
});

it('creates a website account on the first test with the phone number as password', function () {
    ($this->bill)(['name' => 'Karim Ahmed', 'mobile' => '+880 1812-345678', 'gender' => 'male', 'age_years' => 40]);

    $user = User::where('phone', '01812345678')->first();
    expect($user)->not->toBeNull()
        ->and(Hash::check('01812345678', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->patients)->toHaveCount(1);
});

it('adds the next visit to the same account and the same patient', function () {
    ($this->bill)(['name' => 'Karim Ahmed', 'mobile' => '01812345678', 'gender' => 'male', 'age_years' => 40]);
    ($this->bill)(['name' => 'karim  ahmed', 'mobile' => '8801812345678', 'gender' => 'male', 'age_years' => 40]);

    expect(User::count())->toBe(1)
        ->and(Patient::count())->toBe(1)
        ->and(User::first()->invoices()->count())->toBe(2);
});

it('asks before putting a different person on a registered number, and allows family members', function () {
    ($this->bill)(['name' => 'Karim Ahmed', 'mobile' => '01812345678', 'gender' => 'male', 'age_years' => 40]);

    expect(fn () => ($this->bill)(['name' => 'Rina Begum', 'mobile' => '01812345678', 'gender' => 'female', 'age_years' => 35]))
        ->toThrow(ValidationException::class);

    ($this->bill)(['name' => 'Rina Begum', 'mobile' => '01812345678', 'gender' => 'female', 'age_years' => 35], family: true);

    $account = User::first();
    expect(User::count())->toBe(1)
        ->and($account->patients->pluck('name')->all())->toBe(['Karim Ahmed', 'Rina Begum']);
});

it('logs in with the phone number and asks for a new password first', function () {
    $invoice = ($this->bill)(['name' => 'Karim Ahmed', 'mobile' => '01812345678', 'gender' => 'male', 'age_years' => 40]);

    $this->post(route('portal.login'), ['phone' => '01812345678', 'password' => '01812345678'])
        ->assertRedirect(route('portal.dashboard'));
    $this->get(route('portal.dashboard'))->assertRedirect(route('portal.password.edit'));

    $this->put(route('portal.password.update'), [
        'current_password' => '01812345678', 'password' => 'my-secret-9', 'password_confirmation' => 'my-secret-9',
    ])->assertRedirect(route('portal.dashboard'));

    $this->get(route('portal.dashboard'))->assertOk()->assertSee($invoice->invoice_no);
    $this->get(route('portal.invoice', $invoice))->assertOk()->assertSee('CBC');
});

it('never shows another account’s bill', function () {
    $invoice = ($this->bill)(['name' => 'Karim Ahmed', 'mobile' => '01812345678', 'gender' => 'male', 'age_years' => 40]);
    $other = User::create(['name' => 'Other', 'phone' => '01911111111', 'password' => 'pass-123456', 'must_change_password' => false]);

    $this->actingAs($other, 'web')->get(route('portal.invoice', $invoice))->assertNotFound();
});

it('locks the login after five wrong passwords', function () {
    User::create(['name' => 'K', 'phone' => '01812345678', 'password' => '01812345678']);
    foreach (range(1, 5) as $i) {
        $this->post(route('portal.login'), ['phone' => '01812345678', 'password' => 'wrong']);
    }
    $this->post(route('portal.login'), ['phone' => '01812345678', 'password' => '01812345678'])
        ->assertSessionHasErrors('phone');
    $this->assertGuest('web');
});
