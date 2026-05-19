<?php
// admin/history.php
include 'template/header.php';

// Koneksi database
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

// Filter tanggal
$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

// =============================================
// PERBAIKAN QUERY - HAPUS SUBQUERY YANG BERMASALAH
// =============================================
$query = "SELECT 
            t.id_transaksi, 
            t.no_transaksi, 
            TO_CHAR(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as waktu_lengkap,
            a.nama_lengkap, 
            t.total_harga
          FROM transaksi t 
          JOIN admin a ON t.id_admin = a.id_admin 
          WHERE TRUNC(t.created_at) BETWEEN TO_DATE(:awal, 'YYYY-MM-DD') AND TO_DATE(:akhir, 'YYYY-MM-DD')
          ORDER BY t.created_at DESC";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':awal', $tanggal_awal);
oci_bind_by_name($stmt, ':akhir', $tanggal_akhir);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    die("Error query: " . $e['message']);
}

// Hitung total penjualan periode
$query_total = "SELECT COALESCE(SUM(total_harga), 0) as total 
                FROM transaksi 
                WHERE TRUNC(created_at) BETWEEN TO_DATE(:awal, 'YYYY-MM-DD') AND TO_DATE(:akhir, 'YYYY-MM-DD')";
$stmt_total = oci_parse($conn, $query_total);
oci_bind_by_name($stmt_total, ':awal', $tanggal_awal);
oci_bind_by_name($stmt_total, ':akhir', $tanggal_akhir);
oci_execute($stmt_total);
$total_penjualan = oci_fetch_assoc($stmt_total)['TOTAL'];
oci_free_statement($stmt_total);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f5f5f5; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .filter-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .summary-card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .summary-card h3 { color: #666; }
        .total-amount { font-size: 2em; font-weight: bold; color: #28a745; }
        table { width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th { background: #667eea; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .btn-detail { background: #17a2b8; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .form-inline { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-filter { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
  
    
    <div class="container">
        <h2>📊 Riwayat Transaksi</h2>
        
        <!-- Filter Tanggal -->
        <div class="filter-section">
            <form method="GET" class="form-inline">
                <div class="form-group">
                    <label>Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="<?php echo $tanggal_awal; ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?php echo $tanggal_akhir; ?>">
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>
        
        <!-- Total Penjualan -->
        <div class="summary-card">
            <h3>Total Penjualan Periode</h3>
            <p class="total-amount">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></p>
        </div>
        
        <!-- Tabel Transaksi -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = oci_fetch_assoc($stmt)): 
                    
                    // Hitung jumlah item untuk transaksi ini
                    $query_item = "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_transaksi = :id";
                    $stmt_item = oci_parse($conn, $query_item);
                    oci_bind_by_name($stmt_item, ':id', $row['ID_TRANSAKSI']);
                    oci_execute($stmt_item);
                    $item_data = oci_fetch_assoc($stmt_item);
                    $total_item = $item_data['TOTAL'];
                    oci_free_statement($stmt_item);
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['NO_TRANSAKSI']; ?></td>
                    <td><?php echo $row['WAKTU_LENGKAP']; ?></td>
                    <td><?php echo $row['NAMA_LENGKAP']; ?></td>
                    <td><?php echo $total_item; ?> item</td>
                    <td>Rp <?php echo number_format($row['TOTAL_HARGA'], 0, ',', '.'); ?></td>
                    <td><a href="detail_transaksi.php?id=<?php echo $row['ID_TRANSAKSI']; ?>">Bayar & Cetak</a></td>
                </tr>
                <?php endwhile; ?>
                
                <?php if ($no == 1): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px;">Belum ada transaksi</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php 
    oci_free_statement($stmt);
    oci_close($conn);
    include 'template/footer.php'; 
    ?>
</body>
</html>