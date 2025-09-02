<?php
require_once '../config.php';

// --- PENGATURAN RESET ADMIN ---
$username_to_reset = "admin";
$new_pin = "654321"; 
// --------------------------------

echo "<h1>Mereset PIN Admin...</h1>";

$message = "";
$message_type = "";

// Hash PIN baru
$hashed_new_pin = password_hash($new_pin, PASSWORD_DEFAULT);

// Update PIN di database
$sql_update = "UPDATE admin SET password = ? WHERE username = ?";
if ($stmt = mysqli_prepare($conn, $sql_update)) {
    mysqli_stmt_bind_param($stmt, "ss", $hashed_new_pin, $username_to_reset);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $message = "PIN untuk admin '<strong>" . htmlspecialchars($username_to_reset) . "</strong>' berhasil direset!";
            $message_type = "success";
        } else {
            $message = "Akun admin '<strong>" . htmlspecialchars($username_to_reset) . "</strong>' tidak ditemukan. Pastikan akun admin sudah ada.";
            $message_type = "warning";
        }
    } else {
        $message = "Gagal mereset PIN: " . mysqli_error($conn);
        $message_type = "danger";
    }
    mysqli_stmt_close($stmt);
} else {
    $message = "Gagal menyiapkan statement: " . mysqli_error($conn);
    $message_type = "danger";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset PIN Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger text-center">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> PERHATIAN</h4>
            <p>Ini adalah alat perbaikan dengan akses tinggi. Setelah selesai digunakan, **SEGERA HAPUS** file ini dan folder `tools` dari server Anda untuk menjaga keamanan.</p>
        </div>
        <div class="card">
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                    <?php if ($message_type == 'success'): ?>
                        <p>Sekarang Anda bisa login dengan:</p>
                        <ul>
                            <li>Username: <strong><?php echo htmlspecialchars($username_to_reset); ?></strong></li>
                            <li>PIN: <strong><?php echo htmlspecialchars($new_pin); ?></strong></li>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <p>Silakan akses: <a href="http://localhost/ppdb2/admin/login.php">http://localhost/ppdb2/admin/login.php</a> untuk mencoba login.</p>
            </div>
        </div>
    </div>
</body>
</html>
