<?php

namespace Database\Seeders;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Seeder;

class PengumumanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = User::first()?->id;

        Pengumuman::insert([
            [
                'judul'        => 'Pengisian F01 Periode 2026 Telah Dibuka',
                'isi'          => 'Kepada seluruh Admin UPP, pengisian formulir self-assessment F01 untuk Periode Penilaian 2026 telah resmi dibuka. Pastikan semua indikator diisi dengan lengkap beserta bukti dukung sebelum batas waktu yang ditentukan. Hubungi Admin Internal jika ada kendala teknis.',
                'aktif'        => true,
                'published_at' => now()->subDays(2),
                'expired_at'   => now()->addDays(30),
                'created_by'   => $adminId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'judul'        => 'Panduan Pengisian Bukti Dukung F01',
                'isi'          => 'Untuk memastikan kualitas penilaian, setiap indikator wajib dilengkapi dengan bukti dukung berupa URL dokumen yang valid. Format URL harus dapat diakses dan relevan dengan indikator yang bersangkutan. Panduan lengkap tersedia di menu Bantuan.',
                'aktif'        => true,
                'published_at' => now()->subDay(),
                'expired_at'   => null,
                'created_by'   => $adminId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
