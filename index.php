<?php
// user/index.php - ONE PAGE
error_reporting(E_ALL);
ini_set('display_errors', 1);
// include 'header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    echo "<p style='color:red; text-align:center;'>❌ Koneksi database gagal!</p>";
} else {
    $query = "SELECT id_menu, nama_menu, deskripsi, harga, gambar FROM menu WHERE status = 'tersedia'";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
}
?>
<?php
// index.php - VERSI FINAL DENGAN LOGIKA PASTI BERHASIL
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// CEK APAKAH SUDAH LOGIN
if (isset($_SESSION['level'])) {
    if ($_SESSION['level'] == 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($_SESSION['level'] == 'pelanggan') {
        header('Location: user/index.php');
        exit;
    }
}

// KONEKSI DATABASE (SAMA PERSIS DENGAN YANG BERHASIL)
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // LOGIN ADMIN
    if (isset($_POST['login_admin'])) {
        $username = $_POST['username'] ?? '';
        $password = md5($_POST['password'] ?? '');
        
        // QUERY SAMA PERSIS DENGAN YANG BERHASIL
        $query = "SELECT * FROM system.admin WHERE username = '$username' AND password = '$password'";
        $stmt = oci_parse($conn, $query);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        
        if ($row) {
            $_SESSION['user_id'] = $row['ID_ADMIN'];
            $_SESSION['username'] = $row['USERNAME'];
            $_SESSION['nama'] = $row['NAMA_LENGKAP'];
            $_SESSION['level'] = 'admin';
            
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password admin salah!';
        }
        oci_free_statement($stmt);
    }
    
    // LOGIN PELANGGAN
    elseif (isset($_POST['login_pelanggan'])) {
        $nama = trim($_POST['nama_pelanggan'] ?? '');
        
        if (!empty($nama)) {
            $_SESSION['user_nama'] = $nama;
            $_SESSION['user_id'] = uniqid();
            $_SESSION['level'] = 'pelanggan';
            
            header('Location: user/index.php');
            exit;
        } else {
            $error = 'Nama pelanggan tidak boleh kosong!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Boy - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            text-align: center;
            color: #764ba2;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95em;
        }
        
        .role-switch {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: #f5f5f5;
            padding: 5px;
            border-radius: 50px;
        }
        
        .role-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 50px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
            color: #666;
        }
        
        .role-btn.active {
            background: white;
            color: #764ba2;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .role-btn.admin.active {
            color: #667eea;
        }
        
        .role-btn.pelanggan.active {
            color: #28a745;
        }
        
        .form-container {
            transition: all 0.3s ease;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.95em;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #764ba2;
            background: white;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login.admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-login.pelanggan {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .info-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #999;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .info-text i {
            font-style: normal;
            color: #667eea;
            font-weight: 600;
        }
        
        .alert {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 0.95em;
            border: 1px solid #f5c6cb;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .divider {
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .divider span {
            background: white;
            padding: 0 10px;
            color: #999;
            font-size: 0.9em;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
        }
        
        .demo-credentials p {
            margin: 5px 0;
            color: #666;
        }
        
        .demo-credentials strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h1>Cafe Boy</h1>
            <p class="subtitle">Silakan pilih jenis login Anda </p>
            
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Pilihan Role -->
            <div class="role-switch">
                <button type="button" class="role-btn admin active" id="btnAdmin" onclick="showForm('admin')">
                    🔐 Admin
                </button>
                <button type="button" class="role-btn pelanggan" id="btnPelanggan" onclick="showForm('pelanggan')">
                    👤 Pelanggan
                </button>
            </div>
            
            <!-- Form Admin -->
            <div id="formAdmin" class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="admin" placeholder="Masukkan username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" value="admin123" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" name="login_admin" class="btn-login admin">
                        🔐 Login Admin
                    </button>
                </form>
            </div>
            
            <!-- Form Pelanggan (awalnya disembunyikan) -->
            <div id="formPelanggan" class="form-container" style="display: none;">
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Anda</label>
                        <input type="text" name="nama_pelanggan" placeholder="Contoh: Budi" required>
                    </div>
                    <button type="submit" name="login_pelanggan" class="btn-login pelanggan">
                        👤 Masuk sebagai Pelanggan
                    </button>
                </form>
            </div>
            
            <div class="demo-credentials">
                <p><strong>👤 Untuk Pelanggan:</strong> Cukup masukkan nama saja</p>
            </div>
        </div>
    </div>
    
    <script>
        function showForm(role) {
            const btnAdmin = document.getElementById('btnAdmin');
            const btnPelanggan = document.getElementById('btnPelanggan');
            const formAdmin = document.getElementById('formAdmin');
            const formPelanggan = document.getElementById('formPelanggan');
            
            if (role === 'admin') {
                // Aktifkan tombol admin
                btnAdmin.classList.add('active');
                btnPelanggan.classList.remove('active');
                
                // Tampilkan form admin, sembunyikan form pelanggan
                formAdmin.style.display = 'block';
                formPelanggan.style.display = 'none';
            } else {
                // Aktifkan tombol pelanggan
                btnPelanggan.classList.add('active');
                btnAdmin.classList.remove('active');
                
                // Tampilkan form pelanggan, sembunyikan form admin
                formAdmin.style.display = 'none';
                formPelanggan.style.display = 'block';
            }
        }
    </script>
</body>
</html>
<?php oci_close($conn); ?>