<?php
include '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// Redirect ke aspirasi.php karena fitur update sudah terintegrasi di aspirasi.php
header('Location: aspirasi.php');
exit;
?>
