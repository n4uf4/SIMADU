<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_admin = (int) $_SESSION['id'];
$message  = '';
$messageType = '';

// Handle archive action
if (isset($_POST['arsipkan'])) {
    $id_laporan  = (int) $_POST['id_laporan'];
    $keterangan  = trim($_POST['keterangan'] ?? '');

    $stmt = mysqli_prepare($conn, "INSERT INTO arsip (id_laporan, diarsipkan_oleh, keterangan) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iis', $id_laporan, $id_admin, $keterangan);
    if (mysqli_stmt_execute($stmt)) {
        $message = 'Laporan berhasil diarsipkan!';
        $messageType = 'success';
    } else {
        $message = 'Gagal mengarsipkan laporan.';
        $messageType = 'error';
    }
    mysqli_stmt_close($stmt);
}

// Handle restore action
if (isset($_GET['restore'])) {
    $id_arsip = (int) $_GET['restore'];
    $stmt = mysqli_prepare($conn, "DELETE FROM arsip WHERE id_arsip = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_arsip);
    if (mysqli_stmt_execute($stmt)) {
        $message = 'Laporan berhasil dipulihkan dari arsip!';
        $messageType = 'success';
    }
    mysqli_stmt_close($stmt);
}

$arsip = mysqli_query($conn, "
    SELECT a.*,
           l.judul,
           CASE WHEN l.anonim = 'Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor,
           k.nama_kategori,
           admin.nama_lengkap as nama_admin
    FROM arsip a
    JOIN laporan l ON a.id_laporan = l.id_laporan
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    JOIN users admin ON a.diarsipkan_oleh = admin.id
    ORDER BY a.created_at DESC
");

$laporan_aktif = mysqli_query($conn, "
    SELECT l.id_laporan, l.judul
    FROM laporan l
    WHERE l.id_laporan NOT IN (SELECT id_laporan FROM arsip)
    ORDER BY l.judul ASC
");

$total_arsip = $arsip ? mysqli_num_rows($arsip) : 0;
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Arsip Laporan - Admin | Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); }
  </style>
</head>
<body class="bg-surface text-on-background selection:bg-primary-container selection:text-on-primary-container">

<!-- Sidebar -->
<aside id="nav-drawer" class="fixed left-0 top-0 h-full w-72 bg-surface z-[60] shadow-xl transition-transform duration-300 transform md:translate-x-0 -translate-x-full rounded-r-xl overflow-hidden flex flex-col">
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
  <nav class="flex flex-col h-full py-2 gap-1 overflow-y-auto">
    <a href="dashboard.php" class="text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">dashboard</span><span>Dashboard Admin</span>
    </a>
    <a href="laporan.php" class="text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined">inbox</span><span>Laporan Masuk</span>
    </a>
    <a href="arsip.php" class="bg-secondary-container text-on-secondary-container font-bold rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
      <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">archive</span><span>Arsip Laporan</span>
    </a>
    <div class="mt-auto pb-8">
      <a href="../auth/logout.php" class="text-on-surface-variant hover:bg-error-container hover:text-on-error-container rounded-r-full mr-4 px-6 py-4 flex items-center gap-4 transition-all">
        <span class="material-symbols-outlined">logout</span><span>Keluar</span>
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

    <!-- Header -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <a href="dashboard.php" class="flex items-center gap-2 text-primary font-medium text-sm hover:underline w-fit mb-3 group">
          <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>Dashboard
        </a>
        <h2 class="font-headline text-4xl md:text-5xl font-bold text-on-background tracking-tighter">
          Arsip <span class="text-primary">Laporan</span>
        </h2>
        <p class="text-on-surface-variant mt-2 max-w-md font-body">
          Simpan laporan selesai ke arsip dan pulihkan kapan pun diperlukan.
        </p>
      </div>
      <div class="bg-surface-container-low p-4 rounded-2xl border border-outline-variant/10 text-center min-w-[120px]">
        <p class="text-xs uppercase tracking-widest font-bold text-on-surface-variant">Total Arsip</p>
        <p class="font-headline text-4xl font-bold text-on-background mt-1"><?= $total_arsip ?></p>
      </div>
    </section>

    <!-- Alert -->
    <?php if ($message): ?>
    <div class="flex items-center gap-3 p-4 rounded-2xl text-sm font-medium <?= $messageType === 'success' ? 'bg-primary-container/20 text-on-primary-container' : 'bg-error-container text-on-error-container' ?>">
      <span class="material-symbols-outlined"><?= $messageType === 'success' ? 'check_circle' : 'error' ?></span>
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Grid: Archive Form + List -->
    <section class="grid grid-cols-1 lg:grid-cols-5 gap-6">

      <!-- Archive Form -->
      <div class="lg:col-span-2 bg-surface-container-lowest rounded-[2rem] p-6 border border-outline-variant/10 shadow-sm space-y-5 h-fit">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-primary-container/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1">archive</span>
          </div>
          <h2 class="font-headline text-xl font-bold">Arsipkan Laporan</h2>
        </div>
        <form method="POST" class="space-y-4">
          <div class="space-y-2">
            <label class="text-xs uppercase tracking-widest font-bold text-primary block">Pilih Laporan</label>
            <div class="relative">
              <select name="id_laporan" required
                class="w-full appearance-none bg-surface-container-low border-none rounded-2xl px-4 py-3 focus:ring-2 focus:ring-primary text-on-surface">
                <option value="">-- Pilih Laporan --</option>
                <?php while ($l = mysqli_fetch_assoc($laporan_aktif)): ?>
                <option value="<?= $l['id_laporan'] ?>"><?= htmlspecialchars($l['judul']) ?></option>
                <?php endwhile; ?>
              </select>
              <span class="material-symbols-outlined absolute right-3 top-3.5 text-on-surface-variant pointer-events-none">expand_more</span>
            </div>
          </div>
          <div class="space-y-2">
            <label class="text-xs uppercase tracking-widest font-bold text-primary block">Keterangan</label>
            <textarea name="keterangan" rows="4" placeholder="Catatan mengapa laporan diarsipkan..."
              class="w-full bg-surface-container-low border-none rounded-2xl px-4 py-3 focus:ring-2 focus:ring-primary text-on-surface resize-none placeholder:text-on-surface-variant/40"></textarea>
          </div>
          <button type="submit" name="arsipkan"
            class="w-full px-6 py-3 rounded-xl bg-primary text-on-primary font-bold shadow-lg shadow-primary/20 flex items-center justify-center gap-2 hover:bg-primary/90 active:scale-95 transition-all">
            <span class="material-symbols-outlined">archive</span>
            Arsipkan
          </button>
        </form>
      </div>

      <!-- Archive List -->
      <div class="lg:col-span-3 space-y-4">
        <h2 class="font-headline text-xl font-bold">Daftar Arsip <span class="text-on-surface-variant text-base font-normal">(<?= $total_arsip ?>)</span></h2>

        <?php if ($total_arsip === 0): ?>
        <div class="bg-surface-container-lowest rounded-3xl p-10 text-center border border-outline-variant/10">
          <span class="material-symbols-outlined text-6xl block mb-4 opacity-20">archive</span>
          <h3 class="font-headline text-xl font-bold mb-2">Arsip Kosong</h3>
          <p class="text-on-surface-variant text-sm">Belum ada laporan yang diarsipkan.</p>
        </div>
        <?php else: while ($arsip_row = mysqli_fetch_assoc($arsip)): ?>
        <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
          <div class="flex items-start justify-between gap-4">
            <div class="flex gap-4 flex-1 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-on-surface-variant" style="font-variation-settings:'FILL' 1">description</span>
              </div>
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/5 px-2 py-0.5 rounded-full">
                    <?= htmlspecialchars($arsip_row['nama_kategori'] ?? 'Umum') ?>
                  </span>
                </div>
                <p class="font-bold text-on-surface truncate"><?= htmlspecialchars($arsip_row['judul']) ?></p>
                <p class="text-sm text-on-surface-variant mt-0.5">
                  Pelapor: <?= htmlspecialchars($arsip_row['nama_pelapor']) ?>
                </p>
                <p class="text-xs text-on-surface-variant/60 mt-0.5">
                  Diarsipkan oleh <strong><?= htmlspecialchars($arsip_row['nama_admin']) ?></strong>
                  • <?= date('d M Y, H:i', strtotime($arsip_row['created_at'])) ?>
                </p>
                <?php if (!empty($arsip_row['keterangan'])): ?>
                <p class="text-xs text-on-surface-variant/70 mt-1.5 bg-surface-container px-3 py-1.5 rounded-lg italic">
                  "<?= htmlspecialchars($arsip_row['keterangan']) ?>"
                </p>
                <?php endif; ?>
              </div>
            </div>
            <a href="?restore=<?= $arsip_row['id_arsip'] ?>"
              onclick="return confirm('Pulihkan laporan ini dari arsip?')"
              class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-surface-container text-primary font-bold text-sm hover:bg-primary-container/20 transition-colors whitespace-nowrap flex-shrink-0">
              <span class="material-symbols-outlined text-[16px]">restore</span>
              Pulihkan
            </a>
          </div>
        </div>
        <?php endwhile; endif; ?>
      </div>
    </section>

    <div class="h-8"></div>
  </div>
</main>

<script>
  const menuToggle = document.getElementById('menu-toggle');
  const navDrawer  = document.getElementById('nav-drawer');
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
