<?php
session_start();

function getKoneksi() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "pengadu";
    
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    
    if (!$koneksi) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($koneksi, "utf8mb4");
    return $koneksi;
}

$koneksi = getKoneksi();
?>
