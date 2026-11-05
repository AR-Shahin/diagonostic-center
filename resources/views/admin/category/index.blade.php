@extends("admin.layouts.master")

@section("title","Category")

@push(
    "css"
)
<x-utility.datatable-css/>
@endpush

@section("master_content")

<div class="row">
    <div class="col-sm-12 col-md-8">
        <div class="card">
            <div class="card-body">
                <h3>Manage Category</h3>
                <hr>
                <div class="table-responsive text-center">
                    <table class="table table-sm table-bordered data-table">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="col-sm-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <h3>Create Category</h3>
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <x-form.input label="Name" type="text" name="name" placeholder="Enter name" id="name"/>
                    <x-form.submit/>
                </form>
            </div>
        </div>
    </div>
</div>
@stop


@push("script")

<x-utility.datatable-js/>

<script>

initalizeDatatable("{{ route('admin.categories.index') }}",[
            { data: 'DT_RowIndex', 'orderable': false, 'searchable': false },
            {data: 'name', name: 'name'},
            {data: 'status', name: 'status'},
            {data: 'actions', name: 'actions'},
        ]);
</script>
@endpush
