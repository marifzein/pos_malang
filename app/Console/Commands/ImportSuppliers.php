<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSuppliers extends Command
{
    protected $signature = 'import:suppliers';
    protected $description = 'Import Supplier (pj_merk_barang) dari Mekarsari';

    public function handle()
    {
        $this->info('IMPORT DATA SUPPLIER');

        $rows = DB::connection('mekarsari')
            ->table('pj_merk_barang')
            ->orderBy('id_merk_barang')
            ->get();

        $insert = 0;
        $update = 0;

        foreach ($rows as $row) {
            $data = [
                'kode'       => 'SUP-' . str_pad($row->id_merk_barang, 4, '0', STR_PAD_LEFT),
                'nama'       => trim($row->merk) ?: 'Supplier #' . $row->id_merk_barang,
                'is_active'  => $row->dihapus === 'tidak' ? 1 : 0,
                'updated_at' => now(),
            ];

            $exists = DB::table('suppliers')->where('id', $row->id_merk_barang)->first();

            if ($exists) {
                DB::table('suppliers')->where('id', $row->id_merk_barang)->update($data);
                $update++;
            } else {
                DB::table('suppliers')->insert([
                    'id'         => $row->id_merk_barang,
                    'pic'        => null,
                    'telepon'    => null,
                    'email'      => null,
                    'alamat'     => null,
                    ...$data,
                    'created_at' => now(),
                ]);
                $insert++;
            }
        }

        $this->table(['Insert', 'Update'], [[$insert, $update]]);
        $this->info('Selesai.');
    }
}