# KAMUS METRIK ANALYTICS

Dokumen ini mendefinisikan metrik yang digunakan oleh modul Analisis. Untuk setiap metrik: sumber tabel, filter wajib, rumus/pseudocode, rounding, null handling, dan mode perhitungan (live/precompute/hybrid).

1) total_submits
- Nama: `total_submits`
- Sumber: `f02_responses`
- Filter wajib: `is_latest_version = 1`, `periode_id`, optional filters (upp_id, opd_id, aspek_id, indikator_id)
- Formula: `COUNT(*)`
- Rounding / tipe: integer
- Null handling: 0 (jika tidak ada baris)
- Mode: precompute (preferred). For small scopes can be live.

2) validated_count
- Nama: `validated_count`
- Sumber: `f02_responses`
- Filter wajib: same as `total_submits`
- Formula: `SUM(CASE WHEN validated = 1 THEN 1 ELSE 0 END)`
- Rounding: integer
- Null handling: 0
- Mode: precompute

3) pct_validated
- Nama: `pct_validated`
- Sumber: derived from `validated_count / total_submits * 100`
- Filter wajib: same
- Formula: `IF(total_submits = 0, 0, validated_count / total_submits * 100)`
- Rounding: 2 decimal
- Null handling: 0
- Mode: precompute

4) avg_score_aspek
- Nama: `avg_score_aspek`
- Sumber: `f02_responses`
- Filter wajib: `is_latest_version = 1`
- Formula: `AVG(score)` grouped by aspek
- Rounding: 2 decimal
- Null handling: null → treat as 0 in aggregate displays, but store null in aggregates if no data
- Mode: precompute/hybrid

5) avg_score_indikator
- Nama: `avg_score_indikator`
- Sumber: `f02_responses`
- Filter wajib: same
- Formula: `AVG(score)` grouped by indikator
- Rounding: 2 decimal
- Mode: precompute/hybrid

6) median_score
- Nama: `median_score`
- Sumber: `f02_responses`
- Filter wajib: same
- Formula: compute in aggregator job (PHP) or use MySQL 8+ window functions if small dataset
- Rounding: 2 decimal
- Mode: precompute (recommended)

7) avg_IPP
- Nama: `avg_IPP`
- Sumber: synthesized from F02 & F03 (F02*0.75 + F03*0.25)
- Filter wajib: `is_latest_version = 1` on both F02 and F03
- Formula: `IF(F03 missing, use F02 only)`
- Rounding: 2 decimal
- Mode: precompute (recommended); can be computed on-the-fly in small scopes

8) pending_count
- Nama: `pending_count`
- Sumber: derived from `total_submits - validated_count`
- Formula: `GREATEST(0, total_submits - validated_count)`
- Rounding: integer
- Mode: precompute

9) distribution_bins
- Nama: `distribution_bins`
- Sumber: `f02_responses` scores
- Filter wajib: same
- Formula: group `score` into configurable bins (preset names), return counts per bin
- Mode: precompute (store as JSON per aggregate row) or compute on-the-fly for narrow filters

10) top_upps / bottom_upps
- Nama: `ranked_upps`
- Sumber: `analytics_aggregates` / live combine
- Formula: order by `avg_IPP` desc/asc, tie-breaker by `total_responses`
- Mode: read from aggregates for speed

---

Contoh SQL (avg_score per aspek):

```sql
SELECT f.aspek_id, AVG(f.score) AS avg_score
FROM f02_responses f
WHERE f.periode_id = :periode_id
  AND f.is_latest_version = 1
GROUP BY f.aspek_id;
```

Guidelines:
- Semua perhitungan harus konsisten menggunakan `is_latest_version = 1` kecuali eksplisit ditulis lain.
- Rounding: gunakan `ROUND(value, 2)` saat menyimpan ke `analytics_aggregates`.
- Empty sets: store `total_responses = 0` and `avg_score = NULL` (presentasikan sebagai 0 atau '-') di UI sesuai konteks.
- Penyimpanan median: hitung di job agregator dan simpan dalam `median_score`.

Silakan tinjau kamus metrik ini; saya akan tambah metrik tambahan jika diperlukan.
