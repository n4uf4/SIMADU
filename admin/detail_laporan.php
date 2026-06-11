<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') { header("Location: ../auth/login.php"); exit; }
if (!isset($_GET['id'])) { header("Location: laporan.php"); exit; }

$id_laporan = (int)$_GET['id'];

$laporan = mysqli_query($conn, "
    SELECT l.*, 
           CASE WHEN l.anonim='Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor,
       u.no_hp, k.nama_kategori
    FROM laporan l
    LEFT JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users u ON l.id_user = u.id
    WHERE l.id_laporan = $id_laporan
");
$row = mysqli_fetch_assoc($laporan);
if (!$row) { header("Location: laporan.php"); exit; }

$message = '';
$messageType = '';

if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $stmt = mysqli_prepare($conn, "UPDATE laporan SET status = ? WHERE id_laporan = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id_laporan);
    if (mysqli_stmt_execute($stmt)) {
        $message = 'Status berhasil diperbarui!';
        $messageType = 'success';
      $laporan = mysqli_query($conn, "SELECT l.*, CASE WHEN l.anonim='Ya' THEN 'Anonim' ELSE u.nama_lengkap END as nama_pelapor, u.no_hp, k.nama_kategori FROM laporan l LEFT JOIN kategori k ON l.id_kategori=k.id_kategori LEFT JOIN users u ON l.id_user=u.id WHERE l.id_laporan=$id_laporan");
        $row = mysqli_fetch_assoc($laporan);
    }
    mysqli_stmt_close($stmt);
}

if (isset($_POST['tambah_tanggapan'])) {
    $tanggapan_text = trim($_POST['tanggapan']);
    $id_admin = $_SESSION['id'];
    if (empty($tanggapan_text)) {
        $message = 'Tanggapan tidak boleh kosong!';
        $messageType = 'error';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO tanggapan (id_laporan, id_admin, tanggapan) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iis', $id_laporan, $id_admin, $tanggapan_text);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Tanggapan berhasil ditambahkan!';
            $messageType = 'success';
        }
        mysqli_stmt_close($stmt);
    }
}

$foto = mysqli_query($conn, "SELECT * FROM foto_laporan WHERE id_laporan = $id_laporan");
$tanggapan = mysqli_query($conn, "SELECT t.*, u.nama_lengkap FROM tanggapan t JOIN users u ON t.id_admin=u.id WHERE t.id_laporan=$id_laporan ORDER BY t.created_at DESC");

$status_badge = [
    'Menunggu' => 'bg-error-container text-on-error-container',
    'Diproses'  => 'bg-surface-container-highest text-secondary',
    'Selesai'   => 'bg-primary-container/20 text-on-primary-container',
];
$current_badge = $status_badge[$row['status']] ?? 'bg-surface-container-highest text-on-surface-variant';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Detail Laporan #<?= $id_laporan ?> - Admin</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); }
    .sidebar-active { background-color: #0070ea; color: #fff; font-weight: bold; }
  </style>
</head>
<body class="bg-surface text-on-surface selection:bg-primary-container selection:text-on-primary-container">
<div class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <aside class="hidden md:flex flex-col h-full w-72 bg-surface shadow-xl rounded-r-xl z-50">
    <div class="flex flex-col h-full py-8 gap-2">
      <div class="px-6 mb-8 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-primary-container/20 flex items-center justify-center">
          <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings:'FILL' 1">manage_accounts</span>
        </div>
        <div>
          <h3 class="font-display font-bold text-lg text-primary"><?= htmlspecialchars($_SESSION['nama']) ?></h3>
          <p class="text-xs text-on-surface-variant">Biro Kemahasiswaan</p>
          <div class="flex items-center gap-1 mt-1">
            <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
            <span class="text-[10px] uppercase tracking-wider text-primary font-bold">Online</span>
          </div>
        </div>
      </div>
      <nav class="flex-1 space-y-1">
        <a href="dashboard.php" class="flex items-center px-6 py-3 text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 transition-all group">
          <span class="material-symbols-outlined mr-3">dashboard</span><span>Dashboard Admin</span>
        </a>
        <a href="laporan.php" class="flex items-center px-6 py-3 bg-secondary-container text-on-secondary-container font-bold rounded-r-full mr-4 transition-all">
          <span class="material-symbols-outlined mr-3" style="font-variation-settings:'FILL' 1">inbox</span><span>Laporan Masuk</span>
        </a>
        <a href="arsip.php" class="flex items-center px-6 py-3 text-on-surface-variant hover:bg-surface-container-high rounded-r-full mr-4 transition-all group">
          <span class="material-symbols-outlined mr-3">archive</span><span>Arsip Laporan</span>
        </a>
        <a href="../auth/logout.php" class="flex items-center px-6 py-3 text-on-surface-variant hover:bg-error-container/20 hover:text-error rounded-r-full mr-4 transition-all group mt-4">
          <span class="material-symbols-outlined mr-3">logout</span><span>Keluar</span>
        </a>
      </nav>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 flex flex-col h-full overflow-y-auto">
    <!-- Top Bar -->
    <header class="sticky top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm">
      <div class="flex items-center justify-between px-6 py-4 w-full max-w-7xl mx-auto">
        <div class="flex items-center gap-4">
          <button class="md:hidden p-2 rounded-full hover:bg-primary-container/20 transition-all">
            <span class="material-symbols-outlined text-primary">menu</span>
          </button>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-3xl">bubble_chart</span>
            <h1 class="font-display font-bold text-xl text-primary tracking-tight">Sistem Pengaduan</h1>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="hidden sm:block text-right">
            <p class="text-xs font-bold text-on-surface">Detail Laporan #<?= $id_laporan ?></p>
            <p class="text-[10px] text-on-surface-variant">Terakhir diupdate: <?= date('d M Y H:i', strtotime($row['updated_at'] ?? $row['created_at'])) ?></p>
          </div>
          <div class="w-10 h-10 rounded-full border-2 border-primary-container bg-primary-container/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary text-lg">manage_accounts</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full space-y-8">

      <!-- Alert -->
      <?php if ($message): ?>
      <div class="flex items-center gap-3 p-4 rounded-xl font-medium text-sm
        <?= $messageType === 'success' ? 'bg-primary-container/20 text-on-primary-container' : 'bg-error-container text-on-error-container' ?>">
        <span class="material-symbols-outlined"><?= $messageType === 'success' ? 'check_circle' : 'error' ?></span>
        <?= htmlspecialchars($message) ?>
      </div>
      <?php endif; ?>

      <!-- Back + Title -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="space-y-2">
          <a href="laporan.php" class="flex items-center gap-2 text-primary font-medium hover:underline group w-fit">
            <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
            Kembali ke Daftar
          </a>
          <h2 class="font-headline text-3xl md:text-4xl font-bold text-on-background tracking-tighter">
            <?= htmlspecialchars($row['judul']) ?>
          </h2>
          <div class="flex flex-wrap gap-3 items-center">
            <span class="bg-primary-container/20 text-on-primary-container px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">
              <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
            </span>
            <span class="<?= $current_badge ?> px-3 py-1 rounded-full text-xs font-bold uppercase">
              <?= $row['status'] ?>
            </span>
            <span class="text-on-surface-variant text-sm flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">calendar_today</span>
              <?= date('d F Y, H:i', strtotime($row['created_at'])) ?> WIB
            </span>
          </div>
        </div>
        <div class="flex gap-3">
          <a href="hapus_laporan.php?id=<?= $id_laporan ?>" onclick="return confirm('Yakin hapus laporan ini?')"
            class="p-3 bg-surface-container-low text-error hover:bg-error-container hover:text-on-error-container transition-colors rounded-xl shadow-sm active:scale-95">
            <span class="material-symbols-outlined">delete</span>
          </a>
          <a href="arsip.php?archive=<?= $id_laporan ?>"
            class="p-3 bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high transition-colors rounded-xl shadow-sm active:scale-95">
            <span class="material-symbols-outlined">archive</span>
          </a>
        </div>
      </div>

      <!-- Bento Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left: Details + Response -->
        <div class="lg:col-span-8 space-y-8">

          <!-- Description Card -->
          <div class="bg-surface-container-low p-8 rounded-2xl space-y-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
              <span class="material-symbols-outlined text-9xl">description</span>
            </div>
            <h3 class="font-headline text-xl font-bold text-on-surface border-l-4 border-primary pl-4">Deskripsi Pengaduan</h3>
            <p class="font-body text-lg leading-relaxed text-on-surface-variant">
              <?= nl2br(htmlspecialchars($row['isi_laporan'])) ?>
            </p>
            <div class="pt-6 border-t border-outline-variant/30 grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Pelapor -->
              <div class="space-y-4">
                <h4 class="font-headline text-sm font-bold text-primary uppercase tracking-widest">Informasi Pelapor</h4>
                <div class="flex items-center gap-4 bg-surface-container p-4 rounded-xl">
                  <div class="w-12 h-12 rounded-lg bg-primary-container/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">person</span>
                  </div>
                  <div>
                    <p class="font-bold text-on-surface"><?= htmlspecialchars($row['nama_pelapor']) ?></p>
                    <?php if ($row['anonim'] != 'Ya'): ?>
                    <p class="text-sm text-on-surface-variant"><?= htmlspecialchars($row['email'] ?? '-') ?></p>
                    <?php endif; ?>
                    <div class="flex gap-2 mt-1">
                      <?php if ($row['anonim'] == 'Ya'): ?>
                      <span class="text-[10px] bg-surface-container-highest px-2 py-0.5 rounded font-bold uppercase text-on-surface-variant">Anonim</span>
                      <?php endif; ?>
                      <?php if ($row['privat'] == 'Ya'): ?>
                      <span class="text-[10px] bg-secondary/10 text-secondary px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">lock</span>Privat
                      </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Kategori -->
              <div class="space-y-4">
                <h4 class="font-headline text-sm font-bold text-primary uppercase tracking-widest">Kategori</h4>
                <div class="flex items-center gap-4 bg-surface-container p-4 rounded-xl">
                  <span class="material-symbols-outlined text-primary text-3xl">category</span>
                  <div>
                    <p class="font-bold text-on-surface"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?></p>
                    <p class="text-sm text-on-surface-variant">ID Laporan: #<?= $id_laporan ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tanggapan History -->
          <?php if (mysqli_num_rows($tanggapan) > 0): ?>
          <div class="bg-surface-container-low p-8 rounded-2xl space-y-4">
            <h3 class="font-headline text-xl font-bold text-on-surface">Riwayat Tanggapan</h3>
            <div class="space-y-4">
              <?php while ($t = mysqli_fetch_assoc($tanggapan)): ?>
              <div class="bg-surface-container p-5 rounded-xl border-l-4 border-primary">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-bold text-sm text-primary"><?= htmlspecialchars($t['nama_lengkap']) ?></span>
                  <span class="text-xs text-on-surface-variant"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></span>
                </div>
                <p class="text-on-surface-variant leading-relaxed"><?= nl2br(htmlspecialchars($t['tanggapan'])) ?></p>
              </div>
              <?php endwhile; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Response Form -->
          <div class="bg-surface-container-lowest p-8 rounded-2xl shadow-sm space-y-6">
            <h3 class="font-headline text-xl font-bold text-on-surface">Kirim Tanggapan Resmi</h3>
            <form method="POST" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-on-surface-variant mb-2" for="tanggapan">Berikan tanggapan atau instruksi tindak lanjut:</label>
                <textarea id="tanggapan" name="tanggapan" rows="6" placeholder="Tuliskan pesan konfirmasi atau penyelesaian di sini..."
                  class="w-full bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary text-on-surface p-4 transition-all resize-none"></textarea>
              </div>
              <div class="flex justify-end gap-4">
                <button type="reset" class="px-6 py-3 font-bold text-primary hover:bg-primary-container/10 rounded-xl transition-all">Reset</button>
                <button type="submit" name="tambah_tanggapan" class="px-8 py-3 font-bold bg-primary text-on-primary rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                  <span class="material-symbols-outlined">send</span> Kirim Tanggapan
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Right: Status + Photos -->
        <div class="lg:col-span-4 space-y-8">

          <!-- Status Management -->
          <div class="bg-surface-container p-8 rounded-2xl space-y-6">
            <h3 class="font-headline text-xl font-bold text-on-surface">Kelola Status</h3>
            <form method="POST" class="space-y-4">
              <div class="relative">
                <label class="block text-xs font-bold text-primary uppercase tracking-widest mb-2">Status Saat Ini</label>
                <select name="status" id="statusSelect"
                  class="w-full bg-surface-container-lowest border-2 border-outline-variant/30 rounded-xl px-4 py-3 font-bold text-on-surface appearance-none focus:border-primary transition-all">
                  <option value="Menunggu" <?= $row['status'] == 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                  <option value="Diproses" <?= $row['status'] == 'Diproses'  ? 'selected' : '' ?>>🔄 Diproses</option>
                  <option value="Selesai"  <?= $row['status'] == 'Selesai'   ? 'selected' : '' ?>>✅ Selesai</option>
                </select>
                <div class="absolute right-4 bottom-3.5 pointer-events-none text-on-surface-variant">
                  <span class="material-symbols-outlined">expand_more</span>
                </div>
              </div>
              <p class="text-xs text-on-surface-variant leading-relaxed">
                <span class="font-bold text-on-surface">Catatan:</span> Mengubah ke "Selesai" akan menutup laporan ini.
              </p>
              <button type="submit" name="update_status" class="w-full py-3 font-bold bg-primary text-on-primary rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                Perbarui Status
              </button>
            </form>
          </div>

          <!-- Photos -->
          <?php if (mysqli_num_rows($foto) > 0): ?>
          <div class="bg-surface-container-low p-6 rounded-2xl space-y-4">
            <h3 class="font-headline text-lg font-bold text-on-surface">Bukti Foto</h3>
            <div class="grid grid-cols-2 gap-3">
              <?php while ($f = mysqli_fetch_assoc($foto)): ?>
              <a href="../<?= htmlspecialchars($f['path_file']) ?>" target="_blank" class="group relative aspect-square rounded-xl overflow-hidden">
                <img src="../<?= htmlspecialchars($f['path_file']) ?>" alt="Foto bukti" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                  <span class="text-white text-[10px] font-medium">Buka foto</span>
                </div>
              </a>
              <?php endwhile; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="bg-surface-container-low p-6 rounded-2xl text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">image_not_supported</span>
            <p class="text-sm">Tidak ada foto bukti</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="h-16"></div>
    </div>
  </main>
</div>

<script>
  document.getElementById('statusSelect')?.addEventListener('change', (e) => {
    const colors = { Menunggu: '#ba1a1a', Diproses: '#0059bb', Selesai: '#006d32' };
    e.target.style.color = colors[e.target.value] || '';
  });
</script>
</body>
</html>
