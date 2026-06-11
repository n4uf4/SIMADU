<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

$status_filter = $_GET['status'] ?? '';

$where = "WHERE l.id_user = $id_user";
if ($status_filter !== '' && in_array($status_filter, ['Menunggu', 'Diproses', 'Selesai'], true)) {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where .= " AND l.status = '$safe_status'";
}

$data = mysqli_query($conn, "
    SELECT l.*, 
           CASE WHEN l.anonim = 'Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor,
           k.nama_kategori
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    $where
    ORDER BY l.id_laporan DESC
");

$total    = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE id_user = $id_user"));
$menunggu = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE id_user = $id_user AND status='Menunggu'"));
$diproses = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE id_user = $id_user AND status='Diproses'"));
$selesai  = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE id_user = $id_user AND status='Selesai'"));

$status_badges = [
    'Menunggu' => ['bg' => 'bg-error-container', 'text' => 'text-on-error-container', 'icon' => 'schedule'],
    'Diproses'  => ['bg' => 'bg-primary-container', 'text' => 'text-on-primary-container', 'icon' => 'pending_actions'],
    'Selesai'   => ['bg' => 'bg-surface-container-highest', 'text' => 'text-secondary', 'icon' => 'check_circle'],
];
$icon_map = [
    'Menunggu' => 'schedule',
    'Diproses'  => 'pending_actions',
    'Selesai'   => 'check_circle',
];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Riwayat Laporan - Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); padding-bottom: 80px; }
    .card-hover { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,109,50,0.08); }
    .bottom-nav { height: 68px; }
    .filter-chip.active { background: #006d32; color: #ffffff; }
    .filter-chip { transition: all 0.2s; }
  </style>
</head>
<body class="bg-surface text-on-background">

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
  <div class="absolute -top-10 -right-10 w-80 h-80 rounded-full bg-primary-container/8 blur-[100px]"></div>
  <div class="absolute bottom-1/3 -left-20 w-96 h-96 rounded-full bg-secondary-container/8 blur-[120px]"></div>
</div>

<!-- Top App Bar -->
<header class="bg-surface/80 backdrop-blur-md fixed top-0 w-full z-50 shadow-sm">
  <div class="flex items-center justify-between px-6 py-4 w-full max-w-4xl mx-auto">
    <div class="flex items-center gap-3">
      <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
      <h1 class="font-display font-bold text-xl text-primary">Sistem Pengaduan</h1>
    </div>
    <div class="flex items-center gap-3">
      <a href="tambah_laporan.php"
        class="flex items-center gap-2 px-4 py-2 rounded-full bg-primary text-on-primary text-sm font-bold hover:bg-primary/90 transition-colors active:scale-95">
        <span class="material-symbols-outlined text-[18px]">add</span>
        <span class="hidden sm:inline">Buat Laporan</span>
      </a>
      <div class="w-10 h-10 rounded-full bg-primary-container/20 border-2 border-primary/20 flex items-center justify-center">
        <span class="material-symbols-outlined text-primary text-lg">person</span>
      </div>
    </div>
  </div>
</header>

<main class="pt-24 px-6 max-w-4xl mx-auto">

  <!-- Header -->
  <section class="mb-10">
    <a href="dashboard.php" class="flex items-center gap-2 text-primary font-medium hover:underline w-fit text-sm mb-4 group">
      <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
      Kembali ke Dashboard
    </a>
    <h2 class="font-display text-4xl font-bold tracking-tight text-on-surface mb-2">Riwayat Laporan</h2>
    <p class="text-on-surface-variant font-body text-base max-w-lg leading-relaxed">
      Pantau perkembangan aduan Anda dan kelola riwayat pelaporan secara transparan.
    </p>
  </section>

  <!-- Stats Bento Grid -->
  <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <div class="bg-surface-container-low p-5 rounded-2xl border border-outline-variant/10 flex flex-col gap-1">
      <span class="text-xs uppercase tracking-widest font-bold text-on-surface-variant">Total</span>
      <span class="font-headline text-4xl font-bold text-on-surface mt-1"><?= $total ?></span>
    </div>
    <div class="bg-error-container/20 p-5 rounded-2xl border border-outline-variant/10 flex flex-col gap-1">
      <span class="text-xs uppercase tracking-widest font-bold text-error">Menunggu</span>
      <span class="font-headline text-4xl font-bold text-error mt-1"><?= $menunggu ?></span>
    </div>
    <div class="bg-primary-container/15 p-5 rounded-2xl border border-outline-variant/10 flex flex-col gap-1">
      <span class="text-xs uppercase tracking-widest font-bold text-primary">Diproses</span>
      <span class="font-headline text-4xl font-bold text-primary mt-1"><?= $diproses ?></span>
    </div>
    <div class="bg-surface-container-highest/50 p-5 rounded-2xl border border-outline-variant/10 flex flex-col gap-1">
      <span class="text-xs uppercase tracking-widest font-bold text-secondary">Selesai</span>
      <span class="font-headline text-4xl font-bold text-secondary mt-1"><?= $selesai ?></span>
    </div>
  </section>

  <!-- Filter Chips -->
  <div class="flex gap-3 mb-8 overflow-x-auto no-scrollbar pb-2">
    <a href="riwayat_laporan.php"
      class="filter-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap <?= $status_filter === '' ? 'bg-primary text-on-primary active' : 'border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container' ?>">
      Semua Laporan
    </a>
    <a href="?status=Menunggu"
      class="filter-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap <?= $status_filter === 'Menunggu' ? 'bg-primary text-on-primary active' : 'border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container' ?>">
      Menunggu
    </a>
    <a href="?status=Diproses"
      class="filter-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap <?= $status_filter === 'Diproses' ? 'bg-primary text-on-primary active' : 'border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container' ?>">
      Diproses
    </a>
    <a href="?status=Selesai"
      class="filter-chip px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap <?= $status_filter === 'Selesai' ? 'bg-primary text-on-primary active' : 'border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container' ?>">
      Selesai
    </a>
  </div>

  <!-- List -->
  <div class="space-y-5 mb-8">
    <?php if (!$data || mysqli_num_rows($data) === 0): ?>
    <div class="flex flex-col items-center justify-center py-20 text-center bg-surface-container-lowest rounded-3xl border border-outline-variant/10">
      <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center mb-5">
        <span class="material-symbols-outlined text-4xl text-on-surface-variant/30">history</span>
      </div>
      <h3 class="font-display text-xl font-bold mb-2">Belum ada laporan</h3>
      <p class="text-on-surface-variant text-sm max-w-xs">Laporan yang Anda kirimkan akan muncul di sini untuk dipantau.</p>
      <a href="tambah_laporan.php" class="mt-6 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm">
        Buat Laporan Pertama
      </a>
    </div>
    <?php else: while ($row = mysqli_fetch_assoc($data)):
      $badge = $status_badges[$row['status']] ?? ['bg' => 'bg-surface-container-highest', 'text' => 'text-on-surface-variant', 'icon' => 'description'];
      $icon = $icon_map[$row['status']] ?? 'description';
    ?>
    <div class="group bg-surface-container-low rounded-2xl p-6 card-hover relative overflow-hidden border border-outline-variant/5">
      <!-- Status badge top right -->
      <div class="absolute top-4 right-4">
        <span class="px-3 py-1 rounded-full <?= $badge['bg'] ?> <?= $badge['text'] ?> text-xs font-bold uppercase tracking-widest">
          <?= htmlspecialchars($row['status']) ?>
        </span>
      </div>
      
      <div class="flex flex-col md:flex-row md:items-start gap-5">
        <!-- Icon -->
        <div class="w-14 h-14 rounded-xl <?= $row['status'] === 'Selesai' ? 'bg-secondary/10 text-secondary' : ($row['status'] === 'Diproses' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error') ?> flex items-center justify-center flex-shrink-0">
          <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1"><?= $icon ?></span>
        </div>
        
        <!-- Content -->
        <div class="flex-1 space-y-2 min-w-0 pr-20">
          <div class="flex items-center gap-3">
            <span class="text-xs font-bold uppercase tracking-widest text-primary bg-primary/5 px-2 py-0.5 rounded-full">
              <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
            </span>
            <span class="text-on-surface-variant/50 text-xs">• #<?= $row['id_laporan'] ?></span>
          </div>
          <h3 class="font-headline text-xl font-bold text-on-surface leading-tight truncate">
            <?= htmlspecialchars($row['judul']) ?>
          </h3>
          <p class="text-on-surface-variant text-sm">
            <?= date('d M Y, H:i', strtotime($row['created_at'])) ?> WIB
            <?php if($row['anonim'] == 'Ya'): ?>
            <span class="ml-2 bg-surface-container-highest text-on-surface-variant text-[10px] font-bold px-2 py-0.5 rounded uppercase">Anonim</span>
            <?php endif; ?>
            <?php if($row['privat'] == 'Ya'): ?>
            <span class="ml-1 bg-secondary/10 text-secondary text-[10px] font-bold px-2 py-0.5 rounded uppercase">Privat</span>
            <?php endif; ?>
          </p>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="flex items-center gap-3 mt-5 pt-4 border-t border-outline-variant/10 flex-wrap">
        <a href="detail_laporan.php?id=<?= $row['id_laporan'] ?>"
          class="flex items-center gap-2 text-primary text-sm font-bold hover:bg-primary/5 px-3 py-1.5 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-[18px]">visibility</span> Lihat Detail
        </a>
        <?php if ($row['status'] === 'Menunggu'): ?>
        <a href="edit_laporan.php?id=<?= $row['id_laporan'] ?>"
          class="flex items-center gap-2 text-secondary text-sm font-bold hover:bg-secondary/5 px-3 py-1.5 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-[18px]">edit</span> Edit
        </a>
        <a href="hapus_laporan.php?id=<?= $row['id_laporan'] ?>"
          onclick="return confirm('Yakin ingin menghapus laporan ini?')"
          class="flex items-center gap-2 text-error text-sm font-bold hover:bg-error/5 px-3 py-1.5 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-[18px]">delete</span> Hapus
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; endif; ?>
  </div>
</main>

<!-- Bottom Navigation -->
<nav class="fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 bg-surface/90 backdrop-blur-xl shadow-[0_-4px_20px_rgba(11,28,48,0.05)] z-50 bottom-nav">
  <a href="dashboard.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">home</span>
    <span class="font-label text-xs font-medium mt-1">Home</span>
  </a>
  <div class="flex flex-col items-center justify-center bg-primary-container text-on-primary-container rounded-xl px-5 py-2">
    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">history</span>
    <span class="font-label text-xs font-bold mt-1">Riwayat</span>
  </div>
  <a href="tambah_laporan.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">edit_note</span>
    <span class="font-label text-xs font-medium mt-1">Buat</span>
  </a>
  <a href="../auth/logout.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">logout</span>
    <span class="font-label text-xs font-medium mt-1">Keluar</span>
  </a>
</nav>

</body>
</html>
