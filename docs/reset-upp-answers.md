# Reset UPP Answers Command

Command untuk reset/menghapus semua jawaban dari form F01, F02, dan F03 dengan aman.

## ⚠️ PENTING
- **Hanya menghapus jawaban/pengisian dari UPP** - bukan data master
- Semua data UPP, instrumen, aspek, indikator, pertanyaan TETAP AMAN
- Hard delete (permanent) - tidak bisa di-undo, pastikan backup dulu!

## Usage

### Reset satu UPP tertentu:
```bash
php artisan upp:reset-answers {upp_id}
```

Contoh:
```bash
php artisan upp:reset-answers 5
```

### Reset SEMUA UPP:
```bash
php artisan upp:reset-answers --all
```

## Yang Dihapus

### F01 - Pengisian Indikator (UPP Input):
- ✅ `f01_pengisian` - Record pengisian
- ✅ `f01_jawaban` - Jawaban pertanyaan
- ✅ `f01_indikator_nilai` - Nilai indikator
- ✅ `f01_indikator_bukti` - File bukti yang diunggah
- ✅ `f01_bukti_dukung` - URL bukti
- ✅ `f01_aspek_pengisian` - Status pengerjaan aspek

### F02 - Validasi (Validator Input):
- ✅ `f02_validasi` - Record validasi
- ✅ `f02_indikator_validasi` - Nilai validasi per indikator
- ✅ `f02_catatan_indikator` - Komentar validator

### F03 - Survey Kinerja (Responden Input):
- ✅ `f03_pengisian` - Submission survey
- ✅ `f03_jawaban` - Jawaban survey
- ✅ `f03_response_demographic` - Data demografis responden

## Yang TIDAK Dihapus (Data Master - AMAN)

- ✓ UPP data
- ✓ Aspek/Indikator/Pertanyaan (instrumen)
- ✓ Deskripsi indikator
- ✓ Periode
- ✓ F02 Skor template
- ✓ F03 Token & Template
- ✓ Semua user & role data
- ✓ Semua data konfigurasi

## Contoh Output

```
🔍 Checking data structure before reset...
  ✓ UPP (upp)
  ✓ Aspek (aspek)
  ✓ Indikator (indikator)
  ✓ Pertanyaan (pertanyaan)
  ✓ F01 Pengisian (f01_pengisian)
  ✓ F02 Validasi (f02_validasi)
  ✓ F03 Pengisian (f03_pengisian)

UPP ID 5 tidak ditemukan? [yes/no] : no

🔍 Menghitung data yang akan dihapus untuk UPP: RSUD Soedarso Pontianak
  F01 Pengisian: 3
  F02 Validasi: 2
  F03 Pengisian: 18
  Total: 23

Hapus semua jawaban UPP 'RSUD Soedarso Pontianak'? [yes/no] : yes

✅ 3 F01 Pengisian dihapus (hard delete)
✅ 2 F02 Validasi dihapus
✅ 18 F03 Pengisian dihapus (hard delete)

✅ Reset berhasil untuk UPP: RSUD Soedarso Pontianak

✅ VERIFIKASI DATA MASTER:
  ✓ Data UPP: 28 record
  ✓ Data Aspek: 45 record
  ✓ Data Indikator: 156 record
  ✓ Data Pertanyaan: 203 record
  ✓ Data Periode: 5 record
  ✓ Template F02 Scoring: 156 record
  ✓ Template F03 Aspek: 12 record
  ✓ Template F03 Indikator: 87 record
  ✓ Token F03: 142 record

📌 Semua data master tetap aman dan siap digunakan kembali!
```

## Flow Command

1. ✓ Validasi struktur tabel database
2. ✓ Validasi UPP ada (untuk single UPP mode)
3. ✓ Hitung data yang akan dihapus
4. ✓ Konfirmasi from user
5. ✓ Begin transaction
6. ✓ Hard delete all answers
7. ✓ Commit transaction
8. ✓ Verify data master tetap aman

## Catatan

- Menggunakan database transaction - jika error, semua perubahan di-rollback
- Hard delete untuk handle soft deleted records (F01 & F03 menggunakan SoftDeletes)
- Safe - tidak bisa menghapus data master atau instrumen
