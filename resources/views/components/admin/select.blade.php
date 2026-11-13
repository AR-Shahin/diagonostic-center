@props(['name', 'label', 'options' => [], 'value' => null, 'default' => null, 'placeholder' => null, 'required' => false, 'help' => null, 'multiple' => false])
@php
    $id = 'f_'.str_replace(['[', ']'], '_', $name);
    $key = rtrim($name, '[]');
    $current = old($key, $value ?? $default);
    $selected = collect(is_iterable($current) ? $current : [$current])->map(fn ($v) => (string) $v)->all();
@endphp
<div class="mb-3">
    <label class="form-label fw-semibold" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
    <select name="{{ $name }}" id="{{ $id }}" data-default="{{ $default }}" @if ($multiple) multiple @endif
        {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($key)]) }} @required($required)>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected(in_array((string) $optValue, $selected, true))>{{ $optLabel }}</option>
        @endforeach
    </select>
    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
