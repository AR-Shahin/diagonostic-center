 <!-- Fonts -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
 <!-- Font Awesome Icons -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
 <!-- OverlayScrollbars -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" />

  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

 <!-- Theme style (AdminLTE v4) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/adminlte4@4.0.0-rc.7.20260519/dist/css/adminlte.min.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<style>
    /* Hide user panel info when sidebar is collapsed */
    body.sidebar-collapse .sidebar-user-panel {
        display: none !important;
    }

    /* Global Mobile Responsive Styles */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
        }

        .app-header .navbar-nav {
            gap: 0.25rem;
        }

        .app-header .nav-link {
            padding: 0.5rem 0.25rem !important;
        }

        .btn-group .btn {
            font-size: 0.75rem;
            padding: 0.4rem 0.6rem;
        }

        .card {
            margin-bottom: 1rem;
        }

        .table {
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .app-content {
            padding: 0.5rem !important;
        }

        .card-body {
            padding: 1rem 0.75rem;
        }

        h1, h2, h3 {
            font-size: 1.25rem;
        }

        .d-flex {
            flex-direction: column;
            gap: 0.5rem;
        }

        .d-flex.justify-content-between {
            gap: 0;
        }

        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }

    /* Eye-catching UI Improvements */
    .card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        border-radius: 0.5rem;
    }

    .card:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-4px);
    }

    .btn {
        transition: all 0.3s ease;
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5568d3 0%, #6a3e8f 100%);
    }

    .badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .nav-link {
        transition: all 0.3s ease;
    }

    /* Sidebar Custom Background */
    .app-sidebar {
        background-color: #353b48 !important;
    }

    /* Simple Eye-Catching Sidebar Hover & Active Effect */
    .app-sidebar .nav-link {
        transition: all 0.2s ease-in-out;
        border-radius: 0.375rem;
        margin-bottom: 0.2rem;
    }

    .app-sidebar .nav-link:hover,
    .app-sidebar .nav-link.active {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.08);
        box-shadow: inset 4px 0 0 var(--bs-primary);
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }
</style>
@stack("css")
