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
    {{-- CSS PETA --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- CSS DATE PICKER --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />

    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>

<body>
    <script src="{{ asset('dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">

        <!-- Sidebar -->
        @include('dashboard.sidebar')

        <!-- Navbar -->
        @include('dashboard.header')

        {{-- TITLE MONIORING ABSENSI --}}
        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-4 align-items-center">
                        <div class="col">
                            <!-- Page pre-title -->
                            <h2 class="page-title">
                                Monitoring Absensi
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            {{-- TITLE MONITORING ABSENSI --}}

            {{-- KONTEN --}}
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Dari Tanggal</label>
                                            <div class="input-icon mb-3">
                                                <span class="input-icon-addon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                        <path d="M16 3v4" />
                                                        <path d="M8 3v4" />
                                                        <path d="M4 11h16" />
                                                    </svg>
                                                </span>
                                                <input type="text" id="dari" name="dari" value="{{ date('Y-m-d', strtotime('-1 day')) }}" class="form-control" placeholder="Dari Tanggal">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Sampai Tanggal</label>
                                            <div class="input-icon mb-3">
                                                <span class="input-icon-addon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                        <path d="M16 3v4" />
                                                        <path d="M8 3v4" />
                                                        <path d="M4 11h16" />
                                                    </svg>
                                                </span>
                                                <input type="text" id="sampai" name="sampai" value="{{ date('Y-m-d') }}" class="form-control" placeholder="Sampai Tanggal">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>NIP</th>
                                                        <th>Tanggal</th>
                                                        <th>Nama Pegawai</th>
                                                        <th>Jam Masuk</th>
                                                        <th>Jam Pulang</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="loadabsensi"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
    <div class="modal modal-blur fade" id="modal-detailabsensi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="form-label">Foto Masuk</label>
                                <img data-role="foto-in" src="" class="img-fluid">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Lokasi Masuk</label>
                                <input data-role="lokasi-in" type="text" class="form-control" value="" readonly>
                                <div id="map-lokasi-in" class="mt-2" style="height: 250px; border-radius: 8px; overflow: hidden;"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Foto Pulang</label>
                                <img data-role="foto-out" src="" class="img-fluid">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Lokasi Pulang</label>
                                <input data-role="lokasi-out" type="text" class="form-control" value="" readonly>
                                <div id="map-lokasi-out" class="mt-2" style="height: 250px; border-radius: 8px; overflow: hidden;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="example-text-input"
                            placeholder="Your report name">
                    </div>
                    <label class="form-label">Report type</label>
                    <div class="form-selectgroup-boxes row mb-3">
                        <div class="col-lg-6">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="report-type" value="1" class="form-selectgroup-input"
                                    checked>
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="me-3">
                                        <span class="form-selectgroup-check"></span>
                                    </span>
                                    <span class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-title strong mb-1">Simple</span>
                                        <span class="d-block text-muted">Provide only basic data needed for the
                                            report</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="report-type" value="1"
                                    class="form-selectgroup-input">
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="me-3">
                                        <span class="form-selectgroup-check"></span>
                                    </span>
                                    <span class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-title strong mb-1">Advanced</span>
                                        <span class="d-block text-muted">Insert charts and additional advanced analyses
                                            to be inserted in the report</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Report url</label>
                                <div class="input-group input-group-flat">
                                    <span class="input-group-text">
                                        https://tabler.io/reports/
                                    </span>
                                    <input type="text" class="form-control ps-0" value="report-01"
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Visibility</label>
                                <select class="form-select">
                                    <option value="1" selected>Private</option>
                                    <option value="2">Public</option>
                                    <option value="3">Hidden</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Client name</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Reporting period</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div>
                                <label class="form-label">Additional information</label>
                                <textarea class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Cancel
                    </a>
                    <a href="#" class="btn btn-primary ms-auto" data-bs-dismiss="modal">
                        <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Create new report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Script jQuery -->

    {{-- SCRIPT TANGGAL ABSENSI --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>

    <!-- Libs JS -->
    <script src="{{ asset('dist/libs/apexcharts/dist/apexcharts.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/js/jsvectormap.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world-merc.js?1674944402') }}" defer></script>
    <!-- Libs JS -->

    {{-- SCRIPT PETA --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Tabler Core -->
    <script src="{{ asset('dist/js/tabler.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/js/demo.min.js?1674944402') }}" defer></script>
    <!-- Tabler Core -->

    {{-- SCRIPT TANGGAL ABSENSI --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $("#dari, #sampai").datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });

            function loadabsensi() {
                var dari = $("#dari").val();
                var sampai = $("#sampai").val();
                $.ajax({
                    type: 'POST',
                    url: '/getabsensi',
                    data: {
                        _token: "{{ csrf_token() }}",
                        dari: dari,
                        sampai: sampai
                    },
                    cache: false,
                    success: function(respond) {
                        $("#loadabsensi").html(respond);
                    },
                    error: function(xhr) {
                        console.error('Gagal memuat data absensi:', xhr.status, xhr.responseText);
                        $("#loadabsensi").html(
                            '<tr><td colspan="6">Gagal memuat data. Coba lagi.</td></tr>');
                    }
                });
            }

            $(document).on('click', '.btn-detail', function(e) {
                const fotoIn = $(this).data('foto-in') || '';
                const fotoOut = $(this).data('foto-out') || '';
                const lokasiIn = $(this).data('lokasi-in') || '';
                const lokasiOut = $(this).data('lokasi-out') || '';
                $('#modal-detailabsensi img[data-role="foto-in"]').attr('src', fotoIn);
                $('#modal-detailabsensi img[data-role="foto-out"]').attr('src', fotoOut);
                $('#modal-detailabsensi input[data-role="lokasi-in"]').val(lokasiIn);
                $('#modal-detailabsensi input[data-role="lokasi-out"]').val(lokasiOut);

                // Render maps for lokasi in/out
                function parseLatLng(value) {
                    if (!value) return null;
                    var text = String(value);
                    // Extract first two numeric values (lat/lng) from any string format
                    var matches = text.match(/-?\d+\.?\d*/g);
                    if (!matches || matches.length < 2) return null;
                    var a = parseFloat(matches[0]);
                    var b = parseFloat(matches[1]);
                    if (isNaN(a) || isNaN(b)) return null;
                    var lat = a;
                    var lng = b;
                    // If the first value looks like longitude (>90 abs), swap
                    if (Math.abs(lat) > 90 && Math.abs(lng) <= 180) {
                        lat = b;
                        lng = a;
                    }
                    // Validate ranges
                    if (Math.abs(lat) > 90 || Math.abs(lng) > 180) return null;
                    return [lat, lng];
                }

                function renderMap(containerId, latlng) {
                    var container = document.getElementById(containerId);
                    if (!container) return;
                    // Reset Leaflet instance bound to this container if exists
                    try { if (container._leaflet_id) { container._leaflet_id = null; } } catch (e) {}
                    // Initialize map
                    var map = L.map(containerId, { zoomControl: true });
                    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, GIS User Community'
                    });
                    satellite.addTo(map);
                    var center = latlng || [0, 0];
                    map.setView(center, 18);
                    if (latlng) {
                        L.marker(center).addTo(map);
                    }
                }

                var llIn = parseLatLng(lokasiIn);
                var llOut = parseLatLng(lokasiOut);
                // Defer rendering to after modal visible to ensure correct sizing
                var modalEl = document.getElementById('modal-detailabsensi');
                var onShown = function() {
                    renderMap('map-lokasi-in', llIn);
                    renderMap('map-lokasi-out', llOut);
                    modalEl.removeEventListener('shown.bs.modal', onShown);
                };
                if (modalEl) {
                    modalEl.addEventListener('shown.bs.modal', onShown);
                }
            });

            $("#dari, #sampai").on('changeDate', function() { loadabsensi(); });
            $("#dari, #sampai").on('change', function() { loadabsensi(); });

            loadabsensi();
        });
    </script>
    {{-- SCRIPT TANGGAL ABSENSI --}}
</body>

</html>
