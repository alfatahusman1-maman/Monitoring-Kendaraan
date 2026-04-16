# 📸 Fitur Modal Preview Foto Kendaraan

## 🎯 Deskripsi Fitur

Fitur modal popup memungkinkan admin untuk:
1. **Klik foto kendaraan** di tabel untuk membuka popup
2. **Lihat preview gambar besar** di dalam modal
3. **Lihat detail kendaraan lengkap** dalam satu tampilan:
   - ID
   - No Polisi
   - Merk
   - Tipe
   - Tahun
   - Jenis
   - Kondisi

---

## ✨ Fitur-Fitur

### 1. **Modal Popup**
- Muncul dengan animasi fade-in yang smooth
- Ukuran responsif hingga 600px lebar
- Semi-transparent background

### 2. **Preview Gambar**
- Gambar ditampilkan besar (max 400px tinggi)
- Rounded corners dengan shadow effect
- Optimasi untuk berbagai ukuran foto

### 3. **Detail Kendaraan**
- Background abu-abu untuk kontras
- Layout dua kolom (Label | Value)
- Border separator antar baris

### 4. **Close Modal**
- Tombol X di kanan atas
- Bisa close dengan click di luar modal
- Animasi slide-up saat close

---

## 🎨 Styling

```css
/* Modal Container */
- Fixed positioning (overlay penuh layar)
- Fade-in animation (0.3s)
- Z-index: 1000 (di atas semua elemen)

/* Modal Content */
- White background dengan border-radius 12px
- Box shadow untuk depth
- Slide-in animation (0.3s)

/* Detail Section */
- Background #f9f9f9
- Padding 20px
- Detail rows dengan flex layout
```

---

## 💻 Implementasi

### HTML
```html
<div id="photoModal" class="modal">
    <div class="modal-content">
        <!-- Header dengan Close Button -->
        <div class="modal-header">
            <h2>📷 Detail Kendaraan</h2>
            <button class="modal-close" onclick="closePhotoModal()">&times;</button>
        </div>
        
        <!-- Body dengan Photo dan Details -->
        <div class="modal-body">
            <img id="modalPhoto" src="" alt="Foto Kendaraan">
            <div class="modal-details">
                <!-- Detail rows: ID, No Polisi, Merk, Tipe, Tahun, Jenis, Kondisi -->
            </div>
        </div>
    </div>
</div>
```

### JavaScript
```javascript
// Trigger modal
showPhotoModal(photoElement)

// Close modal
closePhotoModal()

// Trigger otomatis saat click foto
document.querySelectorAll('.photo-thumbnail').addEventListener('click', ...)
```

### Data Attributes
Setiap foto di tabel memiliki data attributes:
- `data-id`: ID kendaraan
- `data-no-polisi`: Nomor polisi
- `data-merk`: Merk kendaraan
- `data-tipe`: Tipe kendaraan
- `data-tahun`: Tahun pembuatan
- `data-jenis`: Jenis kendaraan (Roda Dua/Roda Empat)
- `data-kondisi`: Kondisi kendaraan
- `data-foto`: Nama file foto

---

## 🚀 Cara Menggunakan

1. Buka halaman "Data Kendaraan"
   ```
   http://localhost/monitoring_kendaraan/admin/kendaraan.php
   ```

2. Lihat tabel daftar kendaraan

3. **Klik pada foto kendaraan** untuk membuka modal preview

4. Modal akan menampilkan:
   - Foto kendaraan besar
   - Detail lengkap kendaraan

5. Untuk menutup modal:
   - Klik tombol X
   - Atau klik di luar modal area

---

## 🎭 Animasi

### Fade-In (Modal Muncul)
```css
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
duration: 0.3s
```

### Slide-In (Content Masuk)
```css
@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
duration: 0.3s
```

---

## 📱 Responsive

- **Desktop**: Modal max-width 600px
- **Tablet**: Modal width 90%
- **Mobile**: Full width dengan padding
- **Foto**: Max height 400px
- **Details**: Flex layout yang responsive

---

## 🔒 Security

✅ Data di-escape dengan `htmlspecialchars()`
✅ File check sebelum display foto
✅ Data attributes aman dari XSS
✅ Modal auto-close untuk safety

---

## ⚙️ Integrasi Database

Modal membaca data dari:
- Kolom `foto` - nama file di folder `/uploads/`
- Kolom `id`, `no_polisi`, `merk`, `tipe`, `tahun`, `jenis`, `kondisi`

---

## 🎯 File Dimodifikasi

- `admin/kendaraan.php` - Tambah modal HTML, CSS, JavaScript

---

## 💡 Tips

- Foto terbaik ukuran square (1:1 aspect ratio)
- Format yang didukung: JPG, JPEG, PNG, GIF
- Max file size bisa dikonfigurasi di form upload
