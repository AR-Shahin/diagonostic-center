@props(['name', 'label', 'type' => 'text', 'value' => null, 'default' => null, 'required' => false, 'help' => null])
@php($id = 'f_'.str_replace(['[', ']'], '_', $name))
<div class="mb-3">
    <label class="form-label fw-semibold" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value ?? $default) }}" data-default="{{ $default }}"
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @required($required)>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
