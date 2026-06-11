<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if (isset($_SESSION['id'])) {
    $target = ($_SESSION['role'] ?? 'mahasiswa') === 'admin'
        ? '../admin/dashboard.php'
        : '../mahasiswa/dashboard.php';
    header("Location: {$target}");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim      = trim($_POST['nim'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nim === '' || $nama === '' || $no_hp === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua field wajib diisi.';
  } elseif (!preg_match('/^[0-9]{9}$/', $nim)) {
    $error = 'NIM harus terdiri dari 9 digit angka.';
  } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
    $error = 'Nomor HP harus terdiri dari 10 sampai 15 digit angka.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama!';
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE nim = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, 's', $nim);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            mysqli_stmt_close($cek);
            $error = 'NIM sudah terdaftar!';
        } else {
            mysqli_stmt_close($cek);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'mahasiswa';

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nim, nama_lengkap, no_hp, password, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $nim, $nama, $no_hp, $hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $success = 'Registrasi berhasil! Mengalihkan ke halaman login...';
            } else {
                mysqli_stmt_close($stmt);
                $error = 'Registrasi gagal, silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Daftar Akun - Sistem Pengaduan Mahasiswa</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { background-color: #f8f9ff; min-height: max(884px, 100dvh); }
    .neon-glow { box-shadow: 0 0 20px rgba(0, 209, 102, 0.2); }
    .input-focused:focus-within { box-shadow: 0 0 0 2px rgba(0,109,50,0.2); }
  </style>
</head>
<body class="flex min-h-screen items-center justify-center p-4">

<!-- Ambient Background -->
<div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
  <div class="absolute -top-[10%] -right-[5%] w-[40%] h-[40%] rounded-full bg-primary-container/10 blur-[120px]"></div>
  <div class="absolute top-[60%] -left-[10%] w-[50%] h-[50%] rounded-full bg-secondary-container/5 blur-[120px]"></div>
</div>

<!-- Main Card -->
<main class="relative z-10 w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 bg-surface-container-lowest rounded-[2rem] shadow-2xl overflow-hidden ring-1 ring-outline-variant/10">

  <!-- Left: Branding -->
  <section class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-12 bg-surface-container-low overflow-hidden">
    <div class="relative z-20">
      <div class="flex items-center gap-3 mb-12">
        <div class="w-10 h-10 bg-primary-container rounded-lg flex items-center justify-center neon-glow">
          <span class="material-symbols-outlined text-on-primary-container" style="font-variation-settings:'FILL' 1">bubble_chart</span>
        </div>
        <span class="font-display font-bold text-2xl tracking-tight text-on-background">Sistem Pengaduan</span>
      </div>
      <h1 class="font-display text-5xl font-bold leading-tight text-on-background max-w-md">
        Bergabung dan <span class="text-primary">Suarakan</span> Aspirasi Kampus.
      </h1>
      <p class="mt-6 text-on-surface-variant text-lg max-w-sm font-body leading-relaxed">
        Daftarkan akun Anda untuk mulai menyampaikan laporan dan memantau perkembangannya secara transparan.
      </p>
    </div>
    <div class="relative z-20 mt-auto">
      <div class="flex items-center gap-4 p-4 bg-surface-container-highest/50 rounded-2xl backdrop-blur-sm border border-outline-variant/20 max-w-xs">
        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
          <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">verified_user</span>
        </div>
        <div class="text-sm">
          <span class="block text-primary font-bold">Aman & Terpercaya</span>
          <span class="text-xs text-on-surface-variant">Data Anda dilindungi sistem</span>
        </div>
      </div>
    </div>
    <!-- Decorative -->
    <div class="absolute bottom-0 right-0 w-full h-full opacity-20 pointer-events-none">
      <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 via-transparent to-secondary/10"></div>
    </div>
  </section>

  <!-- Right: Register Form -->
  <section class="lg:col-span-5 p-8 md:p-10 lg:p-12 flex flex-col justify-center bg-surface-container-lowest">

    <!-- Mobile Logo -->
    <div class="mb-8 lg:hidden flex items-center gap-2">
      <div class="w-8 h-8 bg-primary-container rounded flex items-center justify-center">
        <span class="material-symbols-outlined text-on-primary-container text-lg" style="font-variation-settings:'FILL' 1">bubble_chart</span>
      </div>
      <span class="font-display font-bold text-xl text-on-background">Sistem Pengaduan</span>
    </div>

    <header class="mb-8">
      <h2 class="font-display text-3xl font-bold text-on-background">Buat Akun Baru</h2>
      <p class="text-on-surface-variant mt-2 font-body">Isi form di bawah untuk mendaftar sebagai mahasiswa.</p>
    </header>

    <!-- Alerts -->
    <?php if ($error): ?>
    <div class="mb-6 flex items-center gap-3 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
      <span class="material-symbols-outlined text-lg">error</span>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="mb-6 flex items-center gap-3 p-4 bg-primary-container/20 text-on-primary-container rounded-xl text-sm font-medium">
      <span class="material-symbols-outlined text-lg">check_circle</span>
      <?= htmlspecialchars($success) ?>
    </div>
    <script>setTimeout(() => window.location = 'login.php', 2000);</script>
    <?php endif; ?>

    <form method="POST" id="registerForm" class="space-y-4">

      <!-- NIM -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider ml-1" for="nim">NIM</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">badge</span>
          </div>
          <input id="nim" name="nim" type="text" required
            placeholder="Nomor Induk Mahasiswa"
            inputmode="numeric"
            pattern="[0-9]{9}"
            maxlength="9"
            value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>"
            class="w-full pl-11 pr-4 py-3.5 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30"/>
        </div>
      </div>

      <!-- Nama -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider ml-1" for="nama">Nama Lengkap</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">person</span>
          </div>
          <input id="nama" name="nama" type="text" required
            placeholder="Nama sesuai KTM"
            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
            class="w-full pl-11 pr-4 py-3.5 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30"/>
        </div>
      </div>

      <!-- No HP -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider ml-1" for="no_hp">Nomor HP</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">phone</span>
          </div>
          <input id="no_hp" name="no_hp" type="tel" required
            placeholder="Contoh: 081234567890"
            inputmode="numeric"
            pattern="[0-9]{10,15}"
            maxlength="15"
            value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"
            class="w-full pl-11 pr-4 py-3.5 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30"/>
        </div>
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider ml-1" for="password">Password</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">lock</span>
          </div>
          <input id="password" name="password" type="password" required
            placeholder="Min. 6 karakter"
            class="w-full pl-11 pr-12 py-3.5 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30"/>
          <button type="button" id="togglePwd1" class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant/50 hover:text-on-surface transition-colors">
            <span class="material-symbols-outlined text-[18px]" id="eyeIcon1">visibility</span>
          </button>
        </div>
      </div>

      <!-- Konfirmasi Password -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider ml-1" for="konfirmasi">Konfirmasi Password</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
          </div>
          <input id="konfirmasi" name="konfirmasi" type="password" required
            placeholder="Ulangi password"
            class="w-full pl-11 pr-12 py-3.5 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30"/>
          <button type="button" id="togglePwd2" class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant/50 hover:text-on-surface transition-colors">
            <span class="material-symbols-outlined text-[18px]" id="eyeIcon2">visibility</span>
          </button>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" name="register" id="registerBtn"
        class="w-full py-4 bg-primary text-on-primary font-bold rounded-xl shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2 group mt-2">
        Daftar Sekarang
        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
      </button>
    </form>

    <div class="mt-8 pt-6 border-t border-outline-variant/10 text-center">
      <p class="text-on-surface-variant font-body">
        Sudah memiliki akun?
        <a href="login.php" class="text-primary font-bold hover:underline decoration-2 underline-offset-4 ml-1">Masuk Sekarang</a>
      </p>
    </div>

    <footer class="mt-4 flex justify-center gap-6 text-[10px] uppercase tracking-widest font-bold text-on-surface-variant/40">
      <a class="hover:text-primary transition-colors" href="#">Bantuan</a>
      <a class="hover:text-primary transition-colors" href="#">Privasi</a>
      <a class="hover:text-primary transition-colors" href="#">Syarat</a>
    </footer>
  </section>
</main>

<script>
  // Toggle password visibility
  function setupToggle(btnId, inputId, iconId) {
    document.getElementById(btnId)?.addEventListener('click', () => {
      const inp = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      const shown = inp.type === 'text';
      inp.type = shown ? 'password' : 'text';
      icon.textContent = shown ? 'visibility' : 'visibility_off';
    });
  }
  setupToggle('togglePwd1', 'password', 'eyeIcon1');
  setupToggle('togglePwd2', 'konfirmasi', 'eyeIcon2');

  // Loading state on submit
  document.getElementById('registerForm')?.addEventListener('submit', () => {
    const btn = document.getElementById('registerBtn');
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Memproses...';
    btn.disabled = true;
  });

  // Focus glow
  document.querySelectorAll('input').forEach(inp => {
    inp.addEventListener('focus', () => inp.parentElement.classList.add('neon-glow'));
    inp.addEventListener('blur', () => inp.parentElement.classList.remove('neon-glow'));
  });
</script>
</body>
</html>
