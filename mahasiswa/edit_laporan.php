<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id'])) { header("Location: ../auth/login.php"); exit; }
if (!isset($_GET['id'])) { header("Location: riwayat_laporan.php"); exit; }

$id_laporan = (int) $_GET['id'];
$id_user    = (int) $_SESSION['id'];

$laporan = mysqli_query($conn, "SELECT * FROM laporan WHERE id_laporan = $id_laporan AND id_user = $id_user");
if (mysqli_num_rows($laporan) == 0) { header("Location: riwayat_laporan.php"); exit; }
$row = mysqli_fetch_assoc($laporan);

// Hanya bisa edit jika status Menunggu
if ($row['status'] !== 'Menunggu') { header("Location: detail_laporan.php?id=$id_laporan"); exit; }

$message = '';
$messageType = '';

if (isset($_POST['update'])) {
    $judul    = trim($_POST['judul'] ?? '');
    $isi      = trim($_POST['isi'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');

    if (empty($judul) || empty($isi)) {
        $message = 'Judul dan isi wajib diisi.';
        $messageType = 'error';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE laporan SET judul = ?, isi_laporan = ?, id_kategori = ? WHERE id_laporan = ? AND id_user = ?");
        mysqli_stmt_bind_param($stmt, 'ssiii', $judul, $isi, $kategori, $id_laporan, $id_user);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Laporan berhasil diperbarui!';
            $messageType = 'success';
            $laporan = mysqli_query($conn, "SELECT * FROM laporan WHERE id_laporan = $id_laporan");
            $row = mysqli_fetch_assoc($laporan);
        } else {
            $message = 'Gagal memperbarui laporan.';
            $messageType = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

$categories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Edit Laporan - Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); padding-bottom: 80px; }
    .bottom-nav { height: 68px; }
  </style>
</head>
<body class="bg-surface text-on-background selection:bg-primary-container selection:text-on-primary-container">

<!-- Ambient -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
  <div class="absolute top-1/4 -right-24 w-96 h-96 bg-primary-container/10 rounded-full blur-[120px]"></div>
  <div class="absolute bottom-1/4 -left-24 w-80 h-80 bg-secondary-container/10 rounded-full blur-[100px]"></div>
</div>

<!-- Top App Bar -->
<header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm">
  <div class="flex items-center justify-between px-6 py-4 w-full max-w-4xl mx-auto">
    <div class="flex items-center gap-3">
      <a href="detail_laporan.php?id=<?= $id_laporan ?>" class="p-2 rounded-full hover:bg-surface-container transition-colors active:scale-95 group">
        <span class="material-symbols-outlined text-primary group-hover:-translate-x-1 transition-transform">arrow_back</span>
      </a>
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
        <h1 class="font-display font-bold text-xl text-primary">Sistem Pengaduan</h1>
      </div>
    </div>
    <span class="text-[10px] uppercase tracking-[0.2em] text-primary font-bold hidden sm:block">Edit Laporan</span>
  </div>
</header>

<main class="pt-24 px-6 max-w-3xl mx-auto space-y-6">

  <!-- Header -->
  <div class="mb-2">
    <h2 class="font-display text-4xl font-bold text-on-background tracking-tight mb-2">Edit Laporan</h2>
    <p class="text-on-surface-variant font-body leading-relaxed">Perbarui judul, kategori, atau isi laporan sebelum diproses oleh admin.</p>
  </div>

  <!-- Alert -->
  <?php if ($message): ?>
  <div class="flex items-center gap-3 p-4 rounded-2xl text-sm font-medium <?= $messageType === 'success' ? 'bg-primary-container/20 text-on-primary-container' : 'bg-error-container text-on-error-container' ?>">
    <span class="material-symbols-outlined"><?= $messageType === 'success' ? 'check_circle' : 'error' ?></span>
    <?= htmlspecialchars($message) ?>
  </div>
  <?php if ($messageType === 'success'): ?>
  <script>setTimeout(() => window.location = 'detail_laporan.php?id=<?= $id_laporan ?>', 1500);</script>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Form -->
  <section class="bg-surface-container-low p-8 rounded-3xl space-y-6 hover:shadow-lg transition-all duration-300">
    <form method="POST" id="editForm" class="space-y-6">

      <!-- Kategori -->
      <div class="space-y-2">
        <label class="text-xs uppercase tracking-widest font-bold text-primary block">Kategori</label>
        <div class="relative">
          <select name="kategori" class="w-full appearance-none bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-3 font-body text-on-surface transition-all">
            <option value="">-- Pilih Kategori --</option>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <option value="<?= $cat['id_kategori'] ?>" <?= ($cat['id_kategori'] == $row['id_kategori']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['nama_kategori']) ?>
            </option>
            <?php endwhile; ?>
          </select>
          <span class="material-symbols-outlined absolute right-4 top-3.5 text-on-surface-variant pointer-events-none">expand_more</span>
        </div>
      </div>

      <!-- Judul -->
      <div class="space-y-2">
        <label class="text-xs uppercase tracking-widest font-bold text-primary block">Judul Laporan</label>
        <input type="text" name="judul" required
          value="<?= htmlspecialchars($row['judul']) ?>"
          class="w-full bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-3 font-body text-on-surface transition-all"/>
      </div>

      <!-- Isi -->
      <div class="space-y-2">
        <label class="text-xs uppercase tracking-widest font-bold text-primary block">Isi Laporan</label>
        <textarea name="isi" rows="8" required
          class="w-full bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-4 font-body text-on-surface transition-all resize-none"><?= htmlspecialchars($row['isi_laporan']) ?></textarea>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-3 justify-end pt-2">
        <a href="detail_laporan.php?id=<?= $id_laporan ?>"
          class="px-6 py-3 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-primary font-bold hover:bg-surface-container-low transition-colors">
          Batal
        </a>
        <button type="submit" name="update" id="updateBtn"
          class="px-7 py-3 rounded-xl bg-primary text-on-primary font-bold shadow-lg shadow-primary/20 flex items-center gap-2 hover:bg-primary/90 active:scale-95 transition-all">
          <span class="material-symbols-outlined text-[20px]">save</span>
          Simpan Perubahan
        </button>
      </div>
    </form>
  </section>

  <!-- Info Card -->
  <div class="bg-primary-container/10 border border-primary/10 p-5 rounded-2xl flex items-start gap-3">
    <span class="material-symbols-outlined text-primary mt-0.5">info</span>
    <div>
      <p class="font-bold text-sm text-on-surface">Laporan hanya dapat diedit saat berstatus "Menunggu"</p>
      <p class="text-xs text-on-surface-variant mt-1">Setelah admin mulai memproses laporan, Anda tidak dapat mengubahnya lagi.</p>
    </div>
  </div>
</main>

<!-- Bottom Navigation -->
<nav class="fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 bg-surface/90 backdrop-blur-xl shadow-[0_-4px_20px_rgba(11,28,48,0.05)] z-50 bottom-nav">
  <a href="dashboard.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">home</span>
    <span class="font-label text-xs font-medium mt-1">Home</span>
  </a>
  <a href="riwayat_laporan.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">history</span>
    <span class="font-label text-xs font-medium mt-1">Riwayat</span>
  </a>
  <a href="tambah_laporan.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">edit_note</span>
    <span class="font-label text-xs font-medium mt-1">Buat</span>
  </a>
  <a href="../auth/logout.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">logout</span>
    <span class="font-label text-xs font-medium mt-1">Keluar</span>
  </a>
</nav>

<script>
  document.getElementById('editForm')?.addEventListener('submit', () => {
    const btn = document.getElementById('updateBtn');
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Menyimpan...';
    btn.disabled = true;
  });

  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('focus', () => el.closest('.bg-surface-container-low')?.classList.add('ring-1', 'ring-primary/20'));
    el.addEventListener('blur',  () => el.closest('.bg-surface-container-low')?.classList.remove('ring-1', 'ring-primary/20'));
  });
</script>
</body>
</html>
