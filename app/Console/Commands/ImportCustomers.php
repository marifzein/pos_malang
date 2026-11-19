<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCustomers extends Command
{
    protected $signature = 'import:customers';
    protected $description = 'Import data pelanggan dari database Mekarsari';

    public function handle()
    {
        $this->info('IMPORT DATA PELANGGAN');

        $rows = DB::connection('mekarsari')
            ->table('pj_pelanggan')
            ->orderBy('id_pelanggan')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Data pelanggan tidak ditemukan.');
            return Command::SUCCESS;
        }

        // Hitung frekuensi kode_unik yang sudah di-trim & lowercase (agar akurat dengan UNIQUE MySQL)
        $codeCounts = [];
        foreach ($rows as $r) {
            $clean = mb_strtolower(trim((string) $r->kode_unik));
            if ($clean !== '') {
                $codeCounts[$clean] = ($codeCounts[$clean] ?? 0) + 1;
            }
        }

        $insert = 0;
        $update = 0;

        foreach ($rows as $row) {
            $rawKode = trim((string) $row->kode_unik);
            $cleanKey = mb_strtolower($rawKode);

            if ($rawKode === '') {
                // Fallback jika kode_unik kosong
                $kodePelanggan = 'CUST-' . str_pad($row->id_pelanggan, 5, '0', STR_PAD_LEFT);
            } elseif (($codeCounts[$cleanKey] ?? 0) > 1) {
                // Jika duplikat, tambahkan ID pelanggan
                $kodePelanggan = $rawKode . '-' . $row->id_pelanggan;
            } else {
                $kodePelanggan = $rawKode;
            }

            // Amankan tanggal '0000-00-00 00:00:00'
            $createdAt = ($row->waktu_input && !str_starts_with((string) $row->waktu_input, '0000-00-00'))
                ? $row->waktu_input
                : now();

            $data = [
                'kode_pelanggan' => $kodePelanggan,
                'nama'           => trim($row->nama) ?: 'Pelanggan #' . $row->id_pelanggan,
                'telepon'        => $row->telp ? trim($row->telp) : null,
                'alamat'         => $row->alamat ? trim($row->alamat) : null,
                'email'          => null,
                'catatan'        => $row->info_tambahan ? trim($row->info_tambahan) : null,
                'is_member'      => 0,
                'status'         => $row->dihapus === 'tidak' ? 1 : 0,
                'created_at'     => $createdAt,
                'updated_at'     => now(),
            ];

            $customer = DB::table('customers')
                ->where('id', $row->id_pelanggan)
                ->first();

            if ($customer) {
                DB::table('customers')
                    ->where('id', $row->id_pelanggan)
                    ->update($data);
                $update++;
            } else {
                DB::table('customers')->insert([
                    'id' => $row->id_pelanggan,
                    ...$data,
                ]);
                $insert++;
            }
        }

        $this->table(['Insert', 'Update'], [[$insert, $update]]);
        $this->info('Selesai.');

        return Command::SUCCESS;
    }
}