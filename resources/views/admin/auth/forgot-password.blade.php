@extends("admin.layouts.app")
@section("title", "Login")
@section("app_content")
<div class="login-page">
    <div class="login-box">
       <div class="login-logo">
          <a href="../../index2.html"><b>Admin</b>LTE</a>
       </div>
       <!-- /.login-logo -->
       <div class="card">
          <div class="card-body login-card-body">
             <p class="login-box-msg">Reset Your Password</p>
        @error("email")
             <span class="text-danger">{{ $message }}</span>
        @enderror
        @if (session("status"))
            <span class="text-success">{{ session("status") }}</span>
        @endif
             <form action="{{ route("admin.password.email") }}" method="post">
                @csrf
                <div class="input-group mb-3">
                   <input type="email" class="form-control" placeholder="Email" name="email">

                   <div class="input-group-text">
                         <span class="fas fa-envelope"></span>
                   </div>
                </div>

                <div class="row">

                   <!-- /.col -->
                   <div class="col-4">
                      <button type="submit" class="btn btn-primary w-100">Submit</button>
                   </div>
                   <!-- /.col -->
                </div>
             </form>

             <p class="mb-1">
                <a href="{{ route("admin.login") }}">Login</a>
             </p>

          </div>
          <!-- /.login-card-body -->
       </div>
    </div>
</div>
@stop
