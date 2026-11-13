<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Patients start with the phone number as password. When the rule is on,
 * they must choose their own password before seeing reports and bills.
 */
class EnsurePortalPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if ($user?->must_change_password && (bool) Setting::get('portal_force_password_change', 1)) {
            return redirect()->route('portal.password.edit')
                ->with('portal_status', __('নিরাপত্তার জন্য প্রথমে নিজের পাসওয়ার্ড সেট করুন।'));
        }

        return $next($request);
    }
}
