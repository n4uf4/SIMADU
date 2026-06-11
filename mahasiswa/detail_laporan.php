<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: riwayat_laporan.php");
    exit;
}

$id_laporan = (int) $_GET['id'];
$id_user    = (int) $_SESSION['id'];

// Validasi laporan yang boleh dilihat: milik sendiri atau publik
$stmt = mysqli_prepare($conn, "SELECT id_laporan FROM laporan WHERE id_laporan = ? AND (id_user = ? OR privat = 'Tidak') LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $id_laporan, $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) == 0) {
    mysqli_stmt_close($stmt);
    header("Location: riwayat_laporan.php");
    exit;
}
mysqli_stmt_close($stmt);

$laporan = mysqli_query($conn, "
    SELECT l.*,
       u.nim AS nim_pelapor,
           CASE WHEN l.anonim = 'Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor,
           k.nama_kategori
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    WHERE l.id_laporan = $id_laporan
");
$row = mysqli_fetch_assoc($laporan);
if (!$row) {
    header("Location: riwayat_laporan.php");
    exit;
}

$foto      = mysqli_query($conn, "SELECT * FROM foto_laporan WHERE id_laporan = $id_laporan");
$tanggapan = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap
    FROM tanggapan t
    JOIN users u ON t.id_admin = u.id
    WHERE t.id_laporan = $id_laporan
    ORDER BY t.created_at DESC
");

$status_badges = [
    'Menunggu' => ['cls' => 'bg-error-container text-on-error-container', 'icon' => 'schedule'],
    'Diproses'  => ['cls' => 'bg-primary-container text-on-primary-container', 'icon' => 'pending_actions'],
    'Selesai'   => ['cls' => 'bg-surface-container-highest text-secondary', 'icon' => 'check_circle'],
];
$badge     = $status_badges[$row['status']] ?? ['cls' => 'bg-surface-container-highest text-on-surface-variant', 'icon' => 'description'];
$badge_cls = $badge['cls'];
$badge_icon = $badge['icon'];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Detail Laporan #<?= $id_laporan ?> - Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); padding-bottom: 80px; }
    .bottom-nav { height: 68px; }
  </style>
</head>
<body class="bg-surface text-on-background selection:bg-primary-container selection:text-on-primary-container">

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
  <div class="absolute -top-10 -right-10 w-80 h-80 rounded-full bg-primary-container/8 blur-[100px]"></div>
  <div class="absolute bottom-0 -left-20 w-96 h-96 rounded-full bg-secondary-container/8 blur-[120px]"></div>
</div>

<!-- Top App Bar -->
<header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm">
  <div class="flex items-center justify-between px-6 py-4 w-full max-w-5xl mx-auto">
    <div class="flex items-center gap-3">
      <a href="riwayat_laporan.php" class="p-2 rounded-full hover:bg-surface-container transition-colors active:scale-95 group">
        <span class="material-symbols-outlined text-primary group-hover:-translate-x-1 transition-transform">arrow_back</span>
      </a>
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
        <h1 class="font-display font-bold text-xl text-primary">Sistem Pengaduan</h1>
      </div>
    </div>
    <div class="hidden sm:flex items-center gap-3">
      <div class="text-right">
        <p class="text-xs font-bold text-on-surface">Laporan #<?= $id_laporan ?></p>
        <p class="text-[10px] text-on-surface-variant"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></p>
      </div>
    </div>
  </div>
</header>

<main class="pt-24 px-6 max-w-5xl mx-auto space-y-6">

  <!-- Title + Status -->
  <section class="space-y-4">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
      <div class="space-y-3 flex-1">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest <?= $badge_cls ?>">
          <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1"><?= $badge_icon ?></span>
          <?= htmlspecialchars($row['status']) ?>
        </span>
        <h2 class="font-headline text-3xl md:text-4xl font-bold text-on-background tracking-tighter">
          <?= htmlspecialchars($row['judul']) ?>
        </h2>
        <div class="flex flex-wrap items-center gap-3">
          <span class="bg-primary-container/20 text-on-primary-container px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">
            <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
          </span>
          <span class="text-on-surface-variant text-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">calendar_today</span>
            <?= date('d F Y, H:i', strtotime($row['created_at'])) ?> WIB
          </span>
          <?php if ($row['anonim'] == 'Ya'): ?>
          <span class="bg-surface-container-highest text-on-surface-variant text-xs font-bold px-2 py-0.5 rounded-full uppercase">Anonim</span>
          <?php endif; ?>
          <?php if ($row['privat'] == 'Ya'): ?>
          <span class="bg-secondary/10 text-secondary text-xs font-bold px-2 py-0.5 rounded-full uppercase flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">lock</span>Privat
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Actions for editable reports -->
      <?php if ($row['status'] === 'Menunggu' && (int) $row['id_user'] === $id_user): ?>
      <div class="flex gap-3">
        <a href="edit_laporan.php?id=<?= $id_laporan ?>"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-low text-secondary font-bold text-sm hover:bg-secondary/10 transition-colors border border-outline-variant/20">
          <span class="material-symbols-outlined text-[18px]">edit</span> Edit
        </a>
        <a href="hapus_laporan.php?id=<?= $id_laporan ?>"
          onclick="return confirm('Yakin ingin menghapus laporan ini?')"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-error-container text-on-error-container font-bold text-sm hover:bg-error/20 transition-colors">
          <span class="material-symbols-outlined text-[18px]">delete</span> Hapus
        </a>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Main Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left: Description + Responses -->
    <div class="lg:col-span-8 space-y-6">

      <!-- Deskripsi -->
      <div class="bg-surface-container-low p-8 rounded-2xl space-y-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
          <span class="material-symbols-outlined text-9xl">description</span>
        </div>
        <h3 class="font-headline text-xl font-bold text-on-surface border-l-4 border-primary pl-4">Deskripsi Pengaduan</h3>
        <p class="font-body text-base leading-relaxed text-on-surface-variant whitespace-pre-line">
          <?= htmlspecialchars($row['isi_laporan']) ?>
        </p>
        <div class="pt-4 border-t border-outline-variant/10 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-surface-container p-4 rounded-xl">
            <p class="text-xs uppercase tracking-widest font-bold text-primary mb-2">Pelapor</p>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">person</span>
              </div>
              <div>
                <p class="font-bold text-on-surface text-sm"><?= htmlspecialchars($row['nama_pelapor']) ?></p>
                <p class="text-xs text-on-surface-variant">NIM: <?= ($row['anonim'] === 'Ya' && (int) $row['id_user'] !== $id_user) ? 'Disembunyikan' : htmlspecialchars($row['nim_pelapor'] ?? '-') ?></p>
              </div>
            </div>
          </div>
          <div class="bg-surface-container p-4 rounded-xl">
            <p class="text-xs uppercase tracking-widest font-bold text-primary mb-2">Kategori</p>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">category</span>
              </div>
              <div>
                <p class="font-bold text-on-surface text-sm"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?></p>
                <p class="text-xs text-on-surface-variant">ID: #<?= $id_laporan ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tanggapan Admin -->
      <?php $jml_tanggapan = $tanggapan ? mysqli_num_rows($tanggapan) : 0; ?>
      <div class="space-y-4">
        <h3 class="font-headline text-xl font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1">forum</span>
          Tanggapan Admin
          <?php if ($jml_tanggapan > 0): ?>
          <span class="text-sm font-normal text-on-surface-variant">(<?= $jml_tanggapan ?>)</span>
          <?php endif; ?>
        </h3>

        <?php if ($jml_tanggapan === 0): ?>
        <div class="bg-surface-container-lowest rounded-2xl p-8 text-center border border-outline-variant/10">
          <span class="material-symbols-outlined text-5xl block mb-3 opacity-20">forum</span>
          <p class="text-on-surface-variant font-medium">Belum ada tanggapan dari admin.</p>
          <p class="text-on-surface-variant/60 text-sm mt-1">Laporan Anda sedang menunggu ditinjau.</p>
        </div>
        <?php else: while ($t = mysqli_fetch_assoc($tanggapan)): ?>
        <div class="bg-surface-container-lowest rounded-2xl p-5 border-l-4 border-primary border border-outline-variant/10 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-primary-container/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-sm">manage_accounts</span>
              </div>
              <span class="font-bold text-sm text-primary"><?= htmlspecialchars($t['nama_lengkap']) ?></span>
              <span class="text-[10px] bg-primary-container/20 text-on-primary-container px-2 py-0.5 rounded-full font-bold uppercase">Admin</span>
            </div>
            <span class="text-xs text-on-surface-variant"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></span>
          </div>
          <p class="text-on-surface-variant leading-relaxed text-sm"><?= nl2br(htmlspecialchars($t['tanggapan'])) ?></p>
        </div>
        <?php endwhile; endif; ?>
      </div>
    </div>

    <!-- Right: Status + Photos -->
    <div class="lg:col-span-4 space-y-6">

      <!-- Status Card -->
      <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10">
        <h3 class="font-headline text-lg font-bold text-on-surface mb-4">Status Laporan</h3>
        <div class="flex flex-col gap-3">
          <?php
          $steps = [
            ['key' => 'Menunggu', 'label' => 'Menunggu Tinjauan', 'icon' => 'schedule'],
            ['key' => 'Diproses', 'label' => 'Sedang Diproses',  'icon' => 'pending_actions'],
            ['key' => 'Selesai',  'label' => 'Selesai',          'icon' => 'check_circle'],
          ];
          $current = $row['status'];
          $statusOrder = ['Menunggu' => 0, 'Diproses' => 1, 'Selesai' => 2];
          $currentOrder = $statusOrder[$current] ?? 0;
          foreach ($steps as $step):
            $stepOrder = $statusOrder[$step['key']] ?? 0;
            $isDone    = $stepOrder < $currentOrder;
            $isActive  = $step['key'] === $current;
          ?>
          <div class="flex items-center gap-3 <?= $isDone ? 'opacity-60' : '' ?>">
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 <?= $isActive ? 'bg-primary text-on-primary' : ($isDone ? 'bg-primary-container/30 text-primary' : 'bg-surface-container-highest text-on-surface-variant/40') ?>">
              <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' <?= ($isActive || $isDone) ? '1' : '0' ?>"><?= $step['icon'] ?></span>
            </div>
            <div class="flex-1">
              <p class="text-sm font-bold <?= $isActive ? 'text-primary' : ($isDone ? 'text-on-surface-variant' : 'text-on-surface-variant/40') ?>">
                <?= $step['label'] ?>
              </p>
              <?php if ($isActive): ?>
              <p class="text-[10px] text-primary font-bold uppercase tracking-widest mt-0.5">Saat ini</p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Photos -->
      <?php $foto_rows = []; while ($f = mysqli_fetch_assoc($foto)) $foto_rows[] = $f; ?>
      <?php if (!empty($foto_rows)): ?>
      <div class="bg-surface-container-low p-5 rounded-2xl space-y-3">
        <h3 class="font-headline text-lg font-bold text-on-surface">Bukti Foto</h3>
        <div class="grid grid-cols-2 gap-3">
          <?php foreach ($foto_rows as $f): ?>
          <a href="../<?= htmlspecialchars($f['path_file']) ?>" target="_blank"
            class="group relative aspect-square rounded-xl overflow-hidden border border-outline-variant/10">
            <img src="../<?= htmlspecialchars($f['path_file']) ?>" alt="Foto bukti"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
              <span class="text-white text-[10px] font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">zoom_in</span>Buka foto
              </span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="bg-surface-container-low p-5 rounded-2xl text-center text-on-surface-variant border border-outline-variant/10">
        <span class="material-symbols-outlined text-4xl block mb-2 opacity-20">image_not_supported</span>
        <p class="text-sm">Tidak ada foto bukti</p>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="h-4"></div>
</main>

<!-- Bottom Navigation -->
<nav class="fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 bg-surface/90 backdrop-blur-xl shadow-[0_-4px_20px_rgba(11,28,48,0.05)] z-50 bottom-nav">
  <a href="dashboard.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">home</span>
    <span class="font-label text-xs font-medium mt-1">Home</span>
  </a>
  <a href="riwayat_laporan.php" class="flex flex-col items-center justify-center bg-primary-container text-on-primary-container rounded-xl px-5 py-2">
    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">history</span>
    <span class="font-label text-xs font-bold mt-1">Riwayat</span>
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
