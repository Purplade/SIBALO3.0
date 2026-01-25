<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>A4</title>

    @php $isExcel = request()->has('exportexcel'); $isPdf = isset($isPdf) && $isPdf; @endphp
    @unless($isExcel || $isPdf)
    <!-- Normalize or reset CSS with your favorite library (skip for Excel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <!-- Load paper.css for happy printing (skip for Excel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
    @endunless

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page { size: A4 }

        #title {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            font-weight: bold;
        }

        .tabeldatapegawai {
            margin-top: 10px;
        }

        .tabeldatapegawai td {
            padding: 5px;
        }

        .tabellaporan {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .tabellaporan tr th {
            border: 1px solid #0a0a0a;
            padding: 8px;
            background-color: rgb(185, 185, 185);
        }

        .tabellaporan tr td {
            border: 1px solid #0a0a0a;
            padding: 5px;
            font-size: 12px;
        }
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->

<body @unless($isExcel || $isPdf) class="A4" @endunless>

    @php
        function selisih($jam_masuk, $jam_keluar)
        {
            [$h, $m, $s] = explode(':', $jam_masuk);
            $dtAwal = mktime($h, $m, $s, '1', '1', '1');
            [$h, $m, $s] = explode(':', $jam_keluar);
            $dtAkhir = mktime($h, $m, $s, '1', '1', '1');
            $dtSelisih = $dtAkhir - $dtAwal;
            $totalmenit = $dtSelisih / 60;
            $jam = explode('.', $totalmenit / 60);
            $sisamenit = $totalmenit / 60 - $jam[0];
            $sisamenit2 = $sisamenit * 60;
            $jml_jam = $jam[0];
            return $jml_jam . ':' . round($sisamenit2);
        }

    @endphp

    @unless($isExcel || $isPdf)
    <section class="sheet padding-10mm">
    @else
    <div>
    @endunless

        <!-- Write HTML just like a web page -->
        <table style="width: 100%">
            <tr>
                <td>
                    <span id="title">
                        <h3 style="margin: 10px 3px 30px 0px">
                            LAPORAN ABSENSI PEGAWAI PERIODE {{ strtoupper($namabulan[$bulan ]) }} {{ $tahun }}<br>
                            SMP NEGERI 3 KOTA KOTAMOBAGU
                        </h3>
                    </span>
                    <span><i>Jl. Arief Rahman Hakim No.18 95716 Kotamobagu Sulawesi</i></span>
                </td>
            </tr>
        </table>
        <table class="tabeldatapegawai">
            <tr>
                <td rowspan="6">
                    @php
                        $foto = $pegawai->foto ?? null;
                        $avatarUrl = null;
                        if (!empty($foto)) {
                            $pathUploads = 'uploads/pegawai/' . $foto; // lokasi utama
                            $pathLegacy = 'pegawai/' . $foto; // fallback lokasi lama
                            if (Storage::disk('public')->exists($pathUploads)) {
                                $avatarUrl = Storage::url($pathUploads);
                            } elseif (Storage::disk('public')->exists($pathLegacy)) {
                                $avatarUrl = Storage::url($pathLegacy);
                            }
                        }
                        // Build local path and data URI for PDF to avoid remote fetch
                        $avatarDataUri = null;
                        if ($isPdf && $avatarUrl) {
                            $relative = ltrim(parse_url($avatarUrl, PHP_URL_PATH) ?: $avatarUrl, '/');
                            $localPath = public_path('/' . $relative);
                            if (is_file($localPath) && is_readable($localPath)) {
                                $mime = function_exists('mime_content_type') ? mime_content_type($localPath) : 'image/jpeg';
                                $encoded = null;
                                // Auto-orient JPEG for PDF (dompdf tidak membaca EXIF)
                                if (stripos($mime, 'jpeg') !== false || stripos($mime, 'jpg') !== false) {
                                    $canExif = function_exists('exif_read_data');
                                    $canGd = function_exists('imagecreatefromjpeg') && function_exists('imagerotate') && function_exists('imagejpeg');
                                    if ($canExif && $canGd) {
                                        try {
                                            $exif = @exif_read_data($localPath);
                                            $orientation = $exif['Orientation'] ?? 1;
                                            $angle = 0;
                                            if ($orientation == 3) { $angle = 180; }
                                            elseif ($orientation == 6) { $angle = -90; }
                                            elseif ($orientation == 8) { $angle = 90; }
                                            if ($angle !== 0) {
                                                $img = @imagecreatefromjpeg($localPath);
                                                if ($img) {
                                                    $rot = @imagerotate($img, $angle, 0);
                                                    if ($rot) {
                                                        ob_start();
                                                        @imagejpeg($rot, null, 90);
                                                        $binary = ob_get_clean();
                                                        @imagedestroy($rot);
                                                        @imagedestroy($img);
                                                        if ($binary) { $encoded = base64_encode($binary); }
                                                    } else {
                                                        @imagedestroy($img);
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $e) { /* ignore */ }
                                    }
                                }
                                if ($encoded === null) {
                                    $encoded = base64_encode(@file_get_contents($localPath));
                                }
                                if ($encoded) {
                                    $avatarDataUri = 'data:' . $mime . ';base64,' . $encoded;
                                }
                            }
                        }
                    @endphp
                    <img src="{{ $avatarDataUri ?: ($avatarUrl ? $avatarUrl : asset('assets/img/sample/avatar/avatar1.jpg')) }}" alt="" width="125" height="150">
                </td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $pegawai->nik }}</td>
            </tr>
            <tr>
                <td>Nama Pegawai</td>
                <td>:</td>
                <td>{{ $pegawai->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pegawai->jabatan }}</td>
            </tr>
            <tr>
                <td>No.Hp</td>
                <td>:</td>
                <td>{{ $pegawai->no_hp }}</td>
            </tr>
        </table>
        <table class="tabellaporan table-basic">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Keterangan</th>
                <th>Total Jam Kerja</th>
            </tr>
            @foreach ($absensi as $d)
                @php
                    $jamterlambat = selisih('07:00:00', $d->jam_in);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d-m-Y', strtotime($d->tgl_absensi)) }}</td>
                    <td>{{ $d->jam_in }}</td>
                    <td>{{ $d->jam_out != null ? $d->jam_out : 'Belum Absen' }}</td>
                    <td>
                        @if ($d->jam_in > '07:00')
                            Terlambat {{ $jamterlambat }}
                        @else
                            Tepat Waktu
                        @endif
                    </td>
                    <td>
                        @if ($d->jam_out != null)
                            @php
                                $totaljamkerja = selisih($d->jam_in, $d->jam_out);
                            @endphp
                        @else
                            @php
                                $totaljamkerja = 0;
                            @endphp
                        @endif
                        {{ $totaljamkerja }}
                    </td>
                </tr>
            @endforeach
        </table>

        <table width="100%" style="margin-top: 100px">
            <tr>
                <td colspan="2" style="text-align: right">Kotamobagu, {{ date('d-m-Y') }}</td>
            </tr>
            <br>
            <tr>
                <td colspan="2" style="text-align: right" height="100px">
                    <u>Andot Pobela</u><br>
                    <i><b>Kepala Sekolah</b></i>
                </td>
            </tr>
        </table>
    @unless($isExcel)
    </section>
    @else
    </div>
    @endunless

</body>

</html>
