<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProducts extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import Produk dari Mekarsari';

    public function handle()
    {
        $this->info('IMPORT DATA PRODUK');

        $rows = DB::connection('mekarsari')
            ->table('pj_barang')
            ->orderBy('id_barang')
            ->get();

        // Cache ID valid untuk mencegah error Foreign Key jika ada data yatim di DB lama
        $validCategoryIds = DB::table('categories')->pluck('id')->flip()->toArray();
        $validSupplierIds = DB::table('suppliers')->pluck('id')->flip()->toArray();
        $brandCache = DB::table('brands')
            ->get()
            ->mapWithKeys(fn ($b) => [mb_strtolower(trim($b->name)) => $b->id])
            ->toArray();

        $insert = 0;
        $opening = 0;

        foreach ($rows as $row) {
            DB::transaction(function () use ($row, &$insert, &$opening, $validCategoryIds, $validSupplierIds, &$brandCache) {

                // 1. Olah kolom 'size' legacy menjadi master 'brands'
                $brandId = null;
                $rawBrand = preg_replace('/\s+/', ' ', trim((string) $row->size)); // rapikan spasi ganda

                if ($rawBrand !== '' && $rawBrand !== '-' && $rawBrand !== '0') {
                    $brandKey = mb_strtolower($rawBrand);

                    if (!isset($brandCache[$brandKey])) {
                        $brandCache[$brandKey] = DB::table('brands')->insertGetId([
                            'name'        => $rawBrand,
                            'description' => null,
                            'is_active'   => 1,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                    $brandId = $brandCache[$brandKey];
                }

                // 2. Validasi category_id & supplier_id agar tidak gagal FK jika di legacy nilainya 0 / terhapus
                $categoryId = isset($validCategoryIds[$row->id_kategori_barang]) ? $row->id_kategori_barang : null;
                $supplierId = isset($validSupplierIds[$row->id_merk_barang]) ? $row->id_merk_barang : null;

                // 3. Amankan kode_barang jika kosong atau duplikat di DB lama
                $kodeBarang = trim((string) $row->kode_barang);
                if ($kodeBarang === '' || DB::table('products')->where('kode_barang', $kodeBarang)->exists()) {
                    $kodeBarang = ($kodeBarang !== '' ? $kodeBarang : 'BRG') . '-' . $row->id_barang;
                }

                // Skip jika ID produk sudah pernah diimpor
                if (DB::table('products')->where('id', $row->id_barang)->exists()) {
                    return;
                }

                DB::table('products')->insert([
                    'id'           => $row->id_barang,
                    'kode_barang'  => $kodeBarang,
                    'barcode'      => null,
                    'nama_barang'  => trim($row->nama_barang),
                    'category_id'  => $categoryId,
                    'supplier_id'  => $supplierId,
                    'brand_id'     => $brandId,
                    'catatan'      => $row->keterangan,
                    'harga'        => $row->harga ?? 0,
                    'harga_beli'   => $row->hargabeli,
                    'harga_diskon' => null,
                    'stok'         => $row->total_stok ?? 0,
                    'min_stok'     => 5,
                    'satuan'       => 'pcs',
                    'is_active'    => $row->dihapus === 'tidak' ? 1 : 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $insert++;

                DB::table('stock_movements')->insert([
                    'product_id'   => $row->id_barang,
                    'type'         => 'OPENING',
                    'qty'          => $row->total_stok ?? 0,
                    'stock_before' => 0,
                    'stock_after'  => $row->total_stok ?? 0,
                    'reference_no' => 'IMPORT-MEKARSARI',
                    'notes'        => 'Saldo awal import produk dari Mekarsari',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $opening++;
            });
        }

        $this->table(['Insert', 'Opening'], [[$insert, $opening]]);
        $this->info('Selesai.');
    }
}