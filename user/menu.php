<?php
// user/menu.php
include 'header.php';

$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

// Ambil menu
$query = "SELECT m.id_menu, m.nama_menu, m.deskripsi, m.harga, m.gambar, k.nama_kategori 
          FROM menu m 
          JOIN kategori_menu k ON m.id_kategori = k.id 
          WHERE m.status = 'tersedia'
          ORDER BY k.nama_kategori, m.nama_menu";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!-- Konten Halaman -->
<h2>Selamat datang di Cafe Boy, <?php echo $_SESSION['user_nama']; ?>! 👋</h2>
<p style="margin-bottom: 30px; color: #666;">Silakan pilih menu favorit Anda.</p>

<!-- Grid Menu - TAMBAHKAN CLASS menu-grid-user -->
<div class="menu-grid-user" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <?php while ($menu = oci_fetch_assoc($stmt)): ?>
    <div class="menu-card-user" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <?php if ($menu['GAMBAR']): ?>
            <img src="../uploads/menu/<?php echo $menu['GAMBAR']; ?>" style="width: 100%; height: 180px; object-fit: cover;">
        <?php else: ?>
            <div style="width: 100%; height: 180px; background: #eee; display: flex; align-items: center; justify-content: center; color: #999;">🍽️</div>
        <?php endif; ?>
        <div class="menu-card-content" style="padding: 15px;">
            <h3><?php echo $menu['NAMA_MENU']; ?></h3>
            <p style="color: #666; margin: 10px 0;"><?php echo $menu['DESKRIPSI'] ?? 'Menu spesial'; ?></p>
            <p class="menu-price" style="font-size: 1.3em; color: #28a745; font-weight: bold;">Rp <?php echo number_format($menu['HARGA'], 0, ',', '.'); ?></p>
            <button class="btn-order" onclick="tambahKeKeranjang(<?php echo $menu['ID_MENU']; ?>, '<?php echo $menu['NAMA_MENU']; ?>', <?php echo $menu['HARGA']; ?>)" 
                    style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                + Tambah ke Keranjang
            </button>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script>
// Data keranjang
let keranjang = JSON.parse(sessionStorage.getItem('keranjang')) || [];

// Fungsi hapus item dari keranjang berdasarkan id
function hapusItemKeranjang(id) {
    let index = keranjang.findIndex(item => item.id === id);
    if (index !== -1) {
        keranjang.splice(index, 1);
        sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
        updateSemuaKeranjang();
        // alert('Item dihapus dari keranjang!');
    }
}
    
    keranjang.forEach(item => {
        let subtotal = item.harga * item.jumlah;
        total += subtotal;
        count += item.jumlah;
       dropdownHtml .= `
    <div class="cart-dropdown-item">
        <div>
            <strong>${item.nama}</strong> x${item.jumlah}
            <div class="item-price">Rp ${subtotal.toLocaleString('id-ID')}</div>
        </div>
        <button onclick="hapusItemKeranjang(${item.id})" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">Hapus</button>
    </div>
`;
    });
    
    // Update badge
    if (document.getElementById('cartBadge')) {
        document.getElementById('cartBadge').innerText = count;
    }
    
    // Update total di navbar
    if (document.getElementById('cartTotalNavbar')) {
        document.getElementById('cartTotalNavbar').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
    
    // Update dropdown
    if (document.getElementById('cartDropdownItems')) {
        if (keranjang.length === 0) {
            document.getElementById('cartDropdownItems').innerHTML = '<p style="text-align: center; color: #999;">Keranjang kosong</p>';
        } else {
            document.getElementById('cartDropdownItems').innerHTML = dropdownHtml;
        }
    }
    if (document.getElementById('cartDropdownTotal')) {
        document.getElementById('cartDropdownTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
}

// Fungsi tambah ke keranjang
function tambahKeKeranjang(id, nama, harga) {
    let item = keranjang.find(i => i.id === id);
    if (item) {
        item.jumlah++;
    } else {
        keranjang.push({ id, nama, harga: parseInt(harga), jumlah: 1 });
    }
    sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
    updateSemuaKeranjang();
}

// Fungsi toggle dropdown
function toggleCart() {
    let dropdown = document.getElementById('cartDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Tutup dropdown jika klik di luar
window.onclick = function(event) {
    let dropdown = document.getElementById('cartDropdown');
    if (dropdown && !event.target.closest('.cart-icon')) {
        dropdown.classList.remove('show');
    }
}

// Load data saat halaman dimuat
window.onload = function() {
    updateSemuaKeranjang();
}
</script>

<?php 
oci_free_statement($stmt);
oci_close($conn);
include 'footer.php'; 
?>