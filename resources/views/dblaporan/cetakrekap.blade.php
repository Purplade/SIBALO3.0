<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>A4</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: A3 landscape;
        }

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
            font-size: 10px;
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

<body class="A3 landscape">

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

    <!-- Each sheet element should have the class "sheet" -->
    <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
    <section class="sheet padding-10mm">

        <!-- Write HTML just like a web page -->
        <table style="width: 100%">
            <tr>
                <td>
                    <span id="title">
                        <h3 style="margin: 10px 3px 30px 0px">
                            REKAP ABSENSI PEGAWAI PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }}<br>
                            SMP NEGERI 3 KOTA KOTAMOBAGU
                        </h3>
                    </span>
                    <span><i>Jl. Arief Rahman Hakim No.18 95716 Kotamobagu Sulawesi</i></span>
                </td>
            </tr>
        </table>
        <table class="tabellaporan">
            <tr>
                <th rowspan="2">NIP</th>
                <th rowspan="2">Nama Pegawai</th>
                <th colspan="31">Tanggal</th>
                <th rowspan="2">TH</th>
                <th rowspan="2">TT</th>
            </tr>
            <tr>
                @php
                    for($i=1; $i <= 31; $i++){
                @endphp
                <th>{{ $i }}</th>
                @php
                    }
                @endphp
            </tr>
            @foreach ($rekap as $d)
            <tr>
                <td>{{ $d->nik }}</td>
                <td>{{ $d->nama_lengkap }}</td>

                @php
                $totalhadir = 0;
                $totalterlambat = 0;
                for($i=1; $i<=31; $i++){
                $tgl = "tgl_".$i;
                if(empty($d->$tgl)) {
                    $hadir = ['',''];
                    $totalhadir += 0;
                }else{
                    $hadir = explode("-",$d->$tgl);
                    $totalhadir += 1;
                    if($hadir[0]>"07:00:00"){
                        $totalterlambat +=1;
                    }
                }
                @endphp

                <td>
                    <span style="color:{{ $hadir[0]>"07:00:00" ? "red" : "" }}">{{ $hadir[0] }}</span><br>
                    
                </td>
                @php
                }   
                @endphp
                <td>{{ $totalhadir }}</td>
                <td>{{ $totalterlambat }}</td>
            </tr>
            @endforeach
        </table>


        <table width="100%" style="margin-top: 100px">
            <tr>
                <td style="text-align: center">Kotamobagu, {{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="text-align: center; vertical-align: bottom;" height="100px">
                    <u>Andot Pobela</u><br>
                    <i><b>Kepala Sekolah</b></i>
                </td>
            </tr>
        </table>
    </section>

</body>

</html>
