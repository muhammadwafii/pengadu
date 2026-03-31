<?php
include 'config/koneksi.php';

// Pastikan session dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

 $error = '';

// Jika form login disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    
    if ($role == 'admin') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Cek admin
        $query = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
        $result = mysqli_query($koneksi, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $username;
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password admin salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Aspirasi Modern</title>
    <!-- Menggunakan Font Poppins agar terlihat modern -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABEL WARNA & DASAR --- */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
            --text-color: #333;
            --shadow-light: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            /* Background Animasi Gradien */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-color);
        }

        /* --- ANIMASI BACKGROUND --- */
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- CONTAINER UTAMA (GLASSMORPHISM) --- */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-light);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* --- HEADER --- */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 26px;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .header p {
            font-size: 14px;
            color: #666;
            font-weight: 300;
        }

        /* --- FORM ELEMENTS --- */
        .form-group { margin-bottom: 20px; position: relative; }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-left: 5px;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid transparent;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }

        .form-control::placeholder {
            color: #aaa;
        }

        /* --- BUTTONS --- */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Warna Tombol */
        .btn-primary {
            background: var(--primary-gradient);
        }

        .btn-success {
            background: var(--success-gradient);
            margin-bottom: 12px;
        }

        .btn-info {
            background: var(--info-gradient);
        }

        /* --- SEPARATORS & UTILS --- */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0;
            color: #888;
            font-size: 12px;
            font-weight: 500;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .student-zone {
            background: rgba(255,255,255,0.5);
            padding: 20px;
            border-radius: 15px;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.8);
        }

        .student-title {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            color: #444;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- ALERT --- */
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            background-color: #ffeaea;
            color: #d63031;
            font-size: 13px;
            border: 1px solid #ffcccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-close {
            background: none;
            border: none;
            font-size: 18px;
            line-height: 1;
            color: #d63031;
            cursor: pointer;
            opacity: 0.6;
        }
        .btn-close:hover { opacity: 1; }

        /* --- ICONS --- */
        .icon { margin-right: 8px; }

    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <h1>🏫 ASPIRASI SEKOLAH</h1>
            <p>Portal Pengaduan Digital</p>
        </div>

        <?php if ($error): ?>
            <div class="alert">
                <span><?php echo $error; ?></span>
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>

        <!-- FORM LOGIN ADMIN -->
        <form method="POST">
            <input type="hidden" name="role" value="admin">
            
            <div class="form-group">
                <label class="form-label">Username Admin</label>
                <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <span class="icon">🔒</span> Masuk Dashboard
            </button>
        </form>

        <p style="text-align: center; font-size: 12px; color: #777; margin-top: 15px; opacity: 0.8;">
            Demo: User <strong>admin</strong> | Pass <strong>12345</strong>
        </p>

        <!-- SEPARATOR GRADIENT -->
        <div class="divider">ATAU AKSES SISWA</div>

        <!-- ZONE SISWA -->
        <div class="student-zone">
            <div class="student-title">👨‍🎓 Area Siswa</div>
            
            <a href="siswa/input_aspirasi.php" class="btn btn-success">
                <span class="icon">📝</span> Buat Aspirasi Baru
            </a>
            
            <a href="siswa/histori.php" class="btn btn-info">
                <span class="icon">🔍</span> Cek History Aspirasi
            </a>
        </div>
    </div>

</body>
</html>