<!doctype html>
<!--
* Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
* @version 1.0.0-beta17
* @link https://tabler.io
* Copyright 2018-2023 The Tabler Authors
* Copyright 2018-2023 codecalm.net Paweł Kuna
* Licensed under MIT (https://github.com/tabler/tabler/blob/master/LICENSE)
-->
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Dashboard - Tabler - Premium and Open Source dashboard template with responsive and high quality UI.</title>
    <!-- CSS files -->
    <link href="{{ asset('dist/css/tabler.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-flags.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-payments.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-vendors.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/demo.min.css?1674944402') }}" rel="stylesheet" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>

    {{-- CSS PETA --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- CSS PETA --}}
</head>

<body>
    <script src="{{ asset('dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">

        <!-- Sidebar -->
        @include('dashboard.sidebar')

        <!-- Navbar -->
        @include('dashboard.header')

        {{-- TITLE DASHBOARD ADMIN --}}
        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <!-- Page pre-title -->

                            <h2 class="page-title">
                                KONFIGURASI LOKASI
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            {{-- TITLE KONFIGURASI LOKASI --}}

            {{-- KONTEN --}}
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        <div class="col-8">
                            <div class="card">
                                <div class="card-body">
                                    @if (Session::get('success'))
                                        <div class="alert alert-success">
                                            {{ Session::get('success') }}
                                        </div>
                                    @endif

                                    @if (Session::get('warning'))
                                        <div class="alert alert-warning">
                                            {{ Session::get('warning') }}
                                    @endif
                                    <form action="/updatelokasisekolah" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-10">
                                                <div class="input-icon mb-3">
                                                    <span class="input-icon-addon">
                                                        <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-map-2">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" />
                                                            <path d="M9 4v13" />
                                                            <path d="M15 7v5.5" />
                                                            <path
                                                                d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" />
                                                            <path d="M19 18v.01" />
                                                        </svg>
                                                    </span>
                                                    <input type="text" value="{{ $lokasi_sklh->lokasi }}"
                                                        id="lokasi_sekolah" class="form-control" name="lokasi_sekolah"
                                                        placeholder="Lokasi Sekolah">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-10">
                                                <div class="input-icon mb-3">
                                                    <span class="input-icon-addon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-radar">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M21 12h-8a1 1 0 1 0 -1 1v8a9 9 0 0 0 9 -9" />
                                                            <path d="M16 9a5 5 0 1 0 -7 7" />
                                                            <path d="M20.486 9a9 9 0 1 0 -11.482 11.495" />
                                                        </svg>
                                                    </span>
                                                    <input type="text" value="{{ $lokasi_sklh->radius }}"
                                                        id="radius" class="form-control" name="radius"
                                                        placeholder="Radius Sekolah">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-10">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Tilt (derajat)</label>
                                                    <input type="range" id="tilt" min="0" max="359" step="1" value="{{ isset($lokasi_sklh->tilt) ? intval($lokasi_sklh->tilt) : 0 }}" class="form-range">
                                                </div>
                                            </div>
                                        </div>
                                    
                                    <input type="hidden" name="tilt_value" id="tilt_value" value="{{ isset($lokasi_sklh->tilt) ? intval($lokasi_sklh->tilt) : 0 }}">
                                        <div class="row">
                                            <div class="col-10">
                                                <button class="btn btn-primary w-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-refresh">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                                    </svg>
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-xl">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-2">TENTUKAN POSISI SEKOLAH</h3>
                            <div class="peta">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- KONTEN --}}

            {{-- FOOTER DASHBOARD --}}
            @include('dashboard.footer')
            {{-- FOOTER DASHBOARD --}}
        </div>
    </div>

    <style>
        #map {
            height: 250px;
        }
    </style>

    {{-- CSS PETA --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- CSS PETA --}}

    {{-- SCRIPT PETA --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Script jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Script jQuery -->

    <!-- Libs JS -->
    <script src="{{ asset('dist/libs/apexcharts/dist/apexcharts.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/js/jsvectormap.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world-merc.js?1674944402') }}" defer></script>
    <!-- Tabler Core -->
    <script src="{{ asset('dist/js/tabler.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/js/demo.min.js?1674944402') }}" defer></script>

    <script>
        $(function() {

            // Pull initial state from existing inputs
            var lokasiInput = document.getElementById('lokasi_sekolah');
            var radiusInput = document.getElementById('radius');

            function parseLokasi(value) {
                if (!value) return null;
                // Expecting "lat,lon"
                var parts = value.split(',').map(function(s) {
                    return s.trim();
                });
                if (parts.length !== 2) return null;
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                if (isNaN(lat) || isNaN(lng)) return null;
                return [lat, lng];
            }

            var existingLatLng = parseLokasi(lokasiInput ? lokasiInput.value : '');
            var fallbackLatLng = existingLatLng || [-7.797068, 110.370791];
            var initialRadius = parseFloat(radiusInput && radiusInput.value ? radiusInput.value : '1000');
            if (isNaN(initialRadius)) initialRadius = 1000;

            var map = L.map('map', { maxZoom: 19, minZoom: 1 });
            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
                maxZoom: 19,
                maxNativeZoom: 19
            });
            satelliteLayer.addTo(map);

            // Zone shape (circle or rectangle)
            var zoneShape = null;
            var tiltInput = document.getElementById('tilt');

            function createRectangle(center, radiusMeters, tiltDeg) {
                // Build a rotated rectangle centered at 'center'
                // Treat input radius as half side (square); convert meters to degrees
                var latC = center[0];
                var lngC = center[1];
                var metersPerDegLat = 111320; // ~ meters per degree latitude
                var metersPerDegLng = 111320 * Math.cos(latC * Math.PI / 180); // adjust by latitude
                var halfSide = radiusMeters; // half side in meters
                var dLat = halfSide / metersPerDegLat;
                var dLng = halfSide / metersPerDegLng;

                // Rectangle corner points before rotation (relative to center)
                var corners = [
                    [-dLat, -dLng], // SW
                    [-dLat,  dLng], // SE
                    [ dLat,  dLng], // NE
                    [ dLat, -dLng]  // NW
                ];

                // Rotate around center by tiltDeg
                var theta = (Number(tiltDeg) || 0) * Math.PI / 180;
                var cosT = Math.cos(theta), sinT = Math.sin(theta);
                var rotated = corners.map(function(off){
                    var y = off[0]; // dLat
                    var x = off[1]; // dLng
                    var yR = y * cosT - x * sinT;
                    var xR = y * sinT + x * cosT;
                    return [latC + yR, lngC + xR];
                });
                // Close the polygon by repeating first point
                rotated.push(rotated[0]);

                return L.polygon(rotated, {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5
                });
            }

            function drawZone(center, radius) {
                if (zoneShape) {
                    map.removeLayer(zoneShape);
                }
                var tiltDeg = tiltInput ? Number(tiltInput.value) || 0 : 0;
                zoneShape = createRectangle(center, radius, tiltDeg);
                zoneShape.addTo(map);
            }

            drawZone(fallbackLatLng, initialRadius);

            // User position marker (same naming style as selfie.blade.php)
            var marker = null;

            function updateInputs(latlng) {
                if (lokasiInput) {
                    var coord = latlng.lat.toFixed(6) + ',' + latlng.lng.toFixed(6);
                    lokasiInput.value = coord;
                    lokasiInput.placeholder = coord;
                }
            }

            // Initialize view: try geolocation first, fallback to existing/default
            function centerMap(latlng, zoom) {
                map.setView(latlng, zoom || 19);
            }

            // Ensure inputs reflect initial marker position
            updateInputs(L.latLng(fallbackLatLng[0], fallbackLatLng[1]));

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var latlng = [pos.coords.latitude, pos.coords.longitude];
                    centerMap(latlng, 19);
                    if (!marker) {
                        marker = L.marker(latlng).addTo(map).bindPopup('Posisi Anda');
                        // Clicking the user marker fills the input and moves the zone
                        marker.on('click', function() {
                            var ll = marker.getLatLng();
                            drawZone([ll.lat, ll.lng], parseFloat(radiusInput.value) || initialRadius);
                            updateInputs(ll);
                        });
                    } else {
                        marker.setLatLng(latlng);
                    }
                }, function() {
                    // On error, fallback
                    centerMap(fallbackLatLng, 19);
                }, {
                    enableHighAccuracy: true,
                    timeout: 8000,
                    maximumAge: 0
                });
            } else {
                centerMap(fallbackLatLng, 19);
            }

            // Map click: move zone center and fill input
            map.on('click', function(e) {
                drawZone([e.latlng.lat, e.latlng.lng], parseFloat(radiusInput.value) || initialRadius);
                updateInputs(e.latlng);
            });

            // When radius input changes, update the zone size live
            if (radiusInput) {
                radiusInput.addEventListener('input', function() {
                    var r = parseFloat(radiusInput.value);
                    if (!isNaN(r) && r > 0) {
                        var center;
                        if (zoneShape && zoneShape.getBounds) {
                            center = zoneShape.getBounds().getCenter();
                        } else if (zoneShape && zoneShape.getLatLng) {
                            center = zoneShape.getLatLng();
                        }
                        center = center || L.latLng(fallbackLatLng[0], fallbackLatLng[1]);
                        drawZone([center.lat, center.lng], r);
                    }
                });
            }

            // shapeType removed; always rectangle
            
            if (tiltInput) {
                tiltInput.addEventListener('input', function() {
                    var r = parseFloat(radiusInput.value) || initialRadius;
                    var center;
                    if (zoneShape && zoneShape.getBounds) {
                        center = zoneShape.getBounds().getCenter();
                    } else if (zoneShape && zoneShape.getLatLng) {
                        center = zoneShape.getLatLng();
                    }
                    center = center || L.latLng(fallbackLatLng[0], fallbackLatLng[1]);
                    drawZone([center.lat, center.lng], r);
                    // sync hidden value for form submit
                    var tiltValue = document.getElementById('tilt_value');
                    if (tiltValue) tiltValue.value = String(tiltInput.value || 0);
                });
            }

            // On successful update (page reload with success message), re-draw the zone using stored values
            (function hydrateFromServer() {
                try {
                    // If your controller returns $lokasi_sklh->tilt, render it
                    var serverTilt = @json(isset($lokasi_sklh->tilt) ? intval($lokasi_sklh->tilt) : 0);
                    if (tiltInput) {
                        tiltInput.value = serverTilt;
                        var tiltValue = document.getElementById('tilt_value');
                        if (tiltValue) tiltValue.value = String(serverTilt);
                    }
                    var r = parseFloat(radiusInput.value) || initialRadius;
                    drawZone(fallbackLatLng, r);
                } catch (e) {
                    // Fallback: already drawn using defaults
                }
            })();
        });
    </script>
</body>

</html>
