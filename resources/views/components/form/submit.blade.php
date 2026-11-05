@props(["text" => "Submit" ,"is_block" => null])
<div class="mb-3">
    <button type="submit" class="btn btn-sm btn-success
    @if($is_block)
        w-100
    @endif
    ">{{ $text }}</button>
</div>
