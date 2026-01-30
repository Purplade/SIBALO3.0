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
</head>

<body>
    <script src="{{ asset('dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">

        <!-- Sidebar -->
        @include('dashboard.sidebar')

        <!-- Navbar -->
        @include('dashboard.header')

        {{-- TITLE PEGAWAI --}}
        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <!-- Page pre-title -->
                            <h2 class="page-title">
                                Data Pegawai
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            {{-- TITLE DATA MASTER PEGAWAI --}}

            {{-- KONTEN --}}
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            @if (Session::get('success'))
                                                <div class="alert alert-success">
                                                    {{ Session::get('success') }}
                                                </div>
                                            @endif

                                            @if (Session::get('warning'))
                                                <div class="alert alert-warning">
                                                    {{ Session::get('warning') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="#" class="btn btn-primary" id="btntambahpegawai">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M12 5l0 14" />
                                                    <path d="M5 12l14 0" />
                                                </svg>
                                                Tambah Data
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <form action="/pegawai" method="GET">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <input type="text" name="nama_pegawai" id="nama_pegawai"
                                                                class="form-control" placeholder="Nama Pegawai"
                                                                value="{{ Request('nama_pegawai') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-search">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                                                    <path d="M21 21l-6 -6" />
                                                                </svg>
                                                                Cari
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>NIP</th>
                                                        <th>Nama</th>
                                                        <th>Jabatan</th>
                                                        <th>No. HP</th>
                                                        <th>Foto</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($pegawai as $d)
                                                        @php
                                                            $path = Storage::url('pegawai/' . $d->foto);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $loop->iteration + $pegawai->firstItem() - 1 }}</td>
                                                            <td>{{ $d->nik }}</td>
                                                            <td>{{ $d->nama_lengkap }}</td>
                                                            <td>{{ $d->jabatan }}</td>
                                                            <td>{{ $d->no_hp }}</td>
                                                            <td>
                                                                @php
                                                                    $fotoUrl = null;
                                                                    if (!empty($d->foto)) {

                                                                        $pathPegawai = 'pegawai/' . $d->foto; // legacy
                                                                    $pathUploadsPegawai = 'uploads/pegawai/' . $d->foto; // target baru
                                                                        if (Storage::disk('public')->exists($pathPegawai)) {
                                                                            $fotoUrl = Storage::url($pathPegawai);
                                                                        } elseif (Storage::disk('public')->exists($pathUploadsPegawai)) {
                                                                            $fotoUrl = Storage::url($pathUploadsPegawai);
                                                                        }
                                                                    }
                                                                @endphp
                                                                @if ($fotoUrl)
                                                                    <img src="{{ $fotoUrl }}" class="avatar" alt="">
                                                                @else
                                                                    <img src="{{ asset('assets/img/profilkosong.png') }}" class="avatar" alt="">
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    {{-- Tombol Edit - PERBAIKI STRUKTUR --}}
                                                                    <a href="#" class="btn btn-info btn-sm edit"
                                                                        nik="{{ $d->nik }}">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            width="24" height="24"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-edit">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                                            <path
                                                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                                            <path d="M16 5l3 3" />
                                                                        </svg>
                                                                    </a>

                                                                    {{-- Tombol Hapus - PERBAIKI ID --}}
                                                                    <form action="/pegawai/{{ $d->nik }}/delete"
                                                                        method="POST" style="margin-left:5px">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm delete-confirm">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                width="24" height="24"
                                                                                viewBox="0 0 24 24" fill="none"
                                                                                stroke="currentColor" stroke-width="2"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                                                <path stroke="none" d="M0 0h24v24H0z"
                                                                                    fill="none" />
                                                                                <path d="M4 7l16 0" />
                                                                                <path d="M10 11l0 6" />
                                                                                <path d="M14 11l0 6" />
                                                                                <path
                                                                                    d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                                <path
                                                                                    d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            {{ $pegawai->links('pagination::bootstrap-5') }}
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
        <div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
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
                                    <input type="radio" name="report-type" value="1"
                                        class="form-selectgroup-input" checked>
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
                                            <span class="d-block text-muted">Insert charts and additional advanced
                                                analyses
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


        {{-- FORM TAMBAH DATA PEGAWAI --}}
        <div class="modal modal-blur fade" id="modal-inputpegawai" tabindex="-1" role="dialog"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Pegawai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="/pegawai/store" method="POST" id="formpegawai" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-barcode">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M4 7v-1a2 2 0 0 1 2 -2h2" />
                                                <path d="M4 17v1a2 2 0 0 0 2 2h2" />
                                                <path d="M16 4h2a2 2 0 0 1 2 2v1" />
                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-1" />
                                                <path d="M5 11h1v2h-1z" />
                                                <path d="M10 11l0 2" />
                                                <path d="M14 11h1v2h-1z" />
                                                <path d="M19 11l0 2" />
                                            </svg>
                                        </span>
                                        <input type="text" value="" class="form-control" name="nik"
                                            placeholder="NIP">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-user">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                            </svg>
                                        </span>
                                        <input type="text" value="" class="form-control"
                                            name="nama_lengkap" placeholder="Nama Lengkap">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-buildings">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15" />
                                                <path d="M16 8h2c1 0 2 1 2 2v11" />
                                                <path d="M3 21h18" />
                                                <path d="M10 12v0" />
                                                <path d="M10 16v0" />
                                                <path d="M10 8v0" />
                                                <path d="M7 12v0" />
                                                <path d="M7 16v0" />
                                                <path d="M7 8v0" />
                                                <path d="M17 12v0" />
                                                <path d="M17 16v0" />
                                            </svg>
                                        </span>
                                        <input type="text" value="" class="form-control" name="jabatan"
                                            placeholder="Jabatan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-phone">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                            </svg>
                                        </span>
                                        <input type="text" value="" class="form-control" name="no_hp"
                                            placeholder="Nomor Telepon">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="file" name="foto" class="form-control">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary w-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-telegram">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" />
                                            </svg>Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- FORM TAMBAH DATA PEGAWAI --}}

        {{-- FORM EDIT DATA PEGAWAI --}}
        <div class="modal modal-blur fade" id="modal-editpegawai" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Pegawai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editform">

                    </div>
                </div>
            </div>
        </div>
        {{-- FORM EDIT DATA PEGAWAI --}}

        <!-- Libs JS -->
        <script src="{{ asset('dist/libs/apexcharts/dist/apexcharts.min.js?1674944402') }}" defer></script>
        <script src="{{ asset('dist/libs/jsvectormap/dist/js/jsvectormap.min.js?1674944402') }}" defer></script>
        <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world.js?1674944402') }}" defer></script>
        <script src="{{ asset('dist/libs/jsvectormap/dist/maps/world-merc.js?1674944402') }}" defer></script>
        <!-- Tabler Core -->
        <script src="{{ asset('dist/js/tabler.min.js?1674944402') }}" defer></script>
        <script src="{{ asset('dist/js/demo.min.js?1674944402') }}" defer></script>
        {{-- JQUERY --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        {{-- notif sweet alert (self-hosted to avoid tracking-prevention warnings) --}}
        <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

        {{-- FUNGSI TOMBOL TAMBAH PEGAWAI --}}
        <script>
            $(function() {
                // Fungsi tombol tambah pegawai - HAPUS alert("test")
                $("#btntambahpegawai").click(function() {
                    $("#modal-inputpegawai").modal("show");
                });

                // Fungsi tombol edit
                $(".edit").click(function(e) {
                    e.preventDefault(); // Mencegah perilaku default link
                    var nik = $(this).attr('nik');
                    $.ajax({
                        type: 'POST',
                        url: '/pegawai/edit',
                        cache: false,
                        data: {
                            _token: "{{ csrf_token() }}",
                            nik: nik
                        },
                        success: function(respond) {
                            $("#editform").html(respond);
                            $("#modal-editpegawai").modal("show");
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            alert('Error loading edit form');
                        }
                    });
                });

                // Reset password pegawai via SweetAlert2
                $(document).on('click', '.btn-reset-password-pegawai', function(e) {
                    e.preventDefault();
                    var nik = $(this).data('nik');
                    Swal.fire({
                        title: 'Anda yakin ingin mereset password ini?',
                        text: 'Password akan diubah menjadi pegawai123',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, reset'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'POST',
                                url: '/pegawai/' + encodeURIComponent(nik) + '/resetpassword',
                                data: { _token: "{{ csrf_token() }}" },
                                success: function(res) {
                                    Swal.fire('Berhasil', 'Password telah direset ke pegawai123', 'success');
                                    $("#editform").find('input[name="password"]').val('pegawai123');
                                },
                                error: function(xhr) {
                                    Swal.fire('Gagal', 'Tidak dapat mereset password', 'error');
                                }
                            });
                        }
                    });
                });

                // Fungsi tombol hapus - PERBAIKI SELECTOR
                $(".btn-group").on('click', '.delete-confirm', function(e) {
                    var form = $(this).closest('form');
                    e.preventDefault();
                    Swal.fire({
                        title: "Apakah Anda Yakin mau menghapus Data ini ?",
                        text: "Jika Ya Data Akan Terhapus Permanen",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Hapus"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                // Validasi form
                $("#formpegawai").submit(function() {
                    var nik = $("input[name='nik']").val();
                    var nama_lengkap = $("input[name='nama_lengkap']").val();
                    var jabatan = $("input[name='jabatan']").val();
                    var no_hp = $("input[name='no_hp']").val();

                    if (nik == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'NIK harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='nik']").focus();
                        });
                        return false;
                    } else if (nama_lengkap == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Nama Lengkap harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='nama_lengkap']").focus();
                        });
                        return false;
                    } else if (jabatan == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Jabatan harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='jabatan']").focus();
                        });
                        return false;
                    } else if (no_hp == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Nomor Telepon harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='no_hp']").focus();
                        });
                        return false;
                    }
                });
            });
        </script>
</body>

</html>
