<?php
// admin/hapus_menu.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if ($id) {
    $conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
    
    // Cek apakah menu digunakan di transaksi
    $query_cek = "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_menu = :id";
    $stmt_cek = oci_parse($conn, $query_cek);
    oci_bind_by_name($stmt_cek, ':id', $id);
    oci_execute($stmt_cek);
    $cek = oci_fetch_assoc($stmt_cek);
    
    if ($cek['TOTAL'] > 0) {
        $_SESSION['error'] = 'Menu tidak bisa dihapus karena sudah digunakan dalam transaksi!';
    } else {
        $query = "DELETE FROM menu WHERE id_menu = :id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':id', $id);
        oci_execute($stmt);
    }
    oci_close($conn);
}
header('Location: menu.php');
exit;
?>