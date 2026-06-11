<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_POST['id_laporan'])){
    header("Location: laporan.php");
    exit;
}

$id_laporan = (int)$_POST['id_laporan'];
$tanggapan_text = $_POST['tanggapan'];
$id_admin = $_SESSION['id'];

if(empty(trim($tanggapan_text))){
    header("Location: detail_laporan.php?id=$id_laporan&error=Tanggapan tidak boleh kosong");
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO tanggapan (id_laporan, id_admin, tanggapan) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'iis', $id_laporan, $id_admin, $tanggapan_text);

if(mysqli_stmt_execute($stmt)){
    header("Location: detail_laporan.php?id=$id_laporan&success=1");
} else {
    header("Location: detail_laporan.php?id=$id_laporan&error=Gagal menambah tanggapan");
}
mysqli_stmt_close($stmt);
exit;
?>
