
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

<style>
    html { scroll-behavior: smooth; }
    section { 
        scroll-margin-top: 120px; 
        padding: 60px 0; 
        min-height: 100vh;
    }
    
    /* HOME STYLE */
    .hero {
        background: linear-gradient(135deg, #6f4e37, #8b5e3c);
        color: white;
        text-align: center;
        padding: 80px 20px;
        border-radius: 20px;
        margin: 20px;
    }
    .hero h1 { font-size: 2.5em; margin-bottom: 20px; }
    .hero p { font-size: 1.2em; margin-bottom: 30px; }
    .btn-order {
        background: white;
        color: #6f4e37;
        padding: 12px 30px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
    }
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        padding: 50px 20px;
        text-align: center;
    }
    .feature .icon { font-size: 50px; }
    .feature h3 { margin: 15px 0; color: #6f4e37; }
    
    /* MENU STYLE */
   .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 30px;
    align-items: stretch; /* Ini penting! Memastikan semua kartu memiliki tinggi yang sama */
}

    .menu-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    height: 100%; /* Memastikan kartu memenuhi tinggi grid */
}
    .menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.12);
}
    .menu-card .gambar-wrapper {
    width: 100%;
    padding-top: 75%; /* Rasio 4:3, kamu bisa ubah ke 100% untuk persegi */
    position: relative;
    overflow: hidden;
    background-color: #f5f5f5;
}
    .menu-card .gambar-wrapper img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain; /* GAMBAR UTUH, TIDAK KEPOTONG */
    background: #f5f5f5;
    padding: 8px;
}
    .menu-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px 10px 0 0;
}
    .menu-card-content { padding: 15px; }
    .menu-price {
        font-size: 1.3em;
        color: #28a745;
        font-weight: bold;
        margin: 10px 0;
    }
   .btn-tambah {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
}

.btn-tambah:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}
    
    /* CONTACT STYLE */
    .contact-container { max-width: 1000px; margin: 0 auto; }
    .contact-container h1 { text-align: center; color: #6f4e37; margin-bottom: 40px; }
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
    .info-item span { font-size: 30px; }
    .info-item h3 { margin: 0 0 5px; color: #6f4e37; }
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
    .contact-form button {
        background: #6f4e37;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
    }
    
    /* TENTANG STYLE */
    .about-container { max-width: 1000px; margin: 0 auto; text-align: center; }
    .about-container h1 { color: #6f4e37; margin-bottom: 40px; }
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
    }
    .team-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .team-icon { font-size: 50px; margin-bottom: 15px; }
    .team-card .role { color: #6f4e37; }
    .alert-success {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
    
    @media (max-width: 768px) {
        .contact-grid { grid-template-columns: 1fr; }
        .menu-grid { grid-template-columns: 1fr 1fr; }
        .hero h1 { font-size: 1.8em; }
    }
    @media (max-width: 480px) {
        .menu-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- SECTION HOME -->
<section id="home">
    <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center; margin: 40px 20px;">
        
        <!-- KOLOM KIRI: HERO + GAMBAR CAFE -->
        <div style="flex: 1; min-width: 280px; text-align: center;">
            <!-- HERO -->
            <div style="margin-bottom: 30px;">
                <h1 style="font-size: 2em; color: #333; margin-bottom: 10px;">☕ Selamat Datang di Cafe Boy</h1>
                <p style="font-size: 1em; color: #666; margin-bottom: 20px;">Tempat nongkrong asik dengan kopi dan makanan enak</p>
                <a href="#menu" style="background: #667eea; color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: bold; display: inline-block;">Lihat Menu →</a>
            </div>
            
            <!-- GAMBAR CAFE -->
            <div>
                <img src="/cafe_boy/images/hero_cafe.png" alt="Cafe Boy" style="width: 100%; max-width: 300px; height: auto; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <p style="margin-top: 10px; color: #666; font-size: 0.9em;">Suasana nyaman di Cafe Boy</p>
            </div>
        </div>
        
        <!-- KOLOM KANAN: FEATURES -->
        <div style="flex: 1; min-width: 280px;">
            <div style="margin-bottom: 20px; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 35px;">☕</span>
                    <div>
                        <h3 style="margin: 0 0 5px;">Kopi Berkualitas</h3>
                        <p style="margin: 0; color: #666;">Bijian kopi pilihan dari berbagai daerah</p>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 20px; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 35px;">🍰</span>
                    <div>
                        <h3 style="margin: 0 0 5px;">Makanan Lezat</h3>
                        <p style="margin: 0; color: #666;">Beragam menu makanan dan camilan</p>
                    </div>
                </div>
            </div>
            
            <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 35px;">📶</span>
                    <div>
                        <h3 style="margin: 0 0 5px;">Free WiFi</h3>
                        <p style="margin: 0; color: #666;">Nyaman untuk bekerja atau nongkrong</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- SECTION MENU -->
<section id="menu">
    <div class="container">
        <h2>Selamat datang, <?php echo $_SESSION['user_nama']; ?>! 👋</h2>
        <p style="margin-bottom: 30px;">Silakan pilih menu favorit Anda.</p>
        <div class="menu-grid">
            <?php while ($menu = oci_fetch_assoc($stmt)): ?>
            <div class="menu-card">
    <div class="gambar-wrapper">
        <?php if ($menu['GAMBAR']): ?>
            <img src="../uploads/menu/<?php echo $menu['GAMBAR']; ?>" alt="<?php echo htmlspecialchars($menu['NAMA_MENU']); ?>">
        <?php else: ?>
            <div style="position: absolute; top:0; left:0; width:100%; height:100%; display: flex; align-items: center; justify-content: center; background:#f5f5f5;">🍽️</div>
        <?php endif; ?>
    </div>
    <div class="menu-card-content">
        <h3><?php echo htmlspecialchars($menu['NAMA_MENU']); ?></h3>
        <p class="menu-desc"><?php echo htmlspecialchars($menu['DESKRIPSI'] ?? 'Menu spesial'); ?></p>
        <div class="menu-price">Rp <?php echo number_format($menu['HARGA'], 0, ',', '.'); ?></div>
        <button class="btn-tambah" onclick="tambahKeKeranjang(<?php echo $menu['ID_MENU']; ?>, '<?php echo htmlspecialchars($menu['NAMA_MENU']); ?>', <?php echo $menu['HARGA']; ?>)">
            + Tambah ke Keranjang
            </button>
    </div>
</div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- SECTION CONTACT -->
<?php
// Proses form contact jika ada POST
$contact_success = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $conn2 = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
    if ($conn2) {
        $nama = trim($_POST['contact_nama'] ?? '');
        $email = trim($_POST['contact_email'] ?? '');
        $pesan = trim($_POST['contact_pesan'] ?? '');
        
        if (empty($nama) || empty($email) || empty($pesan)) {
            $contact_error = "Semua bidang harus diisi!";
        } else {
            $query = "INSERT INTO pesan (id_pesan, nama, email, pesan, tanggal) 
                      VALUES (seq_pesan.NEXTVAL, :nama, :email, :pesan, SYSDATE)";
            $stmt = oci_parse($conn2, $query);
            oci_bind_by_name($stmt, ':nama', $nama);
            oci_bind_by_name($stmt, ':email', $email);
            oci_bind_by_name($stmt, ':pesan', $pesan);
            
            if (oci_execute($stmt)) {
                $contact_success = "✅ Pesan berhasil dikirim!";
            } else {
                $contact_error = "❌ Gagal mengirim pesan";
            }
            oci_free_statement($stmt);
        }
        oci_close($conn2);
    } else {
        $contact_error = "Koneksi database gagal";
    }
}
?>

<section id="contact">
    <div class="contact-container">
        <h1>Punya Kendala?</h1>
        <h1>Hubungi Kami</h1>
        
            <?php if ($contact_success): ?>
    <div class="alert-success" id="contact_success_msg"><?php echo $contact_success; ?></div>
    <script>
        setTimeout(function() {
            var msg = document.getElementById('contact_success_msg');
            if (msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            }
        }, 3000); // 3 detik
    </script>
<?php endif; ?>
        <?php if ($contact_error): ?>
            <div class="alert-error"><?php echo $contact_error; ?></div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <div class="contact-info">
                <div class="info-item"><span>📍</span><div><h3>Alamat</h3><p>Jl Pembangunan No.114 USU</p></div></div>
                <div class="info-item"><span>📞</span><div><h3>Telepon</h3><p>0821-7451-4759</p></div></div>
                <div class="info-item"><span>📧</span><div><h3>Email</h3><p>marthinlubis27@gmail.com</p></div></div>
                <div class="info-item"><span>⏰</span><div><h3>Jam Buka</h3><p>Senin - Minggu: 08:00 - 00:00</p></div></div>
            </div>
            
            <div class="contact-form">
                <h3>Kirim Pesan</h3>
                <form method="POST" action="#contact">
    <input type="text" name="contact_nama" placeholder="Nama Anda" required>
    <input type="email" name="contact_email" placeholder="Email" required>
    <textarea name="contact_pesan" rows="5" placeholder="Pesan..." required></textarea>
    <button type="submit" name="contact_submit">Kirim Pesan</button>
</form>
            </div>
        </div>
    </div>
</section>

<!-- SECTION TENTANG -->
<section id="tentang">
    <?php include 'tentang.php'; ?>
</section>

<script>
let keranjang = JSON.parse(sessionStorage.getItem('keranjang')) || [];

function updateSemuaKeranjang() {
    let total = 0, count = 0, dropdownHtml = '';
    keranjang.forEach(item => {
        let subtotal = item.harga * item.jumlah;
        total += subtotal;
        count += item.jumlah;
        dropdownHtml += `<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
            <div><strong>${item.nama}</strong> x${item.jumlah}<br><small>Rp ${subtotal.toLocaleString('id-ID')}</small></div>
            <button onclick="hapusItemKeranjang(${item.id})" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:5px;">Hapus</button>
        </div>`;
    });
    if (document.getElementById('cartBadge')) document.getElementById('cartBadge').innerText = count;
    if (document.getElementById('cartTotalNavbar')) document.getElementById('cartTotalNavbar').innerText = 'Rp ' + total.toLocaleString('id-ID');
    if (document.getElementById('cartDropdownItems')) {
        if (keranjang.length === 0) document.getElementById('cartDropdownItems').innerHTML = '<p style="text-align:center; color:#999;">Keranjang kosong</p>';
        else document.getElementById('cartDropdownItems').innerHTML = dropdownHtml;
    }
    if (document.getElementById('cartDropdownTotal')) document.getElementById('cartDropdownTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function tambahKeKeranjang(id, nama, harga) {
    let item = keranjang.find(i => i.id === id);
    if (item) item.jumlah++;
    else keranjang.push({ id, nama, harga: parseInt(harga), jumlah: 1 });
    sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
    updateSemuaKeranjang();
    // alert(nama + ' ditambahkan!');
}

function hapusItemKeranjang(id) {
    keranjang = keranjang.filter(item => item.id !== id);
    sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
    updateSemuaKeranjang();
}

function toggleCart() {
    document.getElementById('cartDropdown')?.classList.toggle('show');
}
window.onclick = function(e) {
    let dropdown = document.getElementById('cartDropdown');
    if (dropdown && !e.target.closest('.cart-icon')) dropdown.classList.remove('show');
}

window.addEventListener('scroll', function() {
    let sections = document.querySelectorAll('section');
    let navLinks = document.querySelectorAll('.nav-menu a');
    let current = '';
    
    sections.forEach(section => {
        let sectionTop = section.offsetTop - 150;
        let sectionBottom = sectionTop + section.offsetHeight;
        if (window.scrollY >= sectionTop && window.scrollY < sectionBottom) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});

window.onload = function() { updateSemuaKeranjang(); }
</script>

<?php 
oci_free_statement($stmt);
oci_close($conn);
include 'footer.php'; 
?>