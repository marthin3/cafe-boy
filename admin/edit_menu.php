<?php
// admin/edit_menu.php
include 'template/header.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: menu.php');
    exit;
}

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil data menu
$query_menu = "SELECT * FROM menu WHERE id_menu = :id";
$stmt_menu = oci_parse($conn, $query_menu);
oci_bind_by_name($stmt_menu, ':id', $id);
oci_execute($stmt_menu);
$menu = oci_fetch_assoc($stmt_menu);
oci_free_statement($stmt_menu);

if (!$menu) {
    header('Location: menu.php');
    exit;
}

// Ambil data kategori
$query_kategori = "SELECT * FROM kategori_menu ORDER BY nama_kategori";
$stmt_kategori = oci_parse($conn, $query_kategori);
oci_execute($stmt_kategori);

$success = '';
$error = '';
$gambar_lama = $menu['GAMBAR'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = $_POST['id_kategori'];
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $harga = str_replace('.', '', $_POST['harga']);
    $status = $_POST['status'];
    $stok = $_POST['stok'];
    
    // ========== PROSES UPLOAD GAMBAR BARU ==========
    $gambar = $gambar_lama; // default pakai gambar lama
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/cafe_boy/uploads/menu/';
        
        // Buat folder kalau belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $nama_bersih = preg_replace('/[^a-zA-Z0-9]/', '_', $nama_menu);
        $gambar_baru = time() . '_' . $nama_bersih . '.' . $file_extension;
        $target_file = $target_dir . $gambar_baru;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_types)) {
            if ($_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    // Hapus gambar lama jika ada
                    if (!empty($gambar_lama) && file_exists($target_dir . $gambar_lama)) {
                        unlink($target_dir . $gambar_lama);
                    }
                    $gambar = $gambar_baru;
                } else {
                    $error = "Gagal mengupload gambar.";
                }
            } else {
                $error = "Ukuran file maksimal 2MB.";
            }
        } else {
            $error = "Tipe file harus JPG, JPEG, PNG, atau GIF.";
        }
    }
    // ================================================
    
    if (empty($error)) {
        $query = "UPDATE menu SET 
                  id_kategori = :id_kategori,
                  nama_menu = :nama_menu,
                  deskripsi = :deskripsi,
                  harga = :harga,
                  status = :status,
                  stok = :stok,
                  gambar = :gambar,
                  updated_at = SYSDATE
                  WHERE id_menu = :id";
        
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':id_kategori', $id_kategori);
        oci_bind_by_name($stmt, ':nama_menu', $nama_menu);
        oci_bind_by_name($stmt, ':deskripsi', $deskripsi);
        oci_bind_by_name($stmt, ':harga', $harga);
        oci_bind_by_name($stmt, ':status', $status);
        oci_bind_by_name($stmt, ':stok', $stok);
        oci_bind_by_name($stmt, ':gambar', $gambar);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (oci_execute($stmt)) {
            $success = 'Menu berhasil diupdate!';
            // Refresh data
            $stmt_menu = oci_parse($conn, $query_menu);
            oci_bind_by_name($stmt_menu, ':id', $id);
            oci_execute($stmt_menu);
            $menu = oci_fetch_assoc($stmt_menu);
            oci_free_statement($stmt_menu);
            $gambar_lama = $menu['GAMBAR'];
        } else {
            $e = oci_error($stmt);
            $error = 'Gagal mengupdate menu: ' . $e['message'];
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
    <title>Edit Menu</title>
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
            border-radius: 8px; 
            border: 2px solid #ddd;
            padding: 5px;
        }
        .current-image {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .file-info { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
   
    <div class="container">
        <h2>✏️ Edit Menu</h2>
        
       <?php if ($success): ?>
    <script>
        alert('Menu berhasil diupdate!');
        window.location.href = 'menu.php';
    </script>
<?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Kategori *</label>
                <select name="id_kategori" required>
                    <option value="">Pilih Kategori</option>
                    <?php 
                    oci_execute($stmt_kategori);
                    while ($k = oci_fetch_assoc($stmt_kategori)): 
                    ?>
                    <option value="<?php echo $k['ID']; ?>" <?php echo ($k['ID'] == $menu['ID_KATEGORI']) ? 'selected' : ''; ?>>
                        <?php echo $k['NAMA_KATEGORI']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nama Menu *</label>
                <input type="text" name="nama_menu" value="<?php echo htmlspecialchars($menu['NAMA_MENU']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="3"><?php echo htmlspecialchars($menu['DESKRIPSI']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Harga *</label>
                <input type="text" name="harga" value="<?php echo number_format($menu['HARGA'], 0, ',', '.'); ?>" required onkeyup="formatRupiah(this)">
            </div>
            
            <div class="form-group">
                <label>Stok *</label>
                <input type="number" name="stok" value="<?php echo $menu['STOK']; ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="tersedia" <?php echo ($menu['STATUS'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="habis" <?php echo ($menu['STATUS'] == 'habis') ? 'selected' : ''; ?>>Habis</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Gambar Menu (max 2MB, JPG/PNG/GIF)</label>
                
                <?php if ($menu['GAMBAR']): ?>
                <div class="current-image">
                    <strong>Gambar saat ini:</strong><br>
                    <img src="../uploads/menu/<?php echo $menu['GAMBAR']; ?>" class="preview-image">
                    <p style="margin-top: 5px; font-size: 12px;"><?php echo $menu['GAMBAR']; ?></p>
                </div>
                <?php endif; ?>
                
                <input type="file" name="gambar" id="gambar" accept="image/*" onchange="previewImage(this)">
                <div class="file-info">Kosongkan jika tidak ingin mengubah gambar</div>
                <img id="preview" class="preview-image" src="#" alt="Preview Gambar" style="display: none;">
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Update Menu</button>
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