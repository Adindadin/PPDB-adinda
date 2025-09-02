<?php
require_once '../config.php';

$username = $pin = "";
$errors = [];

if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $pin = trim($_POST["pin"]);

    if(empty($username) || empty($pin)){
        $errors['login'] = "Username dan PIN wajib diisi.";
    } else {
        $sql = "SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username_db, $hashed_pin, $nama_lengkap);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($pin, $hashed_pin)) {
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username_db;
                            $_SESSION["admin_nama"] = $nama_lengkap;
                            header("location: dashboard.php");
                        } else {
                            $errors['login'] = "Username atau PIN salah.";
                        }
                    }
                } else {
                    $errors['login'] = "Username atau PIN salah.";
                }
            } else {
                $errors['login'] = "Oops! Terjadi kesalahan.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card shadow-lg auth-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="card-title">Login Administrator</h3>
                            <p class="text-muted">Silakan masuk untuk mengelola sistem.</p>
                        </div>
                        <?php if (!empty($errors['login'])) { echo '<div class="alert alert-danger">' . $errors['login'] . '</div>'; } ?>
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control">
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
                             <a href="../index.php" class="d-block mt-3">&laquo; Kembali ke Halaman Utama</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
