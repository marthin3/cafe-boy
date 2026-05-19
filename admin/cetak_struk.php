<?php
// admin/cetak_struk.php
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: history.php');
    exit;
}

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil data transaksi
$query = "SELECT t.*, TO_CHAR(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as waktu, a.nama_lengkap, m.nomor_meja
          FROM transaksi t 
          JOIN admin a ON t.id_admin = a.id_admin
          LEFT JOIN meja m ON t.id_meja = m.id_meja
          WHERE t.id_transaksi = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id);
oci_execute($stmt);
$trx = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$trx) {
    header('Location: history.php');
    exit;
}

// Ambil detail item
$query2 = "SELECT d.*, m.nama_menu FROM detail_transaksi d JOIN menu m ON d.id_menu = m.id_menu WHERE d.id_transaksi = :id";
$stmt2 = oci_parse($conn, $query2);
oci_bind_by_name($stmt2, ':id', $id);
oci_execute($stmt2);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Transaksi</title>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .struk-container {
            background: white;
            width: 350px;
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; font-size: 11px; color: #666; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; margin: 5px 0; }
        .total { font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; }
        .btn-group { 
            margin-top: 20px; 
            display: flex; 
            gap: 10px; 
            justify-content: center;
        }
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-print {
            background: #28a745;
            color: white;
        }
        .btn-close {
            background: #6c757d;
            color: white;
        }
        .btn-print:hover { background: #218838; }
        .btn-close:hover { background: #5a6268; }
        @media print {
            body { background: white; }
            .struk-container { box-shadow: none; margin: 0; padding: 10px; }
            .btn-group { display: none; }
        }
    </style>
</head>
<body>
    <div class="struk-container">
        <div class="header">
            <h2>☕ CAFE BOY</h2>
            <p>Jl Pembangunan No.114 USU</p>
            <p>Telp: 0831-7451-4759</p>
            <p>E-mail: marthinlubis27@gmail.com</p>
        </div>
        <div class="line"></div>
        
        <div class="row"><strong>No:</strong><span><?php echo $trx['NO_TRANSAKSI']; ?></span></div>
        <div class="row"><strong>Tanggal:</strong><span><?php echo $trx['WAKTU']; ?></span></div>
        <div class="row"><strong>Kasir:</strong><span><?php echo $trx['NAMA_LENGKAP']; ?></span></div>
        <div class="row"><strong>Meja:</strong><span><?php echo $trx['NOMOR_MEJA'] ?? '-'; ?></span></div>
        
        <div class="line"></div>
        
        <?php while ($item = oci_fetch_assoc($stmt2)): ?>
        <div class="row">
            <span><?php echo $item['NAMA_MENU']; ?> x<?php echo $item['JUMLAH']; ?></span>
            <span>Rp <?php echo number_format($item['SUBTOTAL'], 0, ',', '.'); ?></span>
        </div>
        <?php endwhile; ?>
        
        <div class="line"></div>
        
        <div class="row total"><strong>Total</strong><strong>Rp <?php echo number_format($trx['TOTAL_HARGA'], 0, ',', '.'); ?></strong></div>
        <div class="row"><strong>Bayar</strong><span>Rp <?php echo number_format($trx['BAYAR'], 0, ',', '.'); ?></span></div>
        <div class="row"><strong>Kembalian</strong><span>Rp <?php echo number_format($trx['KEMBALIAN'], 0, ',', '.'); ?></span></div>
        
        <div class="line"></div>
        
        <div class="footer">
            <p>Terima kasih telah berkunjung!</p>
            <p>~ Barang yang sudah dibeli tidak dapat dikembalikan ~</p>
            </div>
        
        <div class="btn-group">
            <button class="btn btn-print" onclick="cetakStruk()">Cetak Struk</button>
            <button class="btn btn-close" onclick="tutupStruk()">❌ Tutup</button>
        </div>
    </div>
    
    <script>
        function cetakStruk() {
            window.print();
        }
        
        function tutupStruk() {
            // Menutup jendela/tab saat ini
            window.close();
            
            // Alternatif: redirect ke halaman history
            setTimeout(function() {
                window.location.href = 'history.php';
            }, 100);
        }
    </script>
</body>
</html>
<?php oci_close($conn); ?>