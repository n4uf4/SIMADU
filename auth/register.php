<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if (isset($_SESSION['id'])) {
    $target = ($_SESSION['role'] ?? 'mahasiswa') === 'admin'
        ? '../admin/dashboard.php'
        : '../mahasiswa/dashboard.php';

    header("Location: {$target}");
    exit;
}

if (isset($_POST['register'])) {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nim === '' || $nama === '' || $no_hp === '' || $password === '' || $konfirmasi === '') {
        echo "<script>alert('Semua field wajib diisi');</script>";
    } elseif ($password !== $konfirmasi) {
        echo "<script>alert('Konfirmasi password tidak sama!');</script>";
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE nim = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, 's', $nim);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            mysqli_stmt_close($cek);
            echo "<script>alert('NIM sudah terdaftar!');</script>";
        } else {
            mysqli_stmt_close($cek);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'mahasiswa';

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nim, nama_lengkap, no_hp, password, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $nim, $nama, $no_hp, $hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                echo "<script>
                alert('Registrasi berhasil');
                window.location='login.php';
                </script>";
                exit;
            }

            mysqli_stmt_close($stmt);
            echo "<script>alert('Registrasi gagal, silakan coba lagi');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Registrasi Mahasiswa</h2>

<form method="POST">
    <input type="text" name="nim" placeholder="NIM" required><br><br>
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
    <input type="text" name="no_hp" placeholder="No HP" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <input type="password" name="konfirmasi" placeholder="Konfirmasi Password" required><br><br>
    <button type="submit" name="register">Daftar</button>
</form>

<p>
Sudah punya akun?
<a href="login.php">Login</a>
</p>

</body>
</html>