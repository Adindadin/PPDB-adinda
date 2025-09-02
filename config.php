<?php
/*
 * File Konfigurasi Database
 *
 * Harap sesuaikan pengaturan di bawah ini dengan konfigurasi server database Anda.
 */

// Pengaturan koneksi database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ganti dengan username database Anda
define('DB_PASSWORD', ''); // Ganti dengan password database Anda
define('DB_DATABASE', 'ppdb2');

// Membuat koneksi
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Memeriksa koneksi
if ($conn === false) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error.
    // Sebaiknya jangan tampilkan error detail di lingkungan produksi.
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Memulai session
// Session digunakan untuk menyimpan status login pengguna di seluruh halaman.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
