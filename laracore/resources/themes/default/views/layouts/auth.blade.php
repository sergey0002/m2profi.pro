<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=3, user-scalable=yes">
    
    <title>M2 Profi - Авторизация</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ theme_asset('libs/formstyler/jquery.formstyler.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('css/media.css') }}">
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/jquery-3.3.1/jquery-3.3.1.min.js') }}"></script>
    
    @stack('styles')
</head>
<body style="overflow-y:scroll;">
    
    @yield('content')
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/formstyler/jquery.formstyler.min.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
