<?php
// admin/template/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Cafe Boy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f0f2f5;
            overflow-x: hidden;
        }
        
        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-menu i {
            width: 22px;
            font-size: 18px;
        }
        
        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #f0f2f5;
        }
        
        /* ========== TOPBAR ========== */
        .topbar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 99;
        }
        
        .topbar-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .topbar-user span {
            color: #555;
        }
        
        .topbar-user .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .topbar-user .logout-btn:hover {
            background: #c82333;
        }
        
        /* ========== CONTAINER ========== */
        .container {
            padding: 30px;
        }
        
        /* ========== MODAL LOGOUT ========== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 350px;
            text-align: center;
            animation: fadeInUp 0.3s ease;
        }
        
        .modal-content h3 {
            margin: 15px 0 10px;
        }
        
        .btn-batal {
            background: #6c757d;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                left: 0;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR KIRI -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>
            <i class="fas fa-mug-hot"></i>
            Cafe Boy
        </h3>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="menu.php" class="<?php echo $current_page == 'menu.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i>
                <span>Menu</span>
            </a>
        </li>
        <li>
            <a href="transaksi_cepat.php" class="<?php echo ($current_page == 'transaksi_cepat.php' || $current_page == 'transaksi.php') ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Transaksi</span>
            </a>
        </li>
        <li>
            <a href="laporan.php" class="<?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Laporan</span>
            </a>
        </li>
        <li>
    <li>
    <a href="pesan_masuk.php" class="<?php echo $current_page == 'pesan_masuk.php' ? 'active' : ''; ?>">
        <i class="fas fa-envelope"></i>
        <span>Pesan Masuk</span>
    </a>
</li>
</li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-home"></i> <?php echo ucfirst(str_replace('.php', '', $current_page)); ?>
        </div>
        <div class="topbar-user">
            <span><i class="fas fa-user-circle"></i> Halo, <?php echo $_SESSION['nama']; ?></span>
            <a href="#" onclick="showModal(); return false;" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- KONTEN UTAMA -->
    <div class="container">