<?php
require_once "config.php";

// Jika sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}
if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    header("location: admin/dashboard.php");
    exit;
}

$identifier = $pin = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']);
    $pin = trim($_POST['pin']);

    if (empty($identifier) || empty($pin)) {
        $errors['login'] = "NIK/Username dan PIN tidak boleh kosong.";
    } else {
        // Coba login sebagai siswa
        $sql_user = "SELECT id, email, password, nama_lengkap FROM users WHERE nik_user = ?";
        if ($stmt_user = mysqli_prepare($conn, $sql_user)) {
            mysqli_stmt_bind_param($stmt_user, "s", $identifier);
            mysqli_stmt_execute($stmt_user);
            mysqli_stmt_store_result($stmt_user);

            if (mysqli_stmt_num_rows($stmt_user) == 1) {
                mysqli_stmt_bind_result($stmt_user, $id, $email, $hashed_pin, $nama_lengkap);
                if (mysqli_stmt_fetch($stmt_user)) {
                    if (password_verify($pin, $hashed_pin)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;
                        $_SESSION["nama_lengkap"] = $nama_lengkap;
                        header("location: dashboard.php");
                        exit;
                    }
                }
            }
            mysqli_stmt_close($stmt_user);
        }

        // Jika login siswa gagal, coba login sebagai admin
        $sql_admin = "SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?";
        if ($stmt_admin = mysqli_prepare($conn, $sql_admin)) {
            mysqli_stmt_bind_param($stmt_admin, "s", $identifier);
            mysqli_stmt_execute($stmt_admin);
            mysqli_stmt_store_result($stmt_admin);

            if (mysqli_stmt_num_rows($stmt_admin) == 1) {
                mysqli_stmt_bind_result($stmt_admin, $id, $username, $hashed_pin, $nama_lengkap);
                if (mysqli_stmt_fetch($stmt_admin)) {
                    if (password_verify($pin, $hashed_pin)) {
                        $_SESSION["admin_loggedin"] = true;
                        $_SESSION["admin_id"] = $id;
                        $_SESSION["admin_username"] = $username;
                        $_SESSION["admin_nama"] = $nama_lengkap;
                        header("location: admin/dashboard.php");
                        exit;
                    }
                }
            }
            mysqli_stmt_close($stmt_admin);
        }

        // Jika keduanya gagal
        $errors['login'] = "NIK/Username atau PIN yang Anda masukkan salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Terpadu - PPDB Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card shadow-lg auth-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="card-title">Login Sistem PPDB</h3>
                            <p class="text-muted">Silakan login dengan akun Anda.</p>
                        </div>
                        
                        <?php if (!empty($errors['login'])) { echo '<div class="alert alert-danger">' . $errors['login'] . '</div>'; } ?>

                        <form action="login.php" method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label">NIK / Username</label>
                                <input type="text" name="identifier" class="form-control">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">PIN (6 Digit)</label>
                                <input type="password" name="pin" class="form-control" maxlength="6">
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>

                        <div class="text-center">
                            <p class="text-muted">Siswa baru? <a href="register.php">Daftar di sini</a></p>
                            <a href="index.php" class="d-block mt-3">&laquo; Kembali ke Halaman Utama</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
