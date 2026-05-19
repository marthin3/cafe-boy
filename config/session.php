<?php
// config/session.php
session_start();

// Fungsi untuk cek login admin
function cek_login_admin() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        header('Location: /cafe_boy/index.php');
        exit;
    }
}

// Alias fungsi (untuk kompatibilitas)
function cek_login() {
    cek_login_admin();
}
?>