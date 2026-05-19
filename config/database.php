<?php
// config/database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// UBAH BAGIAN INI: dari C##cafe_boy menjadi system
$username = 'system';
$password = '12345';  // Gunakan password system Anda
$connection_string = 'lptpmrthn:1521/freepdb1';

$conn = oci_connect($username, $password, $connection_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("Koneksi ke Oracle gagal: " . htmlentities($e['message'], ENT_QUOTES));
}

// Set session
$stmt = oci_parse($conn, "ALTER SESSION SET NLS_LANGUAGE='INDONESIAN'");
oci_execute($stmt);
oci_free_statement($stmt);
?>