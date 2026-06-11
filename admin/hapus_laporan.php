<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: laporan.php");
    exit;
}

$id_laporan = (int)$_GET['id'];

// Ambil foto untuk dihapus
$fotos = mysqli_query($conn, "SELECT path_file FROM foto_laporan WHERE id_laporan = $id_laporan");
while($f = mysqli_fetch_assoc($fotos)){
    $file_path = __DIR__ . '/../' . $f['path_file'];
    if(file_exists($file_path)){
        unlink($file_path);
    }
}

// Hapus laporan (cascade delete akan menghapus foto, tanggapan, arsip)
$stmt = mysqli_prepare($conn, "DELETE FROM laporan WHERE id_laporan = ?");
mysqli_stmt_bind_param($stmt, 'i', $id_laporan);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: laporan.php");
exit;
?>
