<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id'];

$total = mysqli_num_rows(
    mysqli_query($conn,
    "SELECT * FROM laporan WHERE id_user='$id_user'")
);

$menunggu = mysqli_num_rows(
    mysqli_query($conn,
    "SELECT * FROM laporan
     WHERE id_user='$id_user'
     AND status='Menunggu'")
);

$diproses = mysqli_num_rows(
    mysqli_query($conn,
    "SELECT * FROM laporan
     WHERE id_user='$id_user'
     AND status='Diproses'")
);

$selesai = mysqli_num_rows(
    mysqli_query($conn,
    "SELECT * FROM laporan
     WHERE id_user='$id_user'
     AND status='Selesai'")
);
?>

<h2>Dashboard Mahasiswa</h2>

<p>Selamat datang <?= $_SESSION['nama']; ?></p>

<hr>

<p>Total Laporan : <?= $total ?></p>
<p>Menunggu : <?= $menunggu ?></p>
<p>Diproses : <?= $diproses ?></p>
<p>Selesai : <?= $selesai ?></p>

<hr>

<a href="tambah_laporan.php">
Tambah Laporan
</a>

<br><br>

<a href="riwayat_laporan.php">
Riwayat Laporan
</a>

<br><br>

<a href="../auth/logout.php">
Logout
</a>