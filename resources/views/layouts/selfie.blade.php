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
        <div class="pageTitle">Absensi</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->

    {{-- CSS PETA & KAMERA--}}
    <style>
        .webcam-capture,
        .webcam-capture video {
            display: inline-block;
            width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15px;
        }

        #map {
            height: 250px;
        }
    </style>

    {{-- CSS PETA --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- SCRIPT PETA -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- KAMERA --}}
    <div class="row" style="margin-top:70px">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="webcam-capture"></div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            @if (!empty($isOnLeave) && $isOnLeave)
                <button id="takepicture" class="btn-secondary btn-block">
                    <ion-icon name="camera-outline"></ion-icon>
                    Tidak Bisa Absen (Sedang Izin/Sakit)
                </button>
            @elseif ((!empty($isWeekend) && $isWeekend) || (!empty($isHoliday) && $isHoliday))
                <button id="takepicture" class="btn-secondary btn-block" disabled>
                    <ion-icon name="camera-outline"></ion-icon>
                    Tidak Bisa Absen (Hari Libur)
                </button>
            @elseif ($show_pulang)
                <button id="takepicture" class="btn-danger btn-block">
                    <ion-icon name="camera-outline"></ion-icon>
                    Absen Pulang
                </button>
            @else
                <button id="takepicture" class="btn-primary btn-block">
                    <ion-icon name="camera-outline"></ion-icon>
                    Absen Masuk
                </button>
            @endif
        </div>
    </div>
    {{-- KAMERA --}}

    {{-- PETA --}}
    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>
    {{-- PETA --}}
    
    {{-- NOTIFIKASI IN --}}
    <audio id = "notifikasi_in">
        <source src="{{ asset('assets/audio/notifikasi_in.mpeg') }}" type="audio/mpeg">
    </audio>
    {{-- NOTIFIKASI IN --}}

    {{-- NOTIFIKASI OUT --}}
    <audio id = "notifikasi_out">
        <source src="{{ asset('assets/audio/notifikasi_out.mpeg') }}" type="audio/mpeg">
    </audio>
    {{-- NOTIFIKASI OUT --}}

    {{-- NOTIFIKASI RADIUS --}}
    <audio id = "radius_sound">
        <source src="{{ asset('assets/audio/notif_diluar_radius.mpeg') }}" type="audio/mpeg">
    </audio>
    {{-- NOTIFIKASI RADIUS --}}

    <!-- BOTTOM NAVIGATION MENU -->
    @include('layouts.bottomNav')


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
    <script src="{{ asset('offline/idb.js') }}"></script>
    <script src="{{ asset('offline/offline-sync.js') }}"></script>
    {{-- SCRIPT NOTIF --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ///////////// SCRIPT KAMERA ////////////////////  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script>
        var notifikasi_in = document.getElementById('notifikasi_in');
        var notifikasi_out = document.getElementById('notifikasi_out');
        var radius_sound = document.getElementById('radius_sound');
        Webcam.set({
            height: 480,
            width: 640,
            image_format: 'jpeg',
            jpeg_quality: 80
        });

        Webcam.attach('.webcam-capture');

        var lokasi = document.getElementById('lokasi');
        var isWeekend = {{ isset($isWeekend) && $isWeekend ? 'true' : 'false' }};
        var isHoliday = {{ isset($isHoliday) && $isHoliday ? 'true' : 'false' }};
        var isOnLeave = {{ isset($isOnLeave) && $isOnLeave ? 'true' : 'false' }};
        var leaveType = "{{ isset($leaveType) ? $leaveType : '' }}"; // e.g., 'sakit' or ''
        if (isOnLeave) {
            if (leaveType && leaveType.toLowerCase() === 'sakit') {
                Swal.fire({
                    title: 'Sedang Mengajukan Cuti Sakit',
                    text: 'Anda tidak bisa melakukan absensi.',
                    icon: 'info'
                });
            } else {
                Swal.fire({
                    title: 'Sedang mengajukan izin',
                    text: 'Anda tidak bisa melakukan absensi.',
                    icon: 'info'
                });
            }
        }
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        function successCallback(position) {
            lokasi.value = position.coords.latitude + "," + position.coords.longitude;
            var map = L.map('map', { maxZoom: 19, minZoom: 1 }).setView([position.coords.latitude, position.coords.longitude], 19);
            var lokasi_sekolah = "{{ $lokasi_sklh->lokasi }}";
            var lok = lokasi_sekolah.split(",");
            var lat_sekolah = parseFloat(lok[0]);
            var long_sekolah = parseFloat(lok[1]);
            var radius = parseFloat("{{ $lokasi_sklh->radius }}");
            var tilt = parseInt("{{ isset($lokasi_sklh->tilt) ? intval($lokasi_sklh->tilt) : 0 }}", 10) || 0;

            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
                maxZoom: 19,
                maxNativeZoom: 19
            });
            satelliteLayer.addTo(map);

            var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);

            function createRectangle(center, radiusMeters, tiltDeg) {
                var latC = center[0];
                var lngC = center[1];
                var metersPerDegLat = 111320;
                var metersPerDegLng = 111320 * Math.cos(latC * Math.PI / 180);
                var halfSide = radiusMeters;
                var dLat = halfSide / metersPerDegLat;
                var dLng = halfSide / metersPerDegLng;

                var corners = [
                    [-dLat, -dLng], // SW
                    [-dLat,  dLng], // SE
                    [ dLat,  dLng], // NE
                    [ dLat, -dLng]  // NW
                ];

                var theta = (Number(tiltDeg) || 0) * Math.PI / 180;
                var cosT = Math.cos(theta), sinT = Math.sin(theta);
                var rotated = corners.map(function(off){
                    var y = off[0];
                    var x = off[1];
                    var yR = y * cosT - x * sinT;
                    var xR = y * sinT + x * cosT;
                    return [latC + yR, lngC + xR];
                });
                rotated.push(rotated[0]);

                return L.polygon(rotated, {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5
                });
            }

            var zone = createRectangle([lat_sekolah, long_sekolah], radius, tilt);
            zone.addTo(map);
        }

        function errorCallback() {

        }

        $("#takepicture").click(function(e) {
            if (isOnLeave) {
                if (leaveType && leaveType.toLowerCase() === 'sakit') {
                    Swal.fire({
                        title: 'Sedang Mengajukan Cuti Sakit',
                        text: 'Anda tidak bisa melakukan absensi.',
                        icon: 'info'
                    });
                } else {
                    Swal.fire({
                        title: 'Sedang mengajukan izin',
                        text: 'Anda tidak bisa melakukan absensi.',
                        icon: 'info'
                    });
                }
                return;
            }
            if (isWeekend || isHoliday) {
                Swal.fire({
                    title: 'Tidak bisa absen',
                    text: 'Hari ini adalah hari libur (akhir pekan/kalender).',
                    icon: 'info'
                });
                return;
            }
            Webcam.snap(function(uri) {
                image = uri;
            });
            var lokasi = $("#lokasi").val();
            if (window.SibaloOffline && typeof window.SibaloOffline.submitAbsensi === 'function') {
                window.SibaloOffline.submitAbsensi({ image: image, lokasi: lokasi }).then(function(result){
                    if (result && result.queued) {
                        // queued offline: back to home
                        setTimeout(function(){ location.href = '/home'; }, 1200);
                        return;
                    }
                    if (result && result.ok && result.json) {
                        if(result.json.tag==="in") {
                            notifikasi_in.play();
                        }else{
                            notifikasi_out.play();
                        }
                        Swal.fire({
                            title: 'Berhasil !',
                            text: result.json.message,
                            icon: 'success'
                        })
                        setTimeout("location.href='/home'", 3000);
                        return;
                    }
                    // application error
                    var msg = (result && result.json && result.json.message) ? result.json.message : 'Maaf Gagal Absen,silahkan hubungi IT';
                    if (result && result.json && result.json.tag === "radius") {
                        radius_sound.play();
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: msg,
                        icon: 'error'
                    })
                }).catch(function(){
                    // fallback: if something unexpected happens, queue it
                    if (window.SibaloOffline && typeof window.SibaloOffline.submitAbsensi === 'function') {
                        window.SibaloOffline.submitAbsensi({ image: image, lokasi: lokasi });
                    }
                });
            } else {
                // legacy fallback
                $.ajax({
                    type: 'POST',
                    url: '/absensi/store',
                    data: {
                        _token: "{{ csrf_token() }}",
                        image: image,
                        lokasi: lokasi
                    },
                    cache: false,
                    success: function(respond) {
                        var status = respond.split("|");
                        if (status[0] == "success") {
                            if(status[2]=="in") {
                                notifikasi_in.play();
                            }else{
                                notifikasi_out.play();
                            }
                            Swal.fire({
                                title: 'Berhasil !',
                                text: status[1],
                                icon: 'success'
                            })
                            setTimeout("location.href='/home'", 3000);
                        } else {
                            if(status[2]=="radius") {
                                radius_sound.play();
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: status[1] || 'Maaf Gagal Absen,silahkan hubungi IT',
                                icon: 'error'
                            })
                        }
                    }
                });
            }
        });
    </script>


</body>

</html>
