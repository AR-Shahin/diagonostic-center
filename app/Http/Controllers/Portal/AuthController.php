<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RendersSitePage;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Patient login with phone number + password (default password = the phone number).
 */
class AuthController extends Controller
{
    use RendersSitePage;

    private const MAX_ATTEMPTS = 5;

    public function create()
    {
        return $this->page('frontend.portal.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => Phone::normalize($request->input('phone'))]);
        $request->validate([
            'phone' => ['required', 'regex:'.Phone::PATTERN],
            'password' => ['required', 'string'],
        ], ['phone.regex' => __('সঠিক মোবাইল নম্বর লিখুন (01XXXXXXXXX)।')]);

        $key = Str::lower($request->input('phone')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'phone' => __('অনেকবার চেষ্টা করা হয়েছে। :n সেকেন্ড পর আবার চেষ্টা করুন।', ['n' => RateLimiter::availableIn($key)]),
            ]);
        }

        if (! Auth::guard('web')->attempt($request->only('phone', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['phone' => __('মোবাইল নম্বর বা পাসওয়ার্ড সঠিক নয়।')]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        /** @var User $user */
        $user = Auth::guard('web')->user();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }
}
