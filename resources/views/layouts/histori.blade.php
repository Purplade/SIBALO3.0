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

<body>

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->

    <!-- HEADER -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Histori Absensi</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->
    
    <!-- ///////////// SCRIPT APP ////////////////////  -->
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
    {{-- SCRIPT TOMBOL CARI --}}
    <script>
        $(function() {
            function loadHistori() {
                var bulan = $("#bulan").val();
                var tahun = $("#tahun").val();
                $.ajax({
                    type: 'POST',
                    url:'/gethistori',
                    data:{
                        _token:"{{ csrf_token() }}",
                        bulan: bulan,
                        tahun: tahun
                    },
                    cache: false,
                    success: function(respond) {
                        $("#showhistori").html(respond)
                    }
                })
            }

            // Muat histori bulan berjalan saat halaman dibuka
            loadHistori();

            // Tetap dukung pencarian manual
            $("#caridata").click(function(e){
                loadHistori();
            })
        })
    </script>

        {{-- HISTORI ABSENSI --}}
        <div class="row" style="margin-top: 70px">
            <div class="col">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <select name="bulan" id="bulan" class="form-control">
                                <option value="">Bulan</option>
                                @for ($i=1; $i<=12; $i++) <option value="{{ $i }}" {{ date("m") == $i ? 'selected' : '' }}>{{ $namabulan[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <select name="tahun" id="tahun" class="form-control">
                                <option value="">Tahun</option>
                                @php
                                    $tahunmulai = 2025;
                                    $tahunskrg = date("Y");
                                @endphp
                                @for ($tahun=$tahunmulai; $tahun<= $tahunskrg; $tahun++) 
                                <option value="{{ $tahun }}"{{ date("Y") == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>    
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <button class="btn btn-primary btn-block" id="caridata">
                                <ion-icon name="search-outline"></ion-icon> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col" id="showhistori">
                
            </div>
        </div>
        {{-- HISTORI ABSENSI --}}
    
    
        <!-- BOTTOM NAVIGATION MENU -->
        @include('layouts.bottomNav')
</body>

</html>
