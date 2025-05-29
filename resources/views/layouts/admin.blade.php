<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SENA - Panel Administrativo')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/aprendices.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Poppins:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('img/icon/logoSena.png') }}" type="image/png">
    <!-- Scripts base -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Configurar AJAX con CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('img/logo/logoSena.png') }}" alt="Logo SENA">
                <h3>Panel Admin</h3>
                <button id="sidebar-toggle-mobile" class="sidebar-toggle-mobile">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-section="dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.scanner') }}" class="{{ request()->routeIs('admin.scanner') ? 'active' : '' }}" data-section="scanner">
                            <i class="fas fa-qrcode"></i>
                            <span>Escáner QR</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.aprendices') }}" class="{{ request()->routeIs('admin.aprendices') ? 'active' : '' }}" data-section="aprendices">
                            <i class="fas fa-users"></i>
                            <span>Aprendices</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.programas') }}" class="{{ request()->routeIs('admin.programas') ? 'active' : '' }}" data-section="programas">
                            <i class="fas fa-book"></i>
                            <span>Programas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.asistencias.index') }}" class="{{ request()->routeIs('admin.asistencias.*') ? 'active' : '' }}" data-section="asistencias">
                            <i class="fas fa-calendar-check"></i>
                            <span>Asistencias</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reportes') }}" class="{{ request()->routeIs('admin.reportes') ? 'active' : '' }}" data-section="reportes">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion') }}" class="{{ request()->routeIs('admin.configuracion') ? 'active' : '' }}" data-section="configuracion">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                    <li class="sidebar-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="sidebar-logout">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content" id="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="header-right">
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <span class="user-name">{{ Auth::user()->nombres_completos }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="#" class="user-dropdown-item">
                                <i class="fas fa-user"></i> Perfil
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="user-dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-wrapper" id="content-wrapper">
                <div class="content-loader">
                    <div class="spinner"></div>
                </div>
                <div class="content">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Notificación toast -->
    <div id="toast-notification" class="toast-notification"></div>

    <!-- Scripts generales -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar en móvil
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarToggleMobile = document.getElementById('sidebar-toggle-mobile');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }
            
            if (sidebarToggleMobile) {
                sidebarToggleMobile.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-mobile-active');
                });
            }
            
            // Adaptación para pantallas móviles
            function checkMobile() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('sidebar-collapsed');
                    sidebar.classList.remove('sidebar-mobile-active');
                    mainContent.classList.add('expanded');
                } else {
                    mainContent.classList.remove('expanded');
                }
            }
            
            // Ejecutar al cargar y cuando cambie el tamaño de la ventana
            checkMobile();
            window.addEventListener('resize', checkMobile);
            
            // Manejo del dropdown del usuario
            const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
            const userDropdownMenu = document.querySelector('.user-dropdown-menu');
            
            if (userDropdownToggle) {
                userDropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    userDropdownMenu.classList.toggle('active');
                });
                
                // Cerrar el dropdown al hacer clic fuera
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.user-dropdown')) {
                        userDropdownMenu.classList.remove('active');
                    }
                });
            }
        });
        
        // Función para mostrar notificaciones
        function showNotification(message, type = 'success', duration = 3000) {
            const toast = document.getElementById('toast-notification');
            
            // Establecer tipo
            toast.className = 'toast-notification';
            toast.classList.add(`toast-${type}`);
            
            // Establecer mensaje
            toast.textContent = message;
            toast.style.display = 'block';
            
            // Animar entrada
            setTimeout(() => {
                toast.classList.add('show');
                
                // Animar salida después de duración
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 300);
                }, duration);
            }, 10);
        }
    </script>

    <!-- Scripts adicionales -->
    @yield('scripts')
</body>
</html> 