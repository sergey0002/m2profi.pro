<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="/">
    
    <title>@yield('title', 'M2 Profi - Личный кабинет')</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ theme_asset('libs/air-datepicker/css/datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/formstyler/jquery.formstyler.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/aos/aos.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/jBox/dist/jBox.all.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('libs/mpop/magnific-popup.css') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
    .jBox-TooltipDark .jBox-container{background: rgba(0, 0, 0, 0.5); border-radius:4px;}
    .ttopt .jBox-content{ background: rgba(0, 0, 0, 0.5);  border-radius:4px; font-size:10px;}
    .jBox-TooltipDark .jBox-pointer:after{background: rgba(0, 0, 0, 0.7);}
    </style>
    
    <!-- Icon Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.1.96/css/materialdesignicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/jquery-3.3.1/jquery-3.3.1.min.js') }}"></script>
    <script src="/config.js.php"></script>
    
    @stack('head-styles')
</head>
<body style="overflow-y:scroll;">
    <div class="wrapper">
        
        @include('partials.header-lk')
        
        <!-- Decorative Elements -->
        <div class="circle-blur circle-blur_inner-top-left" data-aos="fade-left" data-aos-delay="100"></div>
        <div class="circle-blur circle-blur_inner-top-right" data-aos="fade-right" data-aos-delay="100" data-aos-offset="100"></div>
        <div class="circle-blur circle-blur_inner-center-right" data-aos="fade-left" data-aos-delay="100"></div>
        
        @include('partials.sidenav')
        
        <main>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <section class="section-objects">
                            <div class="container mobc">
                                <div class="page-header" style="margin-bottom:0;">
                                    <div class="page-header__logo">
                                        <img src="{{ theme_asset('images/logo.svg') }}" alt="" />
                                    </div>
                                    <div class="page-header__title">@yield('page-title', 'Личный кабинет')</div>
                                </div>
                                <div>
                                    @yield('content')
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
        
    </div>
    
    <!-- Scripts -->
    <script src="{{ theme_asset('libs/jquery.lazy.min.js') }}"></script>
    <script src="{{ theme_asset('libs/air-datepicker/js/datepicker.min.js') }}"></script>
    <script src="{{ theme_asset('libs/formstyler/jquery.formstyler.min.js') }}"></script>
    <script src="{{ theme_asset('libs/slick/slick.min.js') }}"></script>
    <script src="{{ theme_asset('libs/aos/aos.js') }}"></script>
    <script src="{{ theme_asset('libs/jBox/dist/jBox.all.min.js') }}"></script>
    <script src="{{ theme_asset('libs/mpop/jquery.magnific-popup.js') }}"></script>
    <script src="{{ theme_asset('libs/inputMask/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ theme_asset('js/jquery.mask.js') }}"></script>
    <script>
    $('.money').mask('00 000 000 ', {reverse: true});
    </script>
    
    @stack('scripts')
</body>
</html>
