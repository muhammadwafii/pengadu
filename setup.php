<?php
include 'config/koneksi.php';

$success = false;
$messages = array();

// Buat tabel admin
$sql1 = "CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($koneksi, $sql1)) {
    $messages[] = "✅ Tabel 'admin' berhasil dibuat";
} else {
    $messages[] = "❌ Error tabel 'admin': " . mysqli_error($koneksi);
}

// Insert data admin
$sql_admin = "INSERT IGNORE INTO `admin` VALUES ('admin', '12345')";
if (mysqli_query($koneksi, $sql_admin)) {
    $messages[] = "✅ Data admin berhasil ditambahkan";
} else {
    $messages[] = "ℹ️ Data admin sudah ada atau berhasil ditambahkan";
}

// Buat tabel siswa
$sql2 = "CREATE TABLE IF NOT EXISTS `siswa` (
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`nis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($koneksi, $sql2)) {
    $messages[] = "✅ Tabel 'siswa' berhasil dibuat";
} else {
    $messages[] = "❌ Error tabel 'siswa': " . mysqli_error($koneksi);
}

$sql3 = "CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `ket_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($koneksi, $sql3)) {
    $messages[] = "✅ Tabel 'kategori' berhasil dibuat";
} else {
    $messages[] = "❌ Error tabel 'kategori': " . mysqli_error($koneksi);
}

$sql_kategori = "INSERT IGNORE INTO `kategori` VALUES 
(1, 'Keamanan Sekolah'),
(2, 'Fasilitas Sekolah'),
(3, 'Pengajaran'),
(4, 'Kepemimpinan')";
if (mysqli_query($koneksi, $sql_kategori)) {
    $messages[] = "✅ Data kategori berhasil ditambahkan";
} else {
    $messages[] = "ℹ️ Data kategori sudah ada atau berhasil ditambahkan";
}

// Buat tabel input_aspirasi
$sql4 = "CREATE TABLE IF NOT EXISTS `input_aspirasi` (
  `id_pelaporan` int NOT NULL AUTO_INCREMENT,
  `nis` varchar(20) NOT NULL,
  `id_kategori` int NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `ket` text NOT NULL,
  `tanggal_lapor` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pelaporan`),
  FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`),
  FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($koneksi, $sql4)) {
    $messages[] = "✅ Tabel 'input_aspirasi' berhasil dibuat";
} else {
    $messages[] = "❌ Error tabel 'input_aspirasi': " . mysqli_error($koneksi);
}

// Buat tabel aspirasi
$sql5 = "CREATE TABLE IF NOT EXISTS `aspirasi` (
  `id_aspirasi` int NOT NULL AUTO_INCREMENT,
  `id_pelaporan` int NOT NULL,
  `status` enum('Menunggu','Proses','Selesai') NOT NULL DEFAULT 'Menunggu',
  `feedback` text,
  `tanggal_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_aspirasi`),
  FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($koneksi, $sql5)) {
    $messages[] = "✅ Tabel 'aspirasi' berhasil dibuat";
    $success = true;
} else {
    $messages[] = "❌ Error tabel 'aspirasi': " . mysqli_error($koneksi);
}

mysqli_close($koneksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Pengaduan Aspirasi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        .setup-title {
            text-align: center;
            margin-bottom: 30px;
            color: #667eea;
        }
        .setup-title h1 {
            font-size: 28px;
            font-weight: bold;
        }
        .message-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message-item {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .message-item:last-child {
            border-bottom: none;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success-box h5 {
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-title">
            <h1>🏫 Setup Database</h1>
            <p>Sistem Pengaduan Aspirasi Sekolah</p>
        </div>

        <?php if ($success): ?>
            <div class="success-box">
                <h5>✅ Setup Berhasil!</h5>
                <p class="mb-0">Semua tabel database telah dibuat dengan sukses.</p>
            </div>
        <?php endif; ?>

        <div class="message-list">
            <?php
            foreach ($messages as $msg) {
                echo "<div class='message-item'>" . $msg . "</div>";
            }
            ?>
        </div>

        <div class="text-center">
            <?php if ($success): ?>
                <a href="index.php" class="btn btn-primary btn-lg">🚀 Lanjut ke Login</a>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" onclick="location.reload()">🔄 Ulangi</button>
            <?php endif; ?>
        </div>

        <hr>
        <div class="text-center text-muted small">
            <h6>📋 Data Default Login:</h6>
            <p>
                <strong>Admin:</strong><br>
                Username: admin<br>
                Password: 12345
            </p>
            <p>
                <strong>Siswa:</strong><br>
                NIS: 001 / 002 / 003<br>
                Password: sama dengan NIS
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>