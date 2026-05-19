<?php
// user/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_nama'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Cafe Boy - User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #fdf6ee; padding-top: 110px; }
        
        /* ========== TOP NAVBAR ========== */
        .top-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 20px;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo h2 { margin: 0; font-size: 1.2rem; width: 100px; }
        
        /* MENU DI TENGAH */
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
            justify-content: center;
            flex: 1;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-menu a:hover { background: rgba(255,255,255,0.2); }
        
        /* LOGOUT DI KANAN */
        .logout-desktop {
            width: 100px;
            text-align: right;
        }
        
        .logout-desktop a {
            background: #dc3545;
            color: white;
            padding: 6px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-desktop a:hover { background: #c82333; }
        
        /* HAMBURGER (sembunyi di laptop) */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }
        
        /* ========== CART NAVBAR ========== */
        .cart-navbar {
            background: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 65px;
            left: 0;
            right: 0;
            z-index: 999;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .cart-info { display: flex; align-items: center; gap: 10px; }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .cart-icon span {
            background: #28a745;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 0.6rem;
            position: absolute;
            top: -8px;
            right: -8px;
        }
        
        .cart-summary { display: flex; gap: 10px; align-items: center; }
        .cart-total { font-weight: bold; color: #28a745; font-size: 0.9rem; }
        
        .btn-cart {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .cart-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
        }
        .cart-dropdown.show { display: block; }
        .cart-dropdown-content { padding: 12px; max-height: 250px; overflow-y: auto; }
        .cart-dropdown-footer { padding: 10px; border-top: 1px solid #eee; display: flex; justify-content: space-between; font-weight: bold; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 15px; }
        
        /* SIDEBAR HP */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1001;
            transition: left 0.3s ease;
            padding-top: 60px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        
        .sidebar.open { left: 0; }
        
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            font-size: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .sidebar .logout { background: #dc3545; margin-top: 20px; }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: none;
        }
        .overlay.show { display: block; }
        
        /* ========== RESPONSIVE HP ========== */
        @media (max-width: 576px) {
            body { padding-top: 110px; }
            
            .nav-menu {
                display: none;
            }
            
            .logout-desktop {
                display: none;
            }
            
            .hamburger {
                display: block;
            }
            
            .cart-navbar {
                top: 50px;
                padding: 8px 15px;
            }
        }
        
        @media (min-width: 577px) {
            .sidebar, .overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR HP -->
<div class="sidebar" id="sidebar">
    <a href="#home" onclick="closeSidebar()">🏠 Home</a>
    <a href="#menu" onclick="closeSidebar()">🍽️ Menu</a>
    <a href="#contact" onclick="closeSidebar()">📞 Contact</a>
    <a href="#tentang" onclick="closeSidebar()">📖 Tentang Kami</a>
    <a href="../logout.php" class="logout" onclick="closeSidebar()">🚪 Logout</a>
</div>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- TOP NAVBAR -->
<div class="top-navbar">
    <!-- LOGO KIRI -->
    <div class="logo">
        <h2>☕ Cafe Boy</h2>
    </div>
    
    <!-- MENU TENGAH (untuk laptop) -->
    <div class="nav-menu">
        <a href="#home">Home</a>
        <a href="#menu">Menu</a>
        <a href="#contact">Contact</a>
        <a href="#tentang">Tentang Kami</a>
    </div>
    
    <!-- LOGOUT KANAN (untuk laptop) -->
    <div class="logout-desktop">
        <a href="../logout.php">Logout</a>
    </div>
    
    <!-- HAMBURGER (untuk HP) -->
    <button class="hamburger" onclick="toggleSidebar()">☰</button>
</div>

<!-- CART NAVBAR -->
<div class="cart-navbar">
    <div class="cart-info">
        <div class="cart-icon" onclick="toggleCart()">
            🛒
            <span id="cartBadge">0</span>
        </div>
        <span>Keranjang</span>
    </div>
    <div class="cart-summary">
        <span class="cart-total" id="cartTotalNavbar">Rp 0</span>
        <a href="konfirmasi.php" class="btn-cart">Checkout</a>
    </div>
    <div class="cart-dropdown" id="cartDropdown">
        <div class="cart-dropdown-content" id="cartDropdownItems">
            <p style="text-align: center; color: #999;">Keranjang kosong</p>
        </div>
        <div class="cart-dropdown-footer">
            <span>Total:</span>
            <span id="cartDropdownTotal">Rp 0</span>
        </div>
    </div>
</div>

<div class="container">

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('overlay').classList.toggle('show');
    }
    
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('overlay').classList.remove('show');
    }
    
    function toggleCart() {
        document.getElementById('cartDropdown').classList.toggle('show');
    }
    
    window.onclick = function(e) {
        let dropdown = document.getElementById('cartDropdown');
        if (dropdown && !e.target.closest('.cart-icon')) {
            dropdown.classList.remove('show');
        }
    }
</script>