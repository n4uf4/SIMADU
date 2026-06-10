<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

$id_user = $_SESSION['id'];

$data = mysqli_query($conn,"
SELECT laporan.*,
kategori.nama_kategori

FROM laporan

JOIN kategori
ON laporan.id_kategori=
kategori.id_kategori

WHERE id_user='$id_user'

ORDER BY id_laporan DESC
");
?>

<h2>Riwayat Laporan</h2>

<table border="1" cellpadding="10">

<tr>
<th>No</th>
<th>Judul</th>
<th>Kategori</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php
$no=1;

while($row=mysqli_fetch_assoc($data)){
?>

<tr>

<td><?= $no++ ?></td>

<td><?= $row['judul'] ?></td>

<td><?= $row['nama_kategori'] ?></td>

<td><?= $row['status'] ?></td>

<td>

<a href="#">
Detail</a>

|

<a href="#">
Edit</a>

|

<a href="#">
Hapus</a>

</td>

</tr>

<?php } ?>

</table>