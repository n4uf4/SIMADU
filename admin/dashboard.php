<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../auth/login.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    header("Location: ../mahasiswa/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
</head>
<body>

<h2>Dashboard Admin</h2>

<p>
Selamat datang,
<b><?= $_SESSION['nama']; ?></b>
</p>

<a href="../auth/logout.php">
Logout
</a>

</body>
</html>