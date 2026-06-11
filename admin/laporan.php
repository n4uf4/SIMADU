<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id'])) { header("Location: ../auth/login.php"); exit; }
if ($_SESSION['role'] != 'admin') { header("Location: ../mahasiswa/dashboard.php"); exit; }

// Filter dan search
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT l.*, 
           CASE WHEN l.anonim = 'Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor,
           u.no_hp, k.nama_kategori
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    WHERE 1=1
";

if ($status_filter) {
    $query .= " AND l.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}
if ($search) {
    $query .= " AND (l.judul LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR l.isi_laporan LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$query .= " ORDER BY l.created_at DESC";
$data = mysqli_query($conn, $query);

$total    = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan"));
$menunggu = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE status='Menunggu'"));
$diproses = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE status='Diproses'"));
$selesai  = (int) mysqli_num_rows(mysqli_query($conn, "SELECT id_laporan FROM laporan WHERE status='Selesai'"));

$status_badges = [
    'Menunggu' => 'bg-error-container text-on-error-container',
    'Diproses'  => 'bg-primary-container text-on-primary-container',
    'Selesai'   => 'bg-surface-container-highest text-secondary',
];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Kelola Laporan - Admin | Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); }
    .card-hover { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,109,50,0.08); }
  </style>
</head>
<body class="bg-surface text-on-background selection:bg-primary-container selection:text-on-primary-container">

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
    <a href="dashboard.php" class="text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">dashboard</span>
      <span>Dashboard Admin</span>
    </a>
    <a href="laporan.php" class="bg-secondary-container text-on-secondary-container font-bold rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">inbox</span>
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
      <div class="flex items-center gap-4">
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
  <div class="mt-20 p-6 md:p-8 max-w-7xl mx-auto w-full space-y-8">

    <!-- Page Header -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <a href="dashboard.php" class="flex items-center gap-2 text-primary font-medium text-sm hover:underline w-fit mb-3 group">
          <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
          Dashboard
        </a>
        <h2 class="font-headline text-4xl md:text-5xl font-bold text-on-background tracking-tighter">
          Kelola <span class="text-primary">Laporan</span>
        </h2>
        <p class="text-on-surface-variant mt-2 max-w-md font-body">
          Filter, cari, dan tindak lanjuti semua laporan masuk dari mahasiswa.
        </p>
      </div>
    </section>

    <!-- Stats Row -->
    <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <a href="laporan.php" class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/10 hover:shadow-md transition-all group">
        <p class="text-xs uppercase tracking-widest font-bold text-on-surface-variant">Total</p>
        <p class="font-headline text-4xl font-bold text-on-background mt-2"><?= $total ?></p>
      </a>
      <a href="?status=Menunggu" class="bg-error-container/20 p-5 rounded-2xl border border-outline-variant/10 hover:shadow-md transition-all">
        <p class="text-xs uppercase tracking-widest font-bold text-error">Menunggu</p>
        <p class="font-headline text-4xl font-bold text-error mt-2"><?= $menunggu ?></p>
      </a>
      <a href="?status=Diproses" class="bg-primary-container/15 p-5 rounded-2xl border border-outline-variant/10 hover:shadow-md transition-all">
        <p class="text-xs uppercase tracking-widest font-bold text-primary">Diproses</p>
        <p class="font-headline text-4xl font-bold text-primary mt-2"><?= $diproses ?></p>
      </a>
      <a href="?status=Selesai" class="bg-surface-container-highest/50 p-5 rounded-2xl border border-outline-variant/10 hover:shadow-md transition-all">
        <p class="text-xs uppercase tracking-widest font-bold text-secondary">Selesai</p>
        <p class="font-headline text-4xl font-bold text-secondary mt-2"><?= $selesai ?></p>
      </a>
    </section>

    <!-- Filter / Search -->
    <section class="bg-surface-container-lowest rounded-[2rem] p-6 border border-outline-variant/10 shadow-sm">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
        <div class="md:col-span-3 space-y-2">
          <label class="text-xs uppercase tracking-widest font-bold text-primary">Status</label>
          <div class="relative">
            <select name="status" class="w-full appearance-none bg-surface-container-low border-none rounded-2xl px-4 py-3 focus:ring-2 focus:ring-primary text-on-surface">
              <option value="">Semua Status</option>
              <option value="Menunggu" <?= ($status_filter == 'Menunggu') ? 'selected' : '' ?>>⏳ Menunggu</option>
              <option value="Diproses" <?= ($status_filter == 'Diproses') ? 'selected' : '' ?>>🔄 Diproses</option>
              <option value="Selesai"  <?= ($status_filter == 'Selesai')  ? 'selected' : '' ?>>✅ Selesai</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-3.5 text-on-surface-variant pointer-events-none">expand_more</span>
          </div>
        </div>
        <div class="md:col-span-7 space-y-2">
          <label class="text-xs uppercase tracking-widest font-bold text-primary">Cari</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-3.5 text-on-surface-variant/50">search</span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
              placeholder="Cari judul atau isi laporan..."
              class="w-full bg-surface-container-low border-none rounded-2xl pl-11 pr-4 py-3 focus:ring-2 focus:ring-primary text-on-surface"/>
          </div>
        </div>
        <div class="md:col-span-2 flex gap-2">
          <button type="submit" class="flex-1 px-4 py-3 rounded-2xl bg-primary text-on-primary font-bold hover:bg-primary/90 transition-colors">Filter</button>
          <a href="laporan.php" class="flex-1 px-4 py-3 rounded-2xl bg-surface-container-lowest border border-outline-variant/20 text-primary font-bold text-center">Reset</a>
        </div>
      </form>
    </section>

    <!-- Results Header -->
    <?php $count = $data ? mysqli_num_rows($data) : 0; ?>
    <div class="flex items-center justify-between">
      <h3 class="font-headline text-xl font-bold text-on-surface">
        <?php if ($status_filter || $search): ?>
          Hasil Filter
          <span class="ml-2 text-sm font-normal text-on-surface-variant"><?= $count ?> laporan ditemukan</span>
        <?php else: ?>
          Semua Laporan
          <span class="ml-2 text-sm font-normal text-on-surface-variant"><?= $count ?> total</span>
        <?php endif; ?>
      </h3>
    </div>

    <!-- List -->
    <section class="space-y-4">
      <?php if (!$data || $count === 0): ?>
      <div class="bg-surface-container-lowest rounded-3xl p-12 text-center border border-outline-variant/10">
        <span class="material-symbols-outlined text-6xl block mb-4 opacity-20">inbox</span>
        <h3 class="font-headline text-xl font-bold mb-2">Tidak ada laporan</h3>
        <p class="text-on-surface-variant text-sm">
          <?= ($status_filter || $search) ? 'Coba ubah filter atau kata kunci pencarian.' : 'Belum ada laporan yang masuk.' ?>
        </p>
      </div>
      <?php else: while ($row = mysqli_fetch_assoc($data)):
        $badge = $status_badges[$row['status']] ?? 'bg-surface-container-highest text-on-surface-variant';
      ?>
      <article class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/10 shadow-sm card-hover">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5 justify-between">
          <div class="space-y-3 flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/5 px-2 py-1 rounded-full">
                <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
              </span>
              <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-full <?= $badge ?>">
                <?= htmlspecialchars($row['status']) ?>
              </span>
              <?php if($row['anonim'] == 'Ya'): ?>
              <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-full bg-surface-container-highest text-on-surface-variant">Anonim</span>
              <?php endif; ?>
              <?php if($row['privat'] == 'Ya'): ?>
              <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-full bg-secondary/10 text-secondary">Privat</span>
              <?php endif; ?>
            </div>
            <h2 class="font-headline text-xl md:text-2xl font-bold text-on-background truncate">
              <?= htmlspecialchars($row['judul']) ?>
            </h2>
            <p class="text-on-surface-variant text-sm">
              Oleh <strong><?= htmlspecialchars($row['nama_pelapor']) ?></strong>
              <span class="mx-1">•</span>
              <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
            </p>
          </div>
          <div class="flex items-center gap-3 flex-wrap">
            <a href="detail_laporan.php?id=<?= $row['id_laporan'] ?>"
              class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm hover:bg-primary/90 transition-colors active:scale-95">
              <span class="material-symbols-outlined text-sm">open_in_new</span>
              Detail & Tindak Lanjut
            </a>
          </div>
        </div>
      </article>
      <?php endwhile; endif; ?>
    </section>

    <div class="h-8"></div>
  </div>
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
