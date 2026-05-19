<?php
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi gagal");

$query = "SELECT nama_lengkap, foto FROM admin WHERE id_admin = 1";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$admin = oci_fetch_assoc($stmt);

echo "<h2>Debug Data Admin</h2>";
echo "<pre>";
print_r($admin);
echo "</pre>";

echo "<h2>Cek File Foto</h2>";
$foto_path = $_SERVER['DOCUMENT_ROOT'] . '/cafe_boy/uploads/profile/' . $admin['FOTO'];
echo "Path: " . $foto_path . "<br>";
if (file_exists($foto_path)) {
    echo "✅ FILE ADA!<br>";
    echo '<img src="/cafe_boy/uploads/profile/' . $admin['FOTO'] . '" width="200">';
} else {
    echo "❌ FILE TIDAK ADA!";
}

oci_close($conn);
?>