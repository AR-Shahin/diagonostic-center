@props(["label", "name", "id" => null, "placeholder","value" => null, "col" => 30, "rows" => 2,"summernote" => false])

<div class="mb-3">
    <label for="{{ $id }}"><b>{{ $label }}</b> : </label>
    <textarea class="form-control
    @error($name)
        is-invalid
    @enderror
    @if ($summernote)
    summernote
    @endif
    " name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" cols="{{ $col }}" rows="{{ $rows }}">{{ old($name) ?? $value }}</textarea>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
