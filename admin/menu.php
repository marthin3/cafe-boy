<?php
// admin/menu.php
include 'template/header.php';

// Koneksi database
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil data menu dengan kategori dan GAMBAR
$query = "SELECT m.id_menu, m.nama_menu, k.nama_kategori, m.harga, m.status, m.stok, m.gambar
          FROM menu m 
          JOIN kategori_menu k ON m.id_kategori = k.id 
          ORDER BY k.nama_kategori, m.nama_menu";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<h2 style="margin-bottom: 20px;">🍽️ Daftar Menu</h2>

<div style="margin-bottom: 20px;">
    <a href="tambah_menu.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">➕ Tambah Menu Baru</a>
    
</div>

<table style="width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <thead style="background: #667eea; color: white;">
        <tr>
            <th style="padding: 15px; text-align: left;">No</th>
            <th style="padding: 15px; text-align: left;">Gambar</th>
            <th style="padding: 15px; text-align: left;">Nama Menu</th>
            <th style="padding: 15px; text-align: left;">Kategori</th>
            <th style="padding: 15px; text-align: left;">Harga</th>
            <th style="padding: 15px; text-align: left;">Status</th>
            <th style="padding: 15px; text-align: left;">Stok</th>
            <th style="padding: 15px; text-align: left;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;  // ← INI PENTING! VARIABLE $no DIBERI NILAI AWAL
        while ($row = oci_fetch_assoc($stmt)): 
        ?>
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
            <td style="padding: 12px 15px;">
                <?php if (!empty($row['GAMBAR'])): ?>
                    <img src="../uploads/menu/<?php echo $row['GAMBAR']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                <?php else: ?>
                    <span style="color: #999;">Tidak ada</span>
                <?php endif; ?>
            </td>
            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['NAMA_MENU']); ?></td>
            <td style="padding: 12px 15px;"><?php echo $row['NAMA_KATEGORI']; ?></td>
            <td style="padding: 12px 15px;">Rp <?php echo number_format($row['HARGA'], 0, ',', '.'); ?></td>
            <td style="padding: 12px 15px;">
                <span style="background: <?php echo $row['STATUS'] == 'tersedia' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $row['STATUS'] == 'tersedia' ? '#155724' : '#721c24'; ?>; padding: 5px 10px; border-radius: 20px; font-size: 0.85em;">
                    <?php echo $row['STATUS']; ?>
                </span>
            </td>
            <td style="padding: 12px 15px;"><?php echo $row['STOK']; ?></td>
            <td style="padding: 12px 15px;">
                <a href="edit_menu.php?id=<?php echo $row['ID_MENU']; ?>" style="background: #ffc107; color: #333; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;">Edit</a>
                <a href="hapus_menu.php?id=<?php echo $row['ID_MENU']; ?>" style="background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php oci_free_statement($stmt); ?>
    </tbody>
</table>

<?php oci_close($conn); ?>
<?php include 'template/footer.php'; ?>