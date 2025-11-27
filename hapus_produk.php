<?php
// hapus_produk.php
session_start();
require_once 'koneksi.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Ambil info gambar dulu sebelum dihapus datanya
    $stmt = $koneksi->prepare("SELECT main_image_url FROM equipment_products WHERE id_equipment = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // 2. Hapus Data dari Database
    $delete = $koneksi->prepare("DELETE FROM equipment_products WHERE id_equipment = ?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        // 3. Hapus File Fisik Gambar (Jika ada)
        if ($data && !empty($data['main_image_url'])) {
            if (file_exists($data['main_image_url'])) {
                unlink($data['main_image_url']); // Menghapus file dari folder uploads
            }
        }
        echo "<script>alert('Produk berhasil dihapus!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus produk. Mungkin produk ini sedang digunakan dalam transaksi.'); window.location='admin_produk.php';</script>";
    }
} else {
    header("Location: admin_produk.php");
}
?>