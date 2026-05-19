<?php
// admin/tambah_menu.php
include 'template/header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) {
    die("Koneksi database gagal");
}

// Ambil data kategori
$query_kategori = "SELECT * FROM kategori_menu ORDER BY nama_kategori";
$stmt_kategori = oci_parse($conn, $query_kategori);
oci_execute($stmt_kategori);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = $_POST['id_kategori'];
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $harga = str_replace('.', '', $_POST['harga']);
    $status = $_POST['status'];
    $stok = $_POST['stok'];
    
    // ========== PROSES UPLOAD GAMBAR ==========
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        
        // Tentukan folder tujuan
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/cafe_boy/uploads/menu/';
        
        // Buat folder kalau belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Ambil ekstensi file
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        
        // Buat nama file unik (pakai timestamp + nama menu)
        $nama_bersih = preg_replace('/[^a-zA-Z0-9]/', '_', $nama_menu);
        $gambar = time() . '_' . $nama_bersih . '.' . $file_extension;
        
        $target_file = $target_dir . $gambar;
        
        // Cek tipe file yang diizinkan
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_types)) {
            
            // Cek ukuran file (max 2MB)
            if ($_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    // Sukses upload, $gambar sudah berisi nama file
                } else {
                    $error = "Gagal mengupload file. Coba lagi.";
                }
            } else {
                $error = "Ukuran file maksimal 2MB";
            }
        } else {
            $error = "Tipe file harus JPG, JPEG, PNG, atau GIF";
        }
    }
    // ============================================
    
    // Jika tidak ada error upload, lanjut simpan ke database
    if (empty($error)) {
        $query = "INSERT INTO menu (id_menu, id_kategori, nama_menu, deskripsi, harga, status, stok, gambar, created_at) 
                  VALUES (seq_menu.NEXTVAL, :id_kategori, :nama_menu, :deskripsi, :harga, :status, :stok, :gambar, SYSDATE)";
        
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':id_kategori', $id_kategori);
        oci_bind_by_name($stmt, ':nama_menu', $nama_menu);
        oci_bind_by_name($stmt, ':deskripsi', $deskripsi);
        oci_bind_by_name($stmt, ':harga', $harga);
        oci_bind_by_name($stmt, ':status', $status);
        oci_bind_by_name($stmt, ':stok', $stok);
        oci_bind_by_name($stmt, ':gambar', $gambar);
        
        if (oci_execute($stmt)) {
            $success = 'Menu berhasil ditambahkan!';
        } else {
            $e = oci_error($stmt);
            $error = 'Gagal menyimpan ke database: ' . $e['message'];
        }
        oci_free_statement($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group input[type="file"] { padding: 8px; background: #f8f9fa; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 12px 30px; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-primary { background: #28a745; color: white; }
        .btn-primary:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .preview-image { 
            max-width: 200px; 
            max-height: 200px; 
            margin-top: 10px; 
            display: none; 
            border-radius: 8px; 
            border: 2px solid #ddd;
            padding: 5px;
        }
        .file-info { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    
    
    <div class="container">
        <h2>➕ Tambah Menu Baru</h2>
        
        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Kategori *</label>
                <select name="id_kategori" required>
                    <option value="">Pilih Kategori</option>
                    <?php while ($k = oci_fetch_assoc($stmt_kategori)): ?>
                    <option value="<?php echo $k['ID']; ?>"><?php echo $k['NAMA_KATEGORI']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nama Menu *</label>
                <input type="text" name="nama_menu" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Deskripsi menu..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Harga *</label>
                <input type="text" name="harga" required onkeyup="formatRupiah(this)" placeholder="Contoh: 25000">
            </div>
            
            <div class="form-group">
                <label>Stok *</label>
                <input type="number" name="stok" value="10" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="tersedia">Tersedia</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Gambar Menu (opsional, max 2MB, JPG/PNG/GIF)</label>
                <input type="file" name="gambar" id="gambar" accept="image/*" onchange="previewImage(this)">
                <div class="file-info">File akan disimpan dengan nama unik</div>
                <img id="preview" class="preview-image" src="#" alt="Preview Gambar">
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Simpan Menu</button>
                <a href="menu.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    
    <script>
        function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            input.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }
        
        function previewImage(input) {
            let preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
    
    <?php 
    oci_free_statement($stmt_kategori); 
    oci_close($conn); 
    include 'template/footer.php'; 
    ?>
</body>
</html>