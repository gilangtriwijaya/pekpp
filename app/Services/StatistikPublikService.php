<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatistikPublikService
{
    private const CACHE_VERSION = 'v1';

    public function cacheKey(?int $kegiatanId = null): string
    {
        $id = $this->resolveKegiatanId($kegiatanId);
        return sprintf('statpub:%s:k%s', self::CACHE_VERSION, $id);
    }

    public function rebuildCache(?int $kegiatanId = null): void
    {
        $ttl = max(60, (int) config('app.publik_cache_ttl', 600));
        $data = $this->buildPayload($kegiatanId);

        $wrapped = [
            'success'      => true,
            'app'          => config('app.code', config('app.name')),
            'resource'     => 'statistik',
            'generated_at' => now()->toIso8601String(),
            'cache_ttl'    => $ttl,
            'cache_hit'    => false,
            'data'         => $data,
        ];

        Cache::store('file')->put($this->cacheKey($kegiatanId), $wrapped, $ttl);
    }

    public function buildPayload(?int $kegiatanId = null): array
    {
        $periodeId = $this->resolveKegiatanId($kegiatanId);

        // ── Total UPP aktif di periode ini ──────────────────────────────
        $totalUpp = DB::table('upps')
            ->where('aktif', 1)
            ->whereNull('deleted_at')
            ->count();

        // ── UPP yang sudah ada f01_pengisian versi terbaru di periode ini
        $totalMengisi = DB::table('f01_pengisian')
            ->where('periode_id', $periodeId)
            ->where('is_latest_version', 1)
            ->whereIn('status', ['submitted', 'selesai'])
            ->whereNull('deleted_at')
            ->distinct('upp_id')
            ->count('upp_id');

        $persentase = $totalUpp > 0
            ? round(($totalMengisi / $totalUpp) * 100, 2)
            : 0;

        // ── Rata-rata total_nilai dari f02_validasi yang selesai ─────────
        $avgNilai = DB::table('f02_validasi')
            ->where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->avg('total_nilai') ?? 0;

        // ── Donut kategori berdasar nilai_mentah f02_validasi ────────────
        // Kategori berdasar nilai_mentah (skala bebas, sesuaikan range jika perlu)
        $validasiRows = DB::table('f02_validasi')
            ->where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->pluck('nilai_mentah');

        $kategoriCount = [
            'Sangat Rendah' => 0,
            'Rendah'        => 0,
            'Sedang'        => 0,
            'Tinggi'        => 0,
            'Sangat Tinggi' => 0,
        ];

        foreach ($validasiRows as $nilai) {
            $n = (float) $nilai;
            if ($n <= 20)      $kategoriCount['Sangat Rendah']++;
            elseif ($n <= 35)  $kategoriCount['Rendah']++;
            elseif ($n <= 50)  $kategoriCount['Sedang']++;
            elseif ($n <= 65)  $kategoriCount['Tinggi']++;
            else               $kategoriCount['Sangat Tinggi']++;
        }

        // ── Bar per UPP (nilai_mentah, diurutkan tertinggi) ─────────────
        $barUpp = DB::table('f02_validasi as v')
            ->join('f01_pengisian as p', 'p.id', '=', 'v.f01_pengisian_id')
            ->join('upps as u', 'u.id', '=', 'p.upp_id')
            ->where('v.periode_id', $periodeId)
            ->where('v.status', 'selesai')
            ->where('p.is_latest_version', 1)
            ->select('u.nama', DB::raw('SUM(v.nilai_mentah) as total'))
            ->groupBy('u.id', 'u.nama')
            ->orderByDesc('total')
            ->get();

        // ── Bar per Aspek (rata-rata nilai per aspek) ────────────────────
        $barAspek = DB::table('aspek as a')
            ->join('indikator as i', 'i.aspek_id', '=', 'a.id')
            ->join('f01_indikator_nilai as n', 'n.indikator_id', '=', 'i.id')
            ->join('f01_pengisian as p', 'p.id', '=', 'n.f01_pengisian_id')
            ->where('p.periode_id', $periodeId)
            ->where('p.is_latest_version', 1)
            ->whereIn('p.status', ['submitted', 'selesai'])
            ->where('a.aktif', 1)
            ->whereNull('a.deleted_at')
            ->select('a.nama', DB::raw('ROUND(AVG(n.nilai), 2) as rata'))
            ->groupBy('a.id', 'a.nama')
            ->orderBy('a.urutan')
            ->get();

        return [
            'kegiatan_id' => $periodeId,
            'updated_at'  => now()->toIso8601String(),

            'ringkasan' => [
                'total_nilai_kabupaten' => round((float) $avgNilai, 2),
                'persentase_pengisian'  => $persentase,
                'total_opd_mengisi'     => $totalMengisi,
                'total_opd'             => $totalUpp,
            ],

            'donut_kategori' => [
                'labels' => array_keys($kategoriCount),
                'data'   => array_values($kategoriCount),
            ],

            'bar_opd' => [
                'labels' => $barUpp->pluck('nama')->toArray(),
                'data'   => $barUpp->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            ],

            'bar_variabel' => [
                'labels' => $barAspek->pluck('nama')->toArray(),
                'data'   => $barAspek->pluck('rata')->map(fn($v) => (float) $v)->toArray(),
            ],
        ];
    }

    private function resolveKegiatanId(?int $kegiatanId = null): int
    {
        if ($kegiatanId !== null && $kegiatanId > 0) return $kegiatanId;
        return max(1, (int) config('penilaian.kegiatan_id', 1));
    }
}
