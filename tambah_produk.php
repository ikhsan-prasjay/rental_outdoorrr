<?php
// tambah_produk.php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pesan_sukses = $pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga_per_hari'];
    $stok_awal = intval($_POST['stok_awal']);
    $rent_type = $_POST['rent_type']; // Ambil tipe (item/package)
    $category_id = 1; 

    $url_gambar = "https://via.placeholder.com/600x400?text=No+Image"; 
    
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $_FILES["foto_produk"]["tmp_name"]);
        finfo_close($file_info);
        
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        
        if (in_array($mime_type, $allowed_mimes)) {
            $file_ext = strtolower(pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION));
            $new_file_name = time() . "_" . uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                $url_gambar = $target_file;
            } else {
                $pesan_error = "Gagal memindahkan file foto.";
            }
        } else {
            $pesan_error = "Format foto ditolak! Gunakan JPG, PNG, atau WEBP.";
        }
    }

    if (empty($pesan_error)) {
        if (empty($nama) || empty($harga) || $stok_awal < 1) {
            $pesan_error = "Nama, Harga, dan Stok Awal wajib diisi!";
        } else {
            $koneksi->begin_transaction();
            try {
                // INSERT DENGAN rent_type
                $stmt = $koneksi->prepare("INSERT INTO equipment_products (category_id, rent_type, name, description, rate_per_day, main_image_url) VALUES (?, ?, ?, ?, ?, ?)");
                
                // [PERBAIKAN ERROR ADA DI BARIS INI] 
                // Diubah dari "issds" (5) menjadi "isssds" (6) menyesuaikan jumlah variabel
                $stmt->bind_param("isssds", $category_id, $rent_type, $nama, $deskripsi, $harga, $url_gambar);
                
                $stmt->execute();
                $new_product_id = $koneksi->insert_id; 
                $stmt->close();

                $stmt_stok = $koneksi->prepare("INSERT INTO equipment_items (product_id, status) VALUES (?, 'available')");
                for ($i = 0; $i < $stok_awal; $i++) {
                    $stmt_stok->bind_param("i", $new_product_id);
                    $stmt_stok->execute();
                }
                $stmt_stok->close();

                $koneksi->commit();
                $pesan_sukses = "Berhasil! Produk ($rent_type) & $stok_awal stok masuk gudang.";

            } catch (Exception $e) {
                $koneksi->rollback();
                $pesan_error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f6f8; color: #333; padding: 40px 20px; }
        .container { background: white; max-width: 750px; margin: 0 auto; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #444; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 15px; border: 1px solid #e1e8ed; border-radius: 12px; font-size: 14px; background: #f9fafb; transition: 0.3s; }
        input:focus, textarea:focus { border-color: #d35400; background: white; outline: none; box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1); }
        
        /* Custom Radio for Rent Type */
        .type-selector { display: flex; gap: 15px; }
        .type-option { flex: 1; position: relative; }
        .type-option input { position: absolute; opacity: 0; cursor: pointer; }
        .type-card { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; background: #f9fafb; border: 2px solid #e1e8ed; border-radius: 15px; cursor: pointer; transition: 0.3s; text-align: center; }
        .type-card i { font-size: 2rem; margin-bottom: 10px; color: #95a5a6; }
        .type-card span { font-weight: 600; color: #555; }
        .type-option input:checked ~ .type-card { border-color: #d35400; background: #fff8f0; }
        .type-option input:checked ~ .type-card i, .type-option input:checked ~ .type-card span { color: #d35400; }

        .upload-area { border: 2px dashed #ddd; padding: 30px; text-align: center; border-radius: 15px; cursor: pointer; background: #fafafa; position: relative; transition: 0.3s; }
        .upload-area:hover { border-color: #d35400; }
        .upload-area input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
        .btn-submit { width: 100%; padding: 18px; background: #d35400; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #b04600; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(211,84,0,0.2); }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size: 1.8rem; color: #2c3e50;">Tambah Gear / Paket</h2>
        </div>

        <?php if ($pesan_sukses): ?><div class="alert alert-success"><?php echo $pesan_sukses; ?></div><?php endif; ?>
        <?php if ($pesan_error): ?><div class="alert alert-error"><?php echo $pesan_error; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Pilih Jenis Penyewaan</label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="rent_type" value="item" checked>
                        <div class="type-card">
                            <i class="fas fa-toolbox"></i>
                            <span>Item Satuan</span>
                        </div>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="rent_type" value="package">
                        <div class="type-card">
                            <i class="fas fa-layer-group"></i>
                            <span>Paket Hemat</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Produk / Nama Paket</label>
                <input type="text" name="nama_produk" placeholder="Cth: Paket Camping Berdua / Tenda Eiger" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga Sewa (Rp / Hari)</label>
                    <input type="number" name="harga_per_hari" min="0" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal Fisik</label>
                    <input type="number" name="stok_awal" min="1" value="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>Foto Gear / Ilustrasi Paket</label>
                <div class="upload-area">
                    <input type="file" name="foto_produk" accept="image/*" onchange="document.getElementById('imgPreview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('imgPreview').style.display = 'block'; document.getElementById('upText').style.display = 'none';">
                    <div id="upText"><i class="fas fa-cloud-upload-alt" style="font-size:30px; color:#ccc;"></i><br>Unggah Foto (JPG/PNG)</div>
                    <img id="imgPreview" style="max-width: 100%; max-height: 250px; border-radius: 10px; display: none; margin-top:10px;">
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Detail (Sertakan rincian jika ini Paket)</label>
                <textarea name="deskripsi" rows="4" placeholder="Detail barang..."></textarea>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Simpan ke Katalog</button>
            <a href="admin_produk.php" style="display:block; text-align:center; margin-top:20px; color:#888; text-decoration:none;">Batal & Kembali</a>
        </form>
    </div>
</body>
</html>