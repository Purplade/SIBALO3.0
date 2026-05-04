<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Konfigurasi Jam Absensi</title>
    <link href="{{ asset('dist/css/tabler.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-flags.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-payments.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-vendors.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/demo.min.css?1674944402') }}" rel="stylesheet" />
</head>

<body>
    <script src="{{ asset('dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">
        @include('dashboard.sidebar')
        @include('dashboard.header')

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">KONFIGURASI JAM ABSENSI</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    @if (Session::get('success'))
                                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                                    @endif
                                    @if (Session::get('warning'))
                                        <div class="alert alert-warning">{{ Session::get('warning') }}</div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="/konfigurasijamabsensi/update" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label" for="jam_masuk_mulai">Jam mulai absen masuk</label>
                                            <input
                                                type="time"
                                                class="form-control"
                                                id="jam_masuk_mulai"
                                                name="jam_masuk_mulai"
                                                value="{{ old('jam_masuk_mulai', isset($konfigurasi) ? substr($konfigurasi->jam_masuk_mulai, 0, 5) : '06:00') }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="jam_pulang_mulai">Jam mulai absen pulang</label>
                                            <input
                                                type="time"
                                                class="form-control"
                                                id="jam_pulang_mulai"
                                                name="jam_pulang_mulai"
                                                value="{{ old('jam_pulang_mulai', isset($konfigurasi) ? substr($konfigurasi->jam_pulang_mulai, 0, 5) : '12:00') }}"
                                                required>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            Simpan Konfigurasi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('dashboard.footer')
        </div>
    </div>

    <script src="{{ asset('dist/js/tabler.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/js/demo.min.js?1674944402') }}" defer></script>
</body>

</html>
