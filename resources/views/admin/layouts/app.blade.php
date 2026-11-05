<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Admin | @yield("title")</title>
      @include("admin.includes.css")
   </head>
   <body class="login-page bg-body-secondary">
    @yield("app_content")
      <!-- REQUIRED SCRIPTS -->
      @include("admin.includes.script")
   </body>
</html>
