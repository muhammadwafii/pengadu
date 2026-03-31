<?php
include '../config/koneksi.php';

// Destroy session
session_destroy();

// Redirect ke login
header('Location: ../index.php');
exit;
?>