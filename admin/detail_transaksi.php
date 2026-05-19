<?php
// admin/detail_transaksi.php
include 'template/header.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: history.php');
    exit;
}

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil data transaksi
$query_transaksi = "SELECT t.*, TO_CHAR(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as waktu_lengkap,
                           a.nama_lengkap, m.nomor_meja
                    FROM transaksi t 
                    JOIN admin a ON t.id_admin = a.id_admin
                    LEFT JOIN meja m ON t.id_meja = m.id_meja
                    WHERE t.id_transaksi = :id";
$stmt_transaksi = oci_parse($conn, $query_transaksi);
oci_bind_by_name($stmt_transaksi, ':id', $id);
oci_execute($stmt_transaksi);
$transaksi = oci_fetch_assoc($stmt_transaksi);
oci_free_statement($stmt_transaksi);

if (!$transaksi) {
    header('Location: history.php');
    exit;
}

// Ambil detail transaksi
$query_detail = "SELECT d.*, m.nama_menu 
                FROM detail_transaksi d 
                JOIN menu m ON d.id_menu = m.id_menu 
                WHERE d.id_transaksi = :id
                ORDER BY d.id_detail";
$stmt_detail = oci_parse($conn, $query_detail);
oci_bind_by_name($stmt_detail, ':id', $id);
oci_execute($stmt_detail);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bayar'])) {
    $bayar = str_replace('.', '', $_POST['bayar'] ?? 0);
    $total = $transaksi['TOTAL_HARGA'];
    $kembalian = $bayar - $total;
    
    if ($kembalian < 0) {
        $error = 'Uang kurang Rp ' . number_format(abs($kembalian), 0, ',', '.');
    } else {
        $query_update = "UPDATE transaksi SET bayar = :bayar, kembalian = :kembali WHERE id_transaksi = :id";
        $stmt_update = oci_parse($conn, $query_update);
        oci_bind_by_name($stmt_update, ':bayar', $bayar);
        oci_bind_by_name($stmt_update, ':kembali', $kembalian);
        oci_bind_by_name($stmt_update, ':id', $id);
        
        if (oci_execute($stmt_update)) {
            $success = "Pembayaran berhasil! Kembalian: Rp " . number_format($kembalian, 0, ',', '.');
            $transaksi['BAYAR'] = $bayar;
            $transaksi['KEMBALIAN'] = $kembalian;
        } else {
            $error = "Gagal menyimpan pembayaran";
        }
        oci_free_statement($stmt_update);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f5f5f5; }
        .container { max-width: 900px; margin: 20px auto; padding: 0 20px; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { color: #333; margin-bottom: 20px; }
        h3 { margin-bottom: 15px; color: #444; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 10px 5px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { width: 150px; color: #666; font-weight: 500; }
        .info-table td:last-child { font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #667eea; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-row { font-weight: bold; background: #f8f9fa; }
        .total-final { font-size: 1.2em; font-weight: bold; color: #28a745; }
        .btn-group { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .btn-print { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-bayar { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .bayar-section { background: #f0f7ff; padding: 20px; border-radius: 10px; margin-top: 20px; border: 1px solid #cce5ff; }
        .form-row { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        .kembalian-box { background: #d4edda; padding: 12px; border-radius: 5px; text-align: center; min-width: 150px; }
        .kembalian-box span { font-size: 1.2em; font-weight: bold; color: #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
        
    <div class="container">
        <h2>📄 Detail Transaksi</h2>
        
        <?php if ($error): ?>
            <div class="alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Info Transaksi -->
        <div class="card">
            <table class="info-table">
                <tr><td>No. Transaksi</td><td><strong><?php echo $transaksi['NO_TRANSAKSI']; ?></strong></td></tr>
                <tr><td>Tanggal</td><td><?php echo $transaksi['WAKTU_LENGKAP']; ?></td></tr>
                <tr><td>Kasir</td><td><?php echo $transaksi['NAMA_LENGKAP']; ?></td></tr>
                <tr><td>Meja</td><td><?php echo $transaksi['NOMOR_MEJA'] ?? '-'; ?></td></tr>
            </table>
        </div>
        
        <!-- Detail Item + Pembayaran (dalam satu card) -->
        <div class="card">
            <h3>🛒 Item Pesanan & Pembayaran</h3>
            
            <!-- Tabel Item -->
            <table style="margin-bottom: 20px;">
                <thead><tr><th>No</th><th>Menu</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php $no = 1; while ($detail = oci_fetch_assoc($stmt_detail)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $detail['NAMA_MENU']; ?></td>
                        <td>Rp <?php echo number_format($detail['HARGA_SATUAN'], 0, ',', '.'); ?></td>
                        <td><?php echo $detail['JUMLAH']; ?></td>
                        <td>Rp <?php echo number_format($detail['SUBTOTAL'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Total + Bayar + Kembalian -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; flex-wrap: wrap; gap: 15px;">
                <div>
                    <div><strong>Total:</strong> <span style="font-size: 1.2em; color: #28a745;">Rp <?php echo number_format($transaksi['TOTAL_HARGA'], 0, ',', '.'); ?></span></div>
                    <?php if ($transaksi['BAYAR'] > 0): ?>
                        <div><strong>Bayar:</strong> Rp <?php echo number_format($transaksi['BAYAR'], 0, ',', '.'); ?></div>
                        <div><strong>Kembalian:</strong> Rp <?php echo number_format($transaksi['KEMBALIAN'], 0, ',', '.'); ?></div>
                    <?php endif; ?>
                </div>
                
                <?php if ($transaksi['BAYAR'] == 0): ?>
                    <!-- Form Pembayaran langsung di sini -->
                    <form method="POST" style="flex: 1; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">💰 Uang Bayar</label>
                            <input type="text" name="bayar" id="bayar" onkeyup="hitungKembalian()" placeholder="Masukkan jumlah uang" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <div class="kembalian-box" id="kembalianDisplay">
                            <span>Kembalian: Rp 0</span>
                        </div>
                        <button type="submit" name="update_bayar" class="btn-bayar">✅ Proses Bayar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tombol Aksi -->
        <div class="btn-group">
            <a href="history.php" class="btn-back">← Kembali</a>
            <?php if ($transaksi['BAYAR'] > 0): ?>
                <button class="btn-print" onclick="window.location.href='cetak_struk.php?id=<?php echo $id; ?>'">🖨️ Cetak Struk</button>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function hitungKembalian() {
        let total = <?php echo $transaksi['TOTAL_HARGA']; ?>;
        let bayar = parseInt(document.getElementById('bayar').value.replace(/\./g, '')) || 0;
        let kembalian = bayar - total;
        let el = document.getElementById('kembalianDisplay');
        
        if (kembalian >= 0) {
            el.innerHTML = '<span>Kembalian: Rp ' + kembalian.toLocaleString('id-ID') + '</span>';
            el.style.background = '#d4edda';
        } else {
            el.innerHTML = '<span style="color:#dc3545;">Kembalian: Rp ' + Math.abs(kembalian).toLocaleString('id-ID') + ' (Kurang)</span>';
            el.style.background = '#f8d7da';
        }
    }
    </script>
    
    <?php 
    oci_free_statement($stmt_detail);
    oci_close($conn);
    include 'template/footer.php'; 
    ?>
</body>
</html>