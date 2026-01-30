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

        {{-- TITLE ADMIN --}}
        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <!-- Page pre-title -->
                            <h2 class="page-title">
                                Data Admin
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            {{-- TITLE DATA MASTER ADMIN --}}

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
                                            <a href="#" class="btn btn-primary" id="btntambahadmin">
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
                                            <form action="/admin" method="GET">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <input type="text" name="nama_admin" id="nama_admin"
                                                                class="form-control" placeholder="Nama Admin"
                                                                value="{{ Request('nama_admin') }}">
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
                                                        <th>Nama</th>
                                                        <th>Email</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $currentUserEmail = Auth::guard('user')->user()->email ?? null;
                                                        $rowNumber = $admin->firstItem() - 1;
                                                    @endphp
                                                    @foreach ($admin as $d)
                                                        @if ($d->email === $currentUserEmail)
                                                            @continue
                                                        @endif
                                                        @php
                                                            $path = Storage::url('uploads/admin/' . $d->foto);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ ++$rowNumber }}</td>
                                                            <td>{{ $d->name }}</td>
                                                            <td>{{ $d->email }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    {{-- Tombol Edit - PERBAIKI STRUKTUR --}}
                                                                    <a href="#" class="btn btn-info btn-sm edit"
                                                                        data-name="{{ $d->name }}">
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
                                                                    <form action="/admin/{{ $d->name }}/delete"
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
                                            {{ $admin->links('') }}
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


        {{-- FORM TAMBAH DATA ADMIN --}}
        <div class="modal modal-blur fade" id="modal-inputadmin" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="/admin/store" method="POST" id="formadmin" enctype="multipart/form-data">
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
                                        <input type="text" value="" class="form-control" name="name"
                                            placeholder="Nama Admin">
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
                                        <input type="text" value="" class="form-control" name="email"
                                            placeholder="Email">
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
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-lock-access">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                                                <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                                                <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                                                <path
                                                    d="M8 11m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v3a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" />
                                                <path d="M10 11v-2a2 2 0 1 1 4 0v2" />
                                            </svg>
                                        </span>
                                        <input type="text" value="" class="form-control" name="password"
                                            placeholder="Password">
                                    </div>
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
        {{-- FORM TAMBAH DATA ADMIN --}}

        {{-- FORM EDIT DATA ADMIN --}}
        <div class="modal modal-blur fade" id="modal-editadmin" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editform">

                    </div>
                </div>
            </div>
        </div>
        {{-- FORM EDIT DATA ADMIN --}}

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

        {{-- FUNGSI TOMBOL TAMBAH ADMIN --}}
        <script>
            $(function() {
                // Fungsi tombol tambah admin - HAPUS alert("test")
                $("#btntambahadmin").click(function() {
                    $("#modal-inputadmin").modal("show");
                });

                // Fungsi tombol edit
                $(".edit").click(function(e) {
                    e.preventDefault(); // Mencegah perilaku default link
                    var name = $(this).data('name');
                    $.ajax({
                        type: 'POST',
                        url: '/admin/edit',
                        cache: false,
                        data: {
                            _token: "{{ csrf_token() }}",
                            name: name
                        },
                        success: function(respond) {
                            $("#editform").html(respond);
                            $("#modal-editadmin").modal("show");
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            alert('Error loading edit form');
                        }
                    });
                });

                // Reset password via SweetAlert2
                $(document).on('click', '.btn-reset-password', function(e) {
                    e.preventDefault();
                    var name = $(this).data('name');
                    Swal.fire({
                        title: 'Anda yakin ingin mereset password ini?',
                        text: 'Password akan diubah menjadi admin123',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, reset'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'POST',
                                url: '/admin/' + encodeURIComponent(name) + '/resetpassword',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    Swal.fire('Berhasil',
                                        'Password telah direset ke admin123', 'success');
                                    // Jika modal edit sedang terbuka, set field password ke admin123
                                    $("#editform").find('input[name="password"]').val(
                                        'admin123');
                                },
                                error: function(xhr) {
                                    Swal.fire('Gagal', 'Tidak dapat mereset password',
                                        'error');
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
                $("#formadmin").submit(function() {
                    var name = $("input[name='name']").val();
                    var email = $("input[name='email']").val();
                    var password = $("input[name='password']").val();

                    if (name == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Nama Admin harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='name']").focus();
                        });
                        return false;
                    } else if (email == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Email harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='email']").focus();
                        });
                        return false;
                    } else if (password == "") {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Password harus Di isi',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            $("input[name='password']").focus();
                        });
                        return false;
                    }
                });
            });
        </script>
</body>

</html>
