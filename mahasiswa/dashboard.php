<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

function mahasiswa_count_rows(mysqli $conn, string $sql): int
{
    $result = mysqli_query($conn, $sql);
    return $result instanceof mysqli_result ? mysqli_num_rows($result) : 0;
}

$total    = mahasiswa_count_rows($conn, "SELECT id_laporan FROM laporan WHERE id_user=$id_user");
$menunggu = mahasiswa_count_rows($conn, "SELECT id_laporan FROM laporan WHERE id_user=$id_user AND status='Menunggu'");
$diproses = mahasiswa_count_rows($conn, "SELECT id_laporan FROM laporan WHERE id_user=$id_user AND status='Diproses'");
$selesai  = mahasiswa_count_rows($conn, "SELECT id_laporan FROM laporan WHERE id_user=$id_user AND status='Selesai'");

$recent = mysqli_query($conn, "
    SELECT l.id_laporan, l.judul, l.status, l.created_at, k.nama_kategori
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    WHERE l.id_user = $id_user
    ORDER BY l.created_at DESC
    LIMIT 5
");

$public_reports = mysqli_query($conn, "
  SELECT l.id_laporan, l.judul, l.status, l.created_at, l.anonim, l.privat,
       k.nama_kategori,
       CASE WHEN l.anonim = 'Ya' THEN 'Anonim' ELSE u.nama_lengkap END AS nama_pelapor
  FROM laporan l
  LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
  LEFT JOIN users u ON l.id_user = u.id
  WHERE l.privat = 'Tidak'
  ORDER BY l.created_at DESC
  LIMIT 6
");

$status_badges = [
    'Menunggu' => 'bg-error-container text-on-error-container',
    'Diproses'  => 'bg-primary-container text-on-primary-container',
    'Selesai'   => 'bg-surface-container-highest text-secondary',
];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Dashboard - Sistem Pengaduan Mahasiswa</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); padding-bottom: 80px; }
    .bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.25rem; }
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .bottom-nav { height: 68px; }
    .recent-item { transition: background 0.15s ease; }
  </style>
</head>
<body class="bg-surface text-on-background selection:bg-primary-container selection:text-on-primary-container">

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
  <div class="absolute -top-10 -left-10 w-80 h-80 rounded-full bg-primary-container/10 blur-[120px]"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full bg-secondary-container/8 blur-[120px]"></div>
</div>

<!-- Top App Bar -->
<header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm">
  <div class="flex items-center justify-between px-6 py-4 w-full max-w-6xl mx-auto">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
      <h1 class="font-display font-bold text-xl text-primary">Sistem Pengaduan</h1>
    </div>
    <div class="flex items-center gap-3">
      <button class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center hover:bg-primary-container/20 transition-colors relative">
        <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
        <?php if ($menunggu > 0): ?>
        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
        <?php endif; ?>
      </button>
      <a href="../auth/logout.php" class="w-10 h-10 rounded-full bg-primary-container/20 border-2 border-primary/20 flex items-center justify-center hover:bg-primary-container/30 transition-colors" title="Keluar">
        <span class="material-symbols-outlined text-primary text-lg">person</span>
      </a>
    </div>
  </div>
</header>

<!-- Main Content -->
<main class="relative z-10 max-w-6xl mx-auto px-6 pt-24 pb-4 space-y-8">

  <!-- Hero Header -->
  <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div>
      <div class="flex items-center gap-2 text-primary text-xs font-bold uppercase tracking-[0.2em] mb-2">
        <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
        Mahasiswa
      </div>
      <h2 class="font-headline text-4xl md:text-5xl font-bold tracking-tight">
        Selamat datang, <span class="text-primary"><?= htmlspecialchars($_SESSION['nama']) ?></span>
      </h2>
      <p class="text-on-surface-variant mt-3 max-w-2xl text-base leading-relaxed">
        Pantau status laporan, kirim pengaduan baru, dan cek riwayat penyelesaian dalam satu tempat.
      </p>
    </div>
    <div class="flex gap-3 flex-wrap">
      <a href="tambah_laporan.php"
        class="px-6 py-3 rounded-xl bg-primary text-on-primary font-bold shadow-lg shadow-primary/20 flex items-center gap-2 hover:bg-primary/90 active:scale-95 transition-all">
        <span class="material-symbols-outlined text-[20px]">add</span> Buat Laporan
      </a>
      <a href="riwayat_laporan.php"
        class="px-6 py-3 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-primary font-bold flex items-center gap-2 hover:bg-surface-container-low active:scale-95 transition-all">
        <span class="material-symbols-outlined text-[20px]">history</span> Riwayat
      </a>
    </div>
  </section>

  <!-- Stats Bento -->
  <section class="bento-grid">
    <div class="col-span-6 md:col-span-3 bg-surface-container-lowest p-6 rounded-3xl border border-outline-variant/10 shadow-sm stat-card">
      <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold">Total</p>
      <div class="mt-4 flex items-end justify-between">
        <div>
          <h3 class="font-headline text-5xl font-bold text-on-background"><?= $total ?></h3>
          <p class="text-sm text-on-surface-variant mt-1">Laporan terkirim</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-primary opacity-30">description</span>
      </div>
    </div>
    <div class="col-span-6 md:col-span-3 bg-error-container/25 p-6 rounded-3xl border border-outline-variant/10 stat-card">
      <p class="text-xs uppercase tracking-widest text-error font-bold">Menunggu</p>
      <div class="mt-4 flex items-end justify-between">
        <div>
          <h3 class="font-headline text-5xl font-bold text-on-background"><?= $menunggu ?></h3>
          <p class="text-sm text-on-surface-variant mt-1">Perlu tindak lanjut</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-error opacity-40">schedule</span>
      </div>
    </div>
    <div class="col-span-6 md:col-span-3 bg-surface-container-low p-6 rounded-3xl border border-outline-variant/10 stat-card">
      <p class="text-xs uppercase tracking-widest text-secondary font-bold">Diproses</p>
      <div class="mt-4 flex items-end justify-between">
        <div>
          <h3 class="font-headline text-5xl font-bold text-on-background"><?= $diproses ?></h3>
          <p class="text-sm text-on-surface-variant mt-1">Sedang ditangani</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-secondary opacity-40">pending_actions</span>
      </div>
    </div>
    <div class="col-span-6 md:col-span-3 bg-primary text-on-primary p-6 rounded-3xl shadow-xl shadow-primary/20 stat-card">
      <p class="text-xs uppercase tracking-widest text-on-primary/70 font-bold">Selesai</p>
      <div class="mt-4 flex items-end justify-between">
        <div>
          <h3 class="font-headline text-5xl font-bold"><?= $selesai ?></h3>
          <p class="text-sm text-on-primary/75 mt-1">Sudah diselesaikan</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-on-primary opacity-40">check_circle</span>
      </div>
    </div>
  </section>

  <!-- Recent + Quick Actions -->
  <section class="grid grid-cols-12 gap-6">
    <!-- Recent Reports -->
    <div class="col-span-12 lg:col-span-7 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-headline text-2xl font-bold">Laporan Terbaru</h2>
        <a href="riwayat_laporan.php" class="text-primary text-sm font-bold hover:underline flex items-center gap-1">
          Lihat semua <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
      <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 divide-y divide-outline-variant/10 overflow-hidden">
        <?php if (!$recent || mysqli_num_rows($recent) === 0): ?>
        <div class="p-10 text-center text-on-surface-variant">
          <span class="material-symbols-outlined text-5xl block mb-3 opacity-20">inbox</span>
          <p class="font-medium">Belum ada laporan.</p>
          <a href="tambah_laporan.php" class="mt-4 inline-block text-primary font-bold text-sm hover:underline">
            Buat laporan pertama Anda →
          </a>
        </div>
        <?php else: while ($r = mysqli_fetch_assoc($recent)):
          $badge = $status_badges[$r['status']] ?? 'bg-surface-container-highest text-on-surface-variant';
        ?>
        <div class="p-5 flex items-start gap-4 hover:bg-surface-container-low transition-colors recent-item">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">description</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
              <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/5 px-2 py-0.5 rounded-full">
                <?= htmlspecialchars($r['nama_kategori'] ?? 'Umum') ?>
              </span>
              <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full <?= $badge ?>">
                <?= htmlspecialchars($r['status']) ?>
              </span>
            </div>
            <h3 class="font-headline font-bold text-lg truncate"><?= htmlspecialchars($r['judul']) ?></h3>
            <p class="text-sm text-on-surface-variant mt-1"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></p>
          </div>
          <a href="detail_laporan.php?id=<?= $r['id_laporan'] ?>"
            class="text-primary font-bold text-sm whitespace-nowrap hover:underline flex items-center gap-1">
            Detail <span class="material-symbols-outlined text-sm">chevron_right</span>
          </a>
        </div>
        <?php endwhile; endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-span-12 lg:col-span-5 space-y-4">
      <h2 class="font-headline text-2xl font-bold">Aksi Cepat</h2>
      <div class="bg-surface-container p-4 rounded-3xl border border-outline-variant/10 space-y-3">
        <a href="tambah_laporan.php"
          class="flex items-center gap-4 p-4 rounded-2xl bg-surface-container-lowest hover:bg-primary-container/10 transition-colors group">
          <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1">add_circle</span>
          </div>
          <div>
            <p class="font-bold text-sm text-on-surface">Buat Laporan Baru</p>
            <p class="text-xs text-on-surface-variant mt-0.5">Kirim aduan atau aspirasi</p>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant/40 ml-auto">chevron_right</span>
        </a>
        <a href="riwayat_laporan.php"
          class="flex items-center gap-4 p-4 rounded-2xl bg-surface-container-lowest hover:bg-primary-container/10 transition-colors group">
          <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1">history</span>
          </div>
          <div>
            <p class="font-bold text-sm text-on-surface">Riwayat Laporan</p>
            <p class="text-xs text-on-surface-variant mt-0.5">Lihat status dan detail</p>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant/40 ml-auto">chevron_right</span>
        </a>
      </div>

      <!-- Info Card -->
      <?php if ($menunggu > 0): ?>
      <div class="bg-error-container/20 border border-error/10 p-5 rounded-2xl flex items-start gap-3">
        <span class="material-symbols-outlined text-error mt-0.5">info</span>
        <div>
          <p class="font-bold text-sm text-on-surface">Ada <?= $menunggu ?> laporan menunggu</p>
          <p class="text-xs text-on-surface-variant mt-1">Laporan Anda sedang menunggu ditinjau oleh admin.</p>
        </div>
      </div>
      <?php else: ?>
      <div class="bg-primary-container/10 border border-primary/10 p-5 rounded-2xl flex items-start gap-3">
        <span class="material-symbols-outlined text-primary mt-0.5">tips_and_updates</span>
        <div>
          <p class="font-bold text-sm text-on-surface">Semua laporan tertangani</p>
          <p class="text-xs text-on-surface-variant mt-1">Tidak ada laporan yang perlu perhatian segera.</p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Public Community Feed -->
  <section class="space-y-4 pb-8">
    <div class="flex items-center justify-between gap-4">
      <div>
        <h2 class="font-headline text-2xl font-bold">Laporan Komunitas</h2>
        <p class="text-sm text-on-surface-variant mt-1">Lihat laporan publik dari mahasiswa lain yang tidak ditandai privat.</p>
      </div>
      <span class="text-xs font-bold uppercase tracking-widest text-primary bg-primary/5 px-3 py-1 rounded-full">Publik</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php if (!$public_reports || mysqli_num_rows($public_reports) === 0): ?>
      <div class="md:col-span-2 bg-surface-container-lowest rounded-3xl border border-outline-variant/10 p-10 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3 opacity-20">forum</span>
        <p class="font-medium">Belum ada laporan publik yang bisa dilihat.</p>
        <p class="text-sm mt-1">Saat mahasiswa lain membuat laporan non-privat, laporan tersebut akan tampil di sini.</p>
      </div>
      <?php else: while ($public = mysqli_fetch_assoc($public_reports)):
        $badge = $status_badges[$public['status']] ?? 'bg-surface-container-highest text-on-surface-variant';
      ?>
      <article class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between gap-3 mb-4">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-primary-container/15 flex items-center justify-center text-primary flex-shrink-0">
              <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">groups</span>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold uppercase tracking-widest text-primary truncate"><?= htmlspecialchars($public['nama_kategori'] ?? 'Umum') ?></p>
              <h3 class="font-headline text-lg font-bold text-on-surface truncate"><?= htmlspecialchars($public['judul']) ?></h3>
            </div>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-full <?= $badge ?>">
            <?= htmlspecialchars($public['status']) ?>
          </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs text-on-surface-variant mb-4">
          <span class="bg-surface-container-highest px-2 py-1 rounded-full font-bold"><?= htmlspecialchars($public['nama_pelapor']) ?></span>
          <span><?= date('d M Y, H:i', strtotime($public['created_at'])) ?></span>
          <?php if ($public['anonim'] === 'Ya'): ?>
          <span class="bg-surface-container-highest text-on-surface-variant px-2 py-1 rounded-full font-bold uppercase">Anonim</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center justify-between gap-3">
          <span class="text-xs text-on-surface-variant">Laporan publik</span>
          <a href="detail_laporan.php?id=<?= (int) $public['id_laporan'] ?>" class="text-primary text-sm font-bold hover:underline flex items-center gap-1">
            Lihat detail <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </a>
        </div>
      </article>
      <?php endwhile; endif; ?>
    </div>
  </section>
</main>

<!-- Bottom Navigation -->
<nav class="fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 bg-surface/90 backdrop-blur-xl shadow-[0_-4px_20px_rgba(11,28,48,0.05)] z-50 bottom-nav">
  <div class="flex flex-col items-center justify-center bg-primary-container text-on-primary-container rounded-xl px-5 py-2">
    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">home</span>
    <span class="font-label text-xs font-bold mt-1">Home</span>
  </div>
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

</body>
</html>
