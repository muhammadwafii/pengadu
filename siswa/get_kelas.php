<?php
include '../config/koneksi.php';

header('Content-Type: application/json');

$nis = $_GET['nis'] ?? '';

if (empty($nis)) {
    echo json_encode(['success' => false, 'message' => 'NIS tidak boleh kosong']);
    exit;
}

// Query untuk mencari kelas berdasarkan NIS
$query = "SELECT kelas FROM siswa WHERE nis = '$nis' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'kelas' => $row['kelas'],
        'message' => 'Kelas ditemukan'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'NIS tidak ditemukan di database'
    ]);
}

mysqli_close($koneksi);
?>
