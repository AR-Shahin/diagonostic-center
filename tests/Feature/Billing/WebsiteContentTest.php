<?php

use App\Models\Admin;
use App\Models\Article;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WebRequest;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\WebsiteContentSeeder;

beforeEach(function () {
    $this->branch = Branch::create(['code' => 'MAIN', 'name_en' => 'Main', 'name_bn' => 'প্রধান শাখা', 'is_main' => true]);
    $this->doctor = Doctor::create(['name_en' => 'Dr. Real Person', 'name_bn' => 'ডা. আসল', 'slug' => 'dr-real', 'speciality_en' => 'Medicine', 'speciality_bn' => 'মেডিসিন',
        'days' => [6, 1], 'time_from' => '16:00', 'time_to' => '20:00', 'fee' => 700, 'branch_id' => $this->branch->id]);
});

it('saves an appointment request from the website', function () {
    $this->from(route('site.appointment'))->post(route('site.appointment.store'), [
        'type' => 'doctor', 'doctor' => 'dr-real', 'branch' => $this->branch->id, 'date' => now()->addDay()->toDateString(),
        'slot' => '10:00', 'name' => 'Karim', 'phone' => '+880 1712-345678', 'age' => 40,
    ])->assertRedirect(route('site.appointment'))->assertSessionHas('form_sent', 'appointment');

    $r = WebRequest::sole();
    expect($r->type)->toBe('appointment')->and($r->phone)->toBe('01712345678')->and($r->doctor_id)->toBe($this->doctor->id)->and($r->status)->toBe('new');
});

it('needs an address for home collection, a real phone and a date from today', function () {
    $this->post(route('site.appointment.store'), ['type' => 'home', 'name' => 'A', 'phone' => '12345', 'date' => now()->subDay()->toDateString()])
        ->assertSessionHasErrors(['address', 'phone', 'date']);

    Setting::put(['home_collection_enabled' => 0]);
    $this->post(route('site.appointment.store'), ['type' => 'home', 'name' => 'A', 'phone' => '01712345678', 'date' => now()->toDateString(), 'address' => 'X'])
        ->assertSessionHasErrors('type');
    expect(WebRequest::count())->toBe(0);
});

it('saves contact and corporate messages, and quietly drops bots', function () {
    $this->post(route('site.contact.store'), ['name' => 'B', 'phone' => '01812345678', 'subject' => 'Price', 'message' => 'How much is CBC?'])->assertSessionHas('form_sent', 'contact');
    $this->post(route('site.corporate.store'), ['name' => 'C', 'phone' => '01912345678', 'company' => 'Acme Ltd', 'staff' => 120])->assertSessionHas('form_sent', 'corporate');
    $this->post(route('site.contact.store'), ['name' => 'Bot', 'phone' => '01812345678', 'message' => 'spam', 'website' => 'http://spam'])->assertSessionHas('form_sent');

    expect(WebRequest::pluck('type')->all())->toBe(['contact', 'corporate'])
        ->and(WebRequest::where('type', 'corporate')->value('organisation'))->toBe('Acme Ltd');
});

it('shows only real, published content on the website', function () {
    Doctor::create(['name_en' => 'Dr. Hidden', 'slug' => 'dr-hidden', 'show_on_website' => false]);
    Testimonial::create(['name' => 'Rahima K.', 'text_bn' => 'খুব ভালো সেবা', 'rating' => 5, 'consent' => true]);
    Testimonial::create(['name' => 'No Consent', 'text_bn' => 'অনুমতি নেই', 'rating' => 5, 'consent' => false]);
    Article::create(['title_bn' => 'প্রকাশিত লেখা', 'slug' => 'published', 'body_bn' => '<p>ok</p>', 'published_at' => now()->subHour()]);
    Article::create(['title_bn' => 'খসড়া লেখা', 'slug' => 'draft']);
    $this->seed(WebsiteContentSeeder::class);

    $home = $this->get(route('site.home'))->assertOk();
    $home->assertSee('ডা. আসল')->assertDontSee('Dr. Hidden')
        ->assertSee('খুব ভালো সেবা')->assertDontSee('অনুমতি নেই')
        ->assertSee('প্রকাশিত লেখা')->assertDontSee('খসড়া লেখা')
        ->assertSee('কীভাবে ডায়াগনস্টিক টেস্ট বুক করবেন?');

    $this->get(route('site.doctors'))->assertSee('শনি, সোম')->assertSee('৳ 700');
    $this->get(route('site.article', 'published'))->assertOk()->assertSee('প্রকাশিত লেখা');
    $this->get(route('site.article', 'draft'))->assertNotFound();
    $this->get(route('site.sitemap'))->assertSee('health-tips/published')->assertDontSee('health-tips/draft');
});

it('hides empty sections instead of showing placeholders', function () {
    Doctor::query()->forceDelete();
    $this->get(route('site.home'))->assertOk()->assertDontSee('doctors-title')->assertDontSee('reviews-title')->assertDontSee('id="articles"', false);
    $this->get(route('site.doctors'))->assertSee('চিকিৎসকদের তালিকা শীঘ্রই আসছে');
});

describe('admin', function () {
    beforeEach(function () {
        DatabaseSeeder::rolesAndPermissions();
        $this->owner = Admin::create(['name' => 'Owner', 'email' => 'owner@test.com', 'password' => 'secret']);
        $this->owner->assignRole('Super Admin');
    });

    it('opens every website screen', function () {
        foreach (['admin.doctors.index', 'admin.articles.index', 'admin.faqs.index', 'admin.testimonials.index', 'admin.website-content.index', 'admin.web-requests.index'] as $route) {
            $this->actingAs($this->owner, 'admin')->get(route($route))->assertOk();
        }
    });

    it('saves a doctor with chamber days, and an article without scripts', function () {
        $this->actingAs($this->owner, 'admin')->post(route('admin.doctors.store'), [
            'name_en' => 'Dr. New', 'days' => ['0', '2'], 'time_from' => '09:00', 'time_to' => '12:00', 'accepts_appointments' => '1', 'show_on_website' => '1', 'status' => '1',
        ])->assertSessionHasNoErrors();
        expect(Doctor::where('slug', 'dr-new')->first()->days)->toBe([0, 2]);

        $this->actingAs($this->owner, 'admin')->post(route('admin.articles.store'), [
            'title_bn' => 'নতুন', 'title_en' => 'New tip', 'body_bn' => '<p onclick="x()">Hi</p><script>alert(1)</script>', 'body_en' => "Line one\n\nLine two",
        ])->assertSessionHasNoErrors();
        $a = Article::where('slug', 'new-tip')->first();
        expect($a->body_bn)->toBe('<p>Hi</p>')->and($a->body_en)->toBe("<p>Line one</p>\n<p>Line two</p>")->and($a->published_at)->toBeNull();
    });

    it('saves home page text that the website then uses', function () {
        $this->actingAs($this->owner, 'admin')->post(route('admin.website-content.update'), [
            'hero_eyebrow_bn' => 'ঢাকার বিশ্বস্ত ল্যাব', 'hero_popular' => 'CBC, TSH', 'stat_years' => 12, 'home_collection_enabled' => '1', 'home_collection_areas_bn' => "মিরপুর\nউত্তরা",
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get(route('site.home'))->assertSee('ঢাকার বিশ্বস্ত ল্যাব')->assertSee('উত্তরা')->assertSee('data-count="12"', false);
    });

    it('lets the receptionist handle requests but not edit the website', function () {
        $req = WebRequest::create(['type' => 'appointment', 'name' => 'Z', 'phone' => '01712345678']);
        $desk = Admin::create(['name' => 'Desk', 'email' => 'desk@test.com', 'password' => 'secret']);
        $desk->assignRole('Receptionist');

        $this->actingAs($desk, 'admin')->get(route('admin.web-requests.index'))->assertOk()->assertSee('01712345678');
        $this->actingAs($desk, 'admin')->patch(route('admin.web-requests.update', $req), ['status' => 'confirmed', 'staff_note' => 'Tomorrow 10am'])->assertSessionHasNoErrors();
        expect($req->fresh()->status)->toBe('confirmed')->and($req->fresh()->handled_by)->toBe($desk->id);

        $this->actingAs($desk, 'admin')->get(route('admin.doctors.index'))->assertForbidden();
    });
});
