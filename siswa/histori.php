<?php
include '../config/koneksi.php';

 $nis = '';
 $aspirasi_list = [];
 $error = '';
 $searched = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = $_POST['nis'];
    $searched = true;
    
    if (empty($nis)) {
        $error = 'Masukkan NIS Anda!';
    } else {
        // Query aspirasi berdasarkan NIS
        $query = "SELECT ia.*, k.ket_kategori, a.id_aspirasi, a.status, a.feedback
                  FROM input_aspirasi ia
                  JOIN kategori k ON ia.id_kategori = k.id_kategori
                  LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
                  WHERE ia.nis = '$nis'
                  ORDER BY ia.tanggal_lapor DESC";
        $result = mysqli_query($koneksi, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Fetch feedback history
                $feedback_history = [];
                if ($row['id_aspirasi']) {
                    $fb_query = "SELECT * FROM aspirasi_feedback WHERE id_aspirasi = " . $row['id_aspirasi'] . " ORDER BY tanggal_feedback DESC";
                    $fb_result = mysqli_query($koneksi, $fb_query);
                    if ($fb_result) {
                        while ($fb = mysqli_fetch_assoc($fb_result)) {
                            $feedback_history[] = $fb;
                        }
                    }
                }
                $row['feedback_history'] = $feedback_history;
                $aspirasi_list[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Histori Aspirasi - Pengaduan Aspirasi Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABEL & RESET --- */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
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
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- LAYOUT UTAMA --- */
        .main-container {
            width: 100%;
            max-width: 1200px;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* --- GRID SYSTEM --- */
        .grid-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        @media(min-width: 992px) {
            .grid-layout { grid-template-columns: 350px 1fr; }
        }

        /* --- CARDS & GLASS --- */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        /* --- FORM PENCARIAN (SIDEBAR) --- */
        .search-box { position: sticky; top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        .form-control {
            width: 100%; padding: 14px; border: 2px solid transparent;
            border-radius: 12px; background: rgba(255, 255, 255, 0.6);
            font-size: 14px; transition: 0.3s; outline: none; color: #333;
        }
        .form-control:focus { background: #fff; border-color: #764ba2; box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1); }
        
        .btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            font-weight: 600; cursor: pointer; transition: 0.3s; text-align: center; text-decoration: none; display: inline-block;
        }
        .btn-primary { background: var(--primary-gradient); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6); }
        .btn-secondary { background: rgba(255,255,255,0.5); color: #555; margin-top: 10px; border: 1px solid rgba(0,0,0,0.05); }
        .btn-secondary:hover { background: #fff; color: #333; }

        /* --- LIST ASPIRASI --- */
        .aspirasi-item {
            background: rgba(255,255,255,0.5);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.6);
            border-left: 5px solid #ccc;
            position: relative;
            transition: all 0.3s ease;
        }
        .aspirasi-item:hover { transform: translateY(-3px); background: rgba(255,255,255,0.8); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .aspirasi-item.status-Menunggu { border-left-color: var(--warning); }
        .aspirasi-item.status-Proses { border-left-color: var(--info); }
        .aspirasi-item.status-Selesai { border-left-color: var(--success); }

        .item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; flex-wrap: wrap; gap: 10px; }
        .item-title { font-size: 18px; font-weight: 700; color: #333; }
        .item-meta { font-size: 13px; color: #777; margin-bottom: 8px; display: flex; gap: 15px; }
        .item-desc { font-size: 14px; color: #555; line-height: 1.5; margin-bottom: 15px; background: rgba(255,255,255,0.4); padding: 10px; border-radius: 8px; }

        /* Badges */
        .badge {
            padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: white;
        }
        .badge-Menunggu { background: var(--warning); color: #333; }
        .badge-Proses { background: var(--info); }
        .badge-Selesai { background: var(--success); }

        /* Button Timeline Mini */
        .btn-timeline {
            font-size: 12px; padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd;
            background: white; color: #555; cursor: pointer; transition: 0.2s;
        }
        .btn-timeline:hover { border-color: #667eea; color: #667eea; background: #f8f9fa; }

        /* --- CUSTOM MODAL --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);
            z-index: 2000; display: none; justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; animation: fadeIn 0.3s; }
        
        .modal-content {
            background: white; width: 90%; max-width: 600px; max-height: 85vh;
            border-radius: 20px; overflow-y: auto; padding: 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative;
        }

        .modal-header {
            background: var(--primary-gradient); padding: 20px; color: white;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 10;
        }
        .modal-body { padding: 25px; }
        .close-modal { background: none; border: none; color: white; font-size: 24px; cursor: pointer; opacity: 0.8; }
        .close-modal:hover { opacity: 1; }

        /* Timeline Style */
        .timeline { position: relative; padding-left: 30px; border-left: 2px solid #eee; margin-top: 15px; }
        .timeline-item { position: relative; margin-bottom: 25px; }
        .timeline-dot {
            position: absolute; left: -37px; top: 0; width: 14px; height: 14px;
            border-radius: 50%; border: 3px solid #fff; background: #ccc; box-shadow: 0 0 0 2px #eee;
        }
        .timeline-item.Proses .timeline-dot { background: var(--info); box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2); }
        .timeline-item.Selesai .timeline-dot { background: var(--success); box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2); }

        .timeline-box { background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #eee; }
        .timeline-date { font-size: 11px; color: #999; margin-bottom: 5px; display: block; }

        /* Utilities */
        .text-center { text-align: center; }
        .empty-state { padding: 40px; text-align: center; color: #666; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

    <div class="main-container">
        <!-- Header -->
        <div class="header-section">
            <h2>📋 Histori Aspirasi Siswa</h2>
            <p>Pantau status laporan dan tanggapan sekolah Anda di sini.</p>
        </div>

        <div class="grid-layout">
            
            <!-- KOLOM KIRI: FORM SEARCH -->
            <div class="search-box">
                <div class="glass-card">
                    <h3 style="margin-bottom: 20px; font-size: 18px;">🔍 Cari Aspirasi</h3>
                    
                    <?php if ($error): ?>
                        <div style="background: #ffeaea; color: #d63031; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #ffcccc;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Nomor Induk Siswa (NIS)</label>
                            <input type="text" class="form-control" name="nis" placeholder="Masukkan NIS Anda..." value="<?php echo htmlspecialchars($nis); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Lihat Data</button>
                    </form>

                    <div style="margin-top: 20px; font-size: 12px; color: #666; line-height: 1.5;">
                        <strong>ℹ️ Tips:</strong><br>
                        Pastikan NIS yang dimasukkan sama dengan NIS saat Anda membuat aspirasi pertama kali.
                    </div>

                    <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.1); margin: 20px 0;">

                    <a href="../index.php" class="btn btn-secondary">← Kembali ke Home</a>
                </div>
            </div>

            <!-- KOLOM KANAN: HASIL -->
            <div class="results-area">
                
                <?php if (!$searched): ?>
                    <div class="glass-card text-center" style="padding: 60px 20px;">
                        <div style="font-size: 50px; margin-bottom: 20px;">🔍</div>
                        <h3>Siap Mencari</h3>
                        <p style="color: #777;">Masukkan NIS Anda di formulir sebelah kiri untuk memuat riwayat aspirasi.</p>
                    </div>

                <?php elseif (empty($aspirasi_list)): ?>
                    <div class="glass-card text-center">
                        <div style="font-size: 50px; margin-bottom: 20px;">📭</div>
                        <h3>Data Tidak Ditemukan</h3>
                        <p style="color: #777; margin-bottom: 20px;">Belum ada aspirasi untuk NIS: <strong><?php echo htmlspecialchars($nis); ?></strong></p>
                        <a href="input_aspirasi.php" class="btn btn-primary" style="width: auto; padding: 10px 30px;">Buat Aspirasi Baru</a>
                    </div>

                <?php else: ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 18px;">Hasil Pencarian (<?php echo count($aspirasi_list); ?>)</h3>
                    </div>

                    <?php foreach ($aspirasi_list as $idx => $row): 
                        $status = $row['status'] ?? 'Menunggu';
                    ?>
                        <div class="aspirasi-item status-<?php echo $status; ?>">
                            <div class="item-header">
                                <div class="item-title"><?php echo htmlspecialchars($row['ket_kategori']); ?></div>
                                <span class="badge badge-<?php echo $status; ?>"><?php echo $status; ?></span>
                            </div>
                            
                            <div class="item-meta">
                                <span>📅 <?php echo date('d/m/Y H:i', strtotime($row['tanggal_lapor'])); ?></span>
                                <span>📍 <?php echo htmlspecialchars($row['lokasi']); ?></span>
                            </div>

                            <div class="item-desc">
                                <?php echo nl2br(htmlspecialchars(substr($row['ket'], 0, 200))); ?>...
                            </div>

                            <div style="text-align: right;">
                                <?php if (!empty($row['feedback_history'])): ?>
                                    <button class="btn-timeline" onclick="openModal('modal-<?php echo $idx; ?>')">
                                        🕒 Lihat Timeline Proses (<?php echo count($row['feedback_history']); ?>)
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #999;">⏳ Belum ada feedback...</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- CUSTOM MODAL TIMELINE -->
                        <div id="modal-<?php echo $idx; ?>" class="modal-overlay">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4>🕒 Timeline: <?php echo htmlspecialchars($row['ket_kategori']); ?></h4>
                                    <button class="close-modal" onclick="closeModal('modal-<?php echo $idx; ?>')">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="timeline">
                                        <?php foreach ($row['feedback_history'] as $fb): ?>
                                            <div class="timeline-item <?php echo $fb['status']; ?>">
                                                <div class="timeline-dot"></div>
                                                <div class="timeline-box">
                                                    <span class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($fb['tanggal_feedback'])); ?></span>
                                                    <span class="badge badge-<?php echo $fb['status']; ?>" style="margin-bottom: 8px; display:inline-block;"><?php echo $fb['status']; ?></span>
                                                    <p style="font-size: 14px; color: #333; margin: 0;"><?php echo nl2br(htmlspecialchars($fb['feedback'])); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Fungsi sederhana untuk Modal Custom tanpa Bootstrap
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Tutup modal jika klik di luar area konten
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>