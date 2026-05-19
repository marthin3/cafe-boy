<?php
// admin/dashboard.php
include 'template/header.php';

// Koneksi database
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

// =========================================
// 1. TOTAL MENU
// =========================================
$query_menu = "SELECT COUNT(*) as total FROM menu";
$stmt_menu = oci_parse($conn, $query_menu);
oci_execute($stmt_menu);
$total_menu = oci_fetch_assoc($stmt_menu)['TOTAL'];
oci_free_statement($stmt_menu);

// =========================================
// 2. TOTAL TRANSAKSI HARI INI
// =========================================
$query_transaksi = "SELECT COUNT(*) as total, COALESCE(SUM(total_harga), 0) as total_penjualan 
                   FROM transaksi 
                   WHERE TRUNC(created_at) = TRUNC(SYSDATE)";
$stmt_transaksi = oci_parse($conn, $query_transaksi);
oci_execute($stmt_transaksi);
$transaksi = oci_fetch_assoc($stmt_transaksi);
$total_transaksi = $transaksi['TOTAL'];
$total_penjualan = $transaksi['TOTAL_PENJUALAN'];
oci_free_statement($stmt_transaksi);

// =========================================
// 3. MENU TERLARIS (TOP 5) - PERBAIKAN
// =========================================
$query_top = "SELECT m.nama_menu, COUNT(d.id_detail) as jumlah_dipesan, SUM(d.subtotal) as total
              FROM menu m
              LEFT JOIN detail_transaksi d ON m.id_menu = d.id_menu
              LEFT JOIN transaksi t ON d.id_transaksi = t.id_transaksi
              WHERE t.created_at >= SYSDATE - 30 OR t.created_at IS NULL
              GROUP BY m.id_menu, m.nama_menu
              ORDER BY jumlah_dipesan DESC
              FETCH FIRST 5 ROWS ONLY";
$stmt_top = oci_parse($conn, $query_top);
oci_execute($stmt_top);

// =========================================
// 4. STOK MENIPIS (stok < 5)
// =========================================
$query_stok = "SELECT nama_menu, stok FROM menu WHERE stok < 5 AND stok > 0 ORDER BY stok ASC";
$stmt_stok = oci_parse($conn, $query_stok);
oci_execute($stmt_stok);

// =========================================
// 5. TRANSAKSI TERBARU (5 data) - DENGAN FORMAT WAKTU
// =========================================
$query_recent = "SELECT 
                    t.id_transaksi, 
                    t.no_transaksi, 
                    TO_CHAR(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as waktu_lengkap,
                    t.total_harga, 
                    a.nama_lengkap
                 FROM transaksi t
                 JOIN admin a ON t.id_admin = a.id_admin
                 ORDER BY t.created_at DESC
                 FETCH FIRST 5 ROWS ONLY";
$stmt_recent = oci_parse($conn, $query_recent);
oci_execute($stmt_recent);
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card h3 {
        color: #666;
        font-size: 1em;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .stat-card .number {
        font-size: 2.5em;
        font-weight: bold;
        margin: 10px 0;
    }
    .stat-card.menu .number { color: #667eea; }
    .stat-card.transaksi .number { color: #28a745; }
    .stat-card.uang .number { color: #ffc107; }
    
    .section-title {
        font-size: 1.3em;
        margin: 30px 0 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
    
    .two-column {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .info-box {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .info-box h4 {
        margin-bottom: 15px;
        color: #444;
    }
    
    .top-menu-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .top-menu-item:last-child {
        border-bottom: none;
    }
    
    .menu-name {
        font-weight: 500;
    }
    
    .menu-count {
        background: #667eea;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.9em;
    }
    
    .stok-warning {
        color: #dc3545;
        font-weight: bold;
    }
    
    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .recent-table th {
        text-align: left;
        padding: 10px 5px;
        background: #f8f9fa;
        border-bottom: 2px solid #667eea;
    }
    
    .recent-table td {
        padding: 10px 5px;
        border-bottom: 1px solid #eee;
    }
    
    .btn-detail {
        background: #17a2b8;
        color: white;
        padding: 3px 10px;
        text-decoration: none;
        border-radius: 3px;
        font-size: 0.9em;
    }
</style>

<h2 style="margin-bottom: 20px;">Selamat Datang Admin!</h2>

<!-- STATISTIK UTAMA -->
<div class="dashboard-grid">
    <div class="stat-card menu">
        <h3>Total Menu</h3>
        <div class="number"><?php echo $total_menu; ?></div>
        <p>Menu tersedia</p>
    </div>
    
    <div class="stat-card transaksi">
        <h3>Transaksi Hari Ini</h3>
        <div class="number"><?php echo $total_transaksi; ?></div>
        <p>Transaksi</p>
    </div>
    
    <div class="stat-card uang">
        <h3>Pendapatan Hari Ini</h3>
        <div class="number">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></div>
        <p>Total penjualan</p>
    </div>
</div>

<!-- DUA KOLOM: MENU TERLARIS + STOK MENIPIS -->
<div class="two-column">
    <!-- MENU TERLARIS -->
    <div class="info-box">
        <h4>🔥 Menu Terlaris (30 Hari Terakhir)</h4>
        <?php 
        $ada_top = false;
        while ($top = oci_fetch_assoc($stmt_top)): 
            if ($top['JUMLAH_DIPESAN'] > 0) {
                $ada_top = true;
            }
        ?>
        <div class="top-menu-item">
            <span class="menu-name"><?php echo $top['NAMA_MENU']; ?></span>
            <span class="menu-count"><?php echo $top['JUMLAH_DIPESAN']; ?>x dipesan</span>
        </div>
        <?php endwhile; ?>
        
        <?php if (!$ada_top): ?>
            <p style="text-align: center; color: #999; padding: 20px;">Belum ada data penjualan</p>
        <?php endif; ?>
    </div>
    
    <!-- STOK MENIPIS -->
    <div class="info-box">
        <h4>⚠️ Stok Menipis (Stok < 5)</h4>
        <?php 
        $ada_stok = false;
        while ($stok = oci_fetch_assoc($stmt_stok)): 
            $ada_stok = true;
        ?>
        <div class="top-menu-item">
            <span class="menu-name"><?php echo $stok['NAMA_MENU']; ?></span>
            <span class="stok-warning">Stok: <?php echo $stok['STOK']; ?></span>
        </div>
        <?php endwhile; ?>
        
        <?php if (!$ada_stok): ?>
            <p style="text-align: center; color: #999; padding: 20px;">Semua stok aman</p>
        <?php endif; ?>
    </div>
</div>

<!-- TRANSAKSI TERBARU -->
<div class="info-box">
    <h4>📋 Transaksi Terbaru</h4>
    <table class="recent-table">
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Kasir</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $ada_recent = false;
            while ($recent = oci_fetch_assoc($stmt_recent)): 
                $ada_recent = true;
            ?>
            <tr>
                <td><?php echo $recent['NO_TRANSAKSI']; ?></td>
                <td><?php echo $recent['WAKTU_LENGKAP']; ?></td>
                <td>Rp <?php echo number_format($recent['TOTAL_HARGA'], 0, ',', '.'); ?></td>
                <td><?php echo $recent['NAMA_LENGKAP']; ?></td>
                <td><a href="detail_transaksi.php?id=<?php echo $recent['ID_TRANSAKSI']; ?>" class="btn-detail">Detail</a></td>
            </tr>
            <?php endwhile; ?>
            
            <?php if (!$ada_recent): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Belum ada transaksi</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- AKSI CEPAT -->
<div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="menu.php" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">🍽️ Kelola Menu</a>
    <a href="transaksi.php" style="background: #17a2b8; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">➕ Transaksi Baru</a>
    <a href="laporan.php" style="background: #ffc107; color: #333; padding: 12px 25px; text-decoration: none; border-radius: 5px;">📊 Lihat Laporan</a>
</div>

<?php 
// Tutup semua statement dan koneksi
oci_free_statement($stmt_top);
oci_free_statement($stmt_stok);
oci_free_statement($stmt_recent);
oci_close($conn);
?>

<?php include 'template/footer.php'; ?>