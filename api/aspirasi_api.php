<?php
// Hubungkan ke file koneksi yang sudah kamu punya
require_once('../config/koneksi.php'); 

header('Content-Type: application/json');

// Query mengambil data dari tabel aspirasi (sesuaikan nama tabelmu)
$sql = "SELECT * FROM aspirasi ORDER BY id_aspirasi DESC";
$query = mysqli_query($koneksi, $sql);

$result = array();

if($query) {
    while($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }
    // Kirim respon sukses
    echo json_encode([
        "status" => "success",
        "data" => $result
    ]);
} else {
    // Kirim respon gagal
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data"
    ]);
}

mysqli_close($koneksi);
?>