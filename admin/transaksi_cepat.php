<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// admin/transaksi_cepat.php - VERSI PALING SEDERHANA
include 'template/header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil daftar menu
$query_menu = "SELECT m.id_menu, m.nama_menu, m.harga, k.nama_kategori 
               FROM menu m 
               JOIN kategori_menu k ON m.id_kategori = k.id 
               WHERE m.status = 'tersedia'
               ORDER BY k.nama_kategori, m.nama_menu";
$stmt_menu = oci_parse($conn, $query_menu);
oci_execute($stmt_menu);
$menus = [];
while ($row = oci_fetch_assoc($stmt_menu)) {
    $menus[] = $row;
}

// Ambil daftar meja
$query_meja = "SELECT id_meja, nomor_meja FROM meja ORDER BY nomor_meja";
$stmt_meja = oci_parse($conn, $query_meja);
oci_execute($stmt_meja);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    
    // Ambil data dari form
    $id_menu_array = $_POST['id_menu'] ?? [];
    $jumlah_array = $_POST['jumlah'] ?? [];
    
    // Kumpulkan item yang jumlahnya > 0
    $cart_items = [];
    for ($i = 0; $i < count($id_menu_array); $i++) {
        $id_menu = $id_menu_array[$i];
        $jumlah = intval($jumlah_array[$i]);
        
        if ($jumlah > 0) {
            // Cari data menu
            foreach ($menus as $menu) {
                if ($menu['ID_MENU'] == $id_menu) {
                    $cart_items[] = [
                        'id' => $menu['ID_MENU'],
                        'nama' => $menu['NAMA_MENU'],
                        'harga' => $menu['HARGA'],
                        'jumlah' => $jumlah
                    ];
                    break;
                }
            }
        }
    }
    
    $bayar = str_replace('.', '', $_POST['bayar'] ?? 0);
    $id_meja = $_POST['id_meja'] ?? 0;
    
    // VALIDASI
    if (empty($cart_items)) {
        $error = 'Pilih menu terlebih dahulu (isi jumlahnya)!';
    } elseif (!$id_meja) {
        $error = 'Pilih meja!';
    } else {
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['harga'] * $item['jumlah'];
        }
        
        $kembalian = $bayar - $total;
        
        if ($kembalian < 0) {
            $error = 'Uang kurang Rp ' . number_format(abs($kembalian), 0, ',', '.');
        } else {
            $no_transaksi = 'TRX-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            // Insert transaksi
            $query = "INSERT INTO transaksi (id_transaksi, no_transaksi, id_admin, total_harga, bayar, kembalian, id_meja, created_at) 
                      VALUES (seq_transaksi.NEXTVAL, :no, 1, :total, :bayar, :kembali, :meja, SYSDATE)";
            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ':no', $no_transaksi);
            oci_bind_by_name($stmt, ':total', $total);
            oci_bind_by_name($stmt, ':bayar', $bayar);
            oci_bind_by_name($stmt, ':kembali', $kembalian);
            oci_bind_by_name($stmt, ':meja', $id_meja);
            
            if (oci_execute($stmt)) {
                // Ambil ID transaksi
                $q_id = "SELECT seq_transaksi.CURRVAL as id FROM DUAL";
                $s_id = oci_parse($conn, $q_id);
                oci_execute($s_id);
                $id_transaksi = oci_fetch_assoc($s_id)['ID'];
                
                // Insert detail transaksi
                foreach ($cart_items as $item) {
                    $subtotal = $item['harga'] * $item['jumlah'];
                    $q_detail = "INSERT INTO detail_transaksi (id_detail, id_transaksi, id_menu, jumlah, harga_satuan, subtotal) 
                                VALUES (seq_detail_transaksi.NEXTVAL, :id_trans, :menu, :jml, :harga, :sub)";
                    $s_detail = oci_parse($conn, $q_detail);
                    oci_bind_by_name($s_detail, ':id_trans', $id_transaksi);
                    oci_bind_by_name($s_detail, ':menu', $item['id']);
                    oci_bind_by_name($s_detail, ':jml', $item['jumlah']);
                    oci_bind_by_name($s_detail, ':harga', $item['harga']);
                    oci_bind_by_name($s_detail, ':sub', $subtotal);
                    oci_execute($s_detail);
                    foreach ($items as $item) {
    $subtotal = $item['harga'] * $item['jumlah'];
    
                    // Insert detail
                    $q_detail = "INSERT INTO detail_transaksi (id_detail, id_transaksi, id_menu, jumlah, harga_satuan, subtotal) 
                                VALUES (seq_detail_transaksi.NEXTVAL, :id_trans, :menu, :jml, :harga, :sub)";
                    $s_detail = oci_parse($conn, $q_detail);
                    oci_bind_by_name($s_detail, ':id_trans', $id_transaksi);
                    oci_bind_by_name($s_detail, ':menu', $item['id']);
                    oci_bind_by_name($s_detail, ':jml', $item['jumlah']);
                    oci_bind_by_name($s_detail, ':harga', $item['harga']);
                    oci_bind_by_name($s_detail, ':sub', $subtotal);
                    oci_execute($s_detail);
                    oci_free_statement($s_detail);
                    
                    // ===== UPDATE STOK (TAMBAHKAN DI SINI) =====
                    $q_stok = "UPDATE menu SET stok = stok - :jml WHERE id_menu = :menu";
                    $s_stok = oci_parse($conn, $q_stok);
                    oci_bind_by_name($s_stok, ':jml', $item['jumlah']);
                    oci_bind_by_name($s_stok, ':menu', $item['id']);
                    oci_execute($s_stok);
                    oci_free_statement($s_stok);
                }
                }
                // Update stok menu (kurangi stok sesuai jumlah yang dipesan)
                    $query_update_stok = "UPDATE menu SET stok = stok - :jumlah WHERE id_menu = :id_menu";
                    $stmt_update_stok = oci_parse($conn, $query_update_stok);
                    oci_bind_by_name($stmt_update_stok, ':jumlah', $item['jumlah']);
                    oci_bind_by_name($stmt_update_stok, ':id_menu', $item['id']);
                    oci_execute($stmt_update_stok);
                    oci_free_statement($stmt_update_stok);
                
                $success = "Transaksi berhasil! No: $no_transaksi";
                
                // Reset form
                echo "<script>
                    alert('Transaksi berhasil! No: $no_transaksi');
                    window.location.href = 'transaksi_cepat.php';
                </script>";
                exit;
            } else {
                $e = oci_error($stmt);
                $error = "Gagal menyimpan: " . $e['message'];
            }
        }
    }
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: #f5f5f5; }
    .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
    h2 { margin-bottom: 20px; color: #333; }
    .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .card h3 { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #667eea; }
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; max-height: 450px; overflow-y: auto; }
    .menu-item { border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #f9f9f9; }
    .menu-item h4 { margin-bottom: 5px; }
    .menu-price { color: #28a745; font-weight: bold; margin: 8px 0; font-size: 1.1em; }
    .menu-item input { width: 80px; padding: 8px; text-align: center; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px; }
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th, .cart-table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
    .cart-table th { background: #f8f9fa; }
    .cart-total { font-weight: bold; font-size: 1.3em; text-align: right; margin: 15px 0; padding-top: 10px; border-top: 2px solid #667eea; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
    .btn-simpan { background: #28a745; color: white; padding: 14px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1.1em; font-weight: bold; }
    .btn-simpan:hover { background: #218838; }
    .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
    .kembalian { font-size: 1.3em; font-weight: bold; padding: 12px; background: #f8f9fa; border-radius: 5px; text-align: right; margin-top: 15px; }
    @media (max-width: 768px) { .two-column { grid-template-columns: 1fr; } }
</style>

<div class="container">
    <h2>🧾 Transaksi Baru</h2>
    
    <?php if ($error): ?>
        <div class="alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
    <div class="two-column">
        <!-- KOLOM KIRI: PILIH MENU -->
        <div class="card">
            <h3>📋 Pilih Menu</h3>
            <div class="menu-grid">
                <?php 
                $current_kategori = '';
                foreach ($menus as $menu):
                    if ($current_kategori != $menu['NAMA_KATEGORI']):
                        if ($current_kategori != '') echo '</div>';
                        $current_kategori = $menu['NAMA_KATEGORI'];
                        echo "<div style='font-weight: bold; color: #667eea; margin: 10px 0 5px; grid-column: 1/-1;'>$current_kategori</div>";
                        echo "<div style='grid-column: 1/-1; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;'>";
                    endif;
                ?>
                <div class="menu-item">
                    <h4><?php echo $menu['NAMA_MENU']; ?></h4>
                    <div class="menu-price">Rp <?php echo number_format($menu['HARGA'], 0, ',', '.'); ?></div>
                    <input type="number" name="jumlah[]" min="0" value="0" onchange="updateCart()" onkeyup="updateCart()">
                    <input type="hidden" name="id_menu[]" value="<?php echo $menu['ID_MENU']; ?>">
                </div>
                <?php endforeach; ?>
                <?php if ($current_kategori != '') echo '</div>'; ?>
            </div>
        </div>
        
        <!-- KOLOM KANAN: KERANJANG & FORM -->
        <div class="card">
            <h3>🛒 Keranjang Belanja</h3>
            
            <div class="form-group">
                <label>🪑 Pilih Meja</label>
                <select name="id_meja" required>
                    <option value="">-- Pilih Meja --</option>
                    <?php while ($meja = oci_fetch_assoc($stmt_meja)): ?>
                    <option value="<?php echo $meja['ID_MEJA']; ?>"><?php echo $meja['NOMOR_MEJA']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
                <table class="cart-table" id="cartTable">
                    <thead><tr><th>Menu</th><th>Harga</th><th>Jml</th><th>Subtotal</th></tr></thead>
                    <tbody id="cartBody">
                        <tr><td colspan="4" style="text-align: center;">Isi jumlah menu di kolom kiri</td></tr>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-total" id="cartTotal">Total: Rp 0</div>
            
            <div class="form-group">
                <label>💰 Uang Bayar</label>
                <input type="text" name="bayar" id="bayar" onkeyup="hitungKembalian()" placeholder="Masukkan jumlah uang">
            </div>
            
            <div class="kembalian" id="kembalianDisplay">Kembalian: Rp 0</div>
            
            <button type="submit" name="simpan" class="btn-simpan">✅ Simpan Transaksi</button>
        </div>
    </div>
    </form>
</div>

<script>
function updateCart() {
    let jumlahInputs = document.querySelectorAll('input[name="jumlah[]"]');
    let tbody = document.getElementById('cartBody');
    let total = 0;
    let html = '';
    
    for (let i = 0; i < jumlahInputs.length; i++) {
        let jml = parseInt(jumlahInputs[i].value) || 0;
        if (jml > 0) {
            let menuItem = jumlahInputs[i].closest('.menu-item');
            let nama = menuItem.querySelector('h4').innerText;
            let hargaText = menuItem.querySelector('.menu-price').innerText;
            let harga = parseInt(hargaText.replace(/[^0-9]/g, '')) || 0;
            let subtotal = harga * jml;
            total += subtotal;
            html += `<tr>
                <td>${nama}</td>
                <td>Rp ${harga.toLocaleString('id-ID')}</td>
                <td>${jml}</td>
                <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
            </tr>`;
        }
    }
    
    if (html === '') {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Isi jumlah menu di kolom kiri</td></tr>';
    } else {
        tbody.innerHTML = html;
    }
    document.getElementById('cartTotal').innerHTML = 'Total: Rp ' + total.toLocaleString('id-ID');
    hitungKembalian();
}

function hitungKembalian() {
    let totalText = document.getElementById('cartTotal').innerText;
    let total = parseInt(totalText.replace(/[^0-9]/g, '')) || 0;
    let bayar = parseInt(document.getElementById('bayar').value.replace(/\./g, '')) || 0;
    let kembalian = bayar - total;
    let el = document.getElementById('kembalianDisplay');
    
    if (kembalian >= 0) {
        el.innerHTML = 'Kembalian: Rp ' + kembalian.toLocaleString('id-ID');
        el.style.color = '#28a745';
    } else {
        el.innerHTML = 'Kembalian: Rp ' + Math.abs(kembalian).toLocaleString('id-ID') + ' (Kurang)';
        el.style.color = '#dc3545';
    }
}

// Event listener untuk semua input jumlah
document.querySelectorAll('input[name="jumlah[]"]').forEach(input => {
    input.addEventListener('input', updateCart);
    input.addEventListener('change', updateCart);
});

// Inisialisasi
updateCart();
</script>

<?php 
if (isset($stmt_menu)) oci_free_statement($stmt_menu);
if (isset($stmt_meja)) oci_free_statement($stmt_meja);
oci_close($conn);
include 'template/footer.php'; 
?>