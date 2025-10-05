<?php
session_start(); // Mulai session

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan kembali ke halaman utama
header("location: index.php");
exit;
?>