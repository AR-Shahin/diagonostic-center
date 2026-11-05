@props(["label", "type", "name", "id" => null, "placeholder","value" => null])

<div class="mb-3">
    <label for="{{ $id }}"><b>{{ $label }}</b> : </label>
    <input type="{{ $type }}" class="form-control
    @error($name)
        is-invalid
    @enderror
    " name="{{ $name }}" value="{{ old($name) ?? $value }}" id="{{ $id }}" placeholder="{{ $placeholder }}">
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

