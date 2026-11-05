@props(["label","name", "id" => null,"items","key" => null, "is_multiple" => false,"multi_placeholder" => ''])

<div class="mb-3">
    <label for="{{ $id }}"><b>{{ $label }} : </b></label>

    <select name="{{ $name }}" id="{{ $id }}" class="form-control select2   @error($name)
    is-invalid
@enderror"
    @if ($is_multiple)
    multiple="multiple" data-placeholder="{{ $multi_placeholder }}"
    @endif
    >
        <option value="">Select An Item</option>
        @foreach ($items as $item)
        <option value="{{ $item->id }}"
            @if(old($name) == $item->id )
            selected
            @endif
            >{{ $item->name ?? $item->$key }}</option>
        @endforeach
    </select>

@error($name)
    <span class="text-danger">{{ $message }}</span>
@enderror
</div>
