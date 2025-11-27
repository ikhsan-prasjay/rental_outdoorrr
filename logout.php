<?php
// logout.php
session_start(); 

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Alihkan ke halaman index
header("Location: index.php");
exit;
?>