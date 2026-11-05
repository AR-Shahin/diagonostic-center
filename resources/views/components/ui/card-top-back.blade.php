@props(["heading","permission" => "admin-create","route" =>null,"permissions"])

<div class="d-flex justify-content-between">
    <div><h3>{{ $heading }}</h3></div>
    <div>
        @if (in_array($permission,$permissions))
        @if ($route)
        <a href="{{ $route }}" class="btn btn-sm btn-success"><i class="fa fa-arrow-left"></i> Back</a>
        @endif
        @endif
    </div>
</div>
