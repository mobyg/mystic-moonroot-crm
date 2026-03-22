<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mystic Moonroot CRM') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #6f42c1 0%, #28a745 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .login-logo {
            height: 80px;
            max-width: 100%;
        }

        .btn-primary {
            background: #6f42c1;
            border-color: #6f42c1;
        }

        .btn-primary:hover {
            background: #5a2d91;
            border-color: #5a2d91;
        }

        .btn-link {
            color: #6f42c1;
        }

        .btn-link:hover {
            color: #5a2d91;
        }
    </style>
</head>

<body>
    <main>
        @yield('content')
    </main>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>