@props(['name', 'label', 'checked' => null, 'default' => false, 'help' => null])
@php
    $id = 'f_'.$name;
    $isOn = (bool) old($name, $checked ?? $default);
@endphp
<div class="mb-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" id="{{ $id }}" value="1" data-default="{{ $default ? 1 : 0 }}" @checked($isOn)>
        <label class="form-check-label fw-semibold" for="{{ $id }}">{{ $label }}</label>
    </div>
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
