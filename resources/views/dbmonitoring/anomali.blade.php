<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Audit Anomali - SIBALO</title>
    <link href="{{ asset('dist/css/tabler.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/tabler-vendors.min.css?1674944402') }}" rel="stylesheet" />
    <link href="{{ asset('dist/css/demo.min.css?1674944402') }}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />
</head>

<body>
    <script src="{{ asset('dist/js/demo-theme.min.js?1674944402') }}"></script>
    <div class="page">
        @include('dashboard.sidebar')
        @include('dashboard.header')

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-4 align-items-center">
                        <div class="col">
                            <h2 class="page-title">Audit Anomali (Mode Offline)</h2>
                            <div class="text-muted">
                                Data diambil dari <code>absensi_events</code> dan <code>izin_events</code> (waktu server), untuk memantau proses upload setelah perangkat kembali online.
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="/monitoring" class="btn btn-outline-secondary">Kembali ke Monitoring</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="/monitoring/anomali">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Dari</label>
                                        <input type="text" id="dari" name="dari" value="{{ $dari }}" class="form-control"
                                            placeholder="YYYY-MM-DD">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Sampai</label>
                                        <input type="text" id="sampai" name="sampai" value="{{ $sampai }}"
                                            class="form-control" placeholder="YYYY-MM-DD">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">NIK (opsional)</label>
                                        <input type="text" name="nik" value="{{ $nik }}" class="form-control"
                                            placeholder="contoh: 123456">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Penanda (opsional)</label>
                                        <select name="flag" class="form-select">
                                            <option value="">Semua</option>
                                            @foreach ($flags as $k => $label)
                                                <option value="{{ $k }}" {{ $flag === $k ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" name="show_all" value="1"
                                                {{ $showAll ? 'checked' : '' }}>
                                            <span class="form-check-label">Tampilkan semua event (termasuk yang tidak anomali)</span>
                                        </label>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button class="btn btn-primary" type="submit">Filter</button>
                                        <a class="btn btn-outline-secondary" href="/monitoring/anomali">Reset</a>
                                        <div class="ms-auto text-muted">
                                            Absensi: <strong>{{ $countAbsensi }}</strong> &nbsp;|&nbsp;
                                            Izin: <strong>{{ $countIzin }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-absensi" role="tab">Audit Absensi</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-izin" role="tab">Audit Izin</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-absensi" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Waktu Server</th>
                                                    <th>Waktu Perangkat</th>
                                                    <th>Selisih</th>
                                                    <th>NIK</th>
                                                    <th>Nama</th>
                                                    <th>Jenis</th>
                                                    <th>Status</th>
                                                    <th>Penanda</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($absensiEvents as $e)
                                                    @php
                                                        $flagsArr = $e->anomaly_flags ? (json_decode($e->anomaly_flags, true) ?: []) : [];
                                                        $delay = is_numeric($e->sync_delay_seconds) ? (int) $e->sync_delay_seconds : null;
                                                        $delayText = $delay === null ? '-' : (floor($delay / 60) . 'm ' . ($delay % 60) . 's');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-muted">{{ $e->received_at }}</td>
                                                        <td class="text-muted">{{ $e->captured_at ?? '-' }}</td>
                                                        <td class="text-muted">{{ $delayText }}</td>
                                                        <td>{{ $e->nik }}</td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $e->nama_lengkap ?? '-' }}</div>
                                                            <div class="text-muted small">{{ $e->jabatan ?? '' }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-azure-lt">{{ $e->event_type === 'in' ? 'Masuk' : ($e->event_type === 'out' ? 'Pulang' : 'Tidak diketahui') }}</span>
                                                            <span class="badge bg-indigo-lt">{{ $e->result_tag === 'in' ? 'Masuk' : ($e->result_tag === 'out' ? 'Pulang' : ($e->result_tag ?? '-')) }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($e->result_status === 'success')
                                                                <span class="badge bg-success">berhasil</span>
                                                            @elseif ($e->result_status === 'warning')
                                                                <span class="badge bg-warning">peringatan</span>
                                                            @else
                                                                <span class="badge bg-danger">gagal</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (count($flagsArr))
                                                                @foreach ($flagsArr as $f)
                                                                    <span class="badge bg-orange-lt">{{ $flags[$f] ?? $f }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted">{{ $e->message ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-muted">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $absensiEvents->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-izin" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Waktu Server</th>
                                                    <th>Waktu Perangkat</th>
                                                    <th>Selisih</th>
                                                    <th>NIK</th>
                                                    <th>Nama</th>
                                                    <th>Status</th>
                                                    <th>Penanda</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($izinEvents as $e)
                                                    @php
                                                        $flagsArr = $e->anomaly_flags ? (json_decode($e->anomaly_flags, true) ?: []) : [];
                                                        $delay = is_numeric($e->sync_delay_seconds) ? (int) $e->sync_delay_seconds : null;
                                                        $delayText = $delay === null ? '-' : (floor($delay / 60) . 'm ' . ($delay % 60) . 's');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-muted">{{ $e->received_at }}</td>
                                                        <td class="text-muted">{{ $e->captured_at ?? '-' }}</td>
                                                        <td class="text-muted">{{ $delayText }}</td>
                                                        <td>{{ $e->nik }}</td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $e->nama_lengkap ?? '-' }}</div>
                                                            <div class="text-muted small">{{ $e->jabatan ?? '' }}</div>
                                                        </td>
                                                        <td>
                                                            @if ($e->result_status === 'success')
                                                                <span class="badge bg-success">berhasil</span>
                                                            @elseif ($e->result_status === 'warning')
                                                                <span class="badge bg-warning">peringatan</span>
                                                            @else
                                                                <span class="badge bg-danger">gagal</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (count($flagsArr))
                                                                @foreach ($flagsArr as $f)
                                                                    <span class="badge bg-orange-lt">{{ $flags[$f] ?? $f }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted">{{ $e->message ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-muted">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $izinEvents->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @include('dashboard.footer')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <script src="{{ asset('dist/js/tabler.min.js?1674944402') }}" defer></script>
    <script src="{{ asset('dist/js/demo.min.js?1674944402') }}" defer></script>
    <script>
        $(function() {
            $("#dari, #sampai").datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
        });
    </script>
</body>

</html>

