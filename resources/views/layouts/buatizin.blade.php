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
    {{-- CSS TANGGAL IZIN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
    <style>
        .datepicker-modal {
            max-height: 430px !important
        }
    </style>
    {{-- CSS TANGGAL IZIN --}}
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
        <div class="pageTitle">Form Izin</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->

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

    <!-- SCRIPT FORM TANGGAL IZIN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>

    {{-- SCRIPT NOTIFIKASI SWEET ALERT (self-hosted) --}}
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('offline/idb.js') }}"></script>
    <script src="{{ asset('offline/offline-sync.js') }}"></script>
    
    <script>
        var currYear = (new Date()).getFullYear();

        $(document).ready(function() {
            $(".datepicker").datepicker({
                format: "yyyy-mm-dd"
            });

            // Toggle upload bukti when status = sakit
            $("#status").on('change', function() {
                if ($(this).val() === 's') {
                    $("#wrap-bukti").show();
                } else {
                    $("#wrap-bukti").hide();
                    $("#bukti_sakit").val("");
                }
            });

            // Notify if total pengajuan bulan ini sudah 3x
            function cekTotalIzinBulanIni() {
                $.ajax({
                    type: 'POST',
                    url: '/cekpengajuanizin',
                    data: {
                        _token: "{{ csrf_token() }}",
                        mode: 'countmonth'
                    },
                    cache: false,
                    success: function(total) {
                        if (parseInt(total, 10) >= 3) {
                            Swal.fire({
                                title: 'Perhatian',
                                text: 'Anda sudah mengajukan izin sebanyak 3x bulan ini.',
                                icon: 'warning'
                            });
                        }
                    }
                });
            }

            cekTotalIzinBulanIni();

            $("#formizin").submit(function(e) {
                // Audit fields for offline-first + idempotency
                try {
                    var cu = document.getElementById('client_uuid');
                    var ca = document.getElementById('captured_at');
                    if (cu && !cu.value) {
                        cu.value = (window.SibaloIDB && SibaloIDB.uid) ? SibaloIDB.uid() : ('q_' + Date.now());
                    }
                    if (ca && !ca.value) {
                        ca.value = new Date().toISOString();
                    }
                } catch (err) {}

                var dari = $("#dari").val();
                var sampai = $("#sampai").val();
                var status = $("#status").val();
                var keterangan = $("#keterangan").val();

                if (dari === "" || sampai === "") {
                    Swal.fire({
                        title: 'Oops !',
                        text: 'Tanggal dari dan sampai harus diisi',
                        icon: 'warning'
                    });
                    return false;
                }

                if (new Date(dari) > new Date(sampai)) {
                    Swal.fire({
                        title: 'Oops !',
                        text: 'Tanggal dari tidak boleh lebih besar dari tanggal sampai',
                        icon: 'warning'
                    });
                    return false;
                }

                if (status === "") {
                    Swal.fire({
                        title: 'Oops !',
                        text: 'Status harus diisi',
                        icon: 'warning'
                    });
                    return false;
                }

                if (keterangan === "") {
                    Swal.fire({
                        title: 'Oops !',
                        text: 'Keterangan harus diisi',
                        icon: 'warning'
                    });
                    return false;
                }

                // Offline mode: queue request and auto-sync later
                if (!navigator.onLine && window.SibaloOffline && typeof window.SibaloOffline.enqueueForm === 'function') {
                    e.preventDefault();
                    window.SibaloOffline.enqueueForm(this).then(function(){
                        // redirect to list page (will show current list; queued items will sync later)
                        setTimeout(function(){ window.location.href = '/absensi/izin'; }, 800);
                    });
                    return false;
                }
            })
        });
    </script>
    <!-- SCRIPT FORM TANGGAL IZIN -->

    {{-- FORM IZIN --}}
    <div class="row" style="margin-top:70px">
        <div class="col">
            <form method="POST" action="/absensi/storeizin" id="formizin" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="client_uuid" id="client_uuid">
                <input type="hidden" name="captured_at" id="captured_at">
                <div class="form-group">
                    <input type="text" id="dari" name="dari" class="form-control datepicker"
                        placeholder="Dari Tanggal (yyyy-mm-dd)">
                </div>
                <div class="form-group">
                    <input type="text" id="sampai" name="sampai" class="form-control datepicker"
                        placeholder="Sampai Tanggal (yyyy-mm-dd)">
                </div>
                <div class="form-group">
                    <select name="status" id="status" class="form-control" required>
                        <option value="" disabled selected>Pilih status</option>
                        <option value="i">Izin</option>
                        <option value="s">Sakit</option>
                    </select>
                </div>
                <div class="form-group" id="wrap-bukti" style="display:none">
                    <input type="file" name="bukti_sakit" id="bukti_sakit" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="form-group">
                    <textarea name="keterangan" id="keterangan" cols="30" rows="5" class="form-control"
                        placeholder="Keterangan..."></textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary w-100">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    {{-- FORM IZIN --}}


    <!-- BOTTOM NAVIGATION MENU -->
    @include('layouts.bottomNav')

</body>

</html>
