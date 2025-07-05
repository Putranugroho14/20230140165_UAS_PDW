<?php
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
// Ubah "../login.php" menjadi "login.php" karena logout.php dan login.php berada di folder yang sama
header("Location: login.php");
exit;
?>