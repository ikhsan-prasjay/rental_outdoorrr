<?php
// edit_produk.php
session_start();
require_once 'koneksi.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: admin_produk.php");
    exit();
}
$id = intval($_GET['id']);

// Ambil Data Lama
$stmt = $koneksi->prepare("SELECT * FROM equipment_products WHERE id_equipment = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Produk tidak ditemukan.";
    exit();
}

// PROSES UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga_per_hari'];
    $url_gambar = $data['main_image_url']; // Default gambar lama

    // Cek jika ada upload gambar BARU
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = strtolower(pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        $allowed = array("jpg", "jpeg", "png", "webp");
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                // Hapus gambar lama jika ada dan bukan placeholder
                if (!empty($data['main_image_url']) && file_exists($data['main_image_url'])) {
                    unlink($data['main_image_url']);
                }
                $url_gambar = $target_file; // Update path gambar
            }
        }
    }

    // Update Database
    $update = $koneksi->prepare("UPDATE equipment_products SET name=?, description=?, rate_per_day=?, main_image_url=? WHERE id_equipment=?");
    $update->bind_param("ssdsi", $nama, $deskripsi, $harga, $url_gambar, $id);

    if ($update->execute()) {
        echo "<script>alert('Produk berhasil diupdate!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal update.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f8; padding: 40px; }
        .container { background: white; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #f39c12; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #d35400; }
        label { font-weight: bold; font-size: 14px; }
        .preview { margin: 10px 0; width: 100px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Produk</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Nama Produk</label>
            <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($data['name']); ?>" required>

            <label>Harga Sewa (Rp)</label>
            <input type="number" name="harga_per_hari" value="<?php echo $data['rate_per_day']; ?>" required>

            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="5"><?php echo htmlspecialchars($data['description']); ?></textarea>

            <label>Ganti Foto (Kosongkan jika tidak ingin mengganti)</label><br>
            <?php if(!empty($data['main_image_url'])): ?>
                <img src="<?php echo $data['main_image_url']; ?>" class="preview">
            <?php endif; ?>
            <input type="file" name="foto_produk">

            <button type="submit">Simpan Perubahan</button>
            <a href="admin_produk.php" style="display:block; text-align:center; margin-top:15px; color:#555; text-decoration:none;">Batal</a>
        </form>
    </div>
</body>
</html>