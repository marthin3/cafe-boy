<?php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Selesai</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; text-align: center; padding: 50px; }
        .success-box { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; }
        h1 { color: #28a745; }
        .btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>✅ Pesanan Berhasil!</h1>
        <p>Terima kasih, <?php echo $_SESSION['user_nama']; ?>. Pesanan Anda akan segera diproses.</p>
        <a href="menu.php" class="btn">Kembali ke Menu</a>
    </div>
</body>
</html>