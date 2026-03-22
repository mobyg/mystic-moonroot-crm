<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mystic Moonroot CRM') }}</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    
    <style>
        :root {
            --header-height: 120px;
            --sidebar-width: 250px;
            --primary-color: #6f42c1;
            --secondary-color: #28a745;
        }

        body {
            background-color: #f2f4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .app-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #dee2e6;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-logo {
            height: 100px;
            margin: 10px 0 10px 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.3rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .app-container {
            display: flex;
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
        }

        .app-sidebar {
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid #dee2e6;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-link {
            display: block;
            padding: 15px 25px;
            color: #495057;
            text-decoration: none !important;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(111, 66, 193, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color) !important;
            text-decoration: none !important;
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white !important;
            border-left-color: var(--secondary-color);
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .app-content {
            flex: 1;
            padding: 30px;
            background-color: #f2f4f7;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border: none;
            border-radius: 12px;
            padding: 25px;
            height: 250px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
            cursor: pointer;
            color: #495057;
            display: flex;
            align-items: center;
        }

        .card-header i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .card-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--primary-color);
        }

        .quote-content {
            font-size: 1rem !important;
            text-align: center;
            line-height: 1.6;
            color: #495057;
            font-weight: normal;
        }

        .popular-products {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .product-thumb {
            text-align: center;
            flex: 0 1 80px;
        }

        .product-thumb img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .product-name {
            font-size: 0.75rem;
            margin-top: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 80px;
            color: #6c757d;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #5a2d91;
            border-color: #5a2d91;
        }

        .btn-success {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .dropdown-toggle::after {
            margin-left: 8px;
        }

        .alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 12px 12px 0 0 !important;
        }

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            .app-sidebar {
                width: 100%;
                order: 2;
            }
            
            .app-content {
                order: 1;
                padding: 20px 15px;
            }

            .header-logo {
                font-size: 1rem;
                padding: 0 15px;
                margin: 10px 0 10px 10px;
            }

            .nav-link {
                padding: 12px 20px;
            }
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Custom scrollbar */
        .app-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .app-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .app-sidebar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .app-sidebar::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Header -->
    <div class="app-header">
        <div class="header-logo">
            🌙 MYSTIC MOONROOT CRM ✨
        </div>
        
        <div style="margin-right: 20px;">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-user"></i>
                    @auth
                        {{ Auth::user()->name }}
                    @endauth
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('settings') }}">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    @guest
                        <a class="dropdown-item" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    @else
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="app-container">
        @auth
        <!-- Sidebar -->
        <div class="app-sidebar">
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-tshirt"></i> Products
                </a>
                <a href="{{ route('sales') }}" class="nav-link {{ request()->routeIs('sales') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Sales
                </a>
                <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                    <i class="fas fa-calendar"></i> Calendar
                </a>
                <a href="{{ route('marketing') }}" class="nav-link {{ request()->routeIs('marketing') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i> Marketing
                </a>
                <a href="{{ route('blog') }}" class="nav-link {{ request()->routeIs('blog') ? 'active' : '' }}">
                    <i class="fas fa-blog"></i> Blog
                </a>
                <a href="{{ route('notes.index') }}" class="nav-link {{ request()->routeIs('notes.*') ? 'active' : '' }}">
                    <i class="fas fa-sticky-note"></i> Notes
                </a>
            </nav>
        </div>
        @endauth

        <!-- Content Area -->
        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    
    @yield('scripts')
    
    <script>
        // Basic app functionality
        $(document).ready(function() {
            console.log('🌙 Mystic Moonroot CRM Loaded Successfully! ✨');
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
</body>
</html>