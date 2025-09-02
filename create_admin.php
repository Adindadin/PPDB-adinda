<?php
require_once '../config.php';

// --- PENGATURAN ADMIN DEFAULT ---
$username = "admin";
$pin = "123456"; // Menggunakan PIN, bukan password
$nama_lengkap = "Administrator";
// --------------------------------

echo "<h1>Membuat Akun Admin Default dengan PIN...</h1>";

// Cek apakah admin sudah ada
$sql_check = "SELECT id FROM admin WHERE username = ?";
if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        echo "<p style='color:orange;'>Akun admin dengan username '<strong>" . $username . "</strong>' sudah ada.</p>";
        echo "<p>Jika Anda ingin mereset PIN, hapus admin lama dari database atau gunakan alat reset.</p>";
    } else {
        // Jika belum ada, buat admin baru
        $hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO admin (username, password, nama_lengkap) VALUES (?, ?, ?)"; // Kolom tetap 'password'
        
        if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "sss", $username, $hashed_pin, $nama_lengkap);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                echo "<p style='color:green;'>Akun admin berhasil dibuat!</p>";
                echo "<ul>";
                echo "<li>Username: <strong>" . $username . "</strong></li>";
                echo "<li>PIN: <strong>" . $pin . "</strong></li>";
                echo "</ul>";
                echo "<p style='color:red; font-weight:bold;'>PENTING: Segera hapus file 'create_admin.php' dari server Anda!</p>";
            } else {
                echo "<p style='color:red;'>Gagal membuat akun admin: " . mysqli_error($conn) . "</p>";
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
    mysqli_stmt_close($stmt_check);
}

mysqli_close($conn);
?>
