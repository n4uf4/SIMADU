# 🚀 SIMADU - Deployment Guide Glitch (FREE)

> **Waktu Setup**: 10-15 menit | **Biaya**: GRATIS | **Kesulitan**: ⭐ (Very Easy)

## 🎯 Apa itu Glitch?

Glitch adalah platform untuk hosting web apps gratis:
- ✅ **GRATIS** selamanya
- ✅ Support Node.js (kita custom untuk PHP)
- ✅ Built-in database option
- ✅ Cepat & simple setup
- ✅ Custom domain support
- ⚠️ Node.js focused, tapi bisa di-configure untuk PHP

---

## ⚠️ Note: Glitch vs Replit untuk PHP

| Feature | Glitch | Replit |
|---------|--------|--------|
| PHP Native Support | ❌ (Need workaround) | ✅ Built-in |
| MySQL | ✅ (via plugins) | ✅ Built-in |
| Setup Time | 20-30 min | 15 min |
| Ease | ⭐⭐ | ⭐⭐⭐ |
| **Recommendation** | **Use Replit instead** | **✅ RECOMMENDED** |

---

## 🚨 Rekomendasi

**Untuk SIMADU, gunakan REPLIT lebih baik!**

Alasan:
1. Replit punya PHP native support
2. Setup lebih simple & cepat
3. MySQL lebih mudah dikonfigure
4. Performa lebih bagus untuk PHP

Lihat **`REPLIT_SETUP.md`** untuk panduan Replit (RECOMMENDED!)

---

## 🔄 Jika Ingin Coba Glitch (Alternative)

Kalau Anda tetap ingin coba Glitch:

### **Step 1: Buat Akun Glitch**
1. Buka https://glitch.com
2. Sign up dengan GitHub / Google
3. Verifikasi

### **Step 2: Import Project**
1. Click **"New Project"**
2. Pilih **"Import from GitHub"**
3. Paste: `https://github.com/n4uf4/SIMADU`
4. Select branch: `tampilkan`
5. Create

### **Step 3: Setup PHP Runtime**

Glitch tidak native support PHP, tapi bisa dengan custom start script:

1. Edit `.env` file (create if not exists):
```env
PHP_VERSION=8.2
```

2. Edit `package.json`:
```json
{
  "name": "simadu",
  "version": "1.0.0",
  "description": "SIMADU - PHP App",
  "main": "index.php",
  "scripts": {
    "start": "php -S 0.0.0.0:3000"
  },
  "engines": {
    "node": "18.x"
  }
}
```

3. Create `Procfile`:
```
web: php -S 0.0.0.0:$PORT
```

### **Step 4: Setup Database**

Glitch supports SQLite atau external MySQL:

**Option A: SQLite (Easiest)**
```php
// config/koneksi.php - Updated for SQLite
$db = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
```

**Option B: External MySQL** (Recommend: PlanetScale, Supabase, atau Railway)

---

## 🎯 Final Recommendation

**GUNAKAN REPLIT** ✅

Alasan:
- Setup lebih simple
- PHP native support
- MySQL built-in
- Hanya 15 menit setup
- Lebih cocok untuk SIMADU

Lihat **`REPLIT_SETUP.md`** sekarang!

---

## 📞 Jika Tetap Ingin Glitch

Hubungi support atau lihat:
- Glitch Docs: https://glitch.com/help
- Glitch Community: https://support.glitch.com

---

**REKOMENDASI FINAL: Gunakan REPLIT!** 🚀

Buka `REPLIT_SETUP.md` untuk panduan lengkap.
