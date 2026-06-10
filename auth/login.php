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

if (isset($_POST['login'])) {

    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nim === '' || $password === '') {
        echo "<script>alert('NIM dan password wajib diisi');</script>";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nim, nama_lengkap, password, role FROM users WHERE nim = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $nim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data) {
            $passwordValid = password_verify($password, $data['password']);

            if (!$passwordValid && hash_equals((string) $data['password'], (string) $password)) {
                $passwordValid = true;

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($update, 'si', $hashedPassword, $data['id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            if ($passwordValid) {
                session_regenerate_id(true);

                $_SESSION['id'] = $data['id'];
                $_SESSION['nim'] = $data['nim'];
                $_SESSION['nama'] = $data['nama_lengkap'];
                $_SESSION['role'] = $data['role'] ?? 'mahasiswa';

                $target = $_SESSION['role'] === 'admin'
                    ? '../admin/dashboard.php'
                    : '../mahasiswa/dashboard.php';

                header("Location: {$target}");
                exit;
            }

            echo "<script>alert('Password salah');</script>";
        } else {
            echo "<script>alert('NIM tidak ditemukan');</script>";
        }

    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login Sistem Pengaduan</h2>

<form method="POST">

    <input type="text" name="nim" placeholder="NIM" required><br><br>

    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit" name="login">
        Login
    </button>

</form>

<p>
Belum punya akun?
<a href="register.php">Daftar</a>
</p>

</body>
</html>