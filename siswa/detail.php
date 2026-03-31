<?php
include '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header('Location: histori.php');
    exit;
}

$nis = $_SESSION['nis'];

$query = "SELECT ia.*, k.ket_kategori, a.id_aspirasi, a.status, a.feedback, a.tanggal_update
          FROM input_aspirasi ia
          JOIN kategori k ON ia.id_kategori = k.id_kategori
          LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
          WHERE ia.id_pelaporan = $id AND ia.nis = '$nis'";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: histori.php');
    exit;
}

$data = mysqli_fetch_assoc($result);
$status = $data['status'] ?? 'Menunggu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aspirasi - Pengaduan Aspirasi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🏫 Pengaduan Aspirasi</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">👋 <?php echo $_SESSION['nama']; ?> (<?php echo $_SESSION['kelas']; ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="nav flex-column nav-pills mt-3">
                    <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
                    <a href="input_aspirasi.php" class="nav-link">✏️ Input Aspirasi</a>
                    <a href="histori.php" class="nav-link active">📜 Histori Aspirasi</a>
                    <a href="progres.php" class="nav-link">📈 Progres Perbaikan</a>
                    <hr>
                    <a href="logout.php" class="nav-link text-danger">🚪 Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <a href="histori.php" class="btn btn-secondary mb-3">← Kembali</a>

                <h2 class="page-title">📄 Detail Aspirasi</h2>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">📝 Informasi Aspirasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Kategori:</strong><br>
                                        <span class="badge bg-primary"><?php echo $data['ket_kategori']; ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong><br>
                                        <?php
                                        $badge_class = 'badge-' . strtolower(str_replace(' ', '-', $status));
                                        echo "<span class='badge $badge_class'>$status</span>";
                                        ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <strong>Lokasi Kejadian:</strong><br>
                                    <p><?php echo $data['lokasi']; ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong>Keterangan Aspirasi:</strong><br>
                                    <p><?php echo nl2br($data['ket']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong>Tanggal Lapor:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($data['tanggal_lapor'])); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($status === 'Selesai' && $data['feedback']): ?>
                            <div class="card">
                                <div class="card-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                    <h5 class="mb-0">💬 Umpan Balik Admin</h5>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br($data['feedback']); ?></p>
                                    <small class="text-muted">Diperbarui: <?php echo date('d/m/Y H:i', strtotime($data['tanggal_update'])); ?></small>
                                </div>
                            </div>
                        <?php elseif ($status === 'Proses'): ?>
                            <div class="alert alert-info">
                                <strong>⏳ Status Proses</strong><br>
                                Admin sedang memproses aspirasi Anda. Mohon ditunggu untuk perkembangan selanjutnya.
                            </div>
                        <?php elseif ($status === 'Menunggu'): ?>
                            <div class="alert alert-warning">
                                <strong>⏳ Status Menunggu</strong><br>
                                Aspirasi Anda sedang menunggu untuk diproses oleh admin sekolah.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📊 Status Timeline</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item <?php echo ($status === 'Menunggu' || $status === 'Proses' || $status === 'Selesai') ? 'completed' : ''; ?>">
                                        <strong>1️⃣ Laporan Dikirim</strong>
                                        <br><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($data['tanggal_lapor'])); ?></small>
                                    </div>
                                    <div class="timeline-item <?php echo ($status === 'Proses' || $status === 'Selesai') ? 'completed' : ''; ?> <?php echo ($status === 'Proses') ? 'active' : ''; ?>">
                                        <strong>2️⃣ Sedang Diproses</strong>
                                        <br><small class="text-muted">Menunggu proses dari admin</small>
                                    </div>
                                    <div class="timeline-item <?php echo ($status === 'Selesai') ? 'completed active' : ''; ?>">
                                        <strong>3️⃣ Selesai</strong>
                                        <br><small class="text-muted">Menunggu konfirmasi selesai</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">ℹ️ Informasi</h5>
                            </div>
                            <div class="card-body small">
                                <p><strong>Status:</strong></p>
                                <p>
                                    <span class="badge badge-menunggu">Menunggu</span> - Menunggu diproses
                                </p>
                                <p>
                                    <span class="badge badge-proses">Proses</span> - Sedang diproses
                                </p>
                                <p>
                                    <span class="badge badge-selesai">Selesai</span> - Selesai & ada feedback
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Sistem Pengaduan Aspirasi Sekolah. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>