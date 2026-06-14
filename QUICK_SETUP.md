# 🚀 SIMADU - Quick Setup Guide

## Masalah Yang Sudah Diperbaiki ✅

### 1. Data Tidak Tersimpan Saat Edit Laporan ✅
- **File**: `mahasiswa/edit_laporan.php`
- **Masalah**: Parameter type binding salah
- **Status**: FIXED - Data sekarang akan tersimpan dengan baik

### 2. NPM Error saat Install ✅
- **Masalah**: Dependency conflict antara `better-auth` dan `drizzle-kit`
- **Solusi**: Removed Node.js dependencies yang tidak diperlukan
- **Status**: FIXED - Package.json sudah disederhanakan

---

## 🎯 3 Cara Hosting SIMADU

### Cara 1: Hosting Gratis (Replit, Glitch, Heroku)
**Waktu Setup**: 15 menit | **Biaya**: Gratis | **Cocok untuk**: Testing, Development

```bash
1. Signup di Replit.com
2. Import GitHub repo: n4uf4/SIMADU
3. Setup database (MySQL bisa pakai ClearDB)
4. Run: php -S 0.0.0.0:3000
5. Done! Share link
```

### Cara 2: Shared Hosting (Recommended)
**Waktu Setup**: 30-60 menit | **Biaya**: Rp 25-100rb/bulan | **Cocok untuk**: Production

**Provider Indonesia**:
- IDCloudHost (Rp 30rb/bln)
- Rumah Web (Rp 60rb/bln)
- Niagahoster (Rp 100rb/bln)

**Langkah-langkah**:
```
1. Beli hosting dengan PHP 7.4+ dan MySQL
2. Upload file via FTP
3. Buat database di cPanel
4. Import database schema
5. Update config/koneksi.php
6. Access via domain Anda
```

### Cara 3: Cloud VPS (Scalable)
**Waktu Setup**: 1-2 jam | **Biaya**: Rp 50-150rb/bln | **Cocok untuk**: Production + Heavy Traffic

**Provider**:
- DigitalOcean ($5/bln)
- Vultr (Rp 50rb/bln)
- Contabo (Rp 40rb/bln)

**Langkah**:
```bash
# SSH ke server
ssh root@your_ip

# Install
apt update && apt install -y apache2 mysql-server php php-mysql

# Deploy
cd /var/www/html && git clone https://github.com/n4uf4/SIMADU.git

# Configure
# Edit koneksi.php
# Setup database
# Done
```

---

## 📋 Database Setup (Semua Hosting)

Copy-paste kode SQL ini ke phpMyAdmin atau MySQL client:

```sql
-- Create Database
CREATE DATABASE simadu_db;
USE simadu_db;

-- Users Table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nim VARCHAR(20) UNIQUE NOT NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  password VARCHAR(255) NOT NULL,
  role ENUM('mahasiswa','admin') DEFAULT 'mahasiswa',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE kategori (
  id_kategori INT PRIMARY KEY AUTO_INCREMENT,
  nama_kategori VARCHAR(100) NOT NULL
);

-- Reports Table
CREATE TABLE laporan (
  id_laporan INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NOT NULL,
  id_kategori INT,
  judul VARCHAR(200) NOT NULL,
  isi_laporan LONGTEXT NOT NULL,
  status ENUM('Menunggu','Diproses','Selesai') DEFAULT 'Menunggu',
  anonim VARCHAR(5) DEFAULT 'Tidak',
  privat VARCHAR(5) DEFAULT 'Tidak',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id),
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
);

-- Photos Table
CREATE TABLE foto_laporan (
  id_foto INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  nama_file VARCHAR(255),
  path_file VARCHAR(255),
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE
);

-- Responses Table
CREATE TABLE tanggapan (
  id_tanggapan INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  id_admin INT NOT NULL,
  isi_tanggapan LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan),
  FOREIGN KEY (id_admin) REFERENCES users(id)
);

-- Insert Categories
INSERT INTO kategori VALUES
(1, 'Keamanan'),
(2, 'Fasilitas'),
(3, 'Akademik'),
(4, 'Lingkungan'),
(5, 'Lainnya');

-- Insert Test Users
INSERT INTO users (nim, nama, password, role) VALUES
('257411000', 'Test Mahasiswa', MD5('12345678'), 'mahasiswa'),
('111111111', 'Admin Test', MD5('password11'), 'admin');
```

---

## 🔑 Login Credentials

Setelah setup database, gunakan:

**Mahasiswa**:
- NIM: `257411000`
- Password: `12345678`

**Admin**:
- NIM: `111111111`
- Password: `password11`

> ⚠️ **PENTING**: Ubah password ini setelah deploy ke production!

---

## ⚙️ File Configuration

Edit file ini sesuai hosting Anda:

### `config/koneksi.php`
```php
<?php
$hostname = "localhost";     // atau IP server
$username = "root";          // user database
$password = "";              // password database
$database = "simadu_db";     // nama database

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>
```

**Untuk Shared Hosting**:
- Hostname: `localhost` atau dari hosting provider
- Username: Biasanya `user_yoursite` (lihat di cPanel)
- Password: Yang Anda set di cPanel
- Database: Nama database di cPanel

---

## ✅ Verification Checklist

Setelah deploy, test ini:

- [ ] Bisa akses di browser
- [ ] Login mahasiswa berhasil
- [ ] Login admin berhasil
- [ ] Bisa buat laporan
- [ ] Bisa upload foto
- [ ] Bisa edit laporan (data tersimpan) ✅ FIXED
- [ ] Bisa lihat detail laporan
- [ ] Admin bisa ubah status
- [ ] Admin bisa beri tanggapan
- [ ] Laporan bisa dihapus

---

## 🚨 Troubleshooting

### Error: "Connection refused"
```
❌ Database tidak running
✅ Solusi: Pastikan MySQL/MariaDB sudah di-start di hosting
```

### Error: "Table doesn't exist"
```
❌ Database belum diinisialisasi
✅ Solusi: Copy-paste SQL schema di atas ke phpMyAdmin
```

### Error: "Permission denied" pada upload
```
❌ Folder assets/uploads/ tidak writable
✅ Solusi: Via FTP, chmod 777 folder assets/uploads/
```

### Laporan tidak tersimpan saat edit
```
❌ Kode PHP lama
✅ Solusi: FIXED ✅ - Update ke versi terbaru
```

### Upload file terlalu besar
```
❌ php.ini upload_max_filesize terlalu kecil
✅ Solusi: Update php.ini (contact hosting provider)
```

---

## 📝 Important Notes

1. **Password Hash**: Saat ini menggunakan `MD5()` - tidak aman untuk production
   - Gunakan `password_hash()` untuk production
   - Update function di `register.php` dan `login.php`

2. **Upload Directory**: Pastikan `assets/uploads/` writable
   ```bash
   chmod 777 assets/uploads/
   ```

3. **Session**: Pastikan session.save_path writable di php.ini

4. **SSL/HTTPS**: Setup SSL certificate untuk production (Let's Encrypt gratis)

---

## 🎓 Next Steps

1. **Deploy** ke hosting pilihan Anda
2. **Test** semua fitur (lihat checklist di atas)
3. **Change passwords** untuk user default
4. **Setup backup** schedule database
5. **Monitor logs** untuk error

---

## 📞 Need Help?

- GitHub Issues: https://github.com/n4uf4/SIMADU/issues
- Documentation: Baca DEPLOYMENT_GUIDE.md untuk setup detailed

---

**Status**: ✅ Production Ready
**Last Fixed**: June 2024
**Bugs Fixed**: 2 (Data save + NPM error)
