    <?php
    include 'template/header.php';

    $conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
    if (!$conn) die("Koneksi database gagal");

    // Proses hapus pesan (tanpa alert) - PASTIKAN TIDAK ADA ECHO SEBELUM INI
    if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $query = "DELETE FROM pesan WHERE id_pesan = :id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':id', $id);
        oci_execute($stmt);
        oci_free_statement($stmt);
        // Redirect menggunakan JavaScript (alternatif karena header bermasalah)
        echo "<script>window.location.href='pesan_masuk.php';</script>";
        exit;
    }

    // Ambil semua pesan
    $query = "SELECT id_pesan, nama, email, pesan, 
                    TO_CHAR(tanggal, 'DD/MM/YYYY HH24:MI:SS') as tanggal_formatted 
            FROM pesan ORDER BY id_pesan DESC";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    ?>

    <style>
        .table-pesan {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-pesan th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .table-pesan td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .table-pesan tr:hover {
            background: #f8f9fa;
        }
        .btn-hapus {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dc3545;
            color: white;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-hapus:hover {
            background: #c82333;
            transform: scale(1.05);
        }
    </style>

    <h2>📩 Pesan Masuk dari Pelanggan</h2>

    <table class="table-pesan">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Pesan</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
            <tr>
                <td><?php echo $row['ID_PESAN']; ?></td>
                <td><?php echo htmlspecialchars($row['NAMA']); ?></td>
                <td><?php echo htmlspecialchars($row['EMAIL']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['PESAN'])); ?></td>
                <td><?php echo $row['TANGGAL_FORMATTED']; ?></td>
                <td>
                    <a href="?hapus=<?php echo $row['ID_PESAN']; ?>" class="btn-hapus" title="Hapus pesan" onclick="return confirm('Hapus pesan ini?')">
                        🗑️ 
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php 
    oci_free_statement($stmt);
    oci_close($conn);
    include 'template/footer.php'; 
    ?>