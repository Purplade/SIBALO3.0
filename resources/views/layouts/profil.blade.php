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
    <style>
        html,
        body {
            overflow-x: hidden;
            overflow-y: auto;
        }

        .form-container {
            /* extra space so submit button isn't covered by bottom navigation */
            padding-bottom: 180px;
        }

        form {
            max-width: 100%;
            overflow: hidden;
        }
    </style>
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
        <div class="pageTitle">Profil</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->

    {{-- INFO PEGAWAI --}}
    <div class="section form-container" style="margin-top:4rem">
        <form action="/absensi/{{ $pegawai->nik }}/updateprofile" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
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
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>NIK</strong>
                                <span>{{ $pegawai->nik }}</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Nama Lengkap</strong>
                                <span>{{ $pegawai->nama_lengkap }}</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Jabatan</strong>
                                <span>{{ $pegawai->jabatan }}</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Nomor Telepon</strong>
                            </div>
                            <div class="mt-2 d-flex align-items-center" style="gap:10px;">
                                <input type="text" class="form-control" name="no_hp"
                                    value="{{ old('no_hp', $pegawai->no_hp) }}"
                                    placeholder="Nomor telepon" autocomplete="tel" inputmode="tel"
                                    style="flex:1; min-width: 0;">
                                <button type="submit" class="btn btn-outline-primary"
                                    name="update_field" value="no_hp">Update</button>
                            </div>
                            <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah nomor telepon.</small>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Password</strong>
                            </div>
                            <div class="mt-2 d-flex align-items-center" style="gap:10px;">
                                <input type="password" class="form-control" name="password"
                                    value=""
                                    placeholder="Masukkan password baru" autocomplete="new-password"
                                    style="flex:1; min-width: 0;">
                                <button type="submit" class="btn btn-outline-primary"
                                    name="update_field" value="password">Update</button>
                            </div>
                            <small class="text-muted d-block mt-1">Untuk keamanan, password lama tidak ditampilkan. Isi hanya jika ingin mengganti.</small>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Foto</strong>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="file" name="foto" id="foto" class="form-control" accept=".png,.jpg,.jpeg">
                                    <small class="text-muted d-block mt-1" id="foto-filename">
                                        @if (!empty($pegawai->foto))
                                            Foto saat ini: <strong>{{ $pegawai->foto }}</strong>
                                        @else
                                            Belum ada foto
                                        @endif
                                    </small>
                                    <input type="hidden" name="old_foto" value="{{ $pegawai->foto }}">
                                    <input type="hidden" name="hapus_foto" id="hapus_foto" value="0">
                                </div>
                            </div>
                            @if (!empty($pegawai->foto))
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-outline-danger" id="btn-hapus-foto">
                                            Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        {{-- <div class="text-center mb-3">
                        @php
                            $pegawai = Auth::guard('pegawai')->user();
                            $hasPhoto = !empty($pegawai->foto);
                            $currentPhotoUrl = asset('assets/img/sample/avatar/avatar1.jpg');
                            if ($hasPhoto) {
                                $pathUploads = 'uploads/pegawai/' . $pegawai->foto;
                                $pathLegacy = 'pegawai/' . $pegawai->foto;
                                if (Storage::disk('public')->exists($pathUploads)) {
                                    $currentPhotoUrl = Storage::url($pathUploads);
                                } elseif (Storage::disk('public')->exists($pathLegacy)) {
                                    $currentPhotoUrl = Storage::url($pathLegacy);
                                } else {
                                    $hasPhoto = false;
                                }
                            }
                        @endphp
                        <label>{{ $hasPhoto ? 'Lihat Foto Profil' : 'Upload Foto Profil' }}</label>
                        <div class="custom-file-upload" id="fileUpload1">
                            <input type="file" name="foto" id="fileuploadInput" accept=".png, .jpg, .jpeg">
                            <label for="fileuploadInput"
                                style="position:relative; display:block; width:100%; height:200px; border-radius:12px; overflow:hidden;"
                                data-has-photo="{{ $hasPhoto ? '1' : '0' }}">
                                <img id="photoPreview" src="{{ $currentPhotoUrl }}" data-original="{{ $currentPhotoUrl }}"
                                    alt="preview"
                                    style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; border-radius:12px;" />
                                <div id="uploadOverlay"
                                    style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#6c757d; background:rgba(255,255,255,0.0); pointer-events:auto; cursor:pointer;">
                                    <div style="text-align:center;">
                                        @if ($hasPhoto)
                                            <ion-icon name="image-outline" style="font-size:28px;"></ion-icon>
                                            <div>Preview</div>
                                        @else
                                            <ion-icon name="cloud-upload-outline" style="font-size:28px;"></ion-icon>
                                            <div>Tap to Upload</div>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        </div>
                        <input type="hidden" name="hapus_foto" id="hapus_foto" value="0">
                        @if ($hasPhoto)
                            <div class="form-group boxed" style="margin-top:.5rem;">
                                <div class="input-wrapper">
                                    <button type="button" id="btn-delete-photo" class="btn btn-danger w-100">Hapus Foto
                                        Terpilih</button>
                                </div>
                            </div>
                        @endif
                    </div> --}}
                        <div class="list-group-item">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <ion-icon name="refresh-outline"></ion-icon>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

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
    <script src="{{ asset('offline/idb.js') }}" defer></script>
    <script src="{{ asset('offline/offline-sync.js') }}" defer></script>
    <script>
        (function () {
            var fotoInput = document.getElementById('foto');
            var fotoFilename = document.getElementById('foto-filename');
            var hapusFoto = document.getElementById('hapus_foto');
            var btnHapusFoto = document.getElementById('btn-hapus-foto');
            var form = document.querySelector('form');

            if (fotoInput) {
                fotoInput.addEventListener('change', function () {
                    if (hapusFoto) hapusFoto.value = '0';
                    var name = (this.files && this.files[0]) ? this.files[0].name : '';
                    if (fotoFilename) {
                        fotoFilename.innerHTML = name ? ('File dipilih: <strong>' + name + '</strong>') : (fotoFilename.getAttribute('data-default') || '');
                    }
                });
            }

            if (btnHapusFoto) {
                btnHapusFoto.addEventListener('click', function () {
                    if (!form) return;
                    if (!confirm('Hapus foto profil?')) return;
                    if (hapusFoto) hapusFoto.value = '1';
                    if (fotoInput) fotoInput.value = '';
                    form.submit();
                });
            }
        })();
    </script>
</body>

</html>
