<?php 
include 'header.php'; 
?>

<div class="home-container">
    <div class="hero">
        <h1>☕ Selamat Datang di Cafe Boy</h1>
        <p>Tempat nongkrong asik dengan kopi dan makanan enak</p>
        <a href="menu.php" class="btn-order">Lihat Menu →</a>
    </div>

    <div class="features">
        <div class="feature">
            <span class="icon">☕</span>
            <h3>Kopi Berkualitas</h3>
            <p>Bijian kopi pilihan dari berbagai daerah</p>
        </div>
        <div class="feature">
            <span class="icon">🍰</span>
            <h3>Makanan Lezat</h3>
            <p>Beragam menu makanan dan camilan</p>
        </div>
        <div class="feature">
            <span class="icon">📶</span>
            <h3>Free WiFi</h3>
            <p>Nyaman untuk bekerja atau nongkrong</p>
        </div>
    </div>
</div>

<style>
    .home-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .hero {
        background: linear-gradient(135deg, #6f4e37, #8b5e3c);
        color: white;
        text-align: center;
        padding: 80px 20px;
        border-radius: 20px;
        margin: 20px;
    }
    .hero h1 {
        font-size: 2.5em;
        margin-bottom: 20px;
    }
    .hero p {
        font-size: 1.2em;
        margin-bottom: 30px;
    }
    .btn-order {
        background: white;
        color: #6f4e37;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 30px;
        font-weight: bold;
    }
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        padding: 50px 20px;
        text-align: center;
    }
    .feature .icon {
        font-size: 50px;
    }
    .feature h3 {
        margin: 15px 0;
        color: #6f4e37;
    }
</style>

<?php include 'footer.php'; ?>