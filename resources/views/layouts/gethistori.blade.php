@if ($histori->isEmpty())
    <div class="alert alert-outline warning">
        <p>Data Belum Ada</p>
    </div>
@endif
@foreach ($histori as $d)
    @php
        $fotoInUrl = $d->foto_in ? Storage::url('uploads/absensi/' . $d->foto_in) : '';
        $fotoOutUrl = $d->foto_out ? Storage::url('uploads/absensi/' . $d->foto_out) : '';
        $jamInText = $d->jam_in != null ? $d->jam_in : 'Belum Absen';
        $jamOutText = $d->jam_out != null ? $d->jam_out : 'Belum Absen';
        $thumb = $fotoInUrl ?: $fotoOutUrl;
    @endphp
    <ul class="listview image-listview">
        <li data-foto-in="{{ $fotoInUrl ? url($fotoInUrl) : '' }}"
            data-foto-out="{{ $fotoOutUrl ? url($fotoOutUrl) : '' }}"
            data-jam-in="{{ $jamInText }}" data-jam-out="{{ $jamOutText }}">
            <div class="item">
                @if ($thumb)
                    <img src="{{ url($thumb) }}" alt="image" class="image">
                @endif
                <div class="in">
                    <div>
                        <b>{{ date('d-m-Y', strtotime($d->tgl_absensi)) }}</b><br>
                        @php
                            $isIzin = isset($tanggalIzin[date('Y-m-d', strtotime($d->tgl_absensi))]);
                        @endphp
                        @if ($isIzin && $d->jam_out != null && $d->jam_out < '16:00:00')
                            <small class="text-muted">Pulang lebih awal</small>
                        @endif
                    </div>
                    <a href="#" class="btn-detail-absen" style="display:inline-block; font-size:.85rem;">Lihat Detail</a>
                </div>
            </div>
        </li>
    </ul>
@endforeach

{{-- Modal Detail (khusus untuk partial ini) --}}
<div class="modal fade" id="modal-detail-histori-partial" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Absensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Foto Masuk</label>
                    <img data-role="foto-in" src="" class="img-fluid" alt="Foto Masuk">
                    <div class="mt-2 d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
                        <span class="badge badge-success" data-role="badge-in"></span>
                        <small data-role="lateness" class="text-muted"></small>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Foto Pulang</label>
                    <img data-role="foto-out" src="" class="img-fluid" alt="Foto Pulang">
                    <div class="mt-2 d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
                        <span class="badge badge-danger" data-role="badge-out"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

<script>
    (function(){
        // Attach once per partial render
        document.addEventListener('click', function(e){
            var trigger = e.target.closest('.btn-detail-absen');
            if (!trigger) return;
            e.preventDefault();
            var li = trigger.closest('li');
            if (!li) return;
            var fotoIn = li.getAttribute('data-foto-in') || '';
            var fotoOut = li.getAttribute('data-foto-out') || '';
            var jamIn = li.getAttribute('data-jam-in') || '';
            var jamOut = li.getAttribute('data-jam-out') || '';

            function parseTime(s){
                var p = String(s||'').split(':');
                if (p.length < 2) return null;
                var h = parseInt(p[0]||'0',10), m = parseInt(p[1]||'0',10), sec = parseInt(p[2]||'0',10);
                if (isNaN(h)||isNaN(m)||isNaN(sec)) return null;
                return h*3600 + m*60 + sec;
            }
            var std = parseTime('07:00:00');
            var inSec = parseTime(jamIn);
            var latenessText = '';
            if (inSec != null && std != null && inSec > std) {
                var diff = inSec - std;
                var hh = Math.floor(diff/3600);
                var mm = Math.floor((diff%3600)/60);
                var ss = diff%60;
                var parts = [];
                if (hh>0) parts.push(String(hh).padStart(2,'0')+'j');
                parts.push(String(mm).padStart(2,'0')+'m');
                parts.push(String(ss).padStart(2,'0')+'d');
                latenessText = 'Terlambat ' + parts.join(' ');
            } else if (inSec != null) {
                latenessText = 'Tepat waktu';
            }

            var $modal = $('#modal-detail-histori-partial');
            $modal.find('img[data-role="foto-in"]').attr('src', fotoIn || '');
            $modal.find('img[data-role="foto-out"]').attr('src', fotoOut || '');
            $modal.find('[data-role="badge-in"]').text(jamIn || '—');
            $modal.find('[data-role="badge-out"]').text(jamOut || '—');
            $modal.find('[data-role="lateness"]').text(latenessText);
            $modal.modal('show');
        }, { once: false });
    })();
</script>
