<?php
require_once '../config.php';

$email = $new_password = "";
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $new_password = trim($_POST["new_password"]);

    if (empty($nik) || empty($new_password)) {
        $message = "NIK dan PIN Baru tidak boleh kosong.";
        $message_type = "danger";
    } elseif (strlen($new_password) != 6 || !is_numeric($new_password)) {
        $message = "PIN harus terdiri dari 6 digit angka.";
        $message_type = "danger";
    } else {
        // Cek apakah user ada
        $sql_check = "SELECT id FROM users WHERE email = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) == 1) {
                // User ada, update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET password = ? WHERE email = ?";
                if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                    mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $email);
                    if (mysqli_stmt_execute($stmt_update)) {
                        $message = "Password untuk pengguna <strong>" . htmlspecialchars($email) . "</strong> berhasil direset.";
                        $message_type = "success";
                    } else {
                        $message = "Gagal mereset password.";
                        $message_type = "danger";
                    }
                    mysqli_stmt_close($stmt_update);
                }
            } else {
                $message = "Pengguna dengan email <strong>" . htmlspecialchars($email) . "</strong> tidak ditemukan.";
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Alat Reset Password Siswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="alert alert-danger text-center">
                    <h4><i class="bi bi-exclamation-triangle-fill"></i> PERHATIAN</h4>
                    <p>Ini adalah alat perbaikan dengan akses tinggi. Setelah selesai digunakan, **SEGERA HAPUS** folder `tools` dari server Anda untuk menjaga keamanan.</p>
                </div>
                <div class="card">
                    <div class="card-header"><h3>Reset Password Akun Siswa</h3></div>
                    <div class="card-body">
                        <p>Masukkan email akun siswa yang bermasalah dan tentukan password baru untuk akun tersebut.</p>
                        
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK Akun Siswa</label>
                                <input type="text" name="nik" id="nik" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="text" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
