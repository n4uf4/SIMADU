<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_POST['id_laporan']) || !isset($_POST['status'])){
    header("Location: laporan.php");
    exit;
}

$id_laporan = (int)$_POST['id_laporan'];
$status = $_POST['status'];

// Validasi status
$allowed_status = ['Menunggu', 'Diproses', 'Selesai'];
if(!in_array($status, $allowed_status)){
    header("Location: detail_laporan.php?id=$id_laporan&error=Status tidak valid");
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE laporan SET status = ? WHERE id_laporan = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $id_laporan);

if(mysqli_stmt_execute($stmt)){
    header("Location: detail_laporan.php?id=$id_laporan&success=1");
} else {
    header("Location: detail_laporan.php?id=$id_laporan&error=Gagal update status");
}
mysqli_stmt_close($stmt);
exit;
?>
