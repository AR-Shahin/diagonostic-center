@php
    $nav = [
        ['site.home', 'হোম'],
        ['site.about', 'আমাদের সম্পর্কে'],
        ['site.tests', 'ডায়াগনস্টিক টেস্ট'],
        ['site.packages', 'হেলথ প্যাকেজ'],
        ['site.doctors', 'আমাদের ডাক্তার'],
        ['site.services', 'আমাদের সেবা'],
        ['site.contact', 'যোগাযোগ'],
    ];
@endphp
<header class="site-header" id="siteHeader">
    <nav class="navbar navbar-expand-xl" aria-label="প্রধান মেনু">
        <div class="container">
            <a class="brand" href="{{ route('site.home') }}" aria-label="{{ $site['name'] }} – হোম পেজ">
                @if ($site['logo'])
                    <img src="{{ $site['logo'] }}" alt="" height="40" width="40" class="brand-logo">
                @else
                    <span class="brand-mark" aria-hidden="true"><i class="bi bi-plus-lg"></i></span>
                @endif
                <span class="brand-text">{{ $site['name'] }}</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="মেনু খুলুন">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    @foreach ($nav as [$route, $label])
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="nav-cta">
                    <a class="btn btn-ghost" href="{{ route('site.report') }}"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> রিপোর্ট ডাউনলোড</a>
                    <a class="btn btn-primary" href="{{ route('site.appointment') }}"><i class="bi bi-calendar2-check" aria-hidden="true"></i> অ্যাপয়েন্টমেন্ট নিন</a>
                </div>
            </div>
        </div>
    </nav>
</header>
