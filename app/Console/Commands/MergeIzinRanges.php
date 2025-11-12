<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeIzinRanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage examples:
     *  php artisan izin:merge-ranges --dry-run
     *  php artisan izin:merge-ranges --nik=1234567890 --from=2025-01-01 --to=2025-12-31
     */
    protected $signature = 'izin:merge-ranges {--nik=} {--from=} {--to=} {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Merge historical contiguous single-day pengajuan_izin rows into ranged rows (tgl_izin .. izin_sampai).';

    public function handle(): int
    {
        $nikFilter = $this->option('nik');
        $from = $this->option('from');
        $to = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('pengajuan_izin')
            ->select('id', 'nik', 'tgl_izin', 'izin_sampai', 'status', 'status_approved', 'keterangan', 'surat_sakit')
            ->orderBy('nik')
            ->orderBy('status')
            ->orderBy('keterangan')
            ->orderBy('tgl_izin');

        if (!empty($nikFilter)) {
            $query->where('nik', $nikFilter);
        }
        if (!empty($from)) {
            $query->where('tgl_izin', '>=', $from);
        }
        if (!empty($to)) {
            $query->where('tgl_izin', '<=', $to);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            $this->info('No rows found.');
            return self::SUCCESS;
        }

        $totalSequences = 0;
        $totalMergedRows = 0;
        $totalDeleted = 0;

        // Group by nik/status/keterangan/status_approved
        $grouped = $rows->groupBy(function ($r) {
            $approved = is_null($r->status_approved) ? 'null' : $r->status_approved;
            return $r->nik . '|' . $r->status . '|' . ($r->keterangan ?? '') . '|' . $approved;
        });

        foreach ($grouped as $groupKey => $items) {
            // Work only with single-day rows (izin_sampai is null)
            $items = $items->sortBy('tgl_izin')->values();

            $i = 0;
            while ($i < $items->count()) {
                $startItem = $items[$i];
                // Skip ranged rows; we do not merge them to avoid surprises
                if (!empty($startItem->izin_sampai)) {
                    $i++;
                    continue;
                }

                $sequence = [$startItem];
                $j = $i + 1;
                while ($j < $items->count()) {
                    $prev = $items[$j - 1];
                    $curr = $items[$j];
                    if (!empty($curr->izin_sampai)) {
                        break; // stop on ranged row
                    }
                    $expectedNext = date('Y-m-d', strtotime($prev->tgl_izin . ' +1 day'));
                    if ($curr->tgl_izin === $expectedNext) {
                        $sequence[] = $curr;
                        $j++;
                    } else {
                        break;
                    }
                }

                // Only merge sequences of length >= 2
                if (count($sequence) >= 2) {
                    $totalSequences++;
                    $first = $sequence[0];
                    $last = $sequence[count($sequence) - 1];

                    $newRow = [
                        'nik' => $first->nik,
                        'tgl_izin' => $first->tgl_izin,
                        'izin_sampai' => $last->tgl_izin,
                        'status' => $first->status,
                        'status_approved' => $first->status_approved,
                        'keterangan' => $first->keterangan,
                    ];

                    // If every row in sequence has same non-null surat_sakit, keep it; else null
                    $attachments = collect($sequence)->pluck('surat_sakit')->unique()->filter();
                    if ($attachments->count() === 1) {
                        $newRow['surat_sakit'] = $attachments->first();
                    }

                    $idsToDelete = collect($sequence)->pluck('id')->all();

                    if ($dryRun) {
                        $this->line("[DRY-RUN] Merge NIK {$first->nik} {$first->status} '{$first->keterangan}' : "
                            . $first->tgl_izin . ' .. ' . $last->tgl_izin . ' (' . count($sequence) . ' rows)');
                    } else {
                        DB::transaction(function () use ($newRow, $idsToDelete, &$totalMergedRows, &$totalDeleted) {
                            DB::table('pengajuan_izin')->insert($newRow);
                            $deleted = DB::table('pengajuan_izin')->whereIn('id', $idsToDelete)->delete();
                            $totalMergedRows++;
                            $totalDeleted += $deleted;
                        });
                    }

                    $i = $j; // jump past this sequence
                    continue;
                }

                $i = $i + 1;
            }
        }

        if ($dryRun) {
            $this->info("[DRY-RUN] Found {$totalSequences} mergeable sequences.");
        } else {
            $this->info("Merged {$totalSequences} sequences into {$totalMergedRows} range rows. Deleted {$totalDeleted} single-day rows.");
        }

        return self::SUCCESS;
    }
}


