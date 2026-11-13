@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false, 'help' => null])
@php($id = 'f_'.str_replace(['[', ']'], '_', $name))
<div class="mb-3">
    <label class="form-label fw-semibold" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @required($required)>{{ old($name, $value) }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
