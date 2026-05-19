<?php
// admin/test_pesan.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

$query = "SELECT * FROM pesan ORDER BY id_pesan DESC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

echo "<h2>Test Pesan Masuk</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Nama</th><th>Email</th><th>Pesan</th><th>Tanggal</th></tr>";

while ($row = oci_fetch_assoc($stmt)) {
    echo "<tr>";
    echo "<td>" . $row['ID_PESAN'] . "</td>";
    echo "<td>" . htmlspecialchars($row['NAMA']) . "</td>";
    echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
    echo "<td>" . htmlspecialchars($row['PESAN']) . "</td>";
    echo "<td>" . $row['TANGGAL'] . "</td>";
    echo "</tr>";
}
echo "</table>";

oci_free_statement($stmt);
oci_close($conn);
?>