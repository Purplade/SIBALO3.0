<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
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
        <div class="pageTitle">Pengajuan Izin/Sakit</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->

    {{-- Notif Berhasil --}}
    <div class="row" style="margin-top: 70px">
        <div class="col">
            @php
                $messagesuccess = Session::get('success');
                $messageerror = Session::get('error');
            @endphp
            @if (Session::get('success'))
                <div class="alert alert-success">
                    {{ $messagesuccess }}
                </div>
            @endif
            @if (Session::get('error'))
                <div class="alert alert-danger">
                    {{ $messageerror }}
                </div>
            @endif
        </div>
    </div>
    {{-- Notif Berhasil --}}

    {{-- DATA IZIN (Grouped by Range) --}}
    <div class="row">
        <div class="col">
            @foreach ($dataizin as $d)
                <ul class="listview image-listview">
                    <li>
                        <div class="item">
                            <div class="in">
                                <div>
                                    @php
                                        $dariFmt = date('d-m-Y', strtotime($d->tgl_izin))
                                    @endphp
                                @if (!empty($d->izin_sampai))
                                    @php $sampaiFmt = date('d-m-Y', strtotime($d->izin_sampai)) @endphp
                                        <b>{{ $dariFmt }} s/d {{ $sampaiFmt }}</b><br>
                                    @else
                                        <b>{{ $dariFmt }}</b><br>
                                    @endif
                                    <small class="text-muted">{{ $d->keterangan }}</small>
                                </div>
                                @if ($d->status_approved == 0)
                                    <span class="badge bg-warning">Menunggu Persetujuan</span>
                                @elseif ($d->status_approved == 1)
                                    <span class="badge bg-success">Disetujui</span>
                                @elseif ($d->status_approved == 2)
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </div>
                        </div>
                    </li>
                </ul>
            @endforeach
        </div>
    </div>
    {{-- DATA IZIN (Grouped by Range) --}}

    {{-- BATALKAN IZIN/SAKIT --}}
    <div class="row">
        <div class="col">
            <small class="text-muted">Batalkan pengajuan yang belum disetujui.</small>
            <ul class="listview image-listview">
                @foreach ($dataizin as $d)
                    <li>
                        <div class="item">
                            <div class="in">
                                <div>
                                    @php $dariFmt = date('d-m-Y', strtotime($d->tgl_izin)); @endphp
                                    @if (!empty($d->izin_sampai))
                                        @php $sampaiFmt = date('d-m-Y', strtotime($d->izin_sampai)); @endphp
                                        <b>{{ $dariFmt }} s/d {{ $sampaiFmt }}</b>
                                    @else
                                        <b>{{ $dariFmt }}</b>
                                    @endif
                                    <small class="text-muted d-block">{{ $d->keterangan }}</small>
                                </div>
                                <div>
                                    @if ($d->status_approved == 0 || $d->status_approved == null)
                                        <a href="#" data-url="/absensi/{{ $d->id }}/batalkan" class="btn btn-sm btn-outline-danger btn-batalkan">Batalkan</a>
                                    @else
                                        <span class="badge bg-secondary">Tidak dapat dibatalkan</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    {{-- BATALKAN IZIN/SAKIT --}}

    {{-- TOMBOL TAMBAH IZIN --}}
    <div class="fab-button bottom-right" style="margin-bottom:70px">
        <a href="/absensi/buatizin" class="fab">
            <ion-icon name="add-outline"></ion-icon>
        </a>
    </div>
    {{-- TOMBOL TAMBAH IZIN --}}


    <!-- BOTTOM NAVIGATION MENU -->
    @include('layouts.bottomNav')


    <!-- ///////////// SCRIPT APP ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons (self-hosted to avoid tracking-prevention warnings) -->
    <script type="module" src="{{ asset('vendor/ionicons/ionicons.esm.js') }}"></script>
    <script nomodule src="{{ asset('vendor/ionicons/ionicons.js') }}"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <script src="{{ asset('offline/idb.js') }}" defer></script>
    <script src="{{ asset('offline/offline-sync.js') }}" defer></script>
    {{-- SCRIPT NOTIFIKASI SWEET ALERT (self-hosted) --}}
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        $(function(){
            $(document).on('click', '.btn-batalkan', function(e){
                e.preventDefault();
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Batalkan pengajuan ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, batalkan',
                    cancelButtonText: 'Tidak',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
</body>

</html>
