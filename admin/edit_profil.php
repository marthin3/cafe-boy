<?php
// admin/edit_profil.php
include 'template/header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

$id_admin = $_SESSION['user_id'];

// Ambil data admin
$query = "SELECT * FROM admin WHERE id_admin = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_admin);
oci_execute($stmt);
$admin = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $foto_lama = $admin['FOTO'];
    $foto = $foto_lama;
    
    // Upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/cafe_boy/uploads/profile/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed)) {
            $foto_baru = time() . '_profil.' . $file_extension;
            $target_file = $target_dir . $foto_baru;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Hapus foto lama
                if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
                    unlink($target_dir . $foto_lama);
                }
                $foto = $foto_baru;
            } else {
                $error = "Gagal upload foto";
            }
        } else {
            $error = "Format harus JPG/PNG/GIF";
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE admin SET nama_lengkap = :nama, foto = :foto WHERE id_admin = :id";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':nama', $nama_lengkap);
        oci_bind_by_name($stmt, ':foto', $foto);
        oci_bind_by_name($stmt, ':id', $id_admin);
        
        if (oci_execute($stmt)) {
            $success = "Profil berhasil diupdate!";
            $_SESSION['nama'] = $nama_lengkap;
            // Refresh data
            $admin['NAMA_LENGKAP'] = $nama_lengkap;
            $admin['FOTO'] = $foto;
        } else {
            $error = "Gagal update database";
        }
        oci_free_statement($stmt);
    }
}
?>

<style>
    .profile-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; }
    .profile-preview { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; display: block; border: 4px solid #667eea; }
    .preview-placeholder { width: 150px; height: 150px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 4px solid #667eea; font-size: 50px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
    .btn { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; }
    .btn-secondary { background: #6c757d; }
    .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
</style>

<div class="container">
    <h2>✏️ Edit Profil Admin</h2>
    
    <?php if ($success): ?>
        <div class="alert-success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
        <?php if ($admin['FOTO']): ?>
            <img src="../uploads/profile/<?php echo $admin['FOTO']; ?>" class="profile-preview" id="preview">
        <?php else: ?>
            <div class="preview-placeholder" id="previewPlaceholder">👤</div>
            <img class="profile-preview" id="preview" style="display: none;">
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?php echo $admin['NAMA_LENGKAP']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Foto Profil</label>
                <input type="file" name="foto" accept="image/*" onchange="previewImage(this)">
                <small style="color: #666;">Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>
            
            <button type="submit" class="btn">💾 Simpan Profil</button>
            <a href="dashboard.php" class="btn btn-secondary">← Batal</a>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        let preview = document.getElementById('preview');
        let placeholder = document.getElementById('previewPlaceholder');
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php oci_close($conn); include 'template/footer.php'; ?>