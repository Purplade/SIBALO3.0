<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIBALO</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    {{-- css --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    {{-- css logout --}}
    <style>
        .logout {
            position: absolute;
            color: white;
            font-size: 30px;
            text-decoration: none;
            right: 8px;
        }

        .logout:hover {
            color: white;
        }

        .jam-out-group {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            margin-left: 6px;
        }

        .jam-out-group small {
            margin-top: 2px;
            line-height: 1;
        }

        /* Keep date, jam_in, jam_out aligned per row */
        .in-history {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            align-items: center;
        }

        .in-history .badge-success {
            justify-self: center;
        }

        .in-history .jam-out-group {
            justify-self: end;
        }
    </style>
</head>

<body style="background-color:#e9ecef;">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->



    <!-- HOME -->
    <div id="appCapsule">
        <div class="section" id="user-section">
            <a href="/proseslogout" class="logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-logout">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                    <path d="M9 12h12l-3 -3" />
                    <path d="M18 15l3 -3" />
                </svg>
            </a>
            <div id="user-detail">
                <div class="avatar">
                    @php
                        $foto = Auth::guard('pegawai')->user()->foto ?? null;
                        $avatarUrl = null;
                        if (!empty($foto)) {
                            $pathUploads = 'uploads/pegawai/' . $foto; // lokasi target baru
                            $pathLegacy = 'pegawai/' . $foto; // lokasi lama (fallback)
                            if (Storage::disk('public')->exists($pathUploads)) {
                                $avatarUrl = Storage::url($pathUploads);
                            } elseif (Storage::disk('public')->exists($pathLegacy)) {
                                $avatarUrl = Storage::url($pathLegacy);
                            }
                        }
                    @endphp
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="avatar" class="imaged w64 rounded" style="height:60px">
                    @else
                        <img src="assets/img/sample/avatar/avatar1.jpg" alt="avatar" class="imaged w64 rounded">
                    @endif
                </div>
                <div id="user-info">
                    <h2 id="user-name">{{ Auth::guard('pegawai')->user()->nama_lengkap }}</h2>
                    <span id="user-role">{{ Auth::guard('pegawai')->user()->jabatan }}</span>
                </div>
            </div>
        </div>

        <div class="section" id="menu-section">
            <div class="card">
                <div class="card-body text-center">
                    <div class="list-menu">
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/profil" class="green" style="font-size: 40px;">
                                    <ion-icon name="person-sharp"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Profil</span>
                            </div>
                        </div>
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/absensi/izin" class="danger" style="font-size: 40px;">
                                    <ion-icon name="calendar-number"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Izin</span>
                            </div>
                        </div>
                        <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="/absensi/histori" class="warning" style="font-size: 40px;">
                                    <ion-icon name="document-text"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                <span class="text-center">Histori</span>
                            </div>
                        </div>
                        {{-- <div class="item-menu text-center">
                            <div class="menu-icon">
                                <a href="" class="orange" style="font-size: 40px;">
                                    <ion-icon name="location"></ion-icon>
                                </a>
                            </div>
                            <div class="menu-name">
                                Lokasi
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="section mt-2" id="presence-section">
            <div class="todaypresence">
                <div class="row">
                    <div class="col-6">
                        <div class="card gradasigreen">
                            <div class="card-body">
                                <div class="presencecontent">
                                    <div class="iconpresence">
                                        @if ($absensihariini != null)
                                            @php
                                                $path = Storage::url('uploads/absensi/' . $absensihariini->foto_in);
                                            @endphp
                                            <img src="{{ url($path) }}" alt="" class="imaged w48">
                                        @else
                                            <ion-icon name="image"></ion-icon>
                                        @endif
                                    </div>
                                    <div class="presencedetail">
                                        <h4 class="presencetitle">Masuk</h4>
                                        <span>{{ $absensihariini != null ? $absensihariini->jam_in : 'Belum Absen' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card gradasired">
                            <div class="card-body">
                                <div class="presencecontent">
                                    <div class="iconpresence">
                                        @if ($absensihariini != null && $absensihariini->jam_out != null)
                                            @php
                                                $path = Storage::url('uploads/absensi/' . $absensihariini->foto_out);
                                            @endphp
                                            <img src="{{ url($path) }}" alt="" class="imaged w48">
                                        @else
                                            <ion-icon name="image"></ion-icon>
                                        @endif
                                    </div>
                                    <div class="presencedetail">
                                        <h4 class="presencetitle">Pulang</h4>
                                        <span>{{ $absensihariini != null && $absensihariini->jam_out != null ? $absensihariini->jam_out : 'Belum Absen' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rekapabsensi">
                <h3>Rekap Absensi Bulan {{ $namabulan[(int) $bulanini] }} Tahun {{ $tahunini }}</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body text-center"
                                style="padding: 12px 12px !important; line-height:0.8rem">
                                <span class="badge bg-danger"
                                    style="position: absolute; top: 3px; right:10px font-size: 0.6rem;
                                 z-index: 999;">{{ $rekapabsensi->jmlhadir }}</span>
                                <ion-icon name="accessibility-outline" style="font-size: 1.6rem;"
                                    class="text-primary mb-1"></ion-icon>
                                <br>
                                <span style="font-size: 0.8rem; font-weight: 500">Hadir</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body text-center"
                                style="padding: 12px 12px !important; line-height:0.8rem">
                                <span class="badge bg-danger"
                                    style="position: absolute; top: 3px; right:10px font-size: 0.6rem; 
                                z-index: 999;">{{ $rekapizin->jmlizin }}</span>
                                <ion-icon name="newspaper-outline" style="font-size: 1.6rem;"
                                    class="text-success mb-1"></ion-icon>
                                <br>
                                <span style="font-size: 0.8rem; font-weight: 500">Izin</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body text-center"
                                style="padding: 12px 12px !important; line-height:0.8rem">
                                <span class="badge bg-danger"
                                    style="position: absolute; top: 3px; right:10px font-size: 0.6rem; 
                                z-index: 999;">{{ $rekapizin->jmlsakit }}</span>
                                <ion-icon name="medkit-outline" style="font-size: 1.6rem;"
                                    class="text-warning mb-1"></ion-icon>
                                <br>
                                <span style="font-size: 0.8rem; font-weight: 500">Sakit</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body text-center"
                                style="padding: 12px 12px !important; line-height:0.8rem">
                                <span class="badge bg-danger"
                                    style="position: absolute; top: 3px; right:10px font-size: 0.6rem; 
                                z-index: 999;">{{ $rekapabsensi->jmlterlambat }}</span>
                                <ion-icon name="alarm-outline" style="font-size: 1.6rem;"
                                    class="text-danger mb-1"></ion-icon>
                                <br>
                                <span style="font-size: 0.8rem; font-weight: 500">Telat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="presencetab mt-2">
                <div class="tab-pane fade show active" id="pilled" role="tabpanel">
                    <ul class="nav nav-tabs style1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                                Bulan Ini
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                                Leaderboard
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content mt-2" style="margin-bottom:100px;">
                    <div class="tab-pane fade show active" id="home" role="tabpanel">
                        <ul class="listview image-listview">
                            @php $jamPulangStandar = '16:00'; @endphp
                            @foreach ($historibulanini as $id)
                                @php
                                    $fotoInUrl = $id->foto_in ? Storage::url('uploads/absensi/' . $id->foto_in) : '';
                                    $fotoOutUrl = $id->foto_out ? Storage::url('uploads/absensi/' . $id->foto_out) : '';
                                    $jamInText = $id->jam_in != null ? $id->jam_in : 'Belum Absen';
                                    $jamOutText = $id->jam_out != null ? $id->jam_out : 'Belum Absen';
                                @endphp
                                <li data-foto-in="{{ $fotoInUrl ? url($fotoInUrl) : '' }}"
                                    data-foto-out="{{ $fotoOutUrl ? url($fotoOutUrl) : '' }}"
                                    data-jam-in="{{ $jamInText }}" data-jam-out="{{ $jamOutText }}">
                                    <div class="item">
                                        <div class="icon-box bg-primary">
                                            <ion-icon name="image-outline" role="img" class="md hydrated"
                                                aria-label="image outline"></ion-icon>
                                        </div>
                                        <div class="in in-history">
                                            {{-- Tanam juga di baris untuk fallback --}}
                                            <div style="display:none"
                                                data-foto-in="{{ $fotoInUrl ? url($fotoInUrl) : '' }}"
                                                data-foto-out="{{ $fotoOutUrl ? url($fotoOutUrl) : '' }}"></div>
                                            <div>{{ date('d-m-Y', strtotime($id->tgl_absensi)) }}</div>
                                            <span></span>
                                            <span class="jam-out-group">
                                                <a href="#" class="btn-detail-absen"
                                                    style="display:inline-block; font-size: .85rem;">
                                                    Lihat Detail
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel">
                        <ul class="listview image-listview">
                            @foreach ($leaderboard as $d)
                                <li>
                                    <div class="item">
                                        <img src="assets/img/sample/avatar/avatar1.jpg" alt="image"
                                            class="image">
                                        <div class="in">
                                            <div>
                                                <b>{{ $d->nama_lengkap }}</b><br>
                                                <small class="text-muted">{{ $d->jabatan }}</small>
                                            </div>
                                            <span
                                                class="badge {{ $d->jam_in < '07:00' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $d->jam_in }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- HOME -->


    <!-- BOTTOM NAVIGATION MENU -->
    @include('layouts.bottomNav')




    <!-- ///////////// SCRIPT APP ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <!-- Base Js File -->
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <script src="{{ asset('offline/idb.js') }}" defer></script>
    <script src="{{ asset('offline/offline-sync.js') }}" defer></script>

    <!-- Detail Absensi Modal -->
    <div class="modal fade" id="modal-detail-histori" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Foto Masuk</label>
                        <img data-role="foto-in" src="" class="img-fluid" alt="Foto Masuk">
                        <div class="mt-2 d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
                            <span class="badge badge-success" data-role="badge-in"></span>
                            <small data-role="lateness" class="text-muted"></small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Foto Pulang</label>
                        <img data-role="foto-out" src="" class="img-fluid" alt="Foto Pulang">
                        <div class="mt-2 d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
                            <span class="badge badge-danger" data-role="badge-out"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            var chart = am4core.create("chartdiv", am4charts.PieChart3D);
            chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

            chart.legend = new am4charts.Legend();

            chart.data = [{
                    country: "Hadir",
                    litres: 501.9
                },
                {
                    country: "Sakit",
                    litres: 301.9
                },
                {
                    country: "Izin",
                    litres: 201.1
                },
                {
                    country: "Terlambat",
                    litres: 165.8
                },
            ];



            var series = chart.series.push(new am4charts.PieSeries3D());
            series.dataFields.value = "litres";
            series.dataFields.category = "country";
            series.alignLabels = false;
            series.labels.template.text = "{value.percent.formatNumber('#.0')}%";
            series.labels.template.radius = am4core.percent(-40);
            series.labels.template.fill = am4core.color("white");
            series.colors.list = [
                am4core.color("#1171ba"),
                am4core.color("#fca903"),
                am4core.color("#37db63"),
                am4core.color("#ba113b"),
            ];
        }); // end am4core.ready()
    </script>

    <script>
        $(function() {
            $(document).on('click', '.btn-detail-absen', function(e) {
                e.preventDefault();
                var $row = $(this).closest('.in-history');
                var $item = $(this).closest('li');

                var fotoIn = $item.data('foto-in') || $row.data('foto-in') || '';
                var fotoOut = $item.data('foto-out') || $row.data('foto-out') || '';
                var jamIn = $item.data('jam-in') || '';
                var jamOut = $item.data('jam-out') || '';

                // Lateness vs 07:00:00
                function parseTime(s) {
                    var p = String(s || '').split(':');
                    if (p.length < 2) return null;
                    var h = parseInt(p[0] || '0', 10),
                        m = parseInt(p[1] || '0', 10),
                        sec = parseInt(p[2] || '0', 10);
                    if (isNaN(h) || isNaN(m) || isNaN(sec)) return null;
                    return h * 3600 + m * 60 + sec;
                }
                var std = parseTime('07:00:00');
                var inSec = parseTime(jamIn);
                var latenessText = '';
                if (inSec != null && std != null && inSec > std) {
                    var diff = inSec - std;
                    var hh = Math.floor(diff / 3600);
                    var mm = Math.floor((diff % 3600) / 60);
                    var ss = diff % 60;
                    var parts = [];
                    if (hh > 0) parts.push(String(hh).padStart(2, '0') + 'j');
                    parts.push(String(mm).padStart(2, '0') + 'm');
                    parts.push(String(ss).padStart(2, '0') + 'd');
                    latenessText = 'Terlambat ' + parts.join(' ');
                } else if (inSec != null) {
                    latenessText = 'Tepat waktu';
                }

                var $modal = $('#modal-detail-histori');
                $modal.find('img[data-role="foto-in"]').attr('src', fotoIn || '');
                $modal.find('img[data-role="foto-out"]').attr('src', fotoOut || '');
                $modal.find('[data-role="badge-in"]').text(jamIn || '—');
                $modal.find('[data-role="badge-out"]').text(jamOut || '—');
                $modal.find('[data-role="lateness"]').text(latenessText);
                $modal.modal('show');
            });
        });
    </script>

</body>

</html>
