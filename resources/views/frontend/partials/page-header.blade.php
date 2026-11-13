{{-- Props: $title, $lead, $crumb (current page name), $eyebrow (optional) --}}
@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('site.home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $crumb, 'item' => url()->current()],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

<section class="page-hero" aria-labelledby="page-title">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
        <nav aria-label="breadcrumb" class="reveal">
            <ol class="crumbs">
                <li><a href="{{ route('site.home') }}">হোম</a></li>
                <li aria-current="page">{{ $crumb }}</li>
            </ol>
        </nav>
        @isset($eyebrow)
            <span class="eyebrow reveal">{{ $eyebrow }}</span>
        @endisset
        <h1 class="page-title reveal" id="page-title" style="--d:.05s">{!! $title !!}</h1>
        <p class="page-lead reveal" style="--d:.1s">{{ $lead }}</p>
    </div>
</section>
