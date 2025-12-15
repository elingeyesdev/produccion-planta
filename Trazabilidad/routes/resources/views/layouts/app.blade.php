<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Planta</title>
        <!-- AdminLTE 3 CSS with Bootstrap Icons and FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

        <!-- Custom CSS for Active Menu States and Arrow Rotation -->
        <style>
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #007bff !important;
            color: #fff !important;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link {
            color: #c2c7d0;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        /* Ensure arrow rotation works correctly */
        .nav-sidebar .nav-item.has-treeview > .nav-link > .right {
            transition: transform 0.3s ease-in-out;
        }

        .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link > .right {
            transform: rotate(-90deg);
        }

        /* Sidebar scroll and height - Solo un scroll */
        .main-sidebar {
            height: 100vh !important;
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
            width: 250px !important;
        }
        
        /* En desktop, el sidebar funciona normalmente con AdminLTE */
        @media (min-width: 769px) {
            /* Sidebar en desktop - posición fija a la izquierda */
            .main-sidebar {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                bottom: 0 !important;
                transform: none !important;
                margin-left: 0 !important;
                z-index: 1038 !important;
                float: none !important;
            }
            
            /* En desktop, el contenido SÍ debe tener margen del sidebar */
            body.layout-fixed.sidebar-expanded .content-wrapper,
            body.layout-fixed .content-wrapper,
            .layout-fixed.sidebar-expanded .content-wrapper,
            .layout-fixed .content-wrapper {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-header,
            body.layout-fixed .main-header,
            .layout-fixed.sidebar-expanded .main-header,
            .layout-fixed .main-header {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-footer,
            body.layout-fixed .main-footer,
            .layout-fixed.sidebar-expanded .main-footer,
            .layout-fixed .main-footer {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            /* Ocultar botón hamburguesa en desktop */
            .mobile-sidebar-toggle {
                display: none !important;
            }
            
            .mobile-sidebar-overlay {
                display: none !important;
            }
        }

        .main-sidebar > .brand-link,
        .main-sidebar > .user-panel {
            flex-shrink: 0;
        }

        .sidebar {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            height: auto !important;
            display: flex;
            flex-direction: column;
        }

        .sidebar-footer {
            flex-shrink: 0;
            margin-top: auto;
        }

        /* Ajustar el contenido cuando el sidebar está expandido - Solo en desktop */
        @media (min-width: 769px) {
            /* En desktop, permitir que AdminLTE maneje los márgenes normalmente */
            .sidebar-expanded .content-wrapper,
            .sidebar-expanded .main-footer,
            .sidebar-expanded .main-header {
                margin-left: 250px !important;
            }
        }
        
        /* En móvil, forzar que no haya margen izquierdo - Sobrescribir AdminLTE */
        @media (max-width: 768px) {
            /* Sobrescribir todos los estilos de AdminLTE que agregan margen */
            .sidebar-expanded .content-wrapper,
            .sidebar-expanded .main-footer,
            .sidebar-expanded .main-header,
            body.sidebar-expanded .content-wrapper,
            body.sidebar-expanded .main-footer,
            body.sidebar-expanded .main-header {
                margin-left: 0 !important;
                left: 0 !important;
            }
            
            /* SOLO EN MÓVIL: Asegurar que layout-fixed no cause problemas */
            /* Estos estilos solo se aplican dentro del media query de móvil */
            
            /* Sobrescribir estilos de AdminLTE para sidebar en móvil */
            .main-sidebar::before {
                margin-left: -250px !important;
            }
            
            .main-sidebar.mobile-open::before {
                margin-left: 0 !important;
            }
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #343a40;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #6c757d;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #5a6268;
        }

        /* Asegurar que el menú tenga espacio al final */
        .nav-sidebar {
            padding-bottom: 20px;
            min-height: 100%;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            /* Sidebar responsive - Oculto por defecto, aparece como overlay */
            .main-sidebar {
                width: 250px !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 1050 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease-in-out !important;
                box-shadow: 2px 0 10px rgba(0,0,0,0.2) !important;
                margin-left: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                /* Asegurar que el sidebar no afecte el layout del contenido */
                will-change: transform;
            }
            
            .main-sidebar.mobile-open {
                transform: translateX(0) !important;
            }
            
            /* IMPORTANTE: El contenido NUNCA debe moverse cuando el sidebar se abre */
            /* El sidebar es overlay, no desplaza el contenido */
            .main-sidebar.mobile-open ~ .content-wrapper,
            .main-sidebar.mobile-open ~ .main-footer,
            .main-sidebar.mobile-open ~ .main-header,
            body:has(.main-sidebar.mobile-open) .content-wrapper,
            body:has(.main-sidebar.mobile-open) .main-footer,
            body:has(.main-sidebar.mobile-open) .main-header {
                margin-left: 0 !important;
                transform: none !important;
                left: 0 !important;
            }
            
            /* Asegurar que el wrapper no tenga restricciones */
            .wrapper {
                margin-left: 0 !important;
                overflow-x: hidden;
            }
            
            /* Asegurar que el sidebar tenga scroll interno */
            .main-sidebar .sidebar {
                overflow-y: auto !important;
                overflow-x: hidden !important;
            }
            
            /* Asegurar que el contenido no tenga margen del sidebar */
            /* Asegurar que el contenido no tenga margen del sidebar en móvil */
            /* Sobrescribir TODOS los estilos posibles de AdminLTE */
            .content-wrapper,
            .main-footer,
            .main-header,
            body.layout-fixed .content-wrapper,
            body.layout-fixed.sidebar-expanded .content-wrapper,
            body.sidebar-expanded .content-wrapper,
            body.layout-fixed .main-footer,
            body.layout-fixed.sidebar-expanded .main-footer,
            body.sidebar-expanded .main-footer,
            body.layout-fixed .main-header,
            body.layout-fixed.sidebar-expanded .main-header,
            body.sidebar-expanded .main-header {
                margin-left: 0 !important;
                width: 100% !important;
                left: 0 !important;
                position: relative !important;
                transform: none !important;
            }
            
            /* Asegurar que el contenido no se desborde */
            .content-wrapper {
                overflow-x: hidden;
                position: relative;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            /* Asegurar que el wrapper no tenga restricciones */
            .wrapper {
                margin-left: 0 !important;
                overflow-x: hidden;
            }
            
            /* Forzar que el contenido esté a la izquierda */
            .content {
                margin-left: 0 !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            
            /* Container fluid sin padding extra */
            .container-fluid {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            /* Content header sin margen */
            .content-header {
                margin-left: 0 !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            /* Asegurar que el wrapper no tenga restricciones */
            .wrapper {
                overflow-x: hidden;
            }
            
            /* Tablas responsive */
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
                white-space: nowrap;
            }
            
            /* Cards responsive */
            .card {
                margin-bottom: 1rem;
            }
            
            .card-header {
                padding: 0.75rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            /* Estadísticas responsive */
            .small-box {
                margin-bottom: 1rem;
                min-height: 100px;
            }
            
            .small-box .inner {
                padding: 15px 10px;
            }
            
            .small-box .inner h3 {
                font-size: 1.75rem;
                margin: 0;
                line-height: 1.2;
            }
            
            .small-box .inner p {
                font-size: 0.9rem;
                margin: 8px 0 0 0;
                white-space: normal;
                word-wrap: break-word;
                line-height: 1.3;
                text-align: left;
            }
            
            .small-box .icon {
                font-size: 2.5rem;
                opacity: 0.3;
            }
            
            /* Asegurar que las estadísticas se muestren en 2 columnas en móvil */
            .row .col-6 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            /* Botones responsive */
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
        /* Paginación compacta estilo AdminLTE */
        .pagination {
            margin: 0;
            padding: 0;
        }
        
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .card-footer.clearfix {
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border-top: 1px solid rgba(0,0,0,.125);
        }
        
        .card-footer .float-left {
            padding-top: 0.5rem;
        }
        
        /* Modales responsive */
        .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .modal-content {
                border-radius: 0.25rem;
            }
            
            /* Formularios responsive */
            .form-group {
                margin-bottom: 1rem;
            }
            
            /* Filtros responsive */
            .row.mb-3 > div {
                margin-bottom: 0.5rem;
            }
            
            /* Navbar responsive */
            .main-header {
                padding-left: 50px !important;
            }
            
            .navbar-nav {
                flex-direction: row;
            }
            
            .navbar-nav .nav-item {
                margin-left: 0.5rem;
            }
            
            /* Card header responsive */
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .card-header .card-tools {
                margin-top: 0.5rem;
                width: 100%;
            }
            
            .card-header .card-tools .btn {
                width: 100%;
                margin-bottom: 0.25rem;
            }
            
            /* Content header responsive */
            .content-header {
                padding: 0.75rem 0.5rem !important;
            }
            
            .content-header h1 {
                font-size: 1.25rem;
                margin: 0;
            }
            
            /* Ocultar elementos no esenciales en móvil */
            .d-none-mobile {
                display: none !important;
            }
            
            /* Texto responsive */
            .text-sm-mobile {
                font-size: 0.875rem;
            }
            
            /* Card responsive mejorado */
            .card {
                border-radius: 0.25rem;
                margin-bottom: 1rem;
            }
            
            .card-header {
                display: flex;
                flex-direction: column;
                align-items: flex-start !important;
                padding: 0.75rem;
            }
            
            .card-header .card-title {
                margin-bottom: 0.5rem;
                font-size: 1rem;
            }
            
            .card-header .card-tools {
                width: 100%;
                margin-top: 0.5rem;
            }
            
            .card-header .card-tools .btn {
                width: 100%;
                margin-bottom: 0.25rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            /* Row responsive */
            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .row > [class*="col-"] {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Ajustes para pantallas muy pequeñas */
            .table th,
            .table td {
                padding: 0.375rem;
                font-size: 0.75rem;
            }
            
            .btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .card-header h3,
            .card-header .card-title {
                font-size: 0.95rem;
            }
            
            .small-box {
                min-height: 90px;
            }
            
            .small-box .inner {
                padding: 12px 8px;
            }
            
            .small-box .inner h3 {
                font-size: 1.5rem;
            }
            
            .small-box .inner p {
                font-size: 0.8rem;
                margin-top: 5px;
            }
            
            .small-box .icon {
                font-size: 2rem;
            }
            
            .modal-dialog {
                margin: 0.25rem;
                max-width: calc(100% - 0.5rem);
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            /* Estadísticas en una sola columna en pantallas muy pequeñas si es necesario */
            .row .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        /* Botón para abrir sidebar en móvil */
        .mobile-sidebar-toggle {
            display: none;
        }
        
        .mobile-sidebar-overlay {
            display: none;
        }
        
        @media (max-width: 768px) {
            /* Botón hamburguesa */
            .mobile-sidebar-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 1051;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                width: 42px;
                height: 42px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                cursor: pointer;
                font-size: 18px;
                transition: background 0.2s;
            }
            
            .mobile-sidebar-toggle:hover,
            .mobile-sidebar-toggle:focus {
                background: #0056b3;
                outline: none;
            }
            
            .mobile-sidebar-toggle:active {
                background: #004085;
            }
            
            /* Overlay para cerrar sidebar */
            .mobile-sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
                transition: opacity 0.3s;
            }
            
            .mobile-sidebar-overlay.active {
                display: block;
            }
            
            /* Asegurar que el contenido principal esté visible */
            .content-wrapper {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            .content {
                padding: 0.5rem !important;
            }
            
            /* Container fluid responsive */
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            /* Asegurar que el header tenga espacio para el botón */
            .main-header {
                padding-left: 60px !important;
            }
        }

        /* Estilos adicionales para sobrescribir AdminLTE en móvil - AL FINAL para máxima prioridad */
        @media (max-width: 768px) {
            /* SOLO EN MÓVIL: Forzar que el contenido esté siempre a la izquierda */
            body.layout-fixed.sidebar-expanded .content-wrapper,
            body.layout-fixed .content-wrapper,
            .layout-fixed.sidebar-expanded .content-wrapper,
            .layout-fixed .content-wrapper,
            body.sidebar-expanded.layout-fixed .content-wrapper,
            body.layout-fixed.sidebar-expanded .wrapper .content-wrapper {
                margin-left: 0 !important;
                padding-left: 0 !important;
                left: 0 !important;
                width: 100% !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-header,
            body.layout-fixed .main-header,
            .layout-fixed.sidebar-expanded .main-header,
            .layout-fixed .main-header,
            body.sidebar-expanded.layout-fixed .main-header,
            body.layout-fixed.sidebar-expanded .wrapper .main-header {
                margin-left: 0 !important;
                padding-left: 60px !important; /* Espacio para el botón hamburguesa */
                left: 0 !important;
                width: 100% !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-footer,
            body.layout-fixed .main-footer,
            .layout-fixed.sidebar-expanded .main-footer,
            .layout-fixed .main-footer,
            body.sidebar-expanded.layout-fixed .main-footer,
            body.layout-fixed.sidebar-expanded .wrapper .main-footer {
                margin-left: 0 !important;
                left: 0 !important;
                width: 100% !important;
            }
            
            /* Asegurar que el wrapper no tenga margen en móvil */
            body.layout-fixed.sidebar-expanded .wrapper,
            body.layout-fixed .wrapper {
                margin-left: 0 !important;
            }
        }
        
        /* En desktop, asegurar que AdminLTE funcione normalmente - AL FINAL para máxima prioridad */
        @media (min-width: 769px) {
            /* Permitir que AdminLTE maneje los márgenes normalmente en desktop */
            /* Usar selectores muy específicos para asegurar que se apliquen */
            body.layout-fixed.sidebar-expanded .content-wrapper,
            body.layout-fixed .content-wrapper,
            .layout-fixed.sidebar-expanded .content-wrapper,
            .layout-fixed .content-wrapper,
            body.sidebar-expanded.layout-fixed .content-wrapper {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-header,
            body.layout-fixed .main-header,
            .layout-fixed.sidebar-expanded .main-header,
            .layout-fixed .main-header,
            body.sidebar-expanded.layout-fixed .main-header {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            body.layout-fixed.sidebar-expanded .main-footer,
            body.layout-fixed .main-footer,
            .layout-fixed.sidebar-expanded .main-footer,
            .layout-fixed .main-footer,
            body.sidebar-expanded.layout-fixed .main-footer {
                margin-left: 250px !important;
                left: auto !important;
            }
            
            /* Asegurar que el sidebar esté correctamente posicionado */
            .main-sidebar {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                bottom: 0 !important;
                transform: none !important;
                margin-left: 0 !important;
            }
        }
        </style>
        @stack('css')
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hold-transition sidebar-expanded layout-fixed" id="appBody">
        <div class="wrapper">
            <!-- Mobile Sidebar Toggle -->
            <button class="mobile-sidebar-toggle" onclick="toggleMobileSidebar()" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
            <div class="mobile-sidebar-overlay" onclick="toggleMobileSidebar()"></div>
            
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <!-- Navbar Search -->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="fas fa-search"></i>
                        </a>
                        <div class="navbar-search-block">
                            <form class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>

                    <!-- Messages Dropdown Menu -->

                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-envelope mr-2"></i> 4 new messages
                                <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-users mr-2"></i> 8 friend requests
                                <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-file mr-2"></i> 3 new reports
                                <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Planta</span>
    </a>
    
    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="align-items: flex-start;">
        <div class="image" style="flex-shrink: 0;">
            <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px; object-fit: cover;">
        </div>
        <div class="info" style="flex: 1; min-width: 0; margin-left: 8px; padding-top: 2px;">
            @auth
                @php
                    $user = Auth::user();
                    $nombre = $user->nombre ?? '';
                    $apellido = $user->apellido ?? '';
                    $nombreCompleto = trim($nombre . ' ' . $apellido);
                    $usuario = $user->usuario ?? $user->email ?? '';
                @endphp
                <a href="#" class="d-block" style="color: #c2c7d0; text-decoration: none; line-height: 1.3;">
                    @if($nombreCompleto)
                        <div style="font-weight: 600; font-size: 14px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;">
                            {{ $nombreCompleto }}
                        </div>
                        @if($usuario)
                            <div style="font-size: 12px; color: #c2c7d0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $usuario }}
                            </div>
                        @endif
                    @else
                        <div style="font-weight: 600; font-size: 14px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $usuario ?: 'Usuario' }}
                        </div>
                    @endif
                </a>
            @else
                <a href="#" class="d-block" style="color: #c2c7d0; text-decoration: none; line-height: 1.3;">
                    <div style="font-weight: 600; font-size: 14px; color: #fff;">Usuario</div>
                </a>
            @endauth
        </div>
    </div>
    
    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- PANELES DE CONTROL -->
                @can('ver panel control')
                <li class="nav-header">Paneles de Control</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Panel de Control</p>
                    </a>
                </li>
                @endcan
                @can('ver panel cliente')
                @if(!Auth::user()->can('ver panel control'))
                <li class="nav-header">Paneles de Control</li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('dashboard-cliente') }}" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Panel del Cliente</p>
                    </a>
                </li>
                @endcan
                <!-- PEDIDOS -->
                @canany(['crear pedidos', 'ver mis pedidos', 'gestionar pedidos'])
                <li class="nav-header">Pedidos</li>
                @can('ver mis pedidos')
                <li class="nav-item">
                    <a href="{{ route('mis-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Mis Pedidos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar pedidos')
                <li class="nav-item">
                    <a href="{{ route('gestion-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>Gestión de Pedidos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar pedidos')
                <li class="nav-item">
                    <a href="{{ route('rutas-tiempo-real') }}" class="nav-link">
                        <i class="nav-icon fas fa-route"></i>
                        <p>Seguimiento de Pedidos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('documentacion-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Documentación de Pedidos</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- MATERIA PRIMA -->
                @canany(['ver materia prima', 'solicitar materia prima', 'recepcionar materia prima', 'gestionar proveedores'])
                <li class="nav-header">Materia Prima</li>
                @can('ver materia prima')
                <li class="nav-item">
                    <a href="{{ route('materia-prima-base') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Materias Prima Base</p>
                    </a>
                </li>
                @endcan
                @can('solicitar materia prima')
                <li class="nav-item">
                    <a href="{{ route('solicitar-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Solicitar Materias Prima</p>
                    </a>
                </li>
                @endcan
                @can('recepcionar materia prima')
                <li class="nav-item">
                    <a href="{{ route('recepcion-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Recepcion Materias Prima</p>
                    </a>
                </li>
                @endcan
                @can('gestionar proveedores')
                <li class="nav-item">
                    <a href="{{ route('proveedores.web.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Proveedores</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- LOTES -->
                @can('gestionar lotes')
                <li class="nav-header">Lotes</li>
                <li class="nav-item">
                    <a href="{{ route('gestion-lotes') }}" class="nav-link">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Lotes</p>
                    </a>
                </li>
                @endcan
                <!-- PROCESOS -->
                @canany(['gestionar maquinas', 'gestionar procesos', 'gestionar variables estandar'])
                <li class="nav-header">Procesos</li>
                @can('gestionar maquinas')
                <li class="nav-item has-treeview">
                    <a href="{{ route('maquinas.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-layer-group"></i>
                        Maquinas
                    </a>
                </li>
                @endcan
                @can('gestionar procesos')
                <li class="nav-item">
                    <a href="{{ route('procesos.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Procesos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar variables estandar')
                <li class="nav-item">
                    <a href="{{ route('variables-estandar') }}" class="nav-link">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p>Variables Estandar</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- CERTIFICACIONES -->
                @canany(['certificar lotes', 'ver certificados'])
                <li class="nav-header">Certificaciones</li>
                @can('certificar lotes')
                <li class="nav-item">
                    <a href="{{ route('certificar-lote') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>Certificar Lote</p>
                    </a>
                </li>
                @endcan
                @can('ver certificados')
                <li class="nav-item">
                    <a href="{{ route('certificados') }}" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Certificados</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- ALMACENES -->
                @can('almacenar lotes')
                <li class="nav-header">Almacenes</li>
                <li class="nav-item">
                    <a href="{{ route('almacenaje') }}" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Almacenar lotes</p>
                    </a>
                </li>
                @endcan
                <!-- ADMINISTRACIÓN -->
                @can('gestionar usuarios')
                <li class="nav-header">Administración</li>
                <li class="nav-item">
                    <a href="{{ route('planta-ubicacion') }}" class="nav-link">
                        <i class="nav-icon fas fa-map-marker-alt"></i>
                        <p>Mi Ubicación</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('usuarios') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Usuarios</p>
                    </a>
                </li>
                @endcan
                
                <!-- SOPORTE -->
                <li class="nav-header">SOPORTE</li>
                <li class="nav-item">
                    <a href="{{ route('helpdesk') }}" class="nav-link">
                        <i class="nav-icon fas fa-headset"></i>
                        <p>Centro de Soporte</p>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- CERRAR SESIÓN - Siempre visible al final del sidebar -->
        <div class="sidebar-footer" style="padding: 10px; border-top: 1px solid rgba(255,255,255,.1);">
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button type="submit" class="btn btn-block btn-danger btn-sm" style="cursor: pointer;">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0 text-sm">@yield('page_title')</h1>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </section>
            </div>

            <!-- Footer -->
            <footer class="main-footer">
                <strong>Copyright &copy; {{ date('Y') }} <a href="#">Planta</a>.</strong>
                Todos los derechos reservados.
                <div class="float-right d-none d-sm-inline-block">
                    <b>Version</b> 3.2.0
                </div>
            </footer>
        </div>

        <!-- jQuery, Bootstrap 4, and AdminLTE JS (CDN) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

        <!-- Sidebar State Management -->
        <script>
        $(document).ready(function() {
            // Detectar página actual y marcar como activa
            setActiveMenuItem();
        });

        function setActiveMenuItem() {
            var currentPath = window.location.pathname;

            // Remover todas las clases active existentes
            $('.nav-link').removeClass('active');
            $('.nav-item').removeClass('menu-open');

            // Buscar el enlace que coincida con la ruta actual
            $('.nav-link[href]').each(function() {
                var linkHref = $(this).attr('href');

                if (linkHref) {
                    // Obtener la pathname del enlace para comparación precisa
                    var linkPathname = new URL(linkHref, window.location.origin).pathname;

                    // Comparar rutas exactas
                    if (linkPathname === currentPath) {
                        $(this).addClass('active');

                        // Si es un submenú, marcar el menú padre como activo también
                        var $parentMenu = $(this).closest('li.nav-item.has-treeview');
                        if ($parentMenu.length > 0) {
                            $parentMenu.find('> a').addClass('active');
                            $parentMenu.addClass('menu-open');
                        }
                    }
                }
            });
        }
        
        // Función para toggle del sidebar en móvil
        function toggleMobileSidebar() {
            var sidebar = document.getElementById('mainSidebar');
            var overlay = document.querySelector('.mobile-sidebar-overlay');
            var body = document.getElementById('appBody');
            var contentWrapper = document.querySelector('.content-wrapper');
            var mainHeader = document.querySelector('.main-header');
            var mainFooter = document.querySelector('.main-footer');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
                
                // Asegurar que el contenido NUNCA se mueva
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '0';
                    contentWrapper.style.left = '0';
                }
                if (mainHeader) {
                    mainHeader.style.marginLeft = '0';
                    mainHeader.style.left = '0';
                }
                if (mainFooter) {
                    mainFooter.style.marginLeft = '0';
                    mainFooter.style.left = '0';
                }
                
                // Prevenir scroll del body cuando el sidebar está abierto
                if (sidebar.classList.contains('mobile-open')) {
                    if (body) body.style.overflow = 'hidden';
                    // Asegurar que el sidebar use transform, no margin
                    sidebar.style.marginLeft = '0';
                    sidebar.style.transform = 'translateX(0)';
                } else {
                    if (body) body.style.overflow = '';
                    sidebar.style.marginLeft = '-250px';
                    sidebar.style.transform = 'translateX(-100%)';
                }
            }
        }
        
        // Cerrar sidebar al hacer clic en un enlace en móvil
        $(document).ready(function() {
            if (window.innerWidth <= 768) {
                $('.nav-link').on('click', function() {
                    setTimeout(function() {
                        toggleMobileSidebar();
                    }, 300);
                });
            }
        });
        
        // Ajustar sidebar al cambiar tamaño de ventana
        $(window).on('resize', function() {
            var sidebar = document.getElementById('mainSidebar');
            var overlay = document.querySelector('.mobile-sidebar-overlay');
            var body = document.getElementById('appBody');
            var contentWrapper = document.querySelector('.content-wrapper');
            var mainHeader = document.querySelector('.main-header');
            var mainFooter = document.querySelector('.main-footer');
            
            if (window.innerWidth > 768) {
                // En desktop, restaurar comportamiento normal de AdminLTE
                if (sidebar) {
                    sidebar.classList.remove('mobile-open');
                    sidebar.style.position = 'fixed';
                    sidebar.style.transform = '';
                    sidebar.style.zIndex = '1038';
                    sidebar.style.marginLeft = '0';
                    sidebar.style.left = '0';
                    sidebar.style.top = '0';
                    sidebar.style.bottom = '0';
                }
                if (overlay) overlay.classList.remove('active');
                if (body) body.style.overflow = '';
                
                // En desktop, el contenido DEBE tener margen del sidebar (250px)
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '250px';
                    contentWrapper.style.left = 'auto';
                }
                if (mainHeader) {
                    mainHeader.style.marginLeft = '250px';
                    mainHeader.style.left = 'auto';
                }
                if (mainFooter) {
                    mainFooter.style.marginLeft = '250px';
                    mainFooter.style.left = 'auto';
                }
            } else {
                // En móvil, asegurar que el sidebar esté oculto por defecto
                if (sidebar) {
                    if (!sidebar.classList.contains('mobile-open')) {
                        sidebar.style.transform = 'translateX(-100%)';
                        sidebar.style.marginLeft = '-250px';
                    }
                }
                // Forzar que el contenido no tenga margen
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '0';
                    contentWrapper.style.left = '0';
                }
                if (mainHeader) {
                    mainHeader.style.marginLeft = '0';
                    mainHeader.style.left = '0';
                }
                if (mainFooter) {
                    mainFooter.style.marginLeft = '0';
                    mainFooter.style.left = '0';
                }
            }
        });
        
        // Asegurar que el sidebar esté configurado correctamente al cargar
        $(document).ready(function() {
            var sidebar = document.getElementById('mainSidebar');
            var contentWrapper = document.querySelector('.content-wrapper');
            var mainHeader = document.querySelector('.main-header');
            var mainFooter = document.querySelector('.main-footer');
            
            // Verificar si estamos en móvil o desktop
            if (window.innerWidth <= 768) {
                // MÓVIL: Sidebar oculto, contenido sin margen
                if (sidebar) {
                    sidebar.classList.remove('mobile-open');
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebar.style.marginLeft = '-250px';
                }
                
                // Forzar que el contenido no tenga margen
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '0';
                    contentWrapper.style.left = '0';
                }
                if (mainHeader) {
                    mainHeader.style.marginLeft = '0';
                    mainHeader.style.left = '0';
                }
                if (mainFooter) {
                    mainFooter.style.marginLeft = '0';
                    mainFooter.style.left = '0';
                }
                
                // Observar cambios en el DOM solo en móvil
                var observer = new MutationObserver(function(mutations) {
                    if (window.innerWidth <= 768) {
                        var contentWrapper = document.querySelector('.content-wrapper');
                        var mainHeader = document.querySelector('.main-header');
                        var mainFooter = document.querySelector('.main-footer');
                        
                        if (contentWrapper && contentWrapper.style.marginLeft !== '0px') {
                            contentWrapper.style.marginLeft = '0';
                            contentWrapper.style.left = '0';
                        }
                        if (mainHeader && mainHeader.style.marginLeft !== '0px') {
                            mainHeader.style.marginLeft = '0';
                            mainHeader.style.left = '0';
                        }
                        if (mainFooter && mainFooter.style.marginLeft !== '0px') {
                            mainFooter.style.marginLeft = '0';
                            mainFooter.style.left = '0';
                        }
                    }
                });
                
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['style', 'class'],
                    subtree: true
                });
            } else {
                // DESKTOP: Sidebar visible, contenido con margen
                if (sidebar) {
                    sidebar.style.position = 'fixed';
                    sidebar.style.transform = '';
                    sidebar.style.marginLeft = '0';
                    sidebar.style.left = '0';
                    sidebar.style.top = '0';
                    sidebar.style.bottom = '0';
                    sidebar.style.zIndex = '1038';
                }
                
                // En desktop, el contenido DEBE tener margen del sidebar (250px)
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '250px';
                    contentWrapper.style.left = 'auto';
                }
                if (mainHeader) {
                    mainHeader.style.marginLeft = '250px';
                    mainHeader.style.left = 'auto';
                }
                if (mainFooter) {
                    mainFooter.style.marginLeft = '250px';
                    mainFooter.style.left = 'auto';
                }
            }
            
            // Inicializar el estado del sidebar
            setActiveMenuItem();
        });
        </script>
        @stack('scripts')
        @stack('js')
    </body>
    </html>





