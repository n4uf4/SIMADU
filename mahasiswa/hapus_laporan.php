<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: riwayat_laporan.php");
    exit;
}

$id_laporan = (int)$_GET['id'];
$id_user = $_SESSION['id'];

// Validasi laporan milik user
$cek = mysqli_prepare($conn, "SELECT id_laporan FROM laporan WHERE id_laporan = ? AND id_user = ? LIMIT 1");
mysqli_stmt_bind_param($cek, 'ii', $id_laporan, $id_user);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if(mysqli_stmt_num_rows($cek) == 0){
    mysqli_stmt_close($cek);
    header("Location: riwayat_laporan.php");
    exit;
}
mysqli_stmt_close($cek);

// Hapus foto-foto terkait
$fotos = mysqli_query($conn, "SELECT path_file FROM foto_laporan WHERE id_laporan = $id_laporan");
while($f = mysqli_fetch_assoc($fotos)){
    $file_path = __DIR__ . '/../' . $f['path_file'];
    if(file_exists($file_path)){
        unlink($file_path);
    }
}

// Hapus laporan dan data terkait (cascade delete dari database)
$stmt = mysqli_prepare($conn, "DELETE FROM laporan WHERE id_laporan = ? AND id_user = ?");
mysqli_stmt_bind_param($stmt, 'ii', $id_laporan, $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: riwayat_laporan.php");
exit;
?>
