<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCategories extends Command
{
    protected $signature = 'import:categories';
    protected $description = 'Import kategori dari database Mekarsari ke POS';

    public function handle()
    {
        $this->info('IMPORT DATA KATEGORI');

        $rows = DB::connection('mekarsari')
            ->table('pj_kategori_barang')
            ->orderBy('id_kategori_barang')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Data kategori tidak ditemukan.');
            return Command::SUCCESS;
        }

        $insert = 0;
        $update = 0;

        foreach ($rows as $row) {
            $data = [
                'name'        => trim($row->kategori),
                'description' => null,
                'is_active'   => $row->dihapus === 'tidak' ? 1 : 0,
                'updated_at'  => now(),
            ];

            // Cek berdasarkan ID legacy agar relasi category_id di products tetap akurat
            $exists = DB::table('categories')->where('id', $row->id_kategori_barang)->first();

            if ($exists) {
                DB::table('categories')->where('id', $row->id_kategori_barang)->update($data);
                $update++;
            } else {
                DB::table('categories')->insert([
                    'id'         => $row->id_kategori_barang,
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