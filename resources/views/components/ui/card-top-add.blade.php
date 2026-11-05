@props(["heading","permission" => "admin-create","route","permissions", "is_multiple" =>false, "multiple_buttons" => []])

<div class="d-eflex row justify-content-between ">
    <div><h3>{{ $heading }}</h3></div>
    <div>
        @if ($is_multiple)
            @foreach ($multiple_buttons as $btn)
                @if (in_array($btn["permission"],$permissions))
                <a href="{{ $btn["route"] }}" class="btn btn-sm btn-{{ $btn["btn"] }}"><i class="fa {{ $btn["icon"] }}"></i> {{ $btn["text"] }}</a>
                @endif
            @endforeach
        @endif
        @if (in_array($permission,$permissions))
        <a href="{{ $route }}" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Create</a>
        @endif
    </div>
</div>
