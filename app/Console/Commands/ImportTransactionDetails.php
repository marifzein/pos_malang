<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTransactionDetails extends Command
{
    protected $signature = 'import:transaction-details';
    protected $description = 'Import detail penjualan dari database Mekarsari (Optimized Batch)';

    public function handle()
    {
        $this->info('STARTING OPTIMIZED IMPORT TRANSACTION DETAILS...');

        DB::disableQueryLog();
        DB::connection('mekarsari')->disableQueryLog();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET UNIQUE_CHECKS=0;');

        // Cache seluruh master produk (kode_barang & nama_barang) sekali di awal agar tidak query berulang per batch
        $products = DB::table('products')
            ->select('id', 'kode_barang', 'nama_barang')
            ->get()
            ->keyBy('id');

        $insertCount = 0;

        DB::connection('mekarsari')
            ->table('pj_penjualan_detail')
            ->orderBy('id_penjualan_d')
            ->chunkById(5000, function ($rows) use (&$insertCount, $products) {

                // Ambil tanggal transaksi asli dari tabel transactions untuk batch ini
                $trxIds = $rows->pluck('id_penjualan_m')->unique()->toArray();
                $trxDates = DB::table('transactions')
                    ->whereIn('id', $trxIds)
                    ->pluck('created_at', 'id');

                $batchData = [];

                foreach ($rows as $row) {
                    $product = $products->get($row->id_barang);
                    if (!$product) {
                        continue;
                    }

                    $trxDate = $trxDates[$row->id_penjualan_m] ?? now();

                    $batchData[] = [
                        'id'             => $row->id_penjualan_d,
                        'transaction_id' => $row->id_penjualan_m,
                        'product_id'     => $row->id_barang,
                        'harga_beli'     => $row->hargabeli ?? 0,
                        'kode_barang'    => $product->kode_barang,
                        'nama_barang'    => $product->nama_barang,
                        'harga'          => $row->harga_satuan ?? 0,
                        'qty'            => $row->jumlah_beli ?? 0,
                        'subtotal'       => $row->total ?? 0,
                        'created_at'     => $trxDate,
                        'updated_at'     => $trxDate,
                    ];
                }

                if (!empty($batchData)) {
                    DB::transaction(function () use ($batchData) {
                        // Pecah per 2.000 baris (2.000 x 11 kolom = 22.000 placeholder, aman di bawah batas 65.535)
                        foreach (array_chunk($batchData, 2000) as $chunk) {
                            DB::table('transaction_details')->upsert(
                                $chunk,
                                ['id'],
                                [
                                    'transaction_id',
                                    'product_id',
                                    'harga_beli',
                                    'kode_barang',
                                    'nama_barang',
                                    'harga',
                                    'qty',
                                    'subtotal',
                                    'created_at',
                                    'updated_at',
                                ]
                            );
                        }
                    });

                    $insertCount += count($batchData);
                    $this->info("Processed detail batch... Total: {$insertCount} records.");
                }
            }, 'id_penjualan_d');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::statement('SET UNIQUE_CHECKS=1;');

        $this->info("SELESAI! Total {$insertCount} records detail berhasil di-import.");

        return Command::SUCCESS;
    }
}