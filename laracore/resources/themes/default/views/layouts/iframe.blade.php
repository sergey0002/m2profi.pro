<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'M2 Profi')</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ theme_asset('libs/air-datepicker/css/datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/formstyler/jquery.formstyler.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/jBox/dist/jBox.all.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/mpop/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('css/admin.css') }}">
    
    <!-- Icon Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.1.96/css/materialdesignicons.min.css" />
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/jquery-3.3.1/jquery-3.3.1.min.js') }}"></script>
    <script src="/sites/em/config.js.php"></script>
    
    @stack('head-styles')
    
    <style>
        body { background: #f4f7f6; padding: 20px; }
        .modal-content { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="modal-content">
        @yield('content')
    </div>
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/inputMask/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ theme_asset('libs/mpop/jquery.magnific-popup.js') }}"></script>
    @stack('scripts')
</body>
</html>
