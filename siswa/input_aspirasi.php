<?php
include '../config/koneksi.php';

 $error = '';
 $success = '';
 $id_pelaporan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = $_POST['nis'];
    $kelas = $_POST['kelas'];
    $id_kategori = $_POST['id_kategori'];
    $lokasi = $_POST['lokasi'];
    $ket = $_POST['ket'];
    
    // Validasi input tidak kosong
    if (empty($nis) || empty($kelas) || empty($id_kategori) || empty($lokasi) || empty($ket)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Validasi kelas sudah di-auto fill (artinya NIS valid)
        $check_kelas = "SELECT kelas FROM siswa WHERE nis = '$nis'";
        $check_result = mysqli_query($koneksi, $check_kelas);
        
        // Jika NIS ada di database, validasi kelas match
        if (mysqli_num_rows($check_result) > 0) {
            $db_kelas = mysqli_fetch_assoc($check_result)['kelas'];
            if ($kelas != $db_kelas) {
                $error = 'Kelas tidak sesuai dengan NIS!';
            }
        }
        
        if (empty($error)) {
            // Insert ke input_aspirasi
            $sql = "INSERT INTO input_aspirasi (nis, kelas, id_kategori, lokasi, ket) VALUES ('$nis', '$kelas', '$id_kategori', '$lokasi', '$ket')";
            
            if (mysqli_query($koneksi, $sql)) {
                $id_pelaporan = mysqli_insert_id($koneksi);
                
                // Insert ke aspirasi
                $sql_aspirasi = "INSERT INTO aspirasi (id_pelaporan, status) VALUES ('$id_pelaporan', 'Menunggu')";
                mysqli_query($koneksi, $sql_aspirasi);
                
                $success = '✅ Aspirasi berhasil dikirim! ID Pelaporan: ' . $id_pelaporan;
            } else {
                $error = 'Error: ' . mysqli_error($koneksi);
            }
        }
    }
}

// Get kategori
 $kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY ket_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Aspirasi - Pengaduan Aspirasi Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABEL & RESET --- */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.6);
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: var(--text-main);
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- CONTAINER --- */
        .main-container {
            width: 100%;
            max-width: 1100px;
            margin-top: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .header h2 { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 14px; }

        /* --- LAYOUT GRID --- */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        @media(min-width: 992px) {
            .content-grid { grid-template-columns: 2fr 1fr; }
        }

        /* --- CARDS --- */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        /* --- FORM ELEMENTS --- */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block; margin-bottom: 8px; font-weight: 600;
            font-size: 14px; color: #444;
        }
        .form-control, .form-select {
            width: 100%; padding: 14px 15px; border: 2px solid transparent;
            border-radius: 12px; background: rgba(255, 255, 255, 0.6);
            font-size: 14px; transition: all 0.3s; outline: none; color: #333;
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }
        .form-control[readonly] { background: rgba(0,0,0,0.03); cursor: not-allowed; color: #666; }

        /* Input Group for NIS */
        .input-group-wrapper { position: relative; }
        .input-icon {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            font-size: 18px; pointer-events: none;
        }
        .input-icon.success { color: #2ecc71; }
        .input-icon.error { color: #e74c3c; }

        /* --- BUTTONS --- */
        .btn-group { display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end; }
        .btn {
            padding: 12px 25px; border: none; border-radius: 12px;
            font-weight: 600; cursor: pointer; transition: 0.3s;
            text-decoration: none; font-size: 14px; text-align: center;
        }
        .btn-primary { background: var(--primary-gradient); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6); }
        .btn-secondary { background: rgba(0,0,0,0.05); color: #555; border: 1px solid rgba(0,0,0,0.05); }
        .btn-secondary:hover { background: #fff; color: #333; }

        /* --- INFO BOX --- */
        .info-box { background: rgba(255,255,255,0.5); border-radius: 12px; padding: 15px; margin-top: 15px; border: 1px solid rgba(255,255,255,0.6); }
        .info-title { font-weight: 700; font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .info-list { padding-left: 20px; font-size: 13px; color: #555; line-height: 1.6; }
        .status-badge {
            display: inline-block; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; color: white; margin-bottom: 5px;
        }

        /* --- ALERTS --- */
        .custom-alert {
            padding: 15px; border-radius: 12px; margin-bottom: 20px;
            font-size: 14px; display: flex; align-items: center; gap: 10px;
        }
        .alert-error { background: #ffeaea; color: #c0392b; border: 1px solid #fadbd8; }
        .alert-success-msg { background: #e8f8f5; color: #27ae60; border: 1px solid #d4efdf; }

        /* --- SUCCESS STATE CARD --- */
        .success-card {
            text-align: center; padding: 50px 30px;
            background: rgba(255,255,255,0.9); border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .success-icon { font-size: 60px; margin-bottom: 20px; display: inline-block; animation: bounce 2s infinite; }
        @keyframes bounce { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-20px);} 60% {transform: translateY(-10px);} }

    </style>
</head>
<body>

    <div class="main-container">
        <div class="header">
            <a href="../index.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">← Kembali ke Beranda</a>
            <h2 style="margin-top: 15px;">📝 Formulir Aspirasi</h2>
            <p>Suarakan ide, kritik, dan saran untuk sekolah yang lebih baik.</p>
        </div>

        <?php if ($success): ?>
            <!-- TAMPILKAN JIKA SUKSES -->
            <div class="success-card">
                <div class="success-icon">🎉</div>
                <h2 style="color: var(--text-main); margin-bottom: 10px;">Aspirasi Terkirim!</h2>
                <p style="color: #666; margin-bottom: 20px;">Terima kasih telah berpartisipasi. Aspirasi Anda telah kami terima.</p>
                
                <div class="custom-alert alert-success-msg" style="display: inline-block; width: 100%;">
                    <span>🆔 ID Pelaporan: <strong><?php echo $id_pelaporan; ?></strong></span>
                </div>
                <p style="font-size: 12px; color: #999; margin-bottom: 30px;">Simpan ID ini untuk mengecek status aspirasi Anda.</p>

                <div style="display: flex; flex-direction: column; gap: 10px; max-width: 300px; margin: 0 auto;">
                    <a href="input_aspirasi.php" class="btn btn-primary">📝 Buat Aspirasi Baru</a>
                    <a href="histori.php" class="btn btn-secondary">📋 Cek Riwayat</a>
                </div>
            </div>

        <?php else: ?>

            <div class="content-grid">
                <!-- KOLOM KIRI: FORM -->
                <div class="glass-card">
                    <?php if ($error): ?>
                        <div class="custom-alert alert-error">
                            <span>⚠️</span> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="formInput">
                        <!-- NIS -->
                        <div class="form-group">
                            <label class="form-label">Nomor Induk Siswa (NIS)</label>
                            <div class="input-group-wrapper">
                                <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan NIS Anda..." required>
                                <span id="nisStatus" class="input-icon">-</span>
                            </div>
                            <div id="nisMessage" style="margin-top: 8px; font-size: 13px;"></div>
                        </div>

                        <!-- Kelas (Auto Fill) -->
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Otomatis terisi..." readonly>
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="id_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                // Reset pointer data karena while loop mengambil data
                                $kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY ket_kategori");
                                while ($row = mysqli_fetch_assoc($kategori_query)) {
                                    echo "<option value='" . $row['id_kategori'] . "'>" . $row['ket_kategori'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Lokasi -->
                        <div class="form-group">
                            <label class="form-label">Lokasi Kejadian</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Laboratorium Komputer, Kantin, dll" required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label class="form-label">Isi Aspirasi</label>
                            <textarea class="form-control" id="ket" name="ket" rows="5" placeholder="Jelaskan detail aspirasi Anda..." required></textarea>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="btn-group">
                            <a href="../index.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Kirim Aspirasi ✉️</button>
                        </div>
                    </form>
                </div>

                <!-- KOLOM KANAN: INFO -->
                <div class="glass-card" style="height: fit-content; position: sticky; top: 20px;">
                    <div class="info-title">💡 Tips Menulis Aspirasi</div>
                    <div class="info-box">
                        <ul class="info-list">
                            <li>Gunakan bahasa yang sopan dan jelas.</li>
                            <li>Sertakan detail waktu dan lokasi.</li>
                            <li>Jelaskan dampak masalah tersebut.</li>
                            <li>Hindari penggunaan kata-kata kasar.</li>
                        </ul>
                    </div>

                    <div class="info-title" style="margin-top: 25px;">📊 Status Aspirasi</div>
                    <div class="info-box">
                        <div style="margin-bottom: 10px;">
                            <span class="status-badge" style="background: #f1c40f; color: #333;">Menunggu</span>
                            <small style="display:block; color:#666;">Sedang ditinjau oleh admin.</small>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span class="status-badge" style="background: #3498db;">Proses</span>
                            <small style="display:block; color:#666;">Sedang ditindaklanjuti.</small>
                        </div>
                        <div>
                            <span class="status-badge" style="background: #2ecc71;">Selesai</span>
                            <small style="display:block; color:#666;">Aspirasi telah ditangani.</small>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script>
        // LOGIKA AJAX NIS (Sama seperti asli, tapi styling feedback disesuaikan)
        document.getElementById('nis').addEventListener('blur', function() {
            const nis = this.value.trim();
            const kelasInput = document.getElementById('kelas');
            const nisStatus = document.getElementById('nisStatus');
            const nisMessage = document.getElementById('nisMessage');
            
            // Reset UI
            kelasInput.value = '';
            nisMessage.innerHTML = '';
            nisStatus.textContent = '-';
            nisStatus.className = 'input-icon'; // remove success/error class
            
            if (nis === '') return;
            
            // Tampilkan loading (opsional)
            nisStatus.textContent = '...';
            
            // Panggil file get_kelas.php (Pastikan file ini ada di folder yang sama)
            fetch('get_kelas.php?nis=' + encodeURIComponent(nis))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        kelasInput.value = data.kelas;
                        nisStatus.textContent = '✅';
                        nisStatus.classList.add('success');
                        // Tampilkan pesan sukses custom
                        nisMessage.innerHTML = '<div style="color:#27ae60; font-size:12px; display:flex; align-items:center; gap:5px;"><span>✅</span> ' + data.message + '</div>';
                    } else {
                        nisStatus.textContent = '❌';
                        nisStatus.classList.add('error');
                        // Tampilkan pesan peringatan
                        nisMessage.innerHTML = '<div style="color:#e67e22; font-size:12px; display:flex; align-items:center; gap:5px;"><span>⚠️</span> ' + data.message + ' (Silakan isi kelas manual)</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    nisStatus.textContent = '❌';
                    nisStatus.classList.add('error');
                    nisMessage.innerHTML = '<div style="color:#c0392b; font-size:12px;">❌ Gagal mengambil data server.</div>';
                });
        });

        // Validasi Form Sederhana
        document.getElementById('formInput').addEventListener('submit', function(e) {
            const nis = document.getElementById('nis').value;
            const kelas = document.getElementById('kelas').value;
            const kategori = document.getElementById('kategori').value;
            const lokasi = document.getElementById('lokasi').value;
            const ket = document.getElementById('ket').value;

            if (!nis || !kelas || !kategori || !lokasi || !ket) {
                e.preventDefault();
                // Alert sederhana atau tampilkan pesan error di UI
                alert('Mohon lengkapi semua data yang diperlukan.');
            }
        });
    </script>
</body>
</html>