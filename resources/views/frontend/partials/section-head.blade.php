{{--
    Props: $id (heading id), $eyebrow, $title (may contain <span class="grad">), $sub (optional),
           $align = 'center' | 'start', $light = false (for dark backgrounds)
--}}
<div class="section-head {{ ($align ?? 'center') === 'center' ? 'is-center' : '' }} {{ ($light ?? false) ? 'is-light' : '' }} reveal">
    <span class="eyebrow">{{ $eyebrow }}</span>
    <h2 class="section-title" id="{{ $id }}">{!! $title !!}</h2>
    @isset($sub)
        <p class="section-sub">{{ $sub }}</p>
    @endisset
</div>
