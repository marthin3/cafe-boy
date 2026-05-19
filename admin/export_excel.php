<?php
// admin/export_excel.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil parameter bulan/tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Query data
$query = "SELECT 
            t.no_transaksi,
            TO_CHAR(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as waktu,
            a.nama_lengkap as kasir,
            COUNT(d.id_detail) as total_item,
            t.total_harga,
            t.bayar,
            t.kembalian
          FROM transaksi t
          JOIN admin a ON t.id_admin = a.id_admin
          LEFT JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
          WHERE EXTRACT(MONTH FROM t.created_at) = :bulan 
            AND EXTRACT(YEAR FROM t.created_at) = :tahun
          GROUP BY t.id_transaksi, t.no_transaksi, t.created_at, a.nama_lengkap, t.total_harga, t.bayar, t.kembalian
          ORDER BY t.created_at DESC";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':bulan', $bulan);
oci_bind_by_name($stmt, ':tahun', $tahun);
oci_execute($stmt);

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Transaksi_$bulan-$tahun.xls");
header("Cache-Control: max-age=0");

// Buat tabel Excel
echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>No. Transaksi</th>
        <th>Tanggal</th>
        <th>Kasir</th>
        <th>Total Item</th>
        <th>Total Harga</th>
        <th>Bayar</th>
        <th>Kembalian</th>
      </tr>";

$no = 1;
while ($row = oci_fetch_assoc($stmt)) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . $row['NO_TRANSAKSI'] . "</td>";
    echo "<td>" . $row['WAKTU'] . "</td>";
    echo "<td>" . $row['KASIR'] . "</td>";
    echo "<td>" . $row['TOTAL_ITEM'] . "</td>";
    echo "<td>" . $row['TOTAL_HARGA'] . "</td>";
    echo "<td>" . $row['BAYAR'] . "</td>";
    echo "<td>" . $row['KEMBALIAN'] . "</td>";
    echo "</tr>";
}

// Hitung total
$query_total = "SELECT COUNT(*) as total_transaksi, SUM(total_harga) as total_penjualan
                FROM transaksi
                WHERE EXTRACT(MONTH FROM created_at) = :bulan 
                  AND EXTRACT(YEAR FROM created_at) = :tahun";
$stmt_total = oci_parse($conn, $query_total);
oci_bind_by_name($stmt_total, ':bulan', $bulan);
oci_bind_by_name($stmt_total, ':tahun', $tahun);
oci_execute($stmt_total);
$total = oci_fetch_assoc($stmt_total);

echo "<tr>
        <td colspan='5'><strong>Total Transaksi: {$total['TOTAL_TRANSAKSI']}</strong></td>
        <td><strong>Rp " . number_format($total['TOTAL_PENJUALAN'], 0, ',', '.') . "</strong></td>
        <td colspan='2'></td>
      </tr>";
echo "</table>";

oci_close($conn);
exit;
?>