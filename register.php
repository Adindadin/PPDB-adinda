<?php
require_once 'config.php';

$email = $password = $confirm_password = $nama_lengkap = $nik_user = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi Email
    if (empty(trim($_POST["email"]))) {
        $errors['email'] = "Email tidak boleh kosong.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $errors['email'] = "Email ini sudah terdaftar.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validasi Nama Lengkap
    if (empty(trim($_POST["nama_lengkap"]))) {
        $errors['nama_lengkap'] = "Nama lengkap tidak boleh kosong.";
    } else {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
    }

    // Validasi NIK
    if (empty(trim($_POST["nik_user"]))) {
        $errors['nik_user'] = "NIK tidak boleh kosong.";
    } elseif (!is_numeric($_POST["nik_user"]) || strlen(trim($_POST["nik_user"])) != 16) {
        $errors['nik_user'] = "NIK harus terdiri dari 16 digit angka.";
    } else {
        $sql = "SELECT id FROM users WHERE nik_user = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_nik);
            $param_nik = trim($_POST["nik_user"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $errors['nik_user'] = "NIK ini sudah terdaftar.";
                } else {
                    $nik_user = trim($_POST["nik_user"]);
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validasi Password
    if (empty(trim($_POST["password"]))) {
        $errors['password'] = "PIN tidak boleh kosong.";
    } elseif (strlen(trim($_POST["password"])) != 6 || !is_numeric($_POST["password"])) {
        $errors['password'] = "PIN harus terdiri dari 6 digit angka.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validasi Konfirmasi Password
    if (empty(trim($_POST["confirm_password"]))) {
        $errors['confirm_password'] = "Harap konfirmasi PIN.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($errors['password']) && ($password != $confirm_password)) {
            $errors['confirm_password'] = "PIN tidak cocok.";
        }
    }

    // Jika tidak ada error, masukkan data ke database
    if (empty($errors)) {
        $sql = "INSERT INTO users (email, password, nama_lengkap, nik_user) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_email, $param_password, $param_nama, $param_nik);
            
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Enkripsi password
            $param_nama = $nama_lengkap;
            $param_nik = $nik_user;
            
            if (mysqli_stmt_execute($stmt)) {
                // Redirect ke halaman login
                header("location: login.php?status=registered");
            } else {
                echo "Terjadi kesalahan. Gagal mendaftar.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Akun PPDB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-9">
                <div class="card shadow-lg auth-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="card-title">Buat Akun Baru</h3>
                            <p class="text-muted">Satu langkah lagi untuk menjadi calon siswa.</p>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control <?php echo (!empty($errors['nama_lengkap'])) ? 'is-invalid' : ''; ?>" value="">
                                <div class="invalid-feedback"><?php echo $errors['nama_lengkap'] ?? ''; ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIK (Nomor Induk Kependudukan)</label>
                                <input type="text" name="nik_user" class="form-control <?php echo (!empty($errors['nik_user'])) ? 'is-invalid' : ''; ?>" value="">
                                <div class="invalid-feedback"><?php echo $errors['nik_user'] ?? ''; ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($errors['email'])) ? 'is-invalid' : ''; ?>" value="">
                                <div class="invalid-feedback"><?php echo $errors['email'] ?? ''; ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($errors['password'])) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $errors['password'] ?? ''; ?></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($errors['confirm_password'])) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success">Daftar Akun</button>
                            </div>
                            <p class="text-center text-muted">Sudah punya akun? <a href="login.php">Login di sini</a>.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
