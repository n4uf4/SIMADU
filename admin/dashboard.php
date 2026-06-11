<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id'])) { header("Location: ../auth/login.php"); exit; }
if ($_SESSION['role'] != 'admin') { header("Location: ../mahasiswa/dashboard.php"); exit; }

function dashboard_count_rows(mysqli $conn, string $sql): int
{
  $result = mysqli_query($conn, $sql);
  return $result instanceof mysqli_result ? mysqli_num_rows($result) : 0;
}

function dashboard_query(mysqli $conn, string $sql): mysqli_result|false
{
  return mysqli_query($conn, $sql);
}

$total_laporan = dashboard_count_rows($conn, "SELECT id_laporan FROM laporan");
$menunggu      = dashboard_count_rows($conn, "SELECT id_laporan FROM laporan WHERE status='Menunggu'");
$diproses      = dashboard_count_rows($conn, "SELECT id_laporan FROM laporan WHERE status='Diproses'");
$selesai       = dashboard_count_rows($conn, "SELECT id_laporan FROM laporan WHERE status='Selesai'");

// Recent reports
$recent = dashboard_query($conn, "
    SELECT l.*, k.nama_kategori,
           CASE WHEN l.anonim='Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    ORDER BY l.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Dashboard Admin - Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); }
    .bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; }
    .glow-accent { box-shadow: 0 0 20px rgba(0, 209, 102, 0.2); }
    @keyframes pulse-glow { 0%,100%{opacity:.8;transform:scale(1)} 50%{opacity:1;transform:scale(1.05)} }
  </style>
</head>
<body class="bg-surface text-on-background min-h-screen selection:bg-primary-container selection:text-on-primary-container">

<!-- Sidebar -->
<aside id="nav-drawer" class="fixed left-0 top-0 h-full w-72 bg-surface z-[60] shadow-xl transition-transform duration-300 transform md:translate-x-0 -translate-x-full rounded-r-xl overflow-hidden flex flex-col">
  <!-- Profile -->
  <div class="p-8 flex flex-col gap-4">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-primary-container/20 flex items-center justify-center border border-outline-variant/20">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">manage_accounts</span>
      </div>
      <div>
        <h3 class="font-headline font-bold text-lg text-primary"><?= htmlspecialchars($_SESSION['nama']) ?></h3>
        <p class="text-on-surface-variant text-sm">Biro Kemahasiswaan</p>
        <div class="flex items-center gap-1.5 mt-1">
          <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
          <span class="text-[10px] uppercase tracking-widest font-bold text-primary">Online</span>
        </div>
      </div>
    </div>
  </div>
  <!-- Nav Links -->
  <nav class="flex flex-col h-full py-2 gap-1 overflow-y-auto">
    <a href="dashboard.php" class="bg-secondary-container text-on-secondary-container font-bold rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">dashboard</span>
      <span>Dashboard Admin</span>
    </a>
    <a href="laporan.php" class="text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">inbox</span>
      <span>Laporan Masuk</span>
    </a>
    <a href="arsip.php" class="text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">archive</span>
      <span>Arsip Laporan</span>
    </a>
    <div class="mt-auto pb-8">
      <a href="../auth/logout.php" class="text-on-surface-variant hover:bg-error-container hover:text-on-error-container rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
        <span class="material-symbols-outlined">logout</span>
        <span>Keluar</span>
      </a>
    </div>
  </nav>
</aside>

<!-- Main -->
<main class="md:ml-72 min-h-screen flex flex-col transition-all duration-300">
  <!-- Top AppBar -->
  <header class="fixed top-0 w-full md:w-[calc(100%-18rem)] z-50 bg-surface/80 backdrop-blur-md shadow-sm">
    <div class="flex items-center justify-between px-8 py-4 max-w-7xl mx-auto">
      <div class="flex items-center gap-4">
        <button id="menu-toggle" class="md:hidden text-primary p-2 active:scale-95 transition-transform">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
          <h1 class="font-display font-bold text-2xl text-primary tracking-tight">Sistem Pengaduan</h1>
        </div>
      </div>
      <div class="flex items-center gap-6">
        <div class="hidden md:flex items-center bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant/10">
          <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
          <input class="bg-transparent border-none focus:ring-0 text-sm w-48 placeholder:text-on-surface-variant/50" placeholder="Cari laporan..." type="text"/>
        </div>
        <a href="../auth/logout.php" class="text-on-surface-variant hover:text-error transition-colors" title="Logout">
          <span class="material-symbols-outlined">logout</span>
        </a>
        <div class="w-10 h-10 rounded-full border-2 border-primary-container bg-primary-container/20 flex items-center justify-center">
          <span class="material-symbols-outlined text-primary">manage_accounts</span>
        </div>
      </div>
    </div>
  </header>

  <!-- Content -->
  <div class="mt-20 p-8 max-w-7xl mx-auto w-full space-y-8">

    <!-- Hero Header -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <h2 class="font-headline text-4xl md:text-5xl font-bold text-on-background tracking-tighter">
          Dashboard <span class="text-primary">Admin</span>
        </h2>
        <p class="text-on-surface-variant mt-2 max-w-md font-body leading-relaxed">
          Pantau dan kelola seluruh laporan pengaduan mahasiswa secara real-time.
        </p>
      </div>
      <div class="flex gap-3">
        <a href="arsip.php" class="px-6 py-3 bg-surface-container-lowest text-primary font-bold rounded-lg border border-outline-variant/20 hover:bg-surface-container-low transition-all active:scale-95 flex items-center gap-2">
          <span class="material-symbols-outlined text-xl">archive</span> Arsip
        </a>
        <a href="laporan.php" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-lg shadow-lg hover:shadow-primary/20 transition-all active:scale-95 flex items-center gap-2">
          <span class="material-symbols-outlined text-xl">inbox</span> Semua Laporan
        </a>
      </div>
    </section>

    <!-- Stats Bento -->
    <section class="bento-grid">
      <!-- Total -->
      <div class="col-span-12 md:col-span-3 bg-surface-container-lowest p-8 rounded-2xl flex flex-col justify-between group hover:shadow-xl transition-all duration-500 border border-outline-variant/10">
        <div class="flex justify-between items-start">
          <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">description</span>
          </div>
        </div>
        <div class="mt-8">
          <p class="text-on-surface-variant font-label text-sm uppercase tracking-widest font-semibold">Total Laporan</p>
          <h3 class="font-headline text-5xl font-extrabold text-on-background mt-1 tracking-tight"><?= $total_laporan ?></h3>
        </div>
      </div>
      <!-- Menunggu -->
      <div class="col-span-12 md:col-span-3 bg-error-container/20 p-8 rounded-2xl flex flex-col justify-between group hover:shadow-xl transition-all duration-500 border border-outline-variant/10">
        <div class="flex justify-between items-start">
          <div class="w-14 h-14 bg-error/10 rounded-xl flex items-center justify-center text-error">
            <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">schedule</span>
          </div>
          <?php if($menunggu > 0): ?>
          <span class="text-error text-sm font-bold bg-error/10 px-3 py-1 rounded-full">Perlu Tindakan</span>
          <?php endif; ?>
        </div>
        <div class="mt-8">
          <p class="text-on-surface-variant font-label text-sm uppercase tracking-widest font-semibold">Menunggu</p>
          <h3 class="font-headline text-5xl font-extrabold text-on-background mt-1 tracking-tight"><?= $menunggu ?></h3>
        </div>
      </div>
      <!-- Diproses -->
      <div class="col-span-12 md:col-span-3 bg-surface-container-low p-8 rounded-2xl flex flex-col justify-between group hover:shadow-xl transition-all duration-500 border border-outline-variant/10">
        <div class="w-14 h-14 bg-secondary/10 rounded-xl flex items-center justify-center text-secondary">
          <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">pending_actions</span>
        </div>
        <div class="mt-8">
          <p class="text-on-surface-variant font-label text-sm uppercase tracking-widest font-semibold">Diproses</p>
          <h3 class="font-headline text-5xl font-extrabold text-on-background mt-1 tracking-tight"><?= $diproses ?></h3>
        </div>
      </div>
      <!-- Selesai -->
      <div class="col-span-12 md:col-span-3 bg-primary text-on-primary p-8 rounded-2xl flex flex-col justify-between glow-accent hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-gradient-to-tr from-primary-container to-transparent"></div>
        <div class="w-14 h-14 bg-on-primary/20 rounded-xl flex items-center justify-center relative z-10">
          <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">check_circle</span>
        </div>
        <div class="mt-8 relative z-10">
          <p class="text-on-primary/70 font-label text-sm uppercase tracking-widest font-semibold">Selesai</p>
          <h3 class="font-headline text-5xl font-extrabold mt-1 tracking-tight"><?= $selesai ?></h3>
          <?php if($total_laporan > 0): ?>
          <p class="text-[11px] mt-2 opacity-70"><?= round(($selesai / $total_laporan) * 100) ?>% Terselesaikan</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Recent Reports -->
    <div class="grid grid-cols-12 gap-8">
      <div class="col-span-12 lg:col-span-8 space-y-6">
        <div class="flex items-center justify-between">
          <h4 class="font-headline text-2xl font-bold tracking-tight">Laporan Terbaru</h4>
          <a href="laporan.php" class="text-primary font-bold text-sm hover:underline">Lihat semua →</a>
        </div>
        <div class="bg-surface-container-lowest rounded-3xl overflow-hidden border border-outline-variant/10">
          <div class="divide-y divide-outline-variant/10">
            <?php
            $status_colors = [
              'Menunggu' => 'bg-error-container text-on-error-container',
              'Diproses' => 'bg-surface-container-highest text-secondary',
              'Selesai'  => 'bg-primary-container/20 text-on-primary-container',
            ];
            if (!$recent): ?>
            <div class="p-12 text-center text-error">
              <span class="material-symbols-outlined text-5xl block mb-4 opacity-50">error</span>
              <p>Gagal memuat laporan terbaru.</p>
            </div>
            <?php elseif (mysqli_num_rows($recent) === 0): ?>
            <div class="p-12 text-center text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl block mb-4 opacity-30">inbox</span>
              <p>Belum ada laporan masuk.</p>
            </div>
            <?php else: while ($r = mysqli_fetch_assoc($recent)): 
              $sc = $status_colors[$r['status']] ?? 'bg-surface-container-highest text-on-surface-variant';
            ?>
            <div class="p-6 flex flex-col md:flex-row md:items-center gap-6 group hover:bg-surface-container-low transition-all">
              <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">description</span>
              </div>
              <div class="flex-grow">
                <div class="flex items-center gap-3 mb-1">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/5 px-2 py-0.5 rounded"><?= htmlspecialchars($r['nama_kategori'] ?? 'Umum') ?></span>
                  <span class="text-on-surface-variant/40 text-xs font-medium">#<?= $r['id_laporan'] ?></span>
                </div>
                <h5 class="font-headline font-bold text-lg group-hover:text-primary transition-colors"><?= htmlspecialchars($r['judul']) ?></h5>
                <p class="text-on-surface-variant text-sm mt-1">Oleh: <?= htmlspecialchars($r['nama_pelapor']) ?> • <?= date('d M Y', strtotime($r['created_at'])) ?></p>
              </div>
              <div class="flex flex-row md:flex-col items-center md:items-end gap-4 md:gap-2">
                <span class="px-3 py-1 <?= $sc ?> text-[10px] font-bold rounded-full uppercase"><?= $r['status'] ?></span>
                <a href="detail_laporan.php?id=<?= $r['id_laporan'] ?>" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm">open_in_new</span> Detail
                </a>
              </div>
            </div>
            <?php endwhile; endif; ?>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <aside class="col-span-12 lg:col-span-4 space-y-6">
        <h4 class="font-headline text-2xl font-bold tracking-tight">Aksi Cepat</h4>
        <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10 space-y-4">
          <a href="laporan.php" class="flex items-center gap-4 p-4 bg-surface rounded-xl hover:bg-primary-container/10 transition-colors group">
            <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform text-2xl">inbox</span>
            <div>
              <p class="font-bold text-sm text-on-surface">Kelola Laporan</p>
              <p class="text-xs text-on-surface-variant">Lihat & tindak lanjuti</p>
            </div>
          </a>
          <a href="arsip.php" class="flex items-center gap-4 p-4 bg-surface rounded-xl hover:bg-primary-container/10 transition-colors group">
            <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform text-2xl">archive</span>
            <div>
              <p class="font-bold text-sm text-on-surface">Arsip Laporan</p>
              <p class="text-xs text-on-surface-variant">Laporan yang diarsipkan</p>
            </div>
          </a>
          <a href="../auth/logout.php" class="flex items-center gap-4 p-4 bg-surface rounded-xl hover:bg-error-container/20 transition-colors group">
            <span class="material-symbols-outlined text-error group-hover:scale-110 transition-transform text-2xl">logout</span>
            <div>
              <p class="font-bold text-sm text-on-surface">Keluar</p>
              <p class="text-xs text-on-surface-variant">Akhiri sesi admin</p>
            </div>
          </a>
        </div>

        <!-- Distribution -->
        <div class="bg-inverse-surface text-inverse-on-surface p-8 rounded-3xl relative overflow-hidden">
          <div class="relative z-10">
            <span class="text-primary-container font-headline text-sm font-bold tracking-[0.2em] uppercase">Distribusi Status</span>
            <h5 class="text-2xl font-headline font-bold mt-2 mb-6">Laporan</h5>
            <div class="space-y-4">
              <?php $pct = fn($n) => $total_laporan > 0 ? round(($n/$total_laporan)*100) : 0; ?>
              <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold opacity-70">
                  <span>Menunggu</span><span><?= $pct($menunggu) ?>%</span>
                </div>
                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                  <div class="h-full bg-error rounded-full" style="width: <?= $pct($menunggu) ?>%"></div>
                </div>
              </div>
              <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold opacity-70">
                  <span>Diproses</span><span><?= $pct($diproses) ?>%</span>
                </div>
                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                  <div class="h-full bg-secondary rounded-full" style="width: <?= $pct($diproses) ?>%"></div>
                </div>
              </div>
              <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold opacity-70">
                  <span>Selesai</span><span><?= $pct($selesai) ?>%</span>
                </div>
                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                  <div class="h-full bg-primary-container rounded-full shadow-[0_0_10px_rgba(0,209,102,0.5)]" style="width: <?= $pct($selesai) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
  <div class="h-16"></div>
</main>

<script>
  const menuToggle = document.getElementById('menu-toggle');
  const navDrawer = document.getElementById('nav-drawer');
  let open = false;
  menuToggle?.addEventListener('click', () => {
    open = !open;
    navDrawer.classList.toggle('-translate-x-full', !open);
  });
  document.addEventListener('click', (e) => {
    if (window.innerWidth < 768 && open && !navDrawer.contains(e.target) && !menuToggle.contains(e.target)) {
      open = false;
      navDrawer.classList.add('-translate-x-full');
    }
  });
</script>
</body>
</html>
