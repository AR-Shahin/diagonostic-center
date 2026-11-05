@extends("admin.layouts.master")

@section("title","Admin Create")
@push(
    "css"
)

@endpush
@section("master_content")
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <div><h3>Admin Edit</h3></div>
                    <div>
                        @if (in_array("admin-create",$permissions))
                        <a href="{{ route("admin.admins.index") }}" class="btn btn-sm btn-success"><i class="fa fa-angle-left"></i> Back</a>
                        @endif
                    </div>
                </div>
                <hr>

                <form action="{{ route("admin.admins.update",$admin->id) }}" method="POST" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <x-form.input label="Name" type="text" name="name" placeholder="Enter name" id="name" :value="$admin->name"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input label="Email" type="email" name="email" placeholder="Enter Email" id="email" :value="$admin->email"/>
                        </div>


                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for=""><b>Role : </b></label>
                                <select name="role_id" id="" class="form-control select2">
                                    <option value="">Select A Role</option>
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        @if($admin->roles[0]->id == $role->id)
                                        selected
                                        @endif
                                        >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            @error("role_id")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            </div>
                        </div>
                    </div>
                    <x-form.submit/>

                </form>
            </div>
        </div>
    </div>
</div>

@stop


@push("script")


@endpush
