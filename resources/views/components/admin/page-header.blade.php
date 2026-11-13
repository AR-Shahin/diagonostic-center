@props(['title', 'subtitle' => null, 'icon' => null])
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0">@if ($icon)<i class="{{ $icon }} text-primary me-2"></i>@endif{{ $title }}</h3>
        @if ($subtitle)<small class="text-muted">{{ $subtitle }}</small>@endif
    </div>
    <div class="d-flex gap-2">{{ $slot }}</div>
</div>
