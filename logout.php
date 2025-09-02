<?php
// Inisialisasi sesi
session_start();
 
// Unset semua variabel sesi
$_SESSION = array();
 
// Hancurkan sesi
session_destroy();
 
// Arahkan ke halaman login
header("location: login.php");
exit;
?>