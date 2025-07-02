<header class="section page-header">
    <!-- RD Navbar-->
    <div class="rd-navbar-wrap">
        <nav class="rd-navbar rd-navbar-modern" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
            data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static"
            data-lg-device-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static"
            data-xl-device-layout="rd-navbar-static" data-xxl-layout="rd-navbar-static"
            data-xxl-device-layout="rd-navbar-static" data-lg-stick-up-offset="56px" data-xl-stick-up-offset="56px"
            data-xxl-stick-up-offset="56px" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
            <div class="rd-navbar-inner-outer">
                <div class="rd-navbar-inner">
                    <!-- RD Navbar Panel-->
                    <div class="rd-navbar-panel">
                        <!-- RD Navbar Toggle-->
                        <button class="rd-navbar-toggle"
                            data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                        <!-- RD Navbar Brand-->
                        <div class="rd-navbar-brand">
                            <a class="brand" href="{{ route('home') }}">
                                <img class="brand-logo-dark" src="/images/logoss-po.png" alt="Proyecto nuevo" style="height: 130px; width: auto;">
                            </a>
                        </div>
                    </div>
                    <div class="rd-navbar-right rd-navbar-nav-wrap">
                        <div class="rd-navbar-aside">
                            <ul class="rd-navbar-contacts-2">
                                <li>
                                    <div class="unit unit-spacing-xs">
                                        <div class="unit-left"><span class="icon mdi mdi-phone"></span></div>
                                        <div class="unit-body"><a class="phone" href="tel:+51991824253">+51 991 824 253</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit unit-spacing-xs">
                                        <div class="unit-left"><span class="icon mdi mdi-map-marker"></span></div>
                                        <div class="unit-body"><a class="address" href="#">Calle Callao #137 Comercial Legas Stand 08-2 , Pisco, Peru</a></div>
                                    </div>
                                </li>
                            </ul>
                            <ul class="list-share-2">
                                <li><a class="icon mdi mdi-facebook" href="#" aria-label="Facebook"></a></li>
                                <li><a class="icon mdi mdi-twitter" href="#" aria-label="Twitter"></a></li>
                                <li><a class="icon mdi mdi-instagram" href="#" aria-label="Instagram"></a></li>
                                <li><a class="icon mdi mdi-google-plus" href="#" aria-label="Google Plus"></a></li>
                            </ul>
                        </div>
                        <div class="rd-navbar-main">
                            <!-- RD Navbar Nav-->
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item active"><a class="rd-nav-link" href="{{route('home')}}">Inicio</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="{{route('shop')}}">Tienda</a>
                                </li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#contact">Contacto</a>
                                </li>

                                @guest

                                    <li class="rd-nav-item"><a class="rd-nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                                    </li>

                                    <li class="rd-nav-item"><a class="rd-nav-link"
                                            href="{{ route('register') }}">Registrarse</a>
                                    </li>
                                @else
                                    <li class="rd-nav-item">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.1rem">
                                                <i class="fas fa-user me-2"></i>{{ auth()->user()->name }}
                                            </button>
                                            <div class="dropdown-menu">

                                                <a class="dropdown-item" href="{{route('orders.my')}}">
                                                    <i class="fas fa-shopping-bag me-2"></i>Mis Pedidos
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('logout') }}"
                                                    onclick="event.preventDefault();
                                      document.getElementById('logout-form').submit();">
                                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('Cerrar Sesión') }}
                                                </a>

                                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                    class="d-none">
                                                    @csrf
                                                </form>

                                            </div>
                                        </div>

                                    </li>

                                @endguest


                            </ul>
                        </div>
                    </div>
                    <div class="rd-navbar-project-hamburger rd-navbar-project-hamburger-open rd-navbar-fixed-element-1"
                        data-multitoggle=".rd-navbar-inner" data-multitoggle-blur=".rd-navbar-wrap"
                        data-multitoggle-isolate="data-multitoggle-isolate">
                        <span class="fas fa-shopping-cart" style="font-size: 1.3rem">
                            <span class="badge bg-danger" style="font-size: 0.8rem; position: absolute; top: -8px; right: -8px;">
                                {{Cart::instance('shopping')->content()->count()}}
                            </span>
                        </span>

                    </div>
                    <div class="rd-navbar-project">
                        <div class="rd-navbar-project-header">
                            <h5 class="rd-navbar-project-title">Carrito de Compras</h5>
                            <div class="rd-navbar-project-hamburger rd-navbar-project-hamburger-close"
                                data-multitoggle=".rd-navbar-inner" data-multitoggle-blur=".rd-navbar-wrap"
                                data-multitoggle-isolate="data-multitoggle-isolate">
                                <div class="project-close"><span></span><span></span></div>
                            </div>
                        </div>
                        <div class="rd-navbar-project-content rd-navbar-content">
                            <div>
                                <div class="row gutters-20" data-lightgallery="group">

                                    <div class="col-12">

                                        <x-cart />

                                        @if(Cart::instance('shopping')->content()->count() > 0)
                                            <a href="{{route('orders.checkout')}}" class="button button-secondary button-winona">
                                                <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                                            </a>
                                        @else
                                            <p class="text-muted text-center">Tu carrito está vacío</p>
                                        @endif

                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>
