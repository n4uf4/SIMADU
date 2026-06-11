# 📋 Sistem Pengaduan Mahasiswa

Sistem manajemen pengaduan online untuk memudahkan mahasiswa dalam menyampaikan keluhan/aspirasi kepada kampus dan membantu pihak kampus mengelola laporan secara terstruktur.

## 🎯 Tujuan Proyek

- Mempermudah mahasiswa menyampaikan keluhan atau aspirasi
- Membantu kampus mengelola dan menindaklanjuti laporan terstruktur
- Meningkatkan transparansi penanganan pengaduan
- Menyediakan dokumentasi digital riwayat laporan dan tanggapan
- Mengurangi proses pengaduan manual

## ✨ Fitur Utama

### Mahasiswa
- ✅ Registrasi dan Login menggunakan NIM
- ✅ Membuat laporan pengaduan
- ✅ Upload foto bukti kejadian
- ✅ Laporan anonim (nama tidak ditampilkan)
- ✅ Laporan privat (hanya admin yang lihat)
- ✅ Lihat riwayat laporan
- ✅ Lihat status dan tanggapan admin
- ✅ Edit dan hapus laporan sendiri

### Administrator
- ✅ Login admin
- ✅ Dashboard dengan statistik laporan
- ✅ Lihat detail laporan lengkap
- ✅ Lihat foto bukti
- ✅ Ubah status laporan
- ✅ Berikan tanggapan resmi
- ✅ Cari dan filter laporan
- ✅ Arsipkan laporan

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3
- **Server**: Apache/Nginx

## 📦 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/n4uf4/PENGADUANMHS.git
cd PENGADUANMHS
```

### 2. Setup Database
- Buka phpMyAdmin atau MySQL CLI
- Jalankan script `database.sql`:
```sql
source database.sql;
```

Atau import manual:
1. Buat database `pengaduanmhs`
2. Import file `database.sql`

### 3. Konfigurasi Database
Edit file `config/koneksi.php` jika diperlukan:
```php
$host = "localhost";  // Sesuaikan host
$user = "root";       // Username database
$pass = "";           // Password database
$db   = "pengaduanmhs";  // Nama database
```

### 4. Setup Folder Upload
Buat folder untuk upload foto:
```bash
mkdir assets\\uploads
chmod 755 assets\\uploads
```

### 5. Jalankan Aplikasi
- Letakkan project di folder `htdocs` (XAMPP) atau `www` (WAMP)
- Akses via browser: `http://localhost/PENGADUANMHS/`

## 👥 Login Default

### Admin
- **NIM**: 111111111
- **Password**: password11

### Mahasiswa Demo
- **NIM**: 257411023
- **Password**: 12345678

⚠️ **PENTING**: Ganti password admin setelah login pertama kali!

### Mahasiswa
- Daftar akun baru melalui halaman registrasi

## 📂 Struktur Folder

```
PENGADUANMHS/
├── admin/              # Halaman admin
│   ├── dashboard.php   # Dashboard admin
│   ├── laporan.php     # Daftar laporan
│   ├── detail_laporan.php
│   └── arsip.php       # Arsip laporan
├── auth/               # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── mahasiswa/          # Halaman mahasiswa
│   ├── dashboard.php
│   ├── tambah_laporan.php
│   ├── riwayat_laporan.php
│   ├── detail_laporan.php
│   └── edit_laporan.php
├── config/
│   └── koneksi.php     # Database connection
├── assets/uploads/     # Folder untuk foto bukti
├── database.sql        # Script database
├── index.php
└── README.md
```

## 🗄️ Database Schema

### Tabel Users
- `id` - ID user
- `nim` - Nomor Induk Mahasiswa
- `nama_lengkap` - Nama lengkap
- `no_hp` - Nomor HP
- `password` - Password (hashed)
- `role` - Role (mahasiswa/admin)

### Tabel Laporan
- `id_laporan` - ID laporan
- `id_user` - User ID (pembuat laporan)
- `judul` - Judul laporan
- `isi_laporan` - Isi/deskripsi
- `id_kategori` - Kategori
- `status` - Status (Menunggu/Diproses/Selesai)
- `anonim` - Laporan anonim (Ya/Tidak)
- `privat` - Laporan privat (Ya/Tidak)
- `created_at` - Tanggal dibuat

### Tabel Kategori
- `id_kategori` - ID kategori
- `nama_kategori` - Nama kategori

### Tabel Foto Laporan
- `id_foto` - ID foto
- `id_laporan` - ID laporan
- `nama_file` - Nama file
- `path_file` - Path file

### Tabel Tanggapan
- `id_tanggapan` - ID tanggapan
- `id_laporan` - ID laporan
- `id_admin` - Admin ID (pemberi tanggapan)
- `tanggapan` - Isi tanggapan
- `created_at` - Tanggal tanggapan

### Tabel Arsip
- `id_arsip` - ID arsip
- `id_laporan` - ID laporan
- `diarsipkan_oleh` - Admin ID
- `keterangan` - Keterangan
- `created_at` - Tanggal diarsipkan

## 🔒 Keamanan

- Password di-hash menggunakan `password_hash()` (PASSWORD_DEFAULT)
- Prepared statements untuk mencegah SQL Injection
- Input validation dan sanitasi
- Session management yang aman

## 📝 Kategori Laporan

Default kategori yang tersedia:
- Akademik
- Fasilitas
- Keuangan
- Dosen
- Lainnya

## 🚀 Deployment

Untuk deploy ke server production:
1. Ubah konfigurasi database di `config/koneksi.php`
2. Set permission folder `uploads` ke 755
3. Konfigurasi domain dan SSL
4. Ganti password admin default

## 🐛 Troubleshooting

### Error: Database Connection Failed
- Pastikan MySQL running
- Check username, password, dan nama database di `config/koneksi.php`

### Error: Upload Foto Gagal
- Check folder `uploads` ada dan writable
- Check ukuran file (max 5MB)
- Check format file (jpg, png, gif)

### Error: Login Gagal
- Pastikan NIM dan password benar
- Gunakan admin default jika lupa

## 👨‍💻 Developer

Dikembangkan untuk keperluan UAS/Project Kampus

## 📄 Lisensi

Bebas digunakan untuk keperluan pendidikan dan internal kampus

## 📞 Support

Untuk pertanyaan dan support, silakan hubungi developer atau buat issue di repository
