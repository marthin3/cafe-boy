<?php
// user/tentang.php
$conn = oci_connect('system', '12345', 'lptpmrthn:1521/freepdb1');
if (!$conn) die("Koneksi database gagal");

$query = "SELECT nama_lengkap, foto FROM admin WHERE id_admin = 1";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$admin = oci_fetch_assoc($stmt);
oci_close($conn);
?>

<style>
    .about-container {
        max-width: 500px;
        margin: 0 auto;
        text-align: center;
        padding: 40px 20px;
    }
    /* FOTO KOTAK (BUKAN BULAT) */
    .profile-photo {
        width: 200px;
        height: 200px;
        object-fit: cover;
        object-position: 50% 10%; 
        border: 4px solid #667eea;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 15px; /* sedikit lengkung, bisa diubah jadi 0 jika mau kotak banget */
    }
    .profile-placeholder {
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 60px;
        color: white;
        border-radius: 15px;
    }
    .about-name {
        font-size: 28px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .about-role {
        color: #667eea;
        font-size: 18px;
        margin-bottom: 25px;
    }
    
    /* SOSIAL MEDIA */
    .social-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }
    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #f0f2f5;
        color: #333;
        font-size: 22px;
        text-decoration: none;
        transition: all 0.3s;
    }
    .social-icon:hover {
        transform: translateY(-3px);
    }
    .social-icon.instagram:hover { background: #e4405f; color: white; }
    .social-icon.whatsapp:hover { background: #25D366; color: white; }
    .social-icon.facebook:hover { background: #1877f2; color: white; }
    .social-icon.email:hover { background: #ea4335; color: white; }
    .social-icon.github:hover { background: #333; color: white; }
    
    .about-desc {
        margin-top: 30px;
        color: #666;
        line-height: 1.6;
    }
</style>

<div class="about-container">
    <?php if (!empty($admin['FOTO'])): ?>
        <img src="/cafe_boy/uploads/profile/<?php echo $admin['FOTO']; ?>" class="profile-photo" alt="Profile">
    <?php else: ?>
        <div class="profile-placeholder">👤</div>
    <?php endif; ?>
    <div class="about-name"><?php echo $admin['NAMA_LENGKAP'] ?? 'Marthin Lubis'; ?></div>
    <div class="about-role">Programmer</div>
    
   <!-- SOSIAL MEDIA -->
<div class="social-links">
    <a href="https://www.instagram.com/marthinlubis3?igsh=MXJ0OXdiOHJiZWRzaA==" target="_blank" class="social-icon instagram">
        <i class="fab fa-instagram"></i>
    </a>
    <a href="https://www.linkedin.com/in/marthin-lubis-851b97352" target="_blank" class="social-icon linkedin">
        <i class="fab fa-linkedin-in"></i>
    </a>
    <a href="https://github.com/marthin3" target="_blank" class="social-icon github">
        <i class="fab fa-github"></i>
    </a>
</div>
    
   </div>