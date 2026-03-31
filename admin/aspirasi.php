<?php
include '../config/koneksi.php';

// Pastikan session aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

 $id = isset($_GET['id']) ? $_GET['id'] : null;

// --- LOGIKA DETAIL VIEW ---
if ($id) {
    $query = "SELECT ia.*, k.ket_kategori, a.id_aspirasi, a.status, a.feedback
              FROM input_aspirasi ia
              JOIN kategori k ON ia.id_kategori = k.id_kategori
              LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
              WHERE ia.id_pelaporan = $id";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 0) {
        header('Location: aspirasi.php');
        exit;
    }
    
    $data = mysqli_fetch_assoc($result);
    $id_aspirasi = $data['id_aspirasi'];
    $status = $data['status'] ?? 'Menunggu';
    
    // Fetch feedback history
    $feedback_history = [];
    if ($id_aspirasi) {
        $feedback_query = "SELECT * FROM aspirasi_feedback WHERE id_aspirasi = $id_aspirasi ORDER BY tanggal_feedback DESC";
        $feedback_result = mysqli_query($koneksi, $feedback_query);
        if ($feedback_result) {
            while ($fb = mysqli_fetch_assoc($feedback_result)) {
                $feedback_history[] = $fb;
            }
        }
    }
    
    // Update status dan feedback logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_status = $_POST['status'];
        $feedback = $_POST['feedback'];
        
        if (!$id_aspirasi) {
            $insert_query = "INSERT INTO aspirasi (id_pelaporan, status, feedback, tanggal_update) VALUES ($id, '$new_status', '$feedback', NOW())";
            if (mysqli_query($koneksi, $insert_query)) {
                $id_aspirasi = mysqli_insert_id($koneksi);
                if (!empty($feedback)) {
                    $fb_insert = "INSERT INTO aspirasi_feedback (id_aspirasi, status, feedback) VALUES ($id_aspirasi, '$new_status', '$feedback')";
                    mysqli_query($koneksi, $fb_insert);
                }
            }
        } else {
            $update_query = "UPDATE aspirasi SET status='$new_status', feedback='$feedback', tanggal_update=NOW() WHERE id_aspirasi=$id_aspirasi";
            mysqli_query($koneksi, $update_query);
            
            if (!empty($feedback)) {
                $fb_insert = "INSERT INTO aspirasi_feedback (id_aspirasi, status, feedback) VALUES ($id_aspirasi, '$new_status', '$feedback')";
                mysqli_query($koneksi, $fb_insert);
            }
        }
        
        $success = "Status berhasil diperbarui!";
        // Refresh data terbaru setelah update
        header('Refresh: 1; url=aspirasi.php?id=' . $id);
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detail Aspirasi - Pengaduan Aspirasi</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            /* --- CORE VARIABLES --- */
            :root {
                --glass-bg: rgba(255, 255, 255, 0.75);
                --glass-sidebar: rgba(255, 255, 255, 0.9);
                --glass-border: rgba(255, 255, 255, 0.5);
                --text-main: #2c3e50;
                --text-muted: #7f8c8d;
                --primary: #6c5ce7;
                --sidebar-width: 260px;
                --success: #2ecc71;
                --warning: #f1c40f;
                --info: #3498db;
            }

            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

            body {
                background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
                background-size: 400% 400%;
                animation: gradientBG 15s ease infinite;
                min-height: 100vh;
                color: var(--text-main);
                display: flex;
            }

            @keyframes gradientBG {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            /* --- SIDEBAR (FIXED) --- */
            .sidebar {
                width: var(--sidebar-width);
                background: var(--glass-sidebar);
                backdrop-filter: blur(10px);
                height: 100vh;
                position: fixed;
                left: 0; top: 0;
                padding: 30px 20px;
                border-right: 1px solid var(--glass-border);
                display: flex; flex-direction: column;
                z-index: 100; box-shadow: 5px 0 15px rgba(0,0,0,0.05);
            }
            .sidebar nav {
                flex: 1; /* Mendorong footer ke bawah */
                display: flex;
                flex-direction: column;
            }
            .brand { font-size: 20px; font-weight: 700; color: #444; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
            .brand span { color: var(--primary); }
            .nav-link {
                display: flex; align-items: center; padding: 14px 20px; text-decoration: none;
                color: #555; border-radius: 12px; margin-bottom: 8px; transition: all 0.3s; font-weight: 500;
            }
            .nav-link:hover, .nav-link.active { background: rgba(108, 92, 231, 0.1); color: var(--primary); transform: translateX(5px); }
            
            .sidebar-footer { margin-top: auto; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px; }
            .nav-link.logout {
                background: rgba(255, 235, 238, 0.6);
                color: #c0392b; border: 1px solid rgba(231, 76, 60, 0.2); margin-bottom: 0;
            }
            .nav-link.logout:hover {
                background: #e74c3c; color: white; transform: none !important;
                box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
            }

            /* --- MAIN CONTENT --- */
            .main-content {
                margin-left: var(--sidebar-width); flex: 1; padding: 40px;
                width: calc(100% - var(--sidebar-width));
            }
            .header { margin-bottom: 30px; }
            .btn-back {
                display: inline-flex; align-items: center; padding: 10px 20px;
                background: rgba(255,255,255,0.8); color: #555; border-radius: 10px;
                text-decoration: none; font-size: 14px; margin-bottom: 20px;
                transition: 0.3s; border: 1px solid rgba(255,255,255,0.5);
            }
            .btn-back:hover { background: #fff; transform: translateX(-5px); color: var(--primary); }

            /* --- CARDS --- */
            .glass-card {
                background: var(--glass-bg); backdrop-filter: blur(10px);
                border-radius: 20px; border: 1px solid var(--glass-border);
                padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            }
            .card-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; }

            /* --- FORMS --- */
            .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #555; }
            .form-control, .form-select {
                width: 100%; padding: 12px 15px; border: 2px solid transparent;
                border-radius: 12px; background: rgba(255, 255, 255, 0.6);
                font-size: 14px; transition: 0.3s; font-family: inherit; color: #333;
            }
            .form-control:focus, .form-select:focus { outline: none; background: #fff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1); }
            textarea.form-control { resize: vertical; min-height: 120px; }
            .btn-submit {
                width: 100%; padding: 14px; border: none; border-radius: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; font-weight: 600; cursor: pointer; transition: 0.3s;
            }
            .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }

            /* --- TIMELINE --- */
            .timeline { position: relative; padding-left: 30px; border-left: 2px solid #ddd; margin-top: 15px; }
            .timeline-item { position: relative; margin-bottom: 25px; }
            .timeline-dot {
                position: absolute; left: -37px; top: 0; width: 16px; height: 16px;
                border-radius: 50%; border: 3px solid #fff; background: #ccc; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .timeline-content {
                background: rgba(255,255,255,0.5); padding: 15px; border-radius: 10px;
                border: 1px solid rgba(255,255,255,0.6);
            }
            .timeline-date { font-size: 11px; color: #888; margin-bottom: 5px; display: block; }
            .timeline-status { 
                display: inline-block; padding: 4px 10px; border-radius: 20px; 
                font-size: 11px; font-weight: 600; color: white; margin-bottom: 8px; 
            }

            /* --- ALERTS --- */
            .alert-success { background: rgba(46, 204, 113, 0.15); color: #27ae60; padding: 15px; border-radius: 10px; border: 1px solid #2ecc71; margin-bottom: 20px; }

            /* Grid Layout */
            .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
            @media(max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; width: 100%; padding: 20px; } }

        </style>
    </head>
    <body>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">🏫 <span>Aspirasi</span>Sekolah</div>
            <nav>
                <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
                <a href="aspirasi.php" class="nav-link active">📋 Daftar Aspirasi</a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-link logout">🚪 Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if (isset($success)): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <a href="aspirasi.php" class="btn-back">← Kembali ke Daftar</a>

            <h2 style="margin-bottom: 25px; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Detail Aspirasi #<?php echo $data['id_pelaporan']; ?></h2>

            <div class="detail-grid">
                <!-- KOLOM KIRI: INFORMASI -->
                <div>
                    <!-- Info Siswa -->
                    <div class="glass-card">
                        <div class="card-title">👤 Informasi Pelapor</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <span class="form-label">NIS</span>
                                <div style="font-size: 16px; font-weight: 500;"><?php echo $data['nis']; ?></div>
                            </div>
                            <div>
                                <span class="form-label">Kelas</span>
                                <div style="font-size: 16px; font-weight: 500;"><?php echo $data['kelas']; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Isi Aspirasi -->
                    <div class="glass-card">
                        <div class="card-title">📝 Isi Laporan</div>
                        
                        <div style="margin-bottom: 15px;">
                            <span class="form-label">Kategori</span>
                            <span style="background: #e1f0ff; color: #007bff; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                <?php echo $data['ket_kategori']; ?>
                            </span>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <span class="form-label">Lokasi Kejadian</span>
                            <p style="background: rgba(255,255,255,0.5); padding: 10px; border-radius: 8px;"><?php echo $data['lokasi']; ?></p>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <span class="form-label">Keterangan Lengkap</span>
                            <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 8px; line-height: 1.6; color: #444;">
                                <?php echo nl2br($data['ket']); ?>
                            </div>
                        </div>

                        <div style="font-size: 12px; color: #888;">
                            <i>🕒 Dilaporkan pada: <?php echo date('d/m/Y H:i', strtotime($data['tanggal_lapor'])); ?></i>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: AKSI & HISTORY -->
                <div>
                    <!-- Form Update -->
                    <div class="glass-card" style="position: sticky; top: 20px;">
                        <div class="card-title">⚙️ Update Tindakan</div>
                        <form method="POST">
                            <div style="margin-bottom: 15px;">
                                <label class="form-label">Status Saat Ini</label>
                                <select name="status" class="form-select" required>
                                    <option value="Menunggu" <?php echo ($status == 'Menunggu') ? 'selected' : ''; ?>>⏳ Menunggu</option>
                                    <option value="Proses" <?php echo ($status == 'Proses') ? 'selected' : ''; ?>>🔄 Sedang Proses</option>
                                    <option value="Selesai" <?php echo ($status == 'Selesai') ? 'selected' : ''; ?>>✅ Selesai</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label class="form-label">Feedback / Tanggapan</label>
                                <textarea name="feedback" class="form-control" placeholder="Tulis tanggapan admin di sini..."><?php echo $data['feedback'] ?? ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
                        </form>
                    </div>

                    <!-- History Timeline -->
                    <div class="glass-card">
                        <div class="card-title">🕒 Riwayat Proses (<?php echo count($feedback_history); ?>)</div>
                        <?php if (!empty($feedback_history)): ?>
                            <div class="timeline">
                                <?php foreach ($feedback_history as $fb): ?>
                                    <?php 
                                        $color = '#ccc';
                                        if($fb['status'] == 'Proses') $color = 'var(--info)';
                                        if($fb['status'] == 'Selesai') $color = 'var(--success)';
                                    ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot" style="background: <?php echo $color; ?>;"></div>
                                        <div class="timeline-content">
                                            <span class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($fb['tanggal_feedback'])); ?></span>
                                            <span class="timeline-status" style="background: <?php echo $color; ?>;">
                                                <?php echo $fb['status']; ?>
                                            </span>
                                            <p style="font-size: 13px; margin-top: 5px; color: #333;">
                                                <?php echo nl2br(htmlspecialchars($fb['feedback'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="padding: 20px; text-align: center; color: #999;">
                                <p>⏳ Belum ada feedback. Berikan feedback pertama Anda di atas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- LOGIKA LIST VIEW ---
 $filter_nis = isset($_GET['filter_nis']) ? $_GET['filter_nis'] : '';
 $filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';
 $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// --- UPDATE FILTER VARIABLES ---
 $filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
 $filter_bulan = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : '';
 $filter_tahun = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : '';

 $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $per_page = 10;

 $where = "1=1";
if ($filter_nis) $where .= " AND ia.nis LIKE '%$filter_nis%'";
if ($filter_kategori) $where .= " AND ia.id_kategori = '$filter_kategori'";
if ($filter_status) $where .= " AND a.status = '$filter_status'";

// --- LOGIC FILTER TANGGAL ---
// Prioritas: Tanggal > Bulan > Tahun
if ($filter_tanggal) {
    // Filter Tepat Tanggal (YYYY-MM-DD)
    $where .= " AND DATE(ia.tanggal_lapor) = '$filter_tanggal'";
} elseif ($filter_bulan) {
    // Filter Per Bulan (YYYY-MM)
    $where .= " AND DATE_FORMAT(ia.tanggal_lapor, '%Y-%m') = '$filter_bulan'";
} elseif ($filter_tahun) {
    // Filter Per Tahun (YYYY)
    $where .= " AND YEAR(ia.tanggal_lapor) = '$filter_tahun'";
}

// Hitung total & Pagination Logic
 $count_query = "SELECT COUNT(*) as total FROM input_aspirasi ia
                JOIN kategori k ON ia.id_kategori = k.id_kategori
                LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
                WHERE $where";
 $count_result = mysqli_query($koneksi, $count_query);
 $total_rows = mysqli_fetch_assoc($count_result)['total'];
 $total_pages = ceil($total_rows / $per_page);

if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
 $offset = ($page - 1) * $per_page;

 $query = "SELECT ia.*, k.ket_kategori, a.id_aspirasi, a.status
          FROM input_aspirasi ia
          JOIN kategori k ON ia.id_kategori = k.id_kategori
          LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
          WHERE $where
          ORDER BY ia.tanggal_lapor DESC
          LIMIT $offset, $per_page";
 $result = mysqli_query($koneksi, $query);
 $kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY ket_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Aspirasi - Pengaduan Aspirasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- CORE VARIABLES --- */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-sidebar: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #2c3e50;
            --primary: #6c5ce7;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Sidebar & Layout */
        .sidebar { width: var(--sidebar-width); background: var(--glass-sidebar); backdrop-filter: blur(10px); height: 100vh; position: fixed; left: 0; top: 0; padding: 30px 20px; border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; z-index: 100; }
        .sidebar nav { flex: 1; display: flex; flex-direction: column; } /* Updated */
        
        .brand { font-size: 20px; font-weight: 700; color: #444; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .brand span { color: var(--primary); }
        
        .nav-link { display: flex; align-items: center; padding: 14px 20px; text-decoration: none; color: #555; border-radius: 12px; margin-bottom: 8px; transition: all 0.3s; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: rgba(108, 92, 231, 0.1); color: var(--primary); transform: translateX(5px); }
        
        .sidebar-footer { margin-top: auto; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px; }
        .nav-link.logout {
            background: rgba(255, 235, 238, 0.6); color: #c0392b; border: 1px solid rgba(231, 76, 60, 0.2); margin-bottom: 0;
        }
        .nav-link.logout:hover {
            background: #e74c3c; color: white; transform: none !important;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; width: calc(100% - var(--sidebar-width)); }
        
        /* Filter Section */
        .glass-card { background: var(--glass-bg); backdrop-filter: blur(10px); border-radius: 20px; border: 1px solid var(--glass-border); padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: end; }
        .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #555; }
        .form-control, .form-select { width: 100%; padding: 12px 15px; border: 2px solid transparent; border-radius: 12px; background: rgba(255, 255, 255, 0.6); font-size: 14px; transition: 0.3s; font-family: inherit; color: #333; }
        .form-control:focus, .form-select:focus { outline: none; background: #fff; border-color: var(--primary); }

        /* Buttons */
        .btn { padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 500; transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4); }
        .btn-secondary { background: rgba(0,0,0,0.05); color: #555; border: 1px solid rgba(0,0,0,0.1); }
        .btn-secondary:hover { background: #fff; }

        /* Table */
        .table-container { overflow-x: auto; border-radius: 15px; }
        .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .modern-table th { background: rgba(255,255,255,0.4); padding: 15px; text-align: left; font-weight: 600; font-size: 13px; color: #444; }
        .modern-table td { padding: 15px; background: rgba(255,255,255,0.2); border-bottom: 1px solid rgba(255,255,255,0.3); color: #333; font-size: 14px; }
        .modern-table tr:hover td { background: rgba(255,255,255,0.6); }
        
        /* Badges */
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; color: white; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-menunggu { background: #f39c12; }
        .badge-proses { background: #3498db; }
        .badge-selesai { background: #2ecc71; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .page-link { padding: 8px 15px; border-radius: 8px; background: rgba(255,255,255,0.5); text-decoration: none; color: #555; font-size: 13px; transition: 0.2s; }
        .page-link:hover, .page-link.active { background: var(--primary); color: white; }

        @media(max-width: 768px) { .sidebar { width: 100%; height: auto; position: relative; padding: 15px; } .main-content { margin-left: 0; width: 100%; padding: 20px; } }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">🏫 <span>Aspirasi</span>Sekolah</div>
        <nav>
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="aspirasi.php" class="nav-link active">📋 Daftar Aspirasi</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link logout">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 style="margin-bottom: 25px; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">📋 Data Aspirasi</h2>

        <!-- Filter Panel -->
        <div class="glass-card">
            <form method="GET" class="filter-grid">
                <div>
                    <label class="form-label">Cari NIS</label>
                    <input type="text" name="filter_nis" class="form-control" placeholder="Masukkan NIS..." value="<?php echo htmlspecialchars($filter_nis); ?>">
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="filter_kategori" class="form-select">
                        <option value="">Semua</option>
                        <?php 
                        // Reset pointer data
                        $kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY ket_kategori");
                        while($row = mysqli_fetch_assoc($kategori_list)) { 
                            $sel = ($filter_kategori == $row['id_kategori']) ? 'selected' : '';
                            echo "<option value='{$row['id_kategori']}' $sel>{$row['ket_kategori']}</option>";
                        } 
                        ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="filter_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" <?php echo ($filter_status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Proses" <?php echo ($filter_status == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                        <option value="Selesai" <?php echo ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                
                <!-- --- FILTER TANGGAL BARU --- -->
                <div>
                    <label class="form-label">Tanggal (Hari)</label>
                    <input type="date" name="filter_tanggal" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                </div>
                <div>
                    <label class="form-label">Bulan</label>
                    <input type="month" name="filter_bulan" class="form-control" value="<?php echo htmlspecialchars($filter_bulan); ?>">
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <input type="number" name="filter_tahun" class="form-control" placeholder="2023" min="2000" max="2099" value="<?php echo htmlspecialchars($filter_tahun); ?>">
                </div>
                <!-- -------------------------- -->

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">🔍 Cari</button>
                    <a href="aspirasi.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status = $row['status'] ?? 'Menunggu';
                                $badgeClass = 'badge-' . strtolower($status);
                                echo "
                                <tr>
                                    <td>$no</td>
                                    <td>" . date('d/m/y', strtotime($row['tanggal_lapor'])) . "</td>
                                    <td>{$row['nis']}</td>
                                    <td>{$row['kelas']}</td>
                                    <td>{$row['ket_kategori']}</td>
                                    <td>" . substr($row['lokasi'], 0, 20) . "...</td>
                                    <td><span class='badge $badgeClass'>$status</span></td>
                                    <td>
                                        <a href='aspirasi.php?id={$row['id_pelaporan']}' class='btn btn-primary' style='padding: 5px 15px; font-size: 12px;'>Detail</a>
                                    </td>
                                </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='8' style='text-align:center; padding: 30px;'>Tidak ada data ditemukan</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $qs = '';
            if($filter_nis) $qs .= "&filter_nis=".urlencode($filter_nis);
            if($filter_kategori) $qs .= "&filter_kategori=".urlencode($filter_kategori);
            if($filter_status) $qs .= "&filter_status=".urlencode($filter_status);
            // Tambahkan filter tanggal baru ke query string
            if($filter_tanggal) $qs .= "&filter_tanggal=".urlencode($filter_tanggal);
            if($filter_bulan) $qs .= "&filter_bulan=".urlencode($filter_bulan);
            if($filter_tahun) $qs .= "&filter_tahun=".urlencode($filter_tahun);
            
            if($page > 1) echo "<a href='?page=".($page-1).$qs."' class='page-link'>← Prev</a>";
            for($i=1; $i<=$total_pages; $i++){
                $active = ($i == $page) ? 'active' : '';
                echo "<a href='?page=$i$qs' class='page-link $active'>$i</a>";
            }
            if($page < $total_pages) echo "<a href='?page=".($page+1).$qs."' class='page-link'>Next →</a>";
            ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>