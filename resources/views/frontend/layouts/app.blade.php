@php
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? $pageTitle.' | '.$site['name'] : $site['name'].' – '.$site['tagline'];
    $metaDescription = trim($__env->yieldContent('description')) ?: $site['description'];
    $canonical = url()->current();

    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'DiagnosticLab',
        'name' => $site['name'],
        'url' => url('/'),
        'logo' => $site['logo'],
        'telephone' => $site['phone_set'] ? $site['phone'] : null,
        'email' => $site['email'] !== 'info@example.com' ? $site['email'] : null,
        'address' => $site['address_set'] ? $site['address'] : null,
        'sameAs' => array_values(array_filter($site['social'])) ?: null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('js');</script>

    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large')">
    <meta name="theme-color" content="#1557A6">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'bn_BD' }}">
    <meta property="og:site_name" content="{{ $site['name'] }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($site['logo'])
        <meta property="og:image" content="{{ $site['logo'] }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    <link rel="icon" href="{{ $site['favicon'] ?? asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/site.css') }}">
    <style id="site-theme">{!! \App\Support\SiteTheme::css($site['theme'] ?? null) !!}</style>
    @stack('head')

    <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @stack('schema')
</head>
<body data-msg-form="{{ __('এটি ডেমো ফর্ম। অ্যাডমিন প্যানেলের সঙ্গে যুক্ত হলে কাজ করবে।') }}" data-msg-otp="{{ __('ডেমো: এখানে ওটিপি পাঠানো হবে।') }}">
    <a class="skip-link" href="#main">{{ __('মূল কনটেন্টে যান') }}</a>

    @include('frontend.partials.topbar')
    @include('frontend.partials.navbar')

    <main id="main">
        @yield('content')
    </main>

    @include('frontend.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="{{ asset('frontend/js/site.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
