<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

function ensure_seed_account(mysqli $conn, string $nim, string $nama, string $noHp, string $plainPassword, string $role): void
{
  $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE nim = ? LIMIT 1");
  if (!$stmt) {
    return;
  }

  mysqli_stmt_bind_param($stmt, 's', $nim);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $exists = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_close($stmt);

  if ($exists) {
    return;
  }

  $password = password_hash($plainPassword, PASSWORD_DEFAULT);

  $stmt = mysqli_prepare($conn, "INSERT INTO users (nim, nama_lengkap, no_hp, password, role) VALUES (?, ?, ?, ?, ?)");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sssss', $nim, $nama, $no_hp, $password, $role);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }
}

ensure_seed_account($conn, '111111111', 'Administrator', '-', 'password11', 'admin');
ensure_seed_account($conn, '257411023', 'Mahasiswa Demo', '-', '12345678', 'mahasiswa');

if (isset($_SESSION['id'])) {
    $target = ($_SESSION['role'] ?? 'mahasiswa') === 'admin'
        ? '../admin/dashboard.php'
        : '../mahasiswa/dashboard.php';
    header("Location: {$target}");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nim === '' || $password === '') {
        $error = 'NIM dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nim, nama_lengkap, password, role FROM users WHERE nim = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $nim);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id, $dbNim, $namaLengkap, $dbPassword, $role);
            $hasRow = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $data = $hasRow ? [
                'id' => $id,
                'nim' => $dbNim,
                'nama_lengkap' => $namaLengkap,
                'password' => $dbPassword,
                'role' => $role,
            ] : null;
        } else {
            $data = null;
        }

        if ($data) {
            $storedPassword = (string) ($data['password'] ?? '');
            $passwordValid = password_verify($password, $storedPassword);
            if (!$passwordValid && hash_equals($storedPassword, (string) $password)) {
                $passwordValid = true;
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($upd, 'si', $hashed, $data['id']);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
            if ($passwordValid) {
                session_regenerate_id(true);
                $_SESSION['id']   = $data['id'];
                $_SESSION['nim']  = $data['nim'];
                $_SESSION['nama'] = $data['nama_lengkap'];
                $_SESSION['role'] = $data['role'] ?? 'mahasiswa';
                $target = $_SESSION['role'] === 'admin' ? '../admin/dashboard.php' : '../mahasiswa/dashboard.php';
                header("Location: {$target}");
                exit;
            }
            $error = 'Password salah. Silakan coba lagi.';
        } else {
            $error = 'NIM tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Login - Sistem Pengaduan Mahasiswa</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { background-color: #f8f9ff; overflow: hidden; }
    .neon-glow { box-shadow: 0 0 20px rgba(0, 209, 102, 0.2); }
    .asymmetric-shape { clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%); }
    .input-focused:focus-within .neon-glow { box-shadow: 0 0 28px rgba(0,109,50,0.25); }
    body { min-height: max(884px, 100dvh); }
  </style>
</head>
<body class="flex min-h-screen items-center justify-center p-4">

<!-- Ambient Background -->
<div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
  <div class="absolute -top-[10%] -left-[5%] w-[40%] h-[40%] rounded-full bg-primary-container/10 blur-[120px]"></div>
  <div class="absolute top-[60%] -right-[10%] w-[50%] h-[50%] rounded-full bg-secondary-container/5 blur-[120px]"></div>
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
        Suara Anda, <span class="text-primary">Perubahan</span> Nyata Untuk Kampus.
      </h1>
      <p class="mt-6 text-on-surface-variant text-lg max-w-sm font-body leading-relaxed">
        Platform digital terpadu untuk menyampaikan aspirasi dan laporan demi kenyamanan civitas akademika.
      </p>
    </div>
    <div class="relative z-20 mt-auto">
      <div class="flex items-center gap-4 p-4 bg-surface-container-highest/50 rounded-2xl backdrop-blur-sm border border-outline-variant/20 max-w-xs">
        <div class="flex -space-x-3">
          <div class="w-10 h-10 rounded-full border-2 border-surface bg-primary-container/30 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary text-sm">person</span>
          </div>
          <div class="w-10 h-10 rounded-full border-2 border-surface bg-secondary/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-secondary text-sm">person</span>
          </div>
          <div class="w-10 h-10 rounded-full border-2 border-surface bg-tertiary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-tertiary text-sm">person</span>
          </div>
        </div>
        <div class="text-sm font-medium text-on-surface">
          <span class="block text-primary font-bold">1,200+</span>
          <span class="text-xs text-on-surface-variant">Laporan Terselesaikan</span>
        </div>
      </div>
    </div>
    <!-- Decorative gradient -->
    <div class="absolute bottom-0 right-0 w-full h-full opacity-20 pointer-events-none">
      <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 via-transparent to-secondary/10"></div>
    </div>
  </section>

  <!-- Right: Login Form -->
  <section class="lg:col-span-5 p-8 md:p-12 lg:p-16 flex flex-col justify-center bg-surface-container-lowest">

    <!-- Mobile Logo -->
    <div class="mb-10 lg:hidden flex items-center gap-2">
      <div class="w-8 h-8 bg-primary-container rounded flex items-center justify-center">
        <span class="material-symbols-outlined text-on-primary-container text-lg" style="font-variation-settings:'FILL' 1">bubble_chart</span>
      </div>
      <span class="font-display font-bold text-xl text-on-background">Sistem Pengaduan</span>
    </div>

    <header class="mb-10">
      <h2 class="font-display text-3xl font-bold text-on-background">Selamat Datang</h2>
      <p class="text-on-surface-variant mt-2 font-body">Masuk menggunakan akun mahasiswa Anda.</p>
    </header>

    <!-- Error Alert -->
    <?php if ($error): ?>
    <div class="mb-6 flex items-center gap-3 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
      <span class="material-symbols-outlined text-lg">error</span>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" id="loginForm">
      <!-- NIM -->
      <div class="space-y-2">
        <label class="block text-sm font-semibold text-on-surface-variant ml-1 font-label" for="nim">NIM (Nomor Induk Mahasiswa)</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[20px]">badge</span>
          </div>
          <input id="nim" name="nim" type="text" placeholder="Masukkan NIM Anda"
            value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>"
            class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30" required/>
        </div>
      </div>

      <!-- Password -->
      <div class="space-y-2">
        <div class="flex justify-between items-center px-1">
          <label class="block text-sm font-semibold text-on-surface-variant font-label" for="password">Kata Sandi</label>
          <a class="text-xs font-medium text-primary hover:underline" href="forgot_password.php">Lupa Sandi?</a>
        </div>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant/50 group-focus-within:text-primary transition-colors">
            <span class="material-symbols-outlined text-[20px]">lock</span>
          </div>
          <input id="password" name="password" type="password" placeholder="••••••••"
            class="w-full pl-12 pr-12 py-4 bg-surface-container-low border-transparent border-2 rounded-xl focus:bg-surface-container-lowest focus:border-primary focus:ring-0 transition-all font-body placeholder:text-on-surface-variant/30" required/>
          <button type="button" id="togglePwd" class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant/50 hover:text-on-surface transition-colors">
            <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility</span>
          </button>
        </div>
      </div>

      <!-- Remember me -->
      <div class="flex items-center gap-3 py-1">
        <input id="remember" name="remember" type="checkbox" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 bg-surface-container-low"/>
        <label class="text-sm text-on-surface-variant font-medium cursor-pointer" for="remember">Ingat saya di perangkat ini</label>
      </div>

      <!-- Submit -->
      <button type="submit" name="login" id="loginBtn"
        class="w-full py-4 bg-primary text-on-primary font-bold rounded-xl shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2 group">
        Masuk Sekarang
        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
      </button>
    </form>

    <div class="mt-10 pt-8 border-t border-outline-variant/10 text-center">
      <p class="text-on-surface-variant font-body">
        Belum memiliki akun?
        <a href="register.php" class="text-primary font-bold hover:underline decoration-2 underline-offset-4 ml-1">Daftar Akun Baru</a>
      </p>
    </div>

    <footer class="mt-6 flex justify-center gap-6 text-[10px] uppercase tracking-widest font-bold text-on-surface-variant/40">
      <a class="hover:text-primary transition-colors" href="#">Bantuan</a>
      <a class="hover:text-primary transition-colors" href="#">Privasi</a>
      <a class="hover:text-primary transition-colors" href="#">Syarat</a>
    </footer>
  </section>
</main>

<script>
  // Toggle password visibility
  const pwd = document.getElementById('password');
  const eye = document.getElementById('eyeIcon');
  document.getElementById('togglePwd').addEventListener('click', () => {
    const shown = pwd.type === 'text';
    pwd.type = shown ? 'password' : 'text';
    eye.textContent = shown ? 'visibility' : 'visibility_off';
  });

  // Input focus glow
  document.querySelectorAll('input').forEach(inp => {
    inp.addEventListener('focus', () => inp.parentElement.classList.add('neon-glow'));
    inp.addEventListener('blur',  () => inp.parentElement.classList.remove('neon-glow'));
  });

  // Loading state on submit
  document.getElementById('loginForm').addEventListener('submit', () => {
    const btn = document.getElementById('loginBtn');
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Memverifikasi...';
    btn.disabled = true;
  });
</script>
</body>
</html>
