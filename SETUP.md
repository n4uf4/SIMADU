# 🚀 Panduan Setup Sistem Pengaduan Mahasiswa

Dokumentasi lengkap untuk setup dan menjalankan sistem pengaduan mahasiswa.

## ✅ Persyaratan Sistem

- **Server**: Apache/Nginx dengan PHP 7.4+
- **Database**: MySQL 5.7+ atau MariaDB
- **Browser**: Chrome, Firefox, Safari, Edge (modern)
- **Disk Space**: Minimal 100MB

## 📋 Langkah-Langkah Instalasi

### Step 1: Prepare Server

Pastikan Anda memiliki:
- ✅ XAMPP / WAMP / LAMP sudah terinstall
- ✅ MySQL service running
- ✅ PHP 7.4+ enabled

### Step 2: Download Project

Clone dari repository atau extract ZIP:
```bash
git clone https://github.com/n4uf4/PENGADUANMHS.git
```

Atau untuk XAMPP, letakkan di folder `htdocs`:
```
C:\xampp\htdocs\PENGADUANMHS\
```

### Step 3: Setup Database

#### Cara A: Menggunakan phpMyAdmin (Mudah)

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Login dengan default credentials (user: root, password: kosong)
3. Klik **"Import"** di menu atas
4. Pilih file `database.sql` dari folder project
5. Klik **"Go"**
6. Database dan tabel akan otomatis terbuat ✅

#### Cara B: Menggunakan MySQL Command

1. Buka Command Prompt / Terminal
2. Masuk ke MySQL:
```bash
mysql -u root -p
```
3. Jalankan script:
```bash
source C:\path\to\PENGADUANMHS\database.sql;
```
Atau di Linux/Mac:
```bash
mysql -u root -p pengaduanmhs < database.sql
```

### Step 4: Konfigurasi Koneksi Database

1. Buka file `config/koneksi.php`
2. Sesuaikan dengan konfigurasi database Anda:

```php
<?php
$host = "localhost";      // Host MySQL
$user = "root";           // Username MySQL
$pass = "";               // Password MySQL (kosong jika default)
$db   = "pengaduanmhs";   // Nama database

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal : " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
```

**Contoh untuk kondisi berbeda:**

- **XAMPP Default**: `$pass = ""`
- **WAMP Default**: `$pass = ""`
- **Custom Password**: `$pass = "your_password"`
- **Remote Server**: Sesuaikan host, user, dan password

### Step 5: Setup Folder Upload

1. Buat folder `uploads` di root project (sudah ada defaultnya)
2. Set permission ke 755 (untuk Linux/Mac):
```bash
chmod 755 uploads
```

Atau di Windows, pastikan folder writable.

### Step 6: Jalankan Aplikasi

1. **XAMPP Users**:
   - Start Apache & MySQL dari XAMPP Control Panel
   - Buka browser: `http://localhost/PENGADUANMHS/`

2. **WAMP Users**:
   - Start WAMP
   - Buka browser: `http://localhost/PENGADUANMHS/`

3. **Live Server**:
   - Upload semua file ke server
   - Akses via domain: `https://yourdomain.com/PENGADUANMHS/`

## 🔐 Login Pertama Kali

### Admin Account
```
NIM:      111111111
Password: password11
```

### Akun Mahasiswa Demo
```
NIM:      257411023
Password: 12345678
```

### ⚠️ PENTING!
**Segera ganti password admin setelah login pertama kali!**

### Membuat Akun Mahasiswa
1. Klik link **"Daftar"** di halaman login
2. Isi form registrasi dengan data valid
3. Gunakan akun tersebut untuk login

## 🎮 Cara Menggunakan

### Sebagai Mahasiswa

1. **Dashboard**: Lihat statistik laporan Anda
   - Total laporan
   - Status menunggu, diproses, selesai

2. **Buat Laporan Baru**:
   - Pilih kategori
   - Isi judul dan isi laporan
   - Upload foto (optional)
   - Pilih anonim/privat jika diperlukan
   - Klik "Kirim"

3. **Lihat Riwayat**:
   - Daftar semua laporan Anda
   - Lihat detail laporan
   - Edit/hapus laporan sendiri
   - Lihat tanggapan admin

4. **Detail Laporan**:
   - Lihat informasi lengkap
   - Lihat foto bukti
   - Baca tanggapan admin

### Sebagai Admin

1. **Dashboard**: Overview sistem
   - Total laporan di sistem
   - Statistik status laporan

2. **Kelola Laporan**:
   - Filter berdasarkan status
   - Cari laporan
   - Klik detail untuk aksi lanjutan

3. **Detail Laporan**:
   - Lihat informasi lengkap
   - Update status laporan
   - Tambah tanggapan

4. **Arsip Laporan**:
   - Arsipkan laporan selesai
   - Lihat histori arsip
   - Restore laporan jika diperlukan

## 🐛 Troubleshooting

### Problem: Halaman Putih / Error

**Solusi**:
1. Check file `database.sql` sudah dijalankan
2. Check koneksi di `config/koneksi.php`
3. Check PHP error log

### Problem: Upload Foto Gagal

**Solusi**:
1. Check folder `assets/uploads` writable
2. Check ukuran file (max 5MB)
3. Check format (jpg, png, gif)
4. Check disk space cukup

### Problem: Login Tidak Bisa

**Solusi**:
1. Pastikan database sudah setup
2. Cek NIM dan password benar
3. Coba reset password (buat akun baru)
4. Check MySQL running

### Problem: Foto Tidak Tampil

**Solusi**:
1. Check path_file di database benar
2. Check folder `assets/uploads` ada dan readable
3. Re-upload foto

### Problem: Session Hilang

**Solusi**:
1. Check `php.ini` session setting
2. Check folder `tmp` writable
3. Clear browser cache
4. Jalankan login ulang

## 📊 Database Info

### Default Data
```
Database: pengaduanmhs

Tabel utama:
- users (mahasiswa & admin)
- laporan (laporan pengaduan)
- kategori (kategori laporan)
- foto_laporan (bukti foto)
- tanggapan (respon admin)
- arsip (laporan terarsipkan)

Kategori default:
- Akademik
- Fasilitas
- Keuangan
- Dosen
- Lainnya
```

## 🔒 Security Tips

1. **Ganti Password Admin**
   - Login sebagai admin
   - (Feature akan ditambahkan di version berikutnya)

2. **Regular Backup**
   ```bash
   mysqldump -u root -p pengaduanmhs > backup.sql
   ```

3. **Folder Permissions**
   ```bash
   chmod 755 uploads
   chmod 644 config/koneksi.php
   ```

4. **HTTPS untuk Live Server**
   - Gunakan SSL Certificate
   - Update `BETTER_AUTH_URL` jika ada

## 🚀 Deployment ke Live Server

### Step 1: Upload Files
Upload semua file ke server hosting (via FTP/SFTP)

### Step 2: Setup Database
Import `database.sql` ke database production

### Step 3: Update Config
Edit `config/koneksi.php` dengan credentials production

### Step 4: Set Permissions
```bash
chmod 755 uploads
chmod 755 config/
```

### Step 5: Test
Akses via domain Anda

## 📞 Support & Bantuan

Jika mengalami masalah:
1. Check dokumentasi di README.md
2. Review troubleshooting section di atas
3. Check file `database.sql` sudah lengkap
4. Hubungi developer

## ✨ Next Features (Planned)

- [ ] Change password functionality
- [ ] Email notifications
- [ ] Advanced reporting & analytics
- [ ] Mobile app version
- [ ] API endpoints
- [ ] Real-time notifications

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Status**: Production Ready ✅
