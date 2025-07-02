<!DOCTYPE html>
<html class="wide wow-animation" lang="es">
  <head>
    <title>D' BRASA Y CARBON</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="{{asset('images/poolloo.jpg')}}" type="image/jpeg">
    <!-- Stylesheets-->
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:100,300,300i,400,500,600,700,900%7CRaleway:500">
    <link rel="stylesheet" href="{{asset('css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{asset('css/fonts.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    
    <!-- Bootstrap 5 Compatibility Fixes -->
    <link href="{{ asset('css/bootstrap-fixes.css') }}" rel="stylesheet">

  </head>
  <body>

    @include('layouts.partials.preloader')

    <div class="page">

        <!-- Page Header-->
        @include('layouts.partials.header')

        @if (session()->has('msg'))
            <div class="alert alert-success alert-dismissible fade show mt-4" role="alert" style="background-color: green; color:white">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¡Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

      @yield('content')

      <!-- Page Footer-->
      <footer class="section footer-modern context-dark footer-modern-2" style="background-color: #000;">

        <div class="footer-modern-line-2">
          <div class="container">
            <div class="row row-30 align-items-center">
              <div class="col-sm-6 col-md-7 col-lg-4 col-xl-4">
                <div class="row row-30 align-items-center text-lg-center">
                  <div class="col-md-7 col-xl-6"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/poolloo.jpg') }}" alt="Logo" style="height: 90px; width: auto;"></a></div>
                  <div class="col-md-5 col-xl-6">
                    <div class="iso-1"><span><img src="{{ asset('images/like-icon-58x25.png') }}" alt="" width="58" height="25"/></span><span class="iso-1-big">9.4k</span></div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-12 col-lg-8 col-xl-8 oh-desktop">
                <div class="group-xmd group-sm-justify">
                  <div class="footer-modern-contacts wow slideInUp">
                    <div class="unit unit-spacing-sm align-items-center">
                      <div class="unit-left"><span class="icon icon-24 mdi mdi-phone"></span></div>
                      <div class="unit-body"><a class="phone" href="tel:+51991824253">+51 991 824 253</a></div>
                    </div>
                  </div>
                  <div class="footer-modern-contacts wow slideInDown">
                    <div class="unit unit-spacing-sm align-items-center">
                      <div class="unit-left"><span class="icon mdi mdi-email"></span></div>
                      <div class="unit-body"><a class="mail" href="mailto:info@dbrasasycarbon.com">info@dbrasasycarbon.com</a></div>
                    </div>
                  </div>
                  <div class="wow slideInRight">
                    <ul class="list-inline footer-social-list footer-social-list-2 footer-social-list-3">
                      <li><a class="icon mdi mdi-facebook" href="#" aria-label="Facebook"></a></li>
                      <li><a class="icon mdi mdi-twitter" href="#" aria-label="Twitter"></a></li>
                      <li><a class="icon mdi mdi-instagram" href="#" aria-label="Instagram"></a></li>
                      <li><a class="icon mdi mdi-google-plus" href="#" aria-label="Google Plus"></a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="footer-modern-line-3">
          <div class="container">
            <div class="row row-10 justify-content-between">
              <div class="col-md-6"><span>Calle Callao #137 Comercial Legas Stand 08-2 , Pisco, Peru</span></div>
              <div class="col-md-auto">
                <!-- Rights-->
                <p class="rights"><span>&copy;&nbsp;</span><span class="copyright-year">2025</span><span></span><span>.&nbsp;</span><span>Todos los derechos reservados. Diseñado por Saravia, Tarrillo y los Hermanos Monroy</span></p>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="{{asset('js/core.min.js')}}"></script>
    <script src="{{asset('js/script.js')}}"></script>
    
    <!-- Bootstrap 5 JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    </script>
    <!-- coded by Himic-->
  </body>
</html>
