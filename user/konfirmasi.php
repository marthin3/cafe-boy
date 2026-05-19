<?php
include 'header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

$query_meja = "SELECT id_meja, nomor_meja FROM meja ORDER BY nomor_meja";
$stmt_meja = oci_parse($conn, $query_meja);
oci_execute($stmt_meja);

$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $items = json_decode($_POST['items'], true);
    $total = $_POST['total'];
    $id_meja = $_POST['id_meja'] ?? 0;
    
    if (empty($items)) $error = 'Tidak ada item yang dipesan';
    elseif (!$id_meja) $error = 'Pilih meja dulu!';
    else {
        $no_transaksi = 'TRX-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO transaksi (id_transaksi, no_transaksi, id_admin, total_harga, id_meja, created_at) 
                  VALUES (seq_transaksi.NEXTVAL, :no, 1, :total, :meja, SYSDATE)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':no', $no_transaksi);
        oci_bind_by_name($stmt, ':total', $total);
        oci_bind_by_name($stmt, ':meja', $id_meja);
        
        if (oci_execute($stmt)) {
            $q_id = "SELECT seq_transaksi.CURRVAL as id FROM DUAL";
            $s_id = oci_parse($conn, $q_id);
            oci_execute($s_id);
            $id_transaksi = oci_fetch_assoc($s_id)['ID'];
            
            foreach ($items as $item) {
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
                oci_free_statement($s_detail);
            }
            
            $pesan = "Pesanan berhasil! No: $no_transaksi";
            echo "<script>sessionStorage.removeItem('keranjang');</script>";
        } else $error = "Gagal menyimpan";
    }
}
?>

<style>
    .container { max-width: 800px; margin: 20px auto; padding: 0 15px; }
    h2 { text-align: center; color: #333; margin-bottom: 20px; }
    
    .order-summary {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .item-info {
        flex: 2;
    }
    
    .item-name {
        font-weight: bold;
    }
    
    .item-price {
        color: #666;
        font-size: 0.85rem;
    }
    
    .item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .qty-btn {
        background: #ddd;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .qty-btn.minus { background: #dc3545; color: white; }
    .qty-btn.plus { background: #28a745; color: white; }
    
    .item-subtotal {
        min-width: 100px;
        text-align: right;
        font-weight: bold;
    }
    
    .btn-hapus {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
    }
    
    .total-row {
        text-align: right;
        font-weight: bold;
        font-size: 1.2em;
        padding: 15px;
        border-top: 2px solid #667eea;
        margin-top: 10px;
    }
    
    .meja-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
        margin: 20px 0;
    }
    
    .meja-item {
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }
    
    .meja-item:hover {
        border-color: #667eea;
        background: #f0f3ff;
    }
    
    .meja-item.selected {
        border-color: #28a745;
        background: #d4edda;
        color: #155724;
    }
    
    .btn-konfirmasi {
        background: #28a745;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        margin-right: 10px;
    }
    
    .btn-batal {
        background: #6c757d;
        color: white;
        padding: 12px 25px;
        text-decoration: none;
        border-radius: 8px;
        display: inline-block;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin: 20px 0;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 600px) {
        .meja-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        .item-row {
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn-hapus {
            margin-left: auto;
        }
    }
    
    @media (max-width: 400px) {
        .meja-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<div class="container">
    <h2>📋 Konfirmasi Pesanan</h2>
    
    <?php if ($pesan): ?>
        <div class="alert-success">
            <h3>✅ Pesanan Berhasil!</h3>
            <p><?php echo $pesan; ?></p>
            <p>Terima kasih, pesanan Anda sedang kami siapkan! ❤️</p>
            <a href="index.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">Kembali ke Menu</a>
        </div>
    <?php else: ?>
    
    <div id="orderSummary" class="order-summary"></div>
    
    <h3>🪑 Pilih Meja</h3>
    <div class="meja-grid" id="mejaGrid">
        <?php while ($meja = oci_fetch_assoc($stmt_meja)): ?>
        <div class="meja-item" data-id="<?php echo $meja['ID_MEJA']; ?>" onclick="pilihMeja(this)">
            <?php echo $meja['NOMOR_MEJA']; ?>
        </div>
        <?php endwhile; ?>
    </div>
    
    <form method="POST" id="formOrder">
        <input type="hidden" name="items" id="itemsInput">
        <input type="hidden" name="total" id="totalInput">
        <input type="hidden" name="id_meja" id="mejaInput">
        
        <?php if ($error): ?>
            <div class="alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <button type="submit" class="btn-konfirmasi">✅ Konfirmasi Pesanan</button>
            <a href="index.php" class="btn-batal">← Batal</a>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
let keranjang = JSON.parse(sessionStorage.getItem('keranjang')) || [];
let total = 0;
let selectedMeja = null;

if (keranjang.length === 0 && !<?php echo $pesan ? 'true' : 'false'; ?>) {
    window.location.href = 'index.php';
}

let html = '';
keranjang.forEach(item => {
    let subtotal = item.harga * item.jumlah;
    total += subtotal;
    html += `
        <div class="item-row">
            <div class="item-info">
                <div class="item-name">${item.nama}</div>
                <div class="item-price">Rp ${item.harga.toLocaleString('id-ID')}</div>
            </div>
            <div class="item-quantity">
                <button class="qty-btn minus" onclick="ubahJumlah(${item.id}, -1)">-</button>
                <span style="min-width: 35px; text-align: center;">${item.jumlah}</span>
                <button class="qty-btn plus" onclick="ubahJumlah(${item.id}, 1)">+</button>
            </div>
            <div class="item-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
            <button class="btn-hapus" onclick="hapusItem(${item.id})">Hapus</button>
        </div>
    `;
});
html += `<div class="total-row">Total: Rp ${total.toLocaleString('id-ID')}</div>`;

document.getElementById('orderSummary').innerHTML = html;
document.getElementById('itemsInput').value = JSON.stringify(keranjang);
document.getElementById('totalInput').value = total;

function pilihMeja(element) {
    document.querySelectorAll('.meja-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('mejaInput').value = element.dataset.id;
    selectedMeja = element.dataset.id;
}

function ubahJumlah(id, delta) {
    let cart = JSON.parse(sessionStorage.getItem('keranjang')) || [];
    let item = cart.find(i => i.id === id);
    if (item) {
        let newJumlah = item.jumlah + delta;
        if (newJumlah < 1) {
            cart = cart.filter(i => i.id !== id);
        } else {
            item.jumlah = newJumlah;
        }
        sessionStorage.setItem('keranjang', JSON.stringify(cart));
        location.reload();
    }
}

function hapusItem(id) {
    let cart = JSON.parse(sessionStorage.getItem('keranjang')) || [];
    cart = cart.filter(item => item.id !== id);
    sessionStorage.setItem('keranjang', JSON.stringify(cart));
    location.reload();
}

document.getElementById('formOrder').addEventListener('submit', function(e) {
    if (!selectedMeja) {
        e.preventDefault();
        alert('Pilih meja terlebih dahulu!');
    }
});
</script>

<?php 
oci_free_statement($stmt_meja);
oci_close($conn);
include 'footer.php'; 
?>