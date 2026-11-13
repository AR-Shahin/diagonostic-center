<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public-site language: Bangla by default, English when the visitor picks it.
 * "?lang=en" / "?lang=bn" stores the choice in the session, then redirects to the clean URL.
 */
class SetLocale
{
    public const SUPPORTED = ['bn', 'en'];

    public const DEFAULT = 'bn';

    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');

        if (in_array($requested, self::SUPPORTED, true)) {
            $request->session()->put('site_locale', $requested);

            return redirect($request->fullUrlWithoutQuery('lang'));
        }

        app()->setLocale($request->session()->get('site_locale', self::DEFAULT));

        return $next($request);
    }
}
