<?php
// Inisialisasi sesi
session_start();
 
// Unset semua variabel sesi admin
$_SESSION = array();
 
// Hancurkan sesi
session_destroy();
 
// Arahkan ke halaman login admin
header("location: login.php");
exit;
?>