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
@foreach ($absensi as $d)
    @php
        $foto_in = Storage::url('uploads/absensi/' . $d->foto_in);
        $foto_out = Storage::url('uploads/absensi/' . $d->foto_out);
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $d->nik }}</td>
        <td>{{ $d->tgl_absensi }}</td>
        <td>{{ $d->nama_lengkap }}</td>
        <td>
            @php $jamMasukStandar = '07:00:00'; @endphp
            <div>{{ $d->jam_in }}</div>
            @if ($d->jam_in > $jamMasukStandar)
                <small class="text-muted">Terlambat</small>
            @else
                <small class="text-muted">Tepat waktu</small>
            @endif
        </td>

        <td>
            {!! $d->jam_out != null ? $d->jam_out : '<span class="badge bg-danger">Belum Absen</span>' !!}
        </td>

        <td>
            <a href="#" class="btn btn-detail" data-bs-toggle="modal" data-bs-target="#modal-detailabsensi"
                data-foto-in="{{ Storage::url('uploads/absensi/' . $d->foto_in) }}"
                data-foto-out="{{ Storage::url('uploads/absensi/' . $d->foto_out) }}"
                data-lokasi-in="{{ $d->lokasi_in }}" data-lokasi-out="{{ $d->lokasi_out }}">
                Lihat Detail
            </a>
        </td>
    </tr>
@endforeach
 