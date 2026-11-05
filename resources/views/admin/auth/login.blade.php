@extends("admin.layouts.app")
@section("title", "Login")
@section("app_content")
    <div class="login-box">
       <div class="login-logo">
          <a href="../../index2.html">{{$website->name}}</a>
       </div>
       <!-- /.login-logo -->
       <div class="card">
          <div class="card-body login-card-body">
             <p class="login-box-msg">Sign in to start your session</p>
             @error("email")
             <span class="text-danger">{{ $message }}</span>
          @enderror
          @if (session("status"))
          <span class="text-success">{{ session("status") }}</span>
      @endif
             <form action="{{ route("admin.authenticate") }}" method="post">
                @csrf
                <div class="input-group mb-3">
                   <input type="email" class="form-control" placeholder="Email" name="email" value="admin@mail.com">

                   <div class="input-group-text">
                         <span class="fas fa-envelope"></span>
                   </div>
                </div>
                <div class="input-group mb-3">
                   <input type="password" class="form-control" placeholder="Password" name="password" value="password">
                   <div class="input-group-text">
                         <span class="fas fa-lock"></span>
                   </div>
                </div>
                <div class="row">
                   <div class="col-8">
                      <div class="form-check">
                         <input type="checkbox" id="remember" class="form-check-input" value="1" name="remember">
                         <label for="remember" class="form-check-label">
                         Remember Me
                         </label>
                      </div>
                   </div>
                   <!-- /.col -->
                   <div class="col-4">
                      <button type="submit" class="btn btn-primary w-100">Sign In</button>
                   </div>
                   <!-- /.col -->
                </div>
             </form>

             <!-- /.social-auth-links -->
             <p class="mb-1">
                <a href="{{ route("admin.password.request") }}">I forgot my password</a>
             </p>

          </div>
          <!-- /.login-card-body -->
       </div>
    </div>
@stop
