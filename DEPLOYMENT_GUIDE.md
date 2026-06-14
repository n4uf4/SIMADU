# SIMADU - Deployment & Hosting Guide

## 🎯 Apa yang Sudah Diperbaiki

### ✅ Bug Penyimpanan Data (FIXED)
**Problem**: Data edit laporan mahasiswa tidak tersimpan
- **File**: `mahasiswa/edit_laporan.php` (Line 31)
- **Cause**: Type binding parameter salah (`'sssii'` → harus `'ssiii'`)
- **Status**: ✅ FIXED

### ✅ NPM Dependency Error (FIXED)
**Problem**: `npm install` error dengan `better-auth` dan `drizzle-kit`
- **Solution**: Removed unnecessary Node.js dependencies
- **New package.json**: PHP-only configuration
- **Status**: ✅ FIXED

---

## 📋 Requirements untuk Hosting

Aplikasi SIMADU memerlukan:

### Minimum Requirements
- **PHP**: 7.4 atau lebih tinggi (preferably 8.0+)
- **Database**: MySQL 5.7+ atau MariaDB 10.2+
- **Web Server**: Apache dengan mod_rewrite atau Nginx
- **Storage**: Minimal 1GB untuk uploads
- **RAM**: 512MB minimum

### Production Requirements
- **PHP**: 8.1 atau lebih tinggi
- **Database**: MySQL 8.0+ atau PostgreSQL 12+
- **SSL/TLS**: HTTPS certificate
- **Backup**: Automated daily backup
- **Monitoring**: PHP error logging, database monitoring

---

## 🚀 Local Development Setup

### Step 1: Database Setup

```sql
-- Create Database
CREATE DATABASE simadu_db;
USE simadu_db;

-- Create Users Table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nim VARCHAR(20) UNIQUE NOT NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
  tanggal_buat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status_aktif TINYINT DEFAULT 1
);

-- Create Categories Table
CREATE TABLE kategori (
  id_kategori INT PRIMARY KEY AUTO_INCREMENT,
  nama_kategori VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  icon VARCHAR(50)
);

-- Create Reports Table
CREATE TABLE laporan (
  id_laporan INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NOT NULL,
  id_kategori INT,
  judul VARCHAR(200) NOT NULL,
  isi_laporan LONGTEXT NOT NULL,
  status ENUM('Menunggu', 'Diproses', 'Selesai') DEFAULT 'Menunggu',
  anonim VARCHAR(5) DEFAULT 'Tidak',
  privat VARCHAR(5) DEFAULT 'Tidak',
  tanggal_buat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id),
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
);

-- Create Photo Table
CREATE TABLE foto_laporan (
  id_foto INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  nama_file VARCHAR(255) NOT NULL,
  path_file VARCHAR(255) NOT NULL,
  tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE
);

-- Create Response Table
CREATE TABLE tanggapan (
  id_tanggapan INT PRIMARY KEY AUTO_INCREMENT,
  id_laporan INT NOT NULL,
  id_admin INT NOT NULL,
  isi_tanggapan LONGTEXT,
  tanggal_tanggapan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE,
  FOREIGN KEY (id_admin) REFERENCES users(id)
);

-- Insert Default Categories
INSERT INTO kategori (nama_kategori, deskripsi, icon) VALUES
('Keamanan', 'Laporan keamanan kampus', 'security'),
('Fasilitas', 'Keluhan fasilitas umum', 'home'),
('Akademik', 'Masalah akademik', 'school'),
('Lingkungan', 'Keluhan lingkungan kampus', 'nature'),
('Lainnya', 'Kategori lainnya', 'more');

-- Insert Test Users
INSERT INTO users (nim, nama, password, role) VALUES
('257411000', 'Test Mahasiswa', PASSWORD('12345678'), 'mahasiswa'),
('111111111', 'Admin Test', PASSWORD('password11'), 'admin');
```

### Step 2: File Structure
```
project-root/
├── config/
│   └── koneksi.php          (Database connection)
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── forgot_password.php
├── mahasiswa/
│   ├── dashboard.php
│   ├── tambah_laporan.php
│   ├── edit_laporan.php
│   ├── hapus_laporan.php
│   ├── detail_laporan.php
│   └── riwayat_laporan.php
├── admin/
│   ├── dashboard.php
│   ├── laporan.php
│   ├── detail_laporan.php
│   ├── update_status.php
│   ├── tanggapan.php
│   ├── arsip.php
│   └── hapus_laporan.php
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/             (Photo storage)
├── includes/
│   └── head_stitch.php
└── index.php                (Entry point)
```

### Step 3: Local Testing
```bash
# Start PHP development server
php -S 127.0.0.1:8000

# Access application
# Browser: http://localhost:8000

# Login as Student
# NIM: 257411000
# Password: 12345678

# Login as Admin
# NIM: 111111111
# Password: password11
```

---

## 🌐 Hosting Recommendations

### Option 1: Shared Hosting (Budget Friendly)
**Recommended Providers**:
- **Hostinger** - Affordable, good PHP support
- **Bluehost** - Reliable, cPanel easy setup
- **SiteGround** - Premium but excellent support
- **IDCloudHost** - Indonesian provider, local support

**Setup Steps**:
1. Upload files via FTP/SFTP to `public_html` folder
2. Create MySQL database via cPanel
3. Update `config/koneksi.php` with database credentials
4. Set file permissions: `chmod 755` for directories, `644` for files
5. Create `assets/uploads/` directory with `chmod 777`

### Option 2: VPS (Scalable)
**Recommended Providers**:
- **DigitalOcean** - $5/month, good documentation
- **Linode** - Reliable, good performance
- **Vultr** - Fast, competitive pricing
- **Contabo** - Affordable, generous resources

**Setup Steps**:
```bash
# SSH into server
ssh root@your_server_ip

# Update system
apt update && apt upgrade -y

# Install LAMP Stack
apt install -y apache2 mysql-server php libapache2-mod-php php-mysql

# Enable mod_rewrite
a2enmod rewrite

# Start services
systemctl start apache2 mysql-server
systemctl enable apache2 mysql-server

# Clone repository
cd /var/www/html
git clone https://github.com/n4uf4/SIMADU.git simadu
cd simadu

# Set permissions
chmod -R 755 .
chmod -R 777 assets/uploads

# Configure Apache
# Edit /etc/apache2/sites-available/simadu.conf
```

### Option 3: Cloud Platform (Best for Scalability)
**Recommended Platforms**:
- **Google Cloud** - Free tier available
- **AWS** - Elastic Beanstalk easy deployment
- **Azure** - App Service with MySQL
- **Vercel** - Static files with PHP backend via serverless

---

## ⚙️ Configuration Checklist

### Before Deployment
- [ ] Database created and migrations run
- [ ] `config/koneksi.php` configured correctly
- [ ] `assets/uploads/` directory exists with correct permissions
- [ ] PHP `session.save_path` is writable
- [ ] PHP `upload_tmp_dir` is writable
- [ ] SSL certificate installed (if using HTTPS)
- [ ] `.htaccess` file in place for URL rewriting
- [ ] Error logging configured
- [ ] Database backups scheduled

### Security Checklist
- [ ] Change default admin password
- [ ] Disable `display_errors` in production
- [ ] Enable `error_reporting` to file only
- [ ] Set `open_basedir` restriction
- [ ] Implement rate limiting for login attempts
- [ ] Use prepared statements for all queries ✅ (Already done)
- [ ] Sanitize all user inputs ✅ (Already done)
- [ ] Implement CSRF protection
- [ ] Set secure cookie flags
- [ ] Use HTTPS only
- [ ] Regular security updates

### Performance Checklist
- [ ] Enable gzip compression
- [ ] Implement caching headers
- [ ] Optimize database queries
- [ ] Compress images in uploads
- [ ] Use CDN for static files
- [ ] Enable PHP opcache
- [ ] Monitor database performance

---

## 📊 Database Optimization

### Important Indexes
```sql
-- Add indexes for better performance
ALTER TABLE laporan ADD INDEX idx_user (id_user);
ALTER TABLE laporan ADD INDEX idx_status (status);
ALTER TABLE laporan ADD INDEX idx_kategori (id_kategori);
ALTER TABLE foto_laporan ADD INDEX idx_laporan (id_laporan);
ALTER TABLE tanggapan ADD INDEX idx_laporan (id_laporan);
ALTER TABLE users ADD INDEX idx_nim (nim);
```

### Regular Maintenance
```sql
-- Optimize tables monthly
OPTIMIZE TABLE users, kategori, laporan, foto_laporan, tanggapan;

-- Check table integrity
CHECK TABLE users, kategori, laporan, foto_laporan, tanggapan;

-- Repair if needed
REPAIR TABLE users, kategori, laporan, foto_laporan, tanggapan;
```

---

## 🔄 Backup Strategy

### Automated Database Backup
```bash
# Create backup script
#!/bin/bash
BACKUP_DIR="/home/backup"
DB_NAME="simadu_db"
DB_USER="root"
DB_PASSWORD="your_password"

# Daily backup
mysqldump -u$DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/simadu_$(date +%Y%m%d).sql

# Keep only last 7 days
find $BACKUP_DIR -name "simadu_*.sql" -mtime +7 -delete
```

### File Backup
```bash
# Backup uploads directory
tar -czf /backup/uploads_$(date +%Y%m%d).tar.gz assets/uploads/

# Keep only last 30 days
find /backup -name "uploads_*.tar.gz" -mtime +30 -delete
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. "Database Connection Failed"
```php
// Check config/koneksi.php
// Verify credentials
// Test connection with: mysql -u user -p -h host
```

#### 2. "Upload Failed / Permission Denied"
```bash
# Fix permissions
sudo chown www-data:www-data assets/uploads
sudo chmod 777 assets/uploads
```

#### 3. "Session Not Working"
```php
// Check session.save_path in php.ini
// Ensure /tmp is writable
// Or configure custom session path
ini_set('session.save_path', '/var/www/html/sessions');
```

#### 4. "Slow Loading"
```php
// Enable query logging to identify slow queries
// Optimize images before upload
// Implement pagination
// Add database indexes
```

#### 5. "Memory Limit Exceeded"
```php
// In php.ini or .htaccess
php_value memory_limit 256M
```

---

## 📧 Email Configuration (Optional)

To enable email notifications, configure SMTP:

```php
// In config/koneksi.php or separate email config
$email_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
    'from' => 'noreply@simadu.local'
];
```

---

## 📝 Log Monitoring

### PHP Error Log
```bash
# Check error log
tail -f /var/log/apache2/error.log

# Or in control panel, check error.log in root directory
```

### Database Query Monitoring
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- View slow queries
SELECT * FROM mysql.slow_log;
```

---

## ✅ Post-Deployment Verification

After deployment:

1. **Test Student Features**
   - [ ] Registration works
   - [ ] Login successful
   - [ ] Create report with photo
   - [ ] Edit report
   - [ ] View history
   - [ ] View status updates
   - [ ] Delete report

2. **Test Admin Features**
   - [ ] Admin login works
   - [ ] Dashboard loads statistics
   - [ ] View all reports
   - [ ] Update report status
   - [ ] Add response
   - [ ] Search/filter reports
   - [ ] Archive reports

3. **Verify Data Persistence**
   - [ ] Changes saved to database
   - [ ] Photos upload correctly
   - [ ] Edit laporan saves data ✅ (Fixed)
   - [ ] Status changes persist

4. **Check Security**
   - [ ] Login validation working
   - [ ] Unauthorized access blocked
   - [ ] SQL injection prevented ✅
   - [ ] Input sanitization working ✅

---

## 🎓 First-Time Admin Setup

1. Access database directly:
```sql
INSERT INTO users (nim, nama, password, role) VALUES
('ADMIN001', 'Administrator', PASSWORD('your_secure_password'), 'admin');
```

2. Login to admin panel
3. Create report categories
4. Configure system settings
5. Set up backup schedule

---

## 📞 Support & Resources

- **GitHub**: https://github.com/n4uf4/SIMADU
- **Issues**: https://github.com/n4uf4/SIMADU/issues
- **Documentation**: See README.md

---

**Last Updated**: June 2024
**Version**: 1.0.0
**Status**: Production Ready ✅
