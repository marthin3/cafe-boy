<?php
// admin/meja.php
include 'template/header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Proses tambah meja
if (isset($_POST['tambah'])) {
    $nomor = $_POST['nomor_meja'];
    $kapasitas = $_POST['kapasitas'];
    
    $query = "INSERT INTO meja (id_meja, nomor_meja, kapasitas) VALUES (seq_meja.NEXTVAL, :nomor, :kapasitas)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':nomor', $nomor);
    oci_bind_by_name($stmt, ':kapasitas', $kapasitas);
    oci_execute($stmt);
}

// Proses hapus meja
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM meja WHERE id_meja = :id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':id', $id);
    oci_execute($stmt);
}

// Ambil data meja
$query = "SELECT * FROM meja ORDER BY nomor_meja";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<h2>🪑 Manajemen Meja</h2>

<!-- Form Tambah Meja -->
<div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
    <h3>Tambah Meja Baru</h3>
    <form method="POST" style="display: flex; gap: 10px;">
        <input type="text" name="nomor_meja" placeholder="Nomor Meja (contoh: M6)" required>
        <input type="number" name="kapasitas" placeholder="Kapasitas" value="4" required>
        <button type="submit" name="tambah" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Tambah</button>
    </form>
</div>

<!-- Daftar Meja -->
<div style="background: white; padding: 20px; border-radius: 10px;">
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Meja</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($meja = oci_fetch_assoc($stmt)): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $meja['NOMOR_MEJA']; ?></td>
                <td><?php echo $meja['KAPASITAS']; ?> orang</td>
                <td>
                    <span style="background: <?php echo $meja['STATUS'] == 'tersedia' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $meja['STATUS'] == 'tersedia' ? '#155724' : '#721c24'; ?>; padding: 5px 10px; border-radius: 20px;">
                        <?php echo $meja['STATUS']; ?>
                    </span>
                </td>
                <td>
                    <a href="?hapus=<?php echo $meja['ID_MEJA']; ?>" onclick="return confirm('Hapus meja?')" style="color: #dc3545;">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php 
oci_free_statement($stmt);
oci_close($conn);
include 'template/footer.php'; 
?>