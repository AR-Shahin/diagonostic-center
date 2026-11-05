@extends("admin.layouts.master")

@section("title","Role")

@section("master_content")

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h3>Roles</h3>
                <hr>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tr class="text-center">
                            <th>SL</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>

                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    <a href="" class="btn btn-sm btn-success mx-1"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.roles.assign_permission',$role->id) }}" class="btn btn-sm btn-secondary mx-1"><i class="fa fa-bars"></i></a>
                                    @if ($role->name != "Super Admin")
                                        @php
                                            rowEditModal($role,route("admin.roles.store",$role->id))
                                        @endphp
                                    <x-form.submit-delete :route="route('admin.roles.delete',$role->id)"/>
                                  
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            @if (in_array("role-create",$permissions))
            <div class="col-md-4">
                <h3>Create Role</h3>
                <hr>
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <x-form.input label="Name" type="text" name="name" placeholder="Enter role name" id="name"/>

                    <button class="btn btn-sm btn-success w-100">Submit</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

@stop
