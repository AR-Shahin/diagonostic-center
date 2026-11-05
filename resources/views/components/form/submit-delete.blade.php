
@props(["route"])
<form class="d-inline" action="{{ $route }}" method="post">
    @csrf
     <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
 </form>
