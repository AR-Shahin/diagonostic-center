@extends("admin.layouts.master")

@section("title","Dashboard")

@section("master_content")

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.backup") }}" method="POST" class="d-inline mx-2">
            @csrf
            <button class="btn btn-sm btn-success"><i class="fas fa-trash-restore-alt me-1"></i> Backup Site Data</button>
        </form>
        <form action="{{ route("admin.backup_db") }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-info"><i class="fas fa-trash-restore-alt me-1"></i> Database Backup Only</button>
        </form>
        <form action="{{ route("backup.download") }}" method="POST" class="d-inline">
            @csrf
            <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-primary"><i class="fas fa-trash-restore-alt me-1"></i> Database Backup Only</button>
        </form>
    </div>
</div>

@stop
