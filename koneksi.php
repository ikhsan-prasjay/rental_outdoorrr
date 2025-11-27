<?php
// koneksi.php

$host     = 'localhost';
$user     = 'root';
$password = ''; // Kosong jika Anda tidak mengatur password di Laragon
$db_name  = 'database_rental_outdoor'; // Pastikan namanya sesuai dengan database yang Anda buat

// Membuat koneksi
$koneksi = new mysqli($host, $user, $password, $db_name);

// Mengecek koneksi
if ($koneksi->connect_error) {
    // Error Handling: Tampilkan pesan error jika koneksi gagal
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Catatan: Koneksi akan ditutup otomatis di akhir skrip PHP, 
// atau secara eksplisit dengan $koneksi->close() di akhir file yang menggunakannya.
?>