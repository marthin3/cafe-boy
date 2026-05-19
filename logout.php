<?php
// logout.php - Hapus session dan redirect ke halaman login
session_start();
session_destroy();
header('Location: index.php');
exit;
?>