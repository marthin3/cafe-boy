<?php 
include 'header.php';

// Koneksi database
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

$success = '';
$error = '';

// Proses saat form disubmit (hanya jika method POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Cek apakah data ada, jika tidak set jadi string kosong
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $isi_pesan = isset($_POST['pesan']) ? $_POST['pesan'] : '';
    
    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($isi_pesan)) {
        $error = "Nama, Email, dan Pesan wajib diisi!";
    } else {
        // Query insert ke database
        $query = "INSERT INTO pesan (id_pesan, nama, email, pesan, tanggal) 
                  VALUES (seq_pesan.NEXTVAL, :nama, :email, :pesan, SYSDATE)";
        
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':nama', $nama);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':pesan', $isi_pesan);
        
        if (oci_execute($stmt)) {
            $success = "✅ Pesan Anda berhasil dikirim! Kami akan segera merespon.";
        } else {
            $e = oci_error($stmt);
            $error = "❌ Gagal mengirim pesan: " . $e['message'];
        }
        oci_free_statement($stmt);
    }
}
?>

<div class="contact-container">
    <h1 style="text-align: center; color: #6f4e37;">Punya Kendala?</h1>
    <h1 style="text-align: center; color: #6f4e37; margin-bottom: 40px;">Hubungi Kami</h1>
    
    <!-- Tampilkan notifikasi -->
    <?php if ($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="contact-grid">
        <div class="contact-info">
            <div class="info-item">
                <span>📍</span>
                <div>
                    <h3>Alamat</h3>
                    <p>Jl Pembangunan No.114 USU</p>
                </div>
            </div>
            <div class="info-item">
                <span>📞</span>
                <div>
                    <h3>Telepon</h3>
                    <p>0821-7451-4759</p>
                </div>
            </div>
            <div class="info-item">
                <span>📧</span>
                <div>
                    <h3>Email</h3>
                    <p>marthinlubis27@gmail.com</p>
                </div>
            </div>
            <div class="info-item">
                <span>⏰</span>
                <div>
                    <h3>Jam Buka</h3>
                    <p>Senin - Minggu: 08:00 - 00:00</p>
                </div>
            </div>
        </div>
        
        <div class="contact-form">
            <h3>Kirim Pesan</h3>
            <form method="POST">
                <input type="text" name="nama" placeholder="Nama Anda" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="pesan" rows="5" placeholder="Pesan..." required></textarea>
                <button type="submit">Kirim</button>
            </form>
        </div>
    </div>
</div>

<style>
    .contact-container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
    }
    .contact-container h1 {
        text-align: center;
        color: #6f4e37;
        margin-bottom: 40px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid #c3e6cb;
    }
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid #f5c6cb;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
    .info-item {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        align-items: center;
    }
    .info-item span {
        font-size: 30px;
    }
    .info-item h3 {
        margin: 0 0 5px;
        color: #6f4e37;
    }
    .contact-form {
        background: #f9f5f0;
        padding: 25px;
        border-radius: 15px;
    }
    .contact-form input, .contact-form textarea {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .contact-form input:focus, .contact-form textarea:focus {
        outline: none;
        border-color: #6f4e37;
    }
    .contact-form button {
        background: #3c1b85;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
    }
    .contact-form button:hover {
        background: #8b5e3c;
    }
    @media (max-width: 700px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php 
oci_close($conn);
include 'footer.php'; 
?>