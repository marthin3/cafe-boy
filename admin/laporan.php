<?php
// admin/laporan.php
include 'template/header.php';

// Koneksi database
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil parameter bulan/tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Laporan per menu
$query_menu = "SELECT m.nama_menu, k.nama_kategori, 
                      COUNT(d.id_detail) as total_terjual,
                      SUM(d.subtotal) as total_penjualan
               FROM menu m
               JOIN kategori_menu k ON m.id_kategori = k.id
               LEFT JOIN detail_transaksi d ON m.id_menu = d.id_menu
               LEFT JOIN transaksi t ON d.id_transaksi = t.id_transaksi
               WHERE EXTRACT(MONTH FROM t.created_at) = :bulan 
                 AND EXTRACT(YEAR FROM t.created_at) = :tahun
               GROUP BY m.id_menu, m.nama_menu, k.nama_kategori
               ORDER BY total_penjualan DESC NULLS LAST";
$stmt_menu = oci_parse($conn, $query_menu);
oci_bind_by_name($stmt_menu, ':bulan', $bulan);
oci_bind_by_name($stmt_menu, ':tahun', $tahun);
oci_execute($stmt_menu);

// Total penjualan periode
$query_total = "SELECT COUNT(*) as total_transaksi, COALESCE(SUM(total_harga), 0) as total_penjualan
                FROM transaksi
                WHERE EXTRACT(MONTH FROM created_at) = :bulan 
                  AND EXTRACT(YEAR FROM created_at) = :tahun";
$stmt_total = oci_parse($conn, $query_total);
oci_bind_by_name($stmt_total, ':bulan', $bulan);
oci_bind_by_name($stmt_total, ':tahun', $tahun);
oci_execute($stmt_total);
$total = oci_fetch_assoc($stmt_total);
oci_free_statement($stmt_total);
?>

<h2>📈 Laporan Penjualan</h2>

<div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div>
            <label style="display: block; margin-bottom: 5px;">Bulan</label>
            <select name="bulan" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="01" <?php echo $bulan == '01' ? 'selected' : ''; ?>>Januari</option>
                <option value="02" <?php echo $bulan == '02' ? 'selected' : ''; ?>>Februari</option>
                <option value="03" <?php echo $bulan == '03' ? 'selected' : ''; ?>>Maret</option>
                <option value="04" <?php echo $bulan == '04' ? 'selected' : ''; ?>>April</option>
                <option value="05" <?php echo $bulan == '05' ? 'selected' : ''; ?>>Mei</option>
                <option value="06" <?php echo $bulan == '06' ? 'selected' : ''; ?>>Juni</option>
                <option value="07" <?php echo $bulan == '07' ? 'selected' : ''; ?>>Juli</option>
                <option value="08" <?php echo $bulan == '08' ? 'selected' : ''; ?>>Agustus</option>
                <option value="09" <?php echo $bulan == '09' ? 'selected' : ''; ?>>September</option>
                <option value="10" <?php echo $bulan == '10' ? 'selected' : ''; ?>>Oktober</option>
                <option value="11" <?php echo $bulan == '11' ? 'selected' : ''; ?>>November</option>
                <option value="12" <?php echo $bulan == '12' ? 'selected' : ''; ?>>Desember</option>
            </select>
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px;">Tahun</label>
            <input type="number" name="tahun" value="<?php echo $tahun; ?>" min="2020" max="2030" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <button type="submit" style="background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Tampilkan</button>
    </form>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
        <h3 style="color: #666;">Total Transaksi</h3>
        <p style="font-size: 2em; font-weight: bold; color: #667eea;"><?php echo $total['TOTAL_TRANSAKSI']; ?></p>
    </div>
    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
        <h3 style="color: #666;">Total Penjualan</h3>
        <p style="font-size: 2em; font-weight: bold; color: #28a745;">Rp <?php echo number_format($total['TOTAL_PENJUALAN'], 0, ',', '.'); ?></p>
    </div>
</div>

<h3>📊 Penjualan per Menu</h3>

<table style="width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <thead style="background: #667eea; color: white;">
        <tr>
            <th style="padding: 15px; text-align: left;">No</th>
            <th style="padding: 15px; text-align: left;">Menu</th>
            <th style="padding: 15px; text-align: left;">Kategori</th>
            <th style="padding: 15px; text-align: left;">Terjual</th>
            <th style="padding: 15px; text-align: left;">Total Penjualan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $ada_data = false;
        while ($row = oci_fetch_assoc($stmt_menu)): 
            if ($row['TOTAL_TERJUAL'] > 0) $ada_data = true;
        ?>
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
            <td style="padding: 12px 15px;"><?php echo $row['NAMA_MENU']; ?></td>
            <td style="padding: 12px 15px;"><?php echo $row['NAMA_KATEGORI']; ?></td>
            <td style="padding: 12px 15px;"><?php echo $row['TOTAL_TERJUAL'] ?? 0; ?> pcs</td>
            <td style="padding: 12px 15px;">Rp <?php echo number_format($row['TOTAL_PENJUALAN'] ?? 0, 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
        
        <?php if (!$ada_data): ?>
        <tr>
            <td colspan="5" style="padding: 30px; text-align: center;">Belum ada data penjualan untuk periode ini</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php oci_free_statement($stmt_menu); oci_close($conn); ?>
<?php include 'template/footer.php'; ?>