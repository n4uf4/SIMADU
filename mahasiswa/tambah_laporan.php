<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id'];
$message = '';
$messageType = '';

$defaultCategoryId = null;
$defaultCategoryResult = mysqli_query($conn, "SELECT id_kategori FROM kategori ORDER BY id_kategori ASC LIMIT 1");
if ($defaultCategoryResult instanceof mysqli_result) {
  $defaultCategoryRow = mysqli_fetch_assoc($defaultCategoryResult);
  $defaultCategoryId = isset($defaultCategoryRow['id_kategori']) ? (int) $defaultCategoryRow['id_kategori'] : null;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $kategori = trim($_POST['kategori'] ?? '');
  $kategori = $kategori !== '' ? (int) $kategori : $defaultCategoryId;
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');

    $anonim = isset($_POST['anonim']) ? 'Ya' : 'Tidak';
    $privat = isset($_POST['privat']) ? 'Ya' : 'Tidak';

    if(empty($judul) || empty($isi)){
        $message = 'Judul dan isi laporan wajib diisi.';
        $messageType = 'error';
    } else {
        // Handle file upload
        $files_uploaded = array();
        if(isset($_FILES['foto']) && count($_FILES['foto']['name']) > 0){
          $upload_dir = __DIR__ . '/../assets/uploads/';
            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }

            for($i = 0; $i < count($_FILES['foto']['name']); $i++){
                if($_FILES['foto']['error'][$i] == 0){
                    $file_name = $_FILES['foto']['name'][$i];
                    $file_tmp = $_FILES['foto']['tmp_name'][$i];
                    $file_size = $_FILES['foto']['size'][$i];
                    
                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if(in_array($file_ext, $allowed) && $file_size <= 5000000){
                      $base_name = pathinfo($file_name, PATHINFO_FILENAME);
                      $base_name = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $base_name);
                      $base_name = trim($base_name, '_');
                      if($base_name === ''){
                        $base_name = 'foto';
                      }
                      $new_filename = time() . '_' . $base_name . '.' . $file_ext;
                      if(move_uploaded_file($file_tmp, $upload_dir . $new_filename)){
                            $files_uploaded[] = $new_filename;
                        }
                    }
                }
            }
        }

        if ($kategori === null) {
          $message = 'Tidak ada kategori yang tersedia. Hubungi admin.';
          $messageType = 'error';
        } else {
          $stmt = mysqli_prepare($conn, "INSERT INTO laporan (id_user, id_kategori, judul, isi_laporan, anonim, privat) VALUES (?, ?, ?, ?, ?, ?)");
          if (!$stmt) {
            $message = 'Gagal menyiapkan penyimpanan laporan: ' . mysqli_error($conn);
            $messageType = 'error';
          } else {
          mysqli_stmt_bind_param($stmt, 'iissss', $id_user, $kategori, $judul, $isi, $anonim, $privat);
        
          if(mysqli_stmt_execute($stmt)){
            $id_laporan = mysqli_insert_id($conn);
                
            foreach($files_uploaded as $file){
              $stmt_foto = mysqli_prepare($conn, "INSERT INTO foto_laporan (id_laporan, nama_file, path_file) VALUES (?, ?, ?)");
              $path = 'assets/uploads/' . $file;
              mysqli_stmt_bind_param($stmt_foto, 'iss', $id_laporan, $file, $path);
              mysqli_stmt_execute($stmt_foto);
              mysqli_stmt_close($stmt_foto);
            }
                
            $message = 'Laporan berhasil dikirim! Mengalihkan...';
            $messageType = 'success';
          } else {
            $message = 'Gagal menyimpan laporan: ' . mysqli_stmt_error($stmt);
            $messageType = 'error';
          }
          mysqli_stmt_close($stmt);
          }
        }
    }
}

$categories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <title>Buat Laporan - Sistem Pengaduan</title>
  <?php require_once __DIR__ . '/../includes/head_stitch.php'; ?>
  <style>
    body { min-height: max(884px, 100dvh); padding-bottom: 80px; }
    .upload-area { border: 2px dashed rgba(187,203,185,0.4); transition: all 0.3s ease; }
    .upload-area:hover, .upload-area.drag-over { border-color: #00d166; background: rgba(0,209,102,0.05); }
    .toggle-switch { position: relative; width: 56px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
      position: absolute; cursor: pointer; inset: 0;
      background-color: #cbdbf5; border-radius: 28px; transition: 0.3s;
    }
    .toggle-slider:before {
      content: ""; position: absolute; height: 20px; width: 20px;
      left: 4px; bottom: 4px; background-color: white;
      border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    input:checked + .toggle-slider { background-color: #006d32; }
    input:checked + .toggle-slider:before { transform: translateX(28px); }
    .bottom-nav { height: 68px; }
    select option { background: white; color: #0b1c30; }
    .char-count { font-size: 0.7rem; }
  </style>
</head>
<body class="bg-surface text-on-background antialiased">

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
  <div class="absolute top-1/4 -right-24 w-96 h-96 bg-primary-container/10 rounded-full blur-[120px]"></div>
  <div class="absolute bottom-1/4 -left-24 w-80 h-80 bg-secondary-container/10 rounded-full blur-[100px]"></div>
</div>

<!-- Top App Bar -->
<header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm">
  <div class="flex items-center justify-between px-6 py-4 w-full max-w-4xl mx-auto">
    <div class="flex items-center gap-3">
      <a href="dashboard.php" class="p-2 rounded-full hover:bg-surface-container transition-colors active:scale-95">
        <span class="material-symbols-outlined text-primary">arrow_back</span>
      </a>
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings:'FILL' 1">bubble_chart</span>
        <h1 class="font-display font-bold text-xl text-primary">Sistem Pengaduan</h1>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center hover:bg-primary-container/20 transition-colors">
        <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
      </button>
      <div class="w-10 h-10 rounded-full bg-primary-container/20 border-2 border-primary/20 flex items-center justify-center">
        <span class="material-symbols-outlined text-primary text-lg">person</span>
      </div>
    </div>
  </div>
</header>

<!-- Main Content -->
<main class="pt-24 px-6 max-w-3xl mx-auto pb-8">

  <!-- Header -->
  <div class="mb-10">
    <h2 class="font-display text-4xl font-bold text-on-background tracking-tight mb-2">Buat Laporan Baru</h2>
    <p class="text-on-surface-variant font-body leading-relaxed">Sampaikan keluhan atau aspirasi Anda. Kami menjamin kerahasiaan data sesuai dengan pilihan privasi Anda.</p>
  </div>

  <!-- Alert Messages -->
  <?php if ($message): ?>
  <div class="mb-6 flex items-center gap-3 p-4 rounded-2xl text-sm font-medium <?= $messageType === 'success' ? 'bg-primary-container/20 text-on-primary-container' : 'bg-error-container text-on-error-container' ?>">
    <span class="material-symbols-outlined"><?= $messageType === 'success' ? 'check_circle' : 'error' ?></span>
    <?= htmlspecialchars($message) ?>
  </div>
  <?php if ($messageType === 'success'): ?>
  <script>setTimeout(() => { window.location = 'riwayat_laporan.php'; }, 2000);</script>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Form -->
  <form method="POST" enctype="multipart/form-data" id="laporanForm" class="space-y-6">
    
    <!-- Basic Info Card -->
    <div class="bg-surface-container-low p-8 rounded-3xl space-y-6 hover:shadow-lg transition-all duration-300">
      
      <!-- Judul -->
      <div class="space-y-2">
        <label class="font-label text-xs font-bold text-primary uppercase tracking-wider block">Judul Laporan</label>
        <div class="relative">
          <input type="text" name="judul" id="judulInput" required
            placeholder="Apa yang ingin Anda laporkan?"
            class="w-full bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-3 font-body text-on-surface placeholder:text-on-surface-variant/40 transition-all pr-20"
            maxlength="200"/>
          <span class="absolute right-3 top-3.5 text-xs text-on-surface-variant/40 char-count" id="judulCount">0/200</span>
        </div>
      </div>

      <!-- Kategori -->
      <div class="space-y-2">
        <label class="font-label text-xs font-bold text-primary uppercase tracking-wider block">Kategori</label>
        <div class="relative">
          <select name="kategori"
            class="w-full appearance-none bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-3 font-body text-on-surface transition-all">
            <option value="">-- Pilih Kategori --</option>
            <?php while($row = mysqli_fetch_assoc($categories)): ?>
            <option value="<?= $row['id_kategori'] ?>"><?= htmlspecialchars($row['nama_kategori']) ?></option>
            <?php endwhile; ?>
          </select>
          <span class="material-symbols-outlined absolute right-4 top-3.5 text-on-surface-variant pointer-events-none">expand_more</span>
        </div>
      </div>

      <!-- Deskripsi -->
      <div class="space-y-2">
        <label class="font-label text-xs font-bold text-primary uppercase tracking-wider block">Deskripsi Lengkap</label>
        <div class="relative">
          <textarea name="isi" id="isiInput" required rows="6"
            placeholder="Berikan detail mengenai waktu, lokasi, dan kronologi kejadian..."
            class="w-full bg-surface-container-lowest border-none outline outline-1 outline-outline-variant/20 focus:outline-primary focus:ring-0 rounded-xl px-4 py-4 font-body text-on-surface placeholder:text-on-surface-variant/40 transition-all resize-none"
            maxlength="2000"></textarea>
          <span class="absolute right-3 bottom-3 text-xs text-on-surface-variant/40 char-count" id="isiCount">0/2000</span>
        </div>
      </div>
    </div>

    <!-- Upload + Info Bento -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
      <!-- Upload Area -->
      <div class="md:col-span-3 upload-area bg-surface-container-low p-8 rounded-3xl flex flex-col justify-center items-center group cursor-pointer" id="dropZone">
        <div class="w-16 h-16 rounded-full bg-primary-container/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
          <span class="material-symbols-outlined text-primary text-3xl">add_photo_alternate</span>
        </div>
        <h3 class="font-display font-bold text-lg text-on-surface">Unggah Bukti Foto</h3>
        <p class="text-on-surface-variant text-sm text-center mt-2 px-4">Seret berkas ke sini atau klik untuk memilih gambar (Maks. 5MB)</p>
        <input type="file" name="foto[]" id="fileInput" multiple accept="image/*" class="hidden"/>
        <button type="button" onclick="document.getElementById('fileInput').click()"
          class="mt-5 px-6 py-2.5 rounded-full bg-primary text-on-primary font-label text-sm font-bold hover:bg-primary/90 transition-colors">
          Pilih File
        </button>
        <div id="fileList" class="mt-4 w-full space-y-2 text-sm hidden"></div>
      </div>

      <!-- Info Card -->
      <div class="md:col-span-2 bg-primary-container/10 p-8 rounded-3xl flex flex-col justify-between">
        <span class="material-symbols-outlined text-primary text-4xl mb-4">info</span>
        <div>
          <h4 class="font-headline font-bold text-on-primary-container mb-2">Penting</h4>
          <p class="text-on-primary-container/80 text-sm leading-relaxed">
            Pastikan foto yang diunggah jelas dan relevan dengan laporan. Laporan palsu dapat dikenai sanksi sesuai peraturan kampus.
          </p>
        </div>
        <div class="mt-6 space-y-2 text-xs text-on-primary-container/60">
          <div class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">check</span> Format: JPG, PNG, GIF</div>
          <div class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">check</span> Maksimal 5MB per file</div>
          <div class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">check</span> Bisa unggah lebih dari 1</div>
        </div>
      </div>
    </div>

    <!-- Privacy Toggles -->
    <div class="bg-surface-container-low p-8 rounded-3xl space-y-6">
      <!-- Anonim Toggle -->
      <div class="flex items-center justify-between">
        <div class="flex gap-4">
          <div class="w-12 h-12 rounded-xl bg-surface-container-highest flex items-center justify-center">
            <span class="material-symbols-outlined text-on-surface">visibility_off</span>
          </div>
          <div>
            <h4 class="font-headline font-bold text-on-surface">Laporkan secara Anonim</h4>
            <p class="text-on-surface-variant text-xs mt-0.5">Nama Anda tidak akan terlihat oleh publik</p>
          </div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" name="anonim" id="toggleAnonim"/>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="h-px bg-outline-variant/10"></div>
      <!-- Privat Toggle -->
      <div class="flex items-center justify-between">
        <div class="flex gap-4">
          <div class="w-12 h-12 rounded-xl bg-surface-container-highest flex items-center justify-center">
            <span class="material-symbols-outlined text-on-surface">lock</span>
          </div>
          <div>
            <h4 class="font-headline font-bold text-on-surface">Laporan Privat</h4>
            <p class="text-on-surface-variant text-xs mt-0.5">Hanya admin yang dapat melihat detail laporan ini</p>
          </div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" name="privat" id="togglePrivat"/>
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>

    <!-- Submit -->
    <div class="pt-2">
      <button type="submit" name="simpan" id="submitBtn"
        class="w-full py-5 rounded-2xl bg-primary text-on-primary font-headline text-lg font-bold shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center justify-center gap-3">
        Kirim Pengaduan
        <span class="material-symbols-outlined">send</span>
      </button>
      <p class="text-center text-on-surface-variant text-xs mt-4">Dengan menekan tombol kirim, Anda menyetujui Syarat dan Ketentuan layanan kami.</p>
    </div>
  </form>
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
  <div class="flex flex-col items-center justify-center text-primary px-5 py-2">
    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">edit_note</span>
    <span class="font-label text-xs font-bold mt-1">Buat</span>
  </div>
  <a href="../auth/logout.php" class="flex flex-col items-center justify-center text-on-surface-variant/70 px-5 py-2 hover:text-primary transition-colors">
    <span class="material-symbols-outlined">logout</span>
    <span class="font-label text-xs font-medium mt-1">Keluar</span>
  </a>
</nav>

<script>
  // Character counter
  const judulInput = document.getElementById('judulInput');
  const isiInput = document.getElementById('isiInput');
  judulInput?.addEventListener('input', () => {
    document.getElementById('judulCount').textContent = judulInput.value.length + '/200';
  });
  isiInput?.addEventListener('input', () => {
    document.getElementById('isiCount').textContent = isiInput.value.length + '/2000';
  });

  // File upload display
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');
  const dropZone = document.getElementById('dropZone');

  fileInput?.addEventListener('change', showFiles);
  
  dropZone?.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
  });
  dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
  dropZone?.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    fileInput.files = e.dataTransfer.files;
    showFiles();
  });

  function showFiles() {
    const files = fileInput.files;
    if (files.length > 0) {
      fileList.classList.remove('hidden');
      fileList.innerHTML = Array.from(files).map(f => `
        <div class="flex items-center gap-2 p-2 bg-surface-container rounded-lg">
          <span class="material-symbols-outlined text-primary text-sm">image</span>
          <span class="text-on-surface-variant truncate flex-1">${f.name}</span>
          <span class="text-on-surface-variant/50 text-xs whitespace-nowrap">${(f.size/1024/1024).toFixed(1)}MB</span>
        </div>
      `).join('');
    }
  }

  // Submit loading state
  document.getElementById('laporanForm')?.addEventListener('submit', (e) => {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Mengirim...';
    btn.disabled = true;
  });

  // Focus micro-interactions
  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('focus', () => {
      el.closest('.bg-surface-container-low')?.classList.add('ring-1', 'ring-primary/20');
    });
    el.addEventListener('blur', () => {
      el.closest('.bg-surface-container-low')?.classList.remove('ring-1', 'ring-primary/20');
    });
  });
</script>
</body>
</html>
