<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_POST['simpan'])){

    $id_user = $_SESSION['id'];
    $kategori = $_POST['kategori'];
    $judul = $_POST['judul'];
    $isi = $_POST['isi'];

    $anonim = isset($_POST['anonim'])
        ? 'Ya'
        : 'Tidak';

    $privat = isset($_POST['privat'])
        ? 'Ya'
        : 'Tidak';

    mysqli_query($conn,"
    INSERT INTO laporan
    (
        id_user,
        id_kategori,
        judul,
        isi_laporan,
        anonim,
        privat
    )
    VALUES
    (
        '$id_user',
        '$kategori',
        '$judul',
        '$isi',
        '$anonim',
        '$privat'
    )
    ");

    echo "
    <script>
    alert('Laporan berhasil dikirim');
    window.location='riwayat_laporan.php';
    </script>";
}
?>

<h2>Buat Laporan</h2>

<form method="POST">

Kategori

<select name="kategori">

<?php

$data = mysqli_query($conn,
"SELECT * FROM kategori");

while($row = mysqli_fetch_assoc($data)){

?>

<option
value="<?= $row['id_kategori']; ?>">

<?= $row['nama_kategori']; ?>

</option>

<?php } ?>

</select>

<br><br>

Judul

<br>

<input type="text"
name="judul"
required>

<br><br>

Isi Laporan

<br>

<textarea
name="isi"
rows="5"
required></textarea>

<br><br>

<label>

<input
type="checkbox"
name="anonim">

Laporan Anonim

</label>

<br><br>

<label>

<input
type="checkbox"
name="privat">

Laporan Privat

</label>

<br><br>

<button
type="submit"
name="simpan">

Kirim

</button>

</form>