# Button Styles Documentation

## Pengenalan
File `css/buttons.css` menyediakan styling tombol yang konsisten di seluruh aplikasi untuk halaman admin, keuangan, dan user. Semua tombol memiliki desain modern dengan gradient warna, shadow effect, dan smooth animation.

---

## Cara Penggunaan

### 1. Include CSS File di Halaman
Tambahkan di bagian `<head>` setiap halaman PHP (sebelum `</head>`):

```html
<link rel="stylesheet" href="../css/buttons.css">
```

Atau jika file berada di folder berbeda:
```html
<link rel="stylesheet" href="../../css/buttons.css">
```

---

## Tipe-Tipe Button

### PRIMARY BUTTON (Biru)
Untuk aksi utama seperti submit form, tambah data, update data.

**Kelas:** `.btn-primary` atau `<input type="submit">`

```html
<!-- HTML Button -->
<button type="submit" class="btn-primary">Simpan</button>

<!-- Input Submit (otomatis terdeteksi) -->
<input type="submit" value="Tambah Data">
<input type="submit" name="tambah" value="Tambah">
<input type="submit" name="update" value="Perbarui">

<!-- Link Button -->
<a href="page.php" class="btn btn-primary">Buka Halaman</a>
```

**Warna:** Gradient biru (#007bff → #0056b3)

---

### SUCCESS BUTTON (Hijau)
Untuk aksi positif seperti submit, simpan, atau konfirmasi.

**Kelas:** `.btn-success` atau `class="success"`

```html
<button class="btn-success">Konfirmasi</button>
<input type="submit" class="success" value="Setuju">
```

**Warna:** Gradient hijau (#28a745 → #1e7e34)

---

### DANGER BUTTON (Merah)
Untuk aksi berbahaya seperti hapus, atau pembatalan.

**Kelas:** `.btn-danger`, `class="delete"`, atau `name="hapus"`

```html
<!-- Metode 1 -->
<button class="btn-danger">Hapus</button>

<!-- Metode 2 -->
<button name="hapus" class="btn-danger">Hapus Data</button>

<!-- Link Delete -->
<a href="?action=delete&id=1" class="delete" onclick="return confirm('Yakin ingin dihapus?')">
    Hapus
</a>

<!-- Input Submit -->
<input type="submit" class="delete" value="Hapus">
```

**Warna:** Gradient merah (#dc3545 → #a71d2a)

---

### WARNING BUTTON (Oranye)
Untuk aksi yang memerlukan perhatian khusus.

**Kelas:** `.btn-warning` atau `class="warning"`

```html
<button class="btn-warning">Peringatan</button>
<input type="submit" class="warning" value="Tinjau">
```

**Warna:** Gradient kuning-oranye (#ffc107 → #e0a800)

---

### INFO BUTTON (Cyan)
Untuk aksi informasi atau lihat detail.

**Kelas:** `.btn-info` atau `class="info"`

```html
<button class="btn-info">Informasi</button>
<a href="detail.php?id=1" class="btn-info">Lihat Detail</a>
```

**Warna:** Gradient cyan (#17a2b8 → #0c5460)

---

### SECONDARY/CANCEL BUTTON (Abu-abu)
Untuk aksi membatalkan atau alternatif.

**Kelas:** `.btn-cancel`, `.btn-secondary`, atau `class="cancel"`

```html
<button class="btn-cancel">Batal</button>
<button type="reset" class="btn-secondary">Reset</button>
<a href="?back=true" class="btn-cancel">Kembali</a>
```

**Warna:** Gradient abu-abu (#6c757d → #495057)

---

### EDIT BUTTON
Button untuk edit data (warna hijau).

**Kelas:** `class="edit"`

```html
<a href="?action=edit&id=1" class="edit">Edit</a>
<button class="edit">Edit Data</button>
```

---

### VIEW BUTTON
Button untuk lihat detail (warna cyan).

**Kelas:** `class="view"`

```html
<a href="detail.php?id=1" class="view">Lihat</a>
<button class="view">Tampilkan</button>
```

---

## Button Sizes

### Small Button
Untuk tombol kompak dalam tabel atau list.

```html
<button class="btn-sm">Hapus</button>
<input type="submit" class="btn-sm" value="Edit">
```

**Ukuran:** padding 8px 16px, font-size 12px

---

### Normal Button (Default)
Ukuran standar untuk sebagian besar kasus.

```html
<button>Simpan</button>
<input type="submit" value="Tambah">
```

**Ukuran:** padding 12px 24px, font-size 14px

---

### Large Button
Untuk tombol utama yang prominent.

```html
<button class="btn-lg">Simpan Perubahan</button>
<input type="submit" class="btn-lg" value="Daftar Sekarang">
```

**Ukuran:** padding 14px 32px, font-size 16px

---

## Button Layouts

### Button Group (Horizontal)
Tombol dalam satu baris dengan gap konsisten.

```html
<div class="btn-group">
    <button class="btn-primary">Simpan</button>
    <button class="btn-cancel">Batal</button>
</div>
```

---

### Full Width Button (Block)
Tombol yang memenuhi seluruh lebar container.

```html
<button class="btn-block btn-primary">Simpan Data</button>
```

---

## Fitur Tambahan

### Disabled State
Tombol yang tidak aktif.

```html
<button disabled>Tidak Aktif</button>
<input type="submit" disabled value="Tunggu...">
```

**Efek:** opacity 50%, cursor not-allowed

---

### Loading State
Menambahkan visual loading pada tombol.

```html
<button class="loading">Memproses...</button>
```

**Efek:** Menambahkan ikon ⏳ secara otomatis

---

### Icon + Text
Menambahkan icon pada tombol.

```html
<button class="btn-primary">
    <i class="fas fa-save"></i> Simpan
</button>

<a href="delete?id=1" class="btn-danger">
    <i class="fas fa-trash"></i> Hapus
</a>
```

**Catatan:** Gunakan Font Awesome atau icon library lainnya

---

## Contoh Implementasi Lengkap

### Form dengan Multiple Buttons

```html
<form method="POST">
    <div class="form-group">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" class="form-control" required>
    </div>
    
    <div class="btn-group">
        <input type="submit" name="tambah" class="btn-primary" value="Tambah">
        <button type="reset" class="btn-secondary">Reset</button>
        <a href="back.php" class="btn-cancel">Batal</a>
    </div>
</form>
```

### Tabel dengan Action Buttons

```html
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Data 1</td>
            <td>
                <a href="?action=edit&id=1" class="edit btn-sm">Edit</a>
                <a href="?action=delete&id=1" class="delete btn-sm" onclick="return confirm('Yakin?')">Hapus</a>
            </td>
        </tr>
    </tbody>
</table>
```

---

## Responsive Behavior

### Desktop (> 768px)
- Button ukuran normal dengan padding penuh
- Button group dalam satu baris (flex row)

### Mobile (≤ 768px)
- Button dengan padding lebih kecil
- Button group dalam kolom (flex column)
- Button memenuhi lebar layar

---

## Accessibility

### Focus State
Semua tombol memiliki outline focus untuk keyboard navigation:

```css
button:focus {
    outline: 2px solid rgba(0, 123, 255, 0.5);
    outline-offset: 2px;
}
```

### Color Contrast
Semua warna tombol memenuhi WCAG AA standards untuk contrast ratio.

---

## Animasi

### Hover Effect
- **Transform:** Bergerak naik 2px
- **Shadow:** Bertambah besar untuk efek elevation
- **Duration:** 0.3s smooth transition

### Active Effect
Saat diklik:
- Transform kembali ke posisi normal
- Shadow lebih kecil

### Optional Animation
Untuk efek bounce tambahkan `class="animate"` pada tombol.

---

## Color Palette Reference

| Tipe | Gradient | Hex |
|------|----------|-----|
| Primary | #007bff → #0056b3 | Biru |
| Success | #28a745 → #1e7e34 | Hijau |
| Danger | #dc3545 → #a71d2a | Merah |
| Warning | #ffc107 → #e0a800 | Kuning |
| Info | #17a2b8 → #0c5460 | Cyan |
| Secondary | #6c757d → #495057 | Abu-abu |
| Edit | #27ae60 → #1e7e34 | Hijau Tua |
| View | #17a2b8 → #0c5460 | Cyan |

---

## Tips & Best Practices

1. **Gunakan Semantic Class Names**
   - `.btn-primary` untuk aksi utama
   - `.btn-danger` untuk hapus
   - `.btn-success` untuk konfirmasi positif

2. **Konsisten dengan Label**
   - Gunakan kata kerja: Simpan, Hapus, Edit, Lihat
   - Jangan gunakan: Ok, Cancel, Yes, No

3. **Grouping Buttons**
   - Gunakan `.btn-group` untuk tombol terkait
   - Urutkan: Aksi utama (kiri) → Aksi alternatif (tengah) → Batal (kanan)

4. **Mobile First**
   - Ukuran tombol cukup untuk jari (min 44px)
   - Spacing antar tombol minimal 10px

5. **Accessibility**
   - Gunakan `<button>` untuk aksi JavaScript
   - Gunakan `<a>` untuk navigasi/link
   - Tambahkan `title` attribute untuk tooltip

---

## Browser Support

- Chrome ✅ (Semua versi)
- Firefox ✅ (Semua versi)
- Safari ✅ (Semua versi)
- Edge ✅ (Semua versi)
- IE 11 ⚠️ (Partial - tanpa CSS transitions)

---

## Troubleshooting

### Button tidak menampilkan warna
- Pastikan file CSS sudah di-include dengan path yang benar
- Clear browser cache (Ctrl+Shift+Delete)

### Gradient tidak tampil
- Pastikan browser support CSS gradient (sudah support di semua modern browsers)
- Fallback color akan digunakan untuk browser lama

### Button text terlalu besar/kecil
- Gunakan `.btn-sm` untuk button kecil
- Gunakan `.btn-lg` untuk button besar
- Adjust dengan custom font-size jika diperlukan

---

## Support & Questions

Jika ada pertanyaan atau saran, silakan hubungi tim development.
