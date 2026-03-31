<?php
include '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$nis = $_SESSION['nis'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Perbaikan - Pengaduan Aspirasi</title>
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
                    <a href="histori.php" class="nav-link">📜 Histori Aspirasi</a>
                    <a href="progres.php" class="nav-link active">📈 Progres Perbaikan</a>
                    <hr>
                    <a href="logout.php" class="nav-link text-danger">🚪 Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <h2 class="page-title">📈 Progres Perbaikan</h2>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📊 Grafik Status Aspirasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <?php
                                    $menunggu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi a JOIN input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan WHERE ia.nis='$nis' AND a.status='Menunggu'"))['total'];
                                    $proses = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi a JOIN input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan WHERE ia.nis='$nis' AND a.status='Proses'"))['total'];
                                    $selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi a JOIN input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan WHERE ia.nis='$nis' AND a.status='Selesai'"))['total'];
                                    $total = $menunggu + $proses + $selesai;
                                    
                                    $persentase_menunggu = ($total > 0) ? round(($menunggu / $total) * 100) : 0;
                                    $persentase_proses = ($total > 0) ? round(($proses / $total) * 100) : 0;
                                    $persentase_selesai = ($total > 0) ? round(($selesai / $total) * 100) : 0;
                                    ?>
                                    <div class="col-md-4">
                                        <div class="stat-card menunggu">
                                            <p>Menunggu</p>
                                            <h3><?php echo $menunggu; ?></h3>
                                            <div class="progress mt-2" style="height: 20px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $persentase_menunggu; ?>%">
                                                    <?php echo $persentase_menunggu; ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card proses">
                                            <p>Sedang Diproses</p>
                                            <h3><?php echo $proses; ?></h3>
                                            <div class="progress mt-2" style="height: 20px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $persentase_proses; ?>%">
                                                    <?php echo $persentase_proses; ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card selesai">
                                            <p>Selesai</p>
                                            <h3><?php echo $selesai; ?></h3>
                                            <div class="progress mt-2" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $persentase_selesai; ?>%">
                                                    <?php echo $persentase_selesai; ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Progres -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📋 Detail Progres Aspirasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kategori</th>
                                                <th>Lokasi</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Update Terakhir</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT ia.*, k.ket_kategori, a.id_aspirasi, a.status, a.tanggal_update
                                                      FROM input_aspirasi ia
                                                      JOIN kategori k ON ia.id_kategori = k.id_kategori
                                                      LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
                                                      WHERE ia.nis = '$nis'
                                                      ORDER BY a.tanggal_update DESC";
                                            $result = mysqli_query($koneksi, $query);
                                            $no = 1;
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status = $row['status'] ?? 'Menunggu';
                                                    $badge_class = 'badge-' . strtolower(str_replace(' ', '-', $status));
                                                    echo "
                                                    <tr>
                                                        <td>$no</td>
                                                        <td>" . $row['ket_kategori'] . "</td>
                                                        <td>" . $row['lokasi'] . "</td>
                                                        <td>" . date('d/m/Y', strtotime($row['tanggal_lapor'])) . "</td>
                                                        <td><span class='badge $badge_class'>$status</span></td>
                                                        <td>" . date('d/m/Y H:i', strtotime($row['tanggal_update'])) . "</td>
                                                        <td>
                                                            <a href='detail.php?id=" . $row['id_pelaporan'] . "' class='btn btn-sm btn-primary'>Detail</a>
                                                        </td>
                                                    </tr>";
                                                    $no++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>Belum ada aspirasi</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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