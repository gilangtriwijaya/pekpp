# Format Deskripsi Indikator - Narasi vs Poin

## Ringkasan
Field **Deskripsi** pada Indikator mendukung dua jenis format:
- **Narasi**: Text biasa rata kiri-kanan (tanpa nomor)
- **Poin**: Text dengan penomoran otomatis (1, 2, 3, ...)

---

## Cara Memilih Format

### Format Narasi (`/n`)
Tambahkan `/n` di **awal baris** untuk membuat narasi:

```
/nIni adalah kalimat narasi yang akan ditampilkan
tanpa nomor dan rata kiri-kanan
```

**Hasil Display:**
```
Ini adalah kalimat narasi yang akan ditampilkan tanpa nomor dan rata kiri-kanan
```

---

### Format Poin (Nomor Otomatis)
Baris tanpa `/n` akan menjadi poin dengan nomor otomatis:

```
Ini adalah poin pertama
Ini adalah poin kedua
Ini adalah poin ketiga
```

**Hasil Display:**
```
1. Ini adalah poin pertama
2. Ini adalah poin kedua
3. Ini adalah poin ketiga
```

---

## Kombinasi & Reset Penomoran

**Important:** Penomoran **RESET ke 1** setiap kali ada narasi (`/n`)

### Contoh Kompleks:
```
/nBagian Pendahuluan:
Poin pertama bagian 1
Poin kedua bagian 1
Poin ketiga bagian 1

/nBagian Kedua:
Poin pertama bagian 2
Poin kedua bagian 2

/nKesimpulan
Poin pertama kesimpulan
```

**Hasil Display:**
```
Bagian Pendahuluan:
1. Poin pertama bagian 1
2. Poin kedua bagian 1
3. Poin ketiga bagian 1

Bagian Kedua:
1. Poin pertama bagian 2 (Reset! Mulai dari 1 lagi)
2. Poin kedua bagian 2

Kesimpulan
1. Poin pertama kesimpulan (Reset! Mulai dari 1 lagi)
```

---

## Rules Penting

| Rule | Detail |
|------|--------|
| **Penanda** | Hanya `/n` (case-sensitive, jangan `/N` atau `\n`) |
| **Posisi** | Harus di **awal baris** (sebelum text) |
| **Marker Visible?** | TIDAK - marker `/n` hilang saat display |
| **Field** | Hanya **Deskripsi** saja (Bukti Dukung tidak terformat) |
| **Baris Kosong** | Baris kosong DIABAIKAN (tidak tampil) |
| **Reset** | Penomoran reset setiap ada narasi |

---

## Contoh Real-World

### Input di Textarea:
```
/nUKM wajib memiliki sistem mutu yang terinstregrasi mencakup aspek proses, produk, layanan, dan sumber daya manusia dalam menghasilkan produk berkualitas tinggi
Memiliki sertifikat sistem manajemen mutu (ISO 9001 atau setara)
Memiliki tim quality control yang terlatih dan bersertifikat
Melakukan audit internal minimal 1 kali per tahun
Melakukan corrective and preventive action (CAPA) secara berkala

/nPengimplementasian sistem mutu harus didukung dengan:
Dokumentasi lengkap standar operasional prosedur (SOP)
Pelatihan rutin untuk semua staff terkait mutu
Monitoring dan evaluasi KPI mutu secara statistik
Partisipasi dalam benchmarking dengan industri sejenis
```

### Output Display:
```
UKM wajib memiliki sistem mutu yang terinstregrasi mencakup aspek proses, produk, 
layanan, dan sumber daya manusia dalam menghasilkan produk berkualitas tinggi
1. Memiliki sertifikat sistem manajemen mutu (ISO 9001 atau setara)
2. Memiliki tim quality control yang terlatih dan bersertifikat
3. Melakukan audit internal minimal 1 kali per tahun
4. Melakukan corrective and preventive action (CAPA) secara berkala

Pengimplementasian sistem mutu harus didukung dengan:
1. Dokumentasi lengkap standar operasional prosedur (SOP)
2. Pelatihan rutin untuk semua staff terkait mutu
3. Monitoring dan evaluasi KPI mutu secara statistik
4. Partisipasi dalam benchmarking dengan industri sejenis
```

---

## Technical Implementation

### Files Modified:
1. **`resources/views/f01/indikator/index.blade.php`**
   - Added `formatDeskripsi()` helper function (line ~507)
   - Added `escapeHtml()` safety function
   - Updated `viewDetail()` to use formatDeskripsi()

2. **`resources/views/f01/indikator/modals/create.blade.php`**
   - Updated helper text dengan format tips

### JavaScript Functions:
```javascript
// Parse dan format deskripsi dengan /n markers
formatDeskripsi(text) {
    // Returns: HTML dengan poin terformat dan narasi rata kiri-kanan
}

// Escape HTML characters untuk security
escapeHtml(text) {
    // Prevent XSS attacks
}
```

---

## Debugging Tips

### Format Tidak Bekerja?
1. ✅ Pastikan penanda `/n` di **awal baris** (bukan ditengah)
2. ✅ Jangan gunakan `/N` (capital N) - harus lowercase `/n`
3. ✅ Pastikan Enter/newline setelah baris pertama
4. ✅ Cek di browser Console (F12) untuk error

### Contoh Errors:
```
❌ SALAH: "Teks /n narasi" (marker di tengah)
✅ BENAR: "/nTeks narasi" (marker di awal)

❌ SALAH: "/N Teks narasi" (capital N)
✅ BENAR: "/n Teks narasi" (lowercase n)

❌ SALAH: "/n\nTeks narasi" (extra newline)
✅ BENAR: "/nTeks narasi" (langsung ke text)
```

---

## Support

Untuk pertanyaan atau perubahan format, hubungi tim development.
