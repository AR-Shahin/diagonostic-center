
<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | @yield("title")</title>

 @include("admin.includes.css")

</head>

<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
<div class="app-wrapper">

  <!-- Navbar -->
@include("admin.includes.navbar")
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
@include("admin.includes.sidebar")
  <!-- Content Wrapper. Contains page content -->
  <main class="app-main">

    <!-- Main content -->
    <div class="app-content pt-3">
      <div class="container-fluid">
        @yield("master_content")
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </main>
  <!-- /.content-wrapper -->



  <!-- Main Footer -->
  @include("admin.includes.footer")
</div>
<!-- ./app-wrapper -->

<!-- REQUIRED SCRIPTS -->

@include("admin.includes.script")
</body>
</html>
