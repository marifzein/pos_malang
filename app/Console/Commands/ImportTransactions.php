<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTransactions extends Command
{
    protected $signature = 'import:transactions';
    protected $description = 'Import data penjualan master dari database Mekarsari';

    public function handle()
    {
        $this->info('MULAI IMPORT MASTER TRANSACTIONS...');

        DB::disableQueryLog();
        DB::connection('mekarsari')->disableQueryLog();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET UNIQUE_CHECKS=0;');

        // Cache telepon pelanggan dari tabel customers yang sudah diimpor
        $customerPhones = DB::table('customers')
            ->whereNotNull('telepon')
            ->pluck('telepon', 'id')
            ->toArray();

        $insertCount = 0;

        DB::connection('mekarsari')
            ->table('pj_penjualan_master')
            ->orderBy('id_penjualan_m')
            ->chunkById(5000, function ($rows) use (&$insertCount, $customerPhones) {

                $masterIds = $rows->pluck('id_penjualan_m')->toArray();

                // Hitung total diskon per transaksi dari tabel detail (jika harga_sebelum_diskon > harga_satuan)
                $diskonMap = DB::connection('mekarsari')
                    ->table('pj_penjualan_detail')
                    ->selectRaw('id_penjualan_m, SUM(GREATEST(0, COALESCE(harga_sebelum_diskon, harga_satuan) - harga_satuan) * jumlah_beli) as total_diskon')
                    ->whereIn('id_penjualan_m', $masterIds)
                    ->groupBy('id_penjualan_m')
                    ->pluck('total_diskon', 'id_penjualan_m');

                $batchData = [];

                foreach ($rows as $row) {
                    $grandTotal = (float) ($row->grand_total ?? 0);
                    $diskon     = (float) ($diskonMap[$row->id_penjualan_m] ?? 0);
                    $subtotal   = $grandTotal + $diskon;

                    $cash    = (float) ($row->bayar ?? 0);
                    $card    = (float) ($row->card ?? 0);
                    $voucher = (float) ($row->voucher ?? 0);

                    $totalBayar = $cash + $card + $voucher;
                    $kembalian  = max(0, $totalBayar - $grandTotal);
                    $hutang     = max(0, $grandTotal - $totalBayar);

                    $pelangganId = ($row->id_pelanggan && $row->id_pelanggan > 0) ? $row->id_pelanggan : null;
                    $createdAt   = $row->tanggal ?: now();

                    $batchData[] = [
                        'id'          => $row->id_penjualan_m,
                        'no_nota'     => trim($row->nomor_nota),
                        'user_id'     => $row->id_user ?: 1,
                        'shift_id'    => null,
                        'pelanggan'   => $pelangganId,
                        'telp'        => $pelangganId ? ($customerPhones[$pelangganId] ?? null) : null,
                        'subtotal'    => $subtotal,
                        'diskon'      => $diskon,
                        'grand_total' => $grandTotal,
                        'cash'        => $cash,
                        'voucher'     => $voucher,
                        'card'        => $card,
                        'hutang'      => $hutang,
                        'kembalian'   => $kembalian,
                        'catatan'     => $row->keterangan_lain,
                        'status'      => 'SOLD',
                        'created_at'  => $createdAt,
                        'updated_at'  => $createdAt,
                    ];
                }

                if (!empty($batchData)) {
                    DB::transaction(function () use ($batchData) {
                        // Pecah per 1.000 baris (1.000 x 18 kolom = 18.000 placeholder, aman di bawah batas 65.535)
                        foreach (array_chunk($batchData, 1000) as $chunk) {
                            DB::table('transactions')->upsert(
                                $chunk,
                                ['id'],
                                [
                                    'no_nota',
                                    'user_id',
                                    'pelanggan',
                                    'telp',
                                    'subtotal',
                                    'diskon',
                                    'grand_total',
                                    'cash',
                                    'voucher',
                                    'card',
                                    'hutang',
                                    'kembalian',
                                    'catatan',
                                    'created_at',
                                    'updated_at',
                                ]
                            );
                        }
                    });

                    $insertCount += count($batchData);
                    $this->info("Processed master transactions... Total: {$insertCount} records.");
                }
            }, 'id_penjualan_m');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::statement('SET UNIQUE_CHECKS=1;');

        $this->info("SELESAI! Total {$insertCount} data master transaksi berhasil di-import.");

        return Command::SUCCESS;
    }
}