<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>SIBALO</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="manifest" href="__manifest.json">
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
        <div class="pageTitle">Edit Profil</div>
        <div class="right"></div>
    </div>
    <!-- HEADER -->

    {{-- EDIT PROFILE --}}
    <div class="row" style="margin-top:4rem">
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
    <form action="/absensi/{{ $pegawai->nik }}/updateprofile" method="POST" enctype="multipart/form-data" style="margin-top:1rem">
        @csrf
        <div class="col">
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <input type="text" class="form-control" value="{{ $pegawai->no_hp }}" name="no_hp" placeholder="No. HP"
                        autocomplete="off">
                </div>
            </div>
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <input type="password" class="form-control" name="password" placeholder="Password"
                        autocomplete="off">
                </div>
            </div>
            <div class="custom-file-upload" id="fileUpload1">
                <input type="file" name="foto" id="fileuploadInput" accept=".png, .jpg, .jpeg">
                @php
                    $hasPhoto = !empty($pegawai->foto);
                    if ($hasPhoto) {
                        $pathUploads = 'uploads/pegawai/' . $pegawai->foto; // lokasi baru
                        $pathLegacy = 'pegawai/' . $pegawai->foto; // lokasi lama (fallback)
                        if (Storage::disk('public')->exists($pathUploads)) {
                            $currentPhotoUrl = Storage::url($pathUploads);
                        } elseif (Storage::disk('public')->exists($pathLegacy)) {
                            $currentPhotoUrl = Storage::url($pathLegacy);
                        } else {
                            $currentPhotoUrl = asset('assets/img/sample/avatar/avatar1.jpg');
                            $hasPhoto = false;
                        }
                    } else {
                        $currentPhotoUrl = asset('assets/img/sample/avatar/avatar1.jpg');
                    }
                @endphp
                <label for="fileuploadInput" style="position:relative; display:block; width:100%; height:200px; border-radius:12px; overflow:hidden;" data-has-photo="{{ $hasPhoto ? '1' : '0' }}">
                    <img id="photoPreview"
                        src="{{ $currentPhotoUrl }}"
                        data-original="{{ $currentPhotoUrl }}"
                        alt="preview"
                        style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; border-radius:12px;" />
                    <div id="uploadOverlay" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#6c757d; background:rgba(255,255,255,0.0); pointer-events:auto; cursor:pointer;">
                        <div style="text-align:center;">
                            @if($hasPhoto)
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
            @if($hasPhoto)
            <div class="form-group boxed" style="margin-top:.5rem;">
                <div class="input-wrapper">
                    <button type="button" id="btn-delete-photo" class="btn btn-danger w-100">Hapus Foto Terpilih</button>
                </div>
            </div>
            @endif
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <div class="d-grid" style="gap:.5rem;">
                        <button type="submit" id="btn-update" class="btn btn-primary w-100">
                            <ion-icon name="refresh-outline"></ion-icon>
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- EDIT PROFILE --}}


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
    <script>
        (function() {
            var input = document.getElementById('fileuploadInput');
            var preview = document.getElementById('photoPreview');
            var deleteBtn = document.getElementById('btn-delete-photo');
            var updateBtn = document.getElementById('btn-update');
            var overlay = document.getElementById('uploadOverlay');
            var form = document.querySelector('form');
            var hapusField = document.getElementById('hapus_foto');
            var hasPhoto = (document.querySelector('#fileUpload1 label').getAttribute('data-has-photo') === '1');

            // Store original src so we can restore it
            var originalSrc = preview ? preview.getAttribute('data-original') : '';

            if (input) {
                input.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(ev) {
                            if (preview) {
                                preview.src = ev.target.result;
                            }
                        };
                        reader.readAsDataURL(this.files[0]);
                        // Ensure flag reset when choosing new file
                        if (hapusField) {
                            hapusField.value = '0';
                        }
                        hasPhoto = false;
                    }
                });
            }

            // Delete current photo: set flag and submit form
            if (deleteBtn && hasPhoto) {
                deleteBtn.addEventListener('click', function() {
                    if (!form) return;
                    if (hapusField) {
                        hapusField.value = '1';
                    }
                    if (input) {
                        input.value = '';
                    }
                    form.submit();
                });
            }

            // Preview overlay behavior
            function showPreviewModal(src) {
                var html = '\n<div class="modal modal-blur fade" id="modal-preview" tabindex="-1" role="dialog" aria-hidden="true">\n' +
                    '  <div class="modal-dialog modal-dialog-centered" role="document">\n' +
                    '    <div class="modal-content">\n' +
                    '      <div class="modal-header" style="position:relative;">\n' +
                    '        <h5 class="modal-title">Preview Foto</h5>\n' +
                    '        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" aria-label="Close" style="position:absolute; top:8px; right:8px; z-index:2;">&times;</button>\n' +
                    '      </div>\n' +
                    '      <div class="modal-body">\n' +
                    '        <img src="' + (src || originalSrc) + '" style="width:100%; height:auto; border-radius:8px;"/>\n' +
                    '      </div>\n' +
                    '    </div>\n' +
                    '  </div>\n' +
                    '</div>';
                var container = document.createElement('div');
                container.innerHTML = html;
                document.body.appendChild(container.firstElementChild);
                var modalEl = document.getElementById('modal-preview');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
                // Ensure custom X button reliably closes the modal
                var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
                closeBtns.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        modal.hide();
                    });
                });
                modalEl.addEventListener('hidden.bs.modal', function() {
                    modal.dispose();
                    modalEl.parentNode.removeChild(modalEl);
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function(ev) {
                    // If currently has a saved photo, open preview modal instead of triggering file dialog
                    if (hasPhoto) {
                        ev.preventDefault();
                        showPreviewModal(preview ? preview.src : originalSrc);
                    }
                });
            }
        })();
    </script>
</body>

</html>
