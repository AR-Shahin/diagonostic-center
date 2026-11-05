@extends("admin.layouts.master")

@section("title","Admin")
@push(
    "css"
)
<x-utility.datatable-css/>
@endpush
@section("master_content")


<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <div><h3>Manage Admins</h3></div>
                    <div>
                        @if (in_array("admin-create",$permissions))
                        <a href="{{ route("admin.admins.create") }}" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Create</a>
                        @endif
                    </div>
                </div>

                <hr>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered data-table">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop


@push("script")

<x-utility.datatable-js/>

<script>

      $(function () {
    var table = $('.data-table').DataTable({
        "processing": true,
        "retrieve": true,
        "serverSide": true,
        'paginate': true,
        'searchDelay': 700,
        "bDeferRender": true,
        "responsive": true,
        "autoWidth": true,
        "order": [ [0, 'desc'] ],
        ajax: "{{ route('admin.admins.data_table') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'role', name: 'role'},
            {data: 'actions', name: 'actions'},
        ],
    });
  });
</script>
@endpush
