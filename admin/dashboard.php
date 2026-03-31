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

// Hitung statistik
// Menggunakan alias 'total' untuk mempermudah
 $menunggu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi WHERE status='Menunggu'"))['total'];
 $proses = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi WHERE status='Proses'"))['total'];
 $selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi WHERE status='Selesai'"))['total'];
 $total = $menunggu + $proses + $selesai;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pengaduan Aspirasi</title>
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABEL & DASAR --- */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-sidebar: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #000000;
            --text-muted: #000000;
            --primary: #6c5ce7;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        /* --- ANIMATED BACKGROUND (SAMA DENGAN LOGIN) --- */
       

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

               /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--glass-sidebar);
            backdrop-filter: blur(10px);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 20px;
            border-right: 1px solid var(--glass-border);
            /* Pastikan display flex ada di sini */
            display: flex;
            flex-direction: column; 
            z-index: 100;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05);
        }

        /* --- TAMBAHKAN INI (PENTING) --- */
        /* Ini membuat area menu memenuhi ruang kosong */
        .nav-links {
            display: flex;
            flex-direction: column;
            flex: 1; /* <--- KUNCI: Mendorong footer ke paling bawah */
        }
        /* ------------------------------- */

        .brand {
            font-size: 20px;
            font-weight: 700;
            color: #444;
            margin-bottom: 30px; /* Sedikit dikurangi agar tidak terlalu jauh */
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand span { color: var(--primary); }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            text-decoration: none;
            color: #555;
            border-radius: 12px;
            margin-bottom: 8px; /* Jarak antar menu */
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(108, 92, 231, 0.1);
            color: var(--primary);
            transform: translateX(5px);
        }

        /* Styling Khusus Logout di Footer */
        .sidebar-footer {
            margin-top: 20px; /* Jarak aman dari menu terakhir */
            border-top: 1px solid rgba(0,0,0,0.05);
            padding-top: 20px;
        }

        .sidebar-footer .nav-link {
            color: #e74c3c; /* Warna Merah */
            margin-bottom: 0; /* Reset margin bawah di footer */
        }

        .sidebar-footer .nav-link:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .user-info {
            background: var(--glass-bg);
            padding: 10px 20px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            font-size: 14px;
        }

        /* --- STATS GRID --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--glass-bg);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }

        /* Decorative circle for cards */
        .stat-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: 0.2;
        }

        .stat-card h3 {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card p {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Card Specific Colors */
        .card-total::before { background: #6c5ce7; } /* Purple */
        .card-total h3 { color: #6c5ce7; }

        .card-menunggu::before { background: #f1c40f; } /* Yellow */
        .card-menunggu h3 { color: #f39c12; }

        .card-proses::before { background: #3498db; } /* Blue */
        .card-proses h3 { color: #2980b9; }

        .card-selesai::before { background: #2ecc71; } /* Green */
        .card-selesai h3 { color: #27ae60; }

        /* --- FOOTER --- */
        footer {
            margin-top: 50px;
            text-align: center;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            padding-bottom: 20px;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            .brand { margin-bottom: 0; font-size: 16px; }
            .nav-links { display: none; } /* Simplified for mobile */
            .sidebar-footer { display: none; }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            🏫 <span>Aspirasi</span>Sekolah
        </div>
        
        <nav class="nav-links">
            <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="aspirasi.php" class="nav-link">📋 Daftar Aspirasi</a>
            <!-- Tambahkan menu lain jika perlu -->
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link" style="color: #e74c3c;">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div>
                <h2 style="font-weight: 700; color: #000000; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Dashboard Admin</h2>
                <p style="color: rgba(255,255,255,0.9);">Ringkasan aktivitas aspirasi hari ini.</p>
            </div>
            
            <div class="user-info">
                <span>👋 Hai, <strong><?php echo $_SESSION['username']; ?></strong></span>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="stats-grid">
            <!-- Total -->
            <div class="stat-card card-total">
                <p>Total Aspirasi</p>
                <h3><?php echo $total; ?></h3>
                <small>Semua Data</small>
            </div>

            <!-- Menunggu -->
            <div class="stat-card card-menunggu">
                <p>Menunggu</p>
                <h3><?php echo $menunggu; ?></h3>
                <small>Perlu tindakan</small>
            </div>

            <!-- Proses -->
            <div class="stat-card card-proses">
                <p>Diproses</p>
                <h3><?php echo $proses; ?></h3>
                <small>Sedang dikerjakan</small>
            </div>

            <!-- Selesai -->
            <div class="stat-card card-selesai">
                <p>Selesai</p>
                <h3><?php echo $selesai; ?></h3>
                <small>Telah tuntas</small>
            </div>
        </div>

        <!-- Placeholder Content (Optional) -->
        <div style="background: var(--glass-bg); padding: 30px; border-radius: 20px; border: 1px solid var(--glass-border); backdrop-filter: blur(10px); color: #555;">
            <h4 style="margin-bottom: 10px;">👋 Selamat Datang di Panel Admin</h4>
            <p>Gunakan menu di sebelah kiri untuk mengelola data aspirasi yang masuk. Pastikan untuk merespon aspirasi siswa secara berkala.</p>
        </div>

        <footer>
            <p>&copy; <?php echo date("Y"); ?> Sistem Pengaduan Aspirasi Sekolah.</p>
        </footer>
    </div>

</body>
</html>