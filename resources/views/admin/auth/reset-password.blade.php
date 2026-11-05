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
               <form method="POST" action="{{ route('admin.password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for=""><b>Email</b></label>
            <input type="text" name="email" value="{{ old('email', $request->email) }}" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for=""><b>Password</b></label>
            <input type="password" name="password" class="form-control" >
            @error("password")
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for=""><b>Re Password</b></label>
            <input type="password" name="password_confirmation" class="form-control" >
        </div>
        <div class="mb-3">
            <x-form.submit/>
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
