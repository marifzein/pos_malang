<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportUsers extends Command
{
    protected $signature = 'import:users';
    protected $description = 'Import data user dari database Mekarsari';

    public function handle()
    {
        $this->info('IMPORT DATA USER');
        $this->newLine();

        $rows = DB::connection('mekarsari')
            ->table('pj_user')
            ->orderBy('id_user')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Data user tidak ditemukan.');
            return Command::SUCCESS;
        }

        // Pre-hash password default 1x saja agar tidak berat di dalam loop
        $defaultPasswordHash = Hash::make('87654321!');

        $insert = 0;
        $update = 0;
        $keptOldPassword = 0;

        foreach ($rows as $row) {
            $role = match ((int) $row->id_akses) {
                1, 4    => 'Admin',
                2       => 'Kasir',
                default => 'Kasir',
            };

            $isActive = (
                $row->status === 'Aktif'
                && $row->dihapus === 'tidak'
            ) ? 1 : 0;

            // 1. Amankan kolom 'name' karena di tabel users bersifat UNIQUE
            $baseName = trim((string) $row->nama) ?: trim((string) $row->username);
            $name = $baseName;
            $nameExists = DB::table('users')
                ->where('name', $name)
                ->where('id', '!=', $row->id_user)
                ->exists();

            if ($nameExists) {
                $name = $baseName . ' (' . trim($row->username) . ')';
                // Jika masih bentrok juga, tambahkan ID
                if (DB::table('users')->where('name', $name)->where('id', '!=', $row->id_user)->exists()) {
                    $name = $baseName . '-' . $row->id_user;
                }
            }

            // 2. Amankan kolom 'email' (UNIQUE)
            $cleanUsername = preg_replace('/[^a-z0-9._-]/', '', strtolower(trim((string) $row->username)));
            $cleanUsername = $cleanUsername ?: 'user' . $row->id_user;
            $email = $cleanUsername . '@mekarsari.local';

            if (DB::table('users')->where('email', $email)->where('id', '!=', $row->id_user)->exists()) {
                $email = $cleanUsername . $row->id_user . '@mekarsari.local';
            }

            // 3. Cek apakah password lama sudah format bcrypt (60 karakter diawali $2y$/$2a$/$2b$)
            $oldPass = trim((string) $row->password);
            $isBcrypt = strlen($oldPass) === 60 && preg_match('/^\$2[ayb]\$/', $oldPass);

            if ($isBcrypt) {
                $password = $oldPass;
                $keptOldPassword++;
            } else {
                $password = $defaultPasswordHash;
            }

            $data = [
                'name'              => $name,
                'email'             => $email,
                'role'              => $role,
                'is_active'         => $isActive,
                'email_verified_at' => now(),
                'password'          => $password,
                'updated_at'        => now(),
            ];

            $existing = DB::table('users')->where('id', $row->id_user)->first();

            if ($existing) {
                DB::table('users')->where('id', $row->id_user)->update($data);
                $update++;
            } else {
                DB::table('users')->insert([
                    'id'             => $row->id_user,
                    ...$data,
                    'remember_token' => null,
                    'created_at'     => now(),
                ]);
                $insert++;
            }
        }

        $this->table(
            ['Insert', 'Update', 'Password Lama Dipakai (Bcrypt)', 'Password Reset (87654321!)'],
            [[$insert, $update, $keptOldPassword, ($insert + $update) - $keptOldPassword]]
        );

        $this->newLine();
        $this->info('Selesai.');

        return Command::SUCCESS;
    }
}