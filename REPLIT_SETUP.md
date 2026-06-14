# 🚀 SIMADU - Deployment Guide Replit (FREE)

> **Waktu Setup**: 15-20 menit | **Biaya**: GRATIS | **Kesulitan**: ⭐⭐ (Easy)

## 🎯 Apa itu Replit?

Replit adalah platform coding online yang:
- ✅ **GRATIS** untuk hosting
- ✅ Support PHP + MySQL
- ✅ Setup otomatis & cepat
- ✅ Perfect untuk testing & demo
- ✅ Instant URL untuk sharing
- ⚠️ Beberapa batasan: kalau tidak ada traffic selama 1 jam, server tidur (free plan)

---

## 📋 Langkah-Langkah Setup (15-20 menit)

### **Step 1: Buat Akun Replit**
1. Buka https://replit.com
2. Click "Sign Up"
3. Daftar dengan Email / GitHub / Google
4. Verifikasi email (jika pakai email)

### **Step 2: Import Project dari GitHub**
1. Di Replit Dashboard, click **"+ Create"**
2. Pilih **"Import from GitHub"**
3. Paste URL: `https://github.com/n4uf4/SIMADU`
4. Pastikan **branch** adalah `tampilkan`
5. Click **"Import"**
6. Tunggu ~2-3 menit sampai project selesai di-import

### **Step 3: Setup Database MySQL**

Replit menyediakan **MySQL built-in**. Cara setup:

**3a. Akses Database Shell**
1. Di Replit, klik tab **"Database"** (atau **"Tools"** → **"Database"**)
2. Pilih **"MySQL"**
3. Replit otomatis create database & user untuk Anda

**3b. Catat Database Credentials**
Replit akan menunjukkan:
```
Host: [akan ditampilkan di database tab]
User: [akan ditampilkan di database tab]
Password: [akan ditampilkan di database tab]
Database: [akan ditampilkan di database tab]
Port: 3306
```

Copy & catat semua ini!

**3c. Import Database Schema**
1. Di Database tab, klik **"Console"** atau **"MySQL Console"**
2. Copy-paste SQL schema di bawah ini:

```sql
-- Buat database
CREATE DATABASE IF NOT EXISTS simadu;
USE simadu;

-- Tabel users (mahasiswa)
CREATE TABLE IF NOT EXISTS users (
  id_user INT PRIMARY KEY AUTO_INCREMENT,
  nim VARCHAR(20) UNIQUE NOT NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  password VARCHAR(255) NOT NULL,
  role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel kategori
CREATE TABLE IF NOT EXISTS kategori (
  id_kategori INT PRIMARY KEY AUTO_INCREMENT,
  nama_kategori VARCHAR(100) NOT NULL,
  deskripsi TEXT
);

-- Tabel laporan
CREATE TABLE IF NOT EXISTS laporan (
  id_laporan INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NOT NULL,
  id_kategori INT NOT NULL,
  judul VARCHAR(255) NOT NULL,
  isi_laporan TEXT NOT NULL,
  is_anonim BOOLEAN DEFAULT FALSE,
  is_privat BOOLEAN DEFAULT FALSE,
  status ENUM('pending', 'proses', 'selesai') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user),
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
);

-- Tabel bukti (foto)
CREATE TABLE IF NOT EXISTS bukti (
  id_bukti INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  nama_file VARCHAR(255),
  path_file VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE
);

-- Tabel tanggapan admin
CREATE TABLE IF NOT EXISTS tanggapan (
  id_tanggapan INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  tanggapan TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE
);

-- Insert kategori default
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Pelecehan Seksual', 'Laporan pelecehan atau kekerasan seksual'),
('Diskriminasi', 'Laporan diskriminasi berdasarkan ras, agama, atau identitas lainnya'),
('Kekerasan Fisik', 'Laporan kekerasan fisik atau penganiayaan'),
('Bullying', 'Laporan perundungan atau bullying'),
('Korupsi/Suap', 'Laporan korupsi atau suap akademik'),
('Lainnya', 'Kategori laporan lainnya yang tidak tersebut');

-- Insert test users
INSERT INTO users (nim, nama, email, password, role) VALUES
('257411000', 'Mahasiswa Test', 'mahasiswa@test.com', '12345678', 'mahasiswa'),
('111111111', 'Admin Test', 'admin@test.com', 'password11', 'admin');

-- Insert test data
INSERT INTO laporan (id_user, id_kategori, judul, isi_laporan, is_anonim, is_privat, status) VALUES
(1, 1, 'Laporan Anonim Test', 'Ini adalah laporan test anonim', TRUE, FALSE, 'pending'),
(1, 2, 'Laporan Privat Test', 'Ini adalah laporan test privat', FALSE, TRUE, 'proses');
```

3. Paste semua query di atas ke Console
4. Press Enter/Execute
5. Tunggu sampai semua table terbuat

---

### **Step 4: Update Config File**

1. Di Replit, buka file **`config/koneksi.php`**
2. Ganti credentials dengan yang dari Step 3b:

```php
<?php
// config/koneksi.php

$host = 'YOUR_REPLIT_HOST';        // dari database tab
$user = 'YOUR_REPLIT_USER';        // dari database tab
$pass = 'YOUR_REPLIT_PASSWORD';    // dari database tab
$db = 'simadu';                     // nama database

// Establish connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

?>
```

3. Save file

---

### **Step 5: Konfigurasi Upload Folder**

SIMADU butuh folder untuk upload foto. Di Replit:

1. Buat folder baru untuk uploads:
   - Click **"+ New Folder"**
   - Nama: `uploads`
   - Create di root project

2. Set permissions (lewat Console):
   ```bash
   chmod 755 uploads
   chmod 755 uploads/*
   ```

---

### **Step 6: Run Server**

Di Replit:
1. Click tombol **"Run"** di atas (atau tekan `Ctrl+Enter`)
2. Replit otomatis jalankan `php -S 127.0.0.1:3000` (atau port lain)
3. URL akan muncul di tab **"Webview"** atau **"Browser"**
4. Biasanya: `https://[project-name].[username].repl.co`

---

### **Step 7: Test Aplikasi**

1. **Login Mahasiswa**:
   - NIM: `257411000`
   - Password: `12345678`
   - Test: Buat laporan, upload foto, edit, hapus

2. **Login Admin**:
   - NIM: `111111111`
   - Password: `password11`
   - Test: Lihat laporan, ubah status, berikan tanggapan

3. **Verifikasi Fitur**:
   - ✅ Create laporan
   - ✅ Upload foto
   - ✅ Edit laporan (data disimpan ✅ FIXED!)
   - ✅ Delete laporan
   - ✅ View riwayat
   - ✅ Admin dashboard
   - ✅ Admin update status

---

## ⚠️ Important Notes

### **Database Persistence**
- Replit MySQL **tidak persistent** pada free plan
- Data akan hilang jika project sleep/restart
- Solusi:
  - Upgrade ke paid plan (data persistent)
  - Gunakan backup database secara berkala
  - Untuk production, gunakan shared hosting atau VPS

### **File Upload**
- Replit memiliki file system yang ephemeral
- Uploaded files bisa hilang saat project restart
- Solusi:
  - Gunakan cloud storage (Vercel Blob, AWS S3)
  - Atau upgrade ke paid Replit plan

### **Performance**
- Replit free plan memiliki resource terbatas
- App akan lambat kalau banyak traffic
- Kalau tidak ada traffic 1 jam, server otomatis tidur
- Saat ada request pertama, perlu wake-up time ~30 detik

---

## 🔧 Troubleshooting

### **Error: "Connection failed"**
- Check credentials di `config/koneksi.php`
- Pastikan MySQL database sudah dibuat
- Cek di Database tab apakah database & user sudah ada

### **404 Not Found**
- Pastikan URL sesuai: `https://[project].repl.co/index.php`
- Check file structure di Replit

### **Upload file tidak berfungsi**
- Pastikan folder `uploads` sudah dibuat
- Set permissions: `chmod 755 uploads`

### **Server tidak jalan**
- Check console untuk error messages
- Klik **"Run"** lagi
- Pastikan `php -S` command ada di `.replit` config

---

## 📱 Share dengan Orang Lain

Setelah deploy, Anda bisa share:
- **Public URL**: `https://[project-name].[username].repl.co`
- Orang lain bisa akses langsung
- Tidak perlu install apapun

---

## 🚀 Upgrade ke Hosting Berbayar

Kalau ingin:
- ✅ Database persistent
- ✅ File upload permanent
- ✅ Better performance
- ✅ Custom domain

Pilihan:

### **Shared Hosting** (Rp 25-100k/bulan)
- Providers: IDCloudHost, Rumah Web, Niagahoster
- Setup: ~1 jam
- Cocok untuk: Small-medium projects

### **VPS Cloud** (Rp 50-150k/bulan)
- Providers: DigitalOcean, Vultr, Contabo
- Setup: 1-2 jam
- Cocok untuk: Growing projects

Lihat `DEPLOYMENT_GUIDE.md` untuk detailed setup!

---

## ✅ Verification Checklist

Sebelum anggap selesai, pastikan:

- [ ] Akun Replit sudah buat
- [ ] Project sudah di-import dari GitHub
- [ ] MySQL database sudah setup
- [ ] Database schema sudah di-import
- [ ] Config file sudah diupdate
- [ ] Uploads folder sudah dibuat
- [ ] Server sudah running
- [ ] Mahasiswa bisa login
- [ ] Admin bisa login
- [ ] Bisa buat laporan
- [ ] Bisa upload foto
- [ ] Edit laporan data tersimpan ✅
- [ ] Bisa lihat riwayat
- [ ] Admin bisa update status

---

## 📞 Support

- GitHub: https://github.com/n4uf4/SIMADU
- Issues: https://github.com/n4uf4/SIMADU/issues
- Replit Help: https://docs.replit.com

---

**Selamat! SIMADU Anda sudah live & bisa di-share!** 🎉

Durasi setup: ~15-20 menit
Kesulitan: ⭐⭐ (Easy)
Cost: GRATIS 🎊
