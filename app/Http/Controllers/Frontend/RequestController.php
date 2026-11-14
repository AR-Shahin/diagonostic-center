<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\WebRequest;
use App\Support\Frontend\SiteContent;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Website forms: appointment / home collection, contact message, corporate quote.
 * Each lands in Admin → Website requests. "website" is a hidden honeypot field that only bots fill.
 */
class RequestController extends Controller
{
    public function appointment(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return $this->sent('appointment');
        }
        $homeAllowed = (bool) SiteContent::setting('home_collection_enabled');
        $data = $this->validated($request, [
            'type' => ['required', Rule::in($homeAllowed ? ['test', 'doctor', 'home'] : ['test', 'doctor'])],
            'test' => ['nullable', 'string', 'max:255'],
            'doctor' => ['nullable', 'string', 'max:255', Rule::exists('doctors', 'slug')->where('status', true)->where('accepts_appointments', true)->whereNull('deleted_at')],
            'branch' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('status', true)],
            'date' => ['required', 'date', 'after_or_equal:today', 'before:+3 months'],
            'slot' => ['nullable', 'string', 'max:40'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'address' => ['required_if:type,home', 'nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        WebRequest::create([
            'type' => $data['type'] === 'home' ? 'home_collection' : 'appointment',
            'name' => $data['name'],
            'phone' => $data['phone'],
            'age' => $data['age'] ?? null,
            'branch_id' => $data['branch'] ?? null,
            'doctor_id' => $data['doctor'] ? Doctor::where('slug', $data['doctor'])->value('id') : null,
            'item' => $this->itemName($data['test'] ?? null),
            'preferred_date' => $data['date'],
            'preferred_time' => $data['slot'] ?? null,
            'address' => $data['address'] ?? null,
            'message' => $data['note'] ?? null,
            'ip' => $request->ip(),
        ]);

        return $this->sent('appointment');
    }

    public function contact(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return $this->sent('contact');
        }
        $data = $this->validated($request, [
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);
        WebRequest::create(['type' => 'contact', 'ip' => $request->ip()] + $data);

        return $this->sent('contact');
    }

    public function corporate(Request $request): RedirectResponse
    {
        if ($this->isBot($request)) {
            return $this->sent('corporate', '#corporate');
        }
        $data = $this->validated($request, [
            'company' => ['required', 'string', 'max:255'],
            'staff' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);
        WebRequest::create([
            'type' => 'corporate', 'name' => $data['name'], 'phone' => $data['phone'],
            'organisation' => $data['company'], 'staff_count' => $data['staff'] ?? null, 'ip' => $request->ip(),
        ]);

        return $this->sent('corporate', '#corporate');
    }

    private function validated(Request $request, array $rules): array
    {
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);

        return $request->validate($rules + [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:'.Phone::PATTERN],
        ], [
            'phone.regex' => __('সঠিক মোবাইল নম্বর দিন (01XXXXXXXXX)।'),
        ]);
    }

    /** The visitor picks a slug; store the readable name so staff know what was asked for. */
    private function itemName(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }
        $test = LabTest::where('slug', $slug)->first(['name_en', 'name_bn']);
        if ($test) {
            return $test->name_en;
        }
        $package = Package::where('slug', $slug)->first(['name_en']);

        return $package ? 'Package: '.$package->name_en : str($slug)->limit(100)->toString();
    }

    private function isBot(Request $request): bool
    {
        return filled($request->input('website'));
    }

    private function sent(string $form, string $anchor = ''): RedirectResponse
    {
        return redirect()->to(url()->previous().$anchor)->with('form_sent', $form);
    }
}
