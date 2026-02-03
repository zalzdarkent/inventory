# Inventory Management System

## 📋 Daftar Isi
- [Deskripsi Sistem](#deskripsi-sistem)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Penggunaan](#penggunaan)

---

## Deskripsi Sistem

**Inventory Management System** adalah aplikasi web yang dirancang untuk mengelola persediaan barang secara efisien. Sistem ini membantu bisnis dalam:
- Melacak stok barang secara real-time
- Mengelola lokasi penyimpanan inventaris
- Mencatat pergerakan barang masuk dan keluar
- Menganalisis pola pergerakan inventaris
- Membuat laporan dan insight bisnis

Sistem ini dilengkapi dengan dashboard interaktif untuk monitoring yang mudah dan intuitif.

---

## Fitur Utama

### 1. **Dashboard Inventory** 📊
- Visualisasi ringkasan inventory secara keseluruhan
- Grafik pergerakan barang harian
- Insight dan statistik inventory
- Filter data berdasarkan periode waktu
- Monitoring occupancy lokasi penyimpanan

### 2. **Manajemen Barang (Item)** 📦
- Tambah, edit, dan hapus data barang
- Kelompokkan barang berdasarkan kategori
- Tracking stok real-time
- Riwayat perubahan barang

### 3. **Manajemen Lokasi (Location)** 📍
- Kelola lokasi/gudang penyimpanan
- Monitoring kapasitas lokasi
- Alokasi barang per lokasi

### 4. **Riwayat Inventory** 📜
- Log lengkap pergerakan barang masuk (IN), keluar (OUT), dan koreksi (ADJ)
- Tracking adjustment/koreksi stok
- Laporan perubahan inventory
- Filter dan pencarian data historis

### 5. **Sistem Autentikasi** 🔐
- Login dengan Email/Username dan Password
- Manajemen user dan hak akses
- Session management yang aman

---

## Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| **Backend** | PHP 7.4+ |
| **Database** | SQL Server (MSSQL) |
| **Frontend** | HTML5, CSS3, Bootstrap 5 |
| **JavaScript** | Vanilla JS + Chart.js |
| **Server** | Apache/Nginx |

---

## Persyaratan Sistem

- **Server Web**: Apache atau Nginx
- **PHP**: Versi 7.4 atau lebih tinggi
- **Database**: SQL Server 2016 atau lebih tinggi
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)
- **RAM Minimal**: 2GB
- **Storage**: 1GB minimum

---

## Instalasi

### 1. **Persiapan**
```bash
# Clone atau copy project ke folder web server
cp -r inventory /var/www/html/

# Pastikan folder uploads memiliki permission yang tepat
chmod -R 755 assets/uploads/
```

### 2. **Konfigurasi Database**
```php
# Edit file: config/koneksi.php
$server = "nama_server_anda";
$user = "sa";
$password = "password_anda";
$database = "nama_database";
```

### 3. **Testing Koneksi**
```bash
# Akses file test koneksi
http://localhost/inventory/config/test_sqlsrv.php
```

### 4. **Setup Database**
- Pastikan database SQL Server sudah dibuat
- Jalankan script setup database (jika ada)
- Verifikasi tabel sudah tersedia

### 5. **Akses Aplikasi**
```
URL: http://localhost/inventory/
Username: (sesuai user yang dibuat)
Password: (sesuai password)
```

---

## Penggunaan

### Workflow Standar

**1. Login ke Sistem**
- Masukkan email/username dan password
- Klik tombol Login

**2. Dashboard**
- Lihat overview inventory secara real-time
- Gunakan filter untuk periode tertentu
- Analisis insight dan statistik

**3. Manajemen Barang**
- Navigasi ke Menu Barang (Item)
- Tambah barang baru
- Update stok barang
- Lihat riwayat perubahan

**4. Manajemen Lokasi**
- Setup lokasi/gudang penyimpanan
- Monitor kapasitas lokasi
- Alokasikan barang ke lokasi

**5. Riwayat Inventory**
- Catat pergerakan barang IN/OUT
- Lihat log lengkap perubahan inventory
- Export laporan jika diperlukan

---

## Struktur Folder

```
inventory/
├── config/              # Konfigurasi database dan koneksi
├── module/              # Module utama aplikasi
│   ├── Action/          # Business logic
│   └── modal/           # Modal dialogs
├── query/               # SQL queries
├── ui/                  # Template UI
│   ├── pages/           # Halaman-halaman
│   ├── Header/
│   ├── Navbar/
│   └── Footer/
├── assets/              # CSS, JS, Images
│   ├── css/
│   ├── js/
│   └── images/
├── index.php            # Main entry point
├── login.php            # Login page
└── session.php          # Session management
```

---

## Fitur Keamanan

✅ Authentication & Authorization  
✅ SQL Injection Prevention (Prepared Statements)  
✅ CSRF Protection  
✅ Session Management  
✅ Password Hashing  
✅ Input Validation & Sanitization  

---

## Troubleshooting

### Masalah: "Connection to server failed"
- Verifikasi SQL Server sudah running
- Check credential di config/koneksi.php
- Pastikan firewall tidak memblokir port SQL Server

### Masalah: "Halaman tidak ditemukan"
- Clear browser cache
- Verify session sudah aktif
- Check routing di session.php

### Masalah: Upload file gagal
- Pastikan folder assets/uploads/ writable
- Check PHP max upload size setting

---

## Catatan Penting

- **Backup Database**: Lakukan backup rutin untuk data inventory
- **User Management**: Setup user dengan hak akses sesuai role
- **Log Audit**: Semua perubahan inventory tercatat dalam riwayat
- **Performance**: Untuk data besar, gunakan filter date range untuk optimal performance

---

## Support & Maintenance

Untuk pertanyaan atau perlu bantuan:
- Check dokumentasi di atas
- Review log error di application
- Hubungi tim development

---

**Inventory Management System v1.0**  
*Last Updated: January 2026*
