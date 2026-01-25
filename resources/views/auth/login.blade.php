<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIBALO</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
</head>

<body class="bg-white">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->


    <!-- App Capsule -->
    <div id="appCapsule" class="pt-0">

        <div class="login-form mt-1">
            <div class="section">
                <a href="/panel" aria-label="Ke Halaman Admin">
                    <img src="{{ asset('assets/img/logosekolah.png') }}" alt="Logo Sekolah" class="form-image">
                </a>
            </div>
            <div class="section mt-1">
                <h1>SIBALO</h1>
                <h4>Silahkan Login</h4>
            </div>
            <div class="section mt-3 mb-5">
                <form action="/proseslogin" method="POST">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-10 col-md-6 col-lg-6">
                            <div class="form-group boxed">
                                <div class="input-wrapper">
                                    <input type="text" name="nik" class="form-control" id="nik" placeholder="NIP">
                                    <i class="clear-input">
                                        <ion-icon name="close-circle"></ion-icon>
                                    </i>
                                </div>
                            </div>
        
                            <div class="form-group boxed">
                                <div class="input-wrapper">
                                    <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                                    <i class="clear-input">
                                        <ion-icon name="close-circle"></ion-icon>
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @php
                    $messagewarning = Session::get('warning');
                    @endphp

                    @if (Session::get('warning'))
                    <div class="alert alert-outline-warning">
                        {{ $messagewarning }}
                    </div>
                    @endif
                    <div class="form-group boxed">
                        <div class="input-wrapper">
                            <button type="submit" class="btn btn-primary w-10 btn-lg">Log in</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>
    <!-- * App Capsule -->



    <!-- ///////////// Js Files ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <script src="{{ asset('offline/idb.js') }}" defer></script>
    <script src="{{ asset('offline/offline-sync.js') }}" defer></script>


</body>

</html>