<?php
// tambah_produk.php
session_start();
require_once 'koneksi.php';

// 1. CEK AKSES ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pesan_sukses = "";
$pesan_error = "";

// 2. PROSES FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga_per_hari'];
    $category_id = 1; // Default kategori

    // --- LOGIKA UPLOAD FOTO (DIPERBAIKI) ---
    $url_gambar = ""; // Default kosong
    
    // Cek apakah ada file yang diupload
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $target_dir = "uploads/";
        
        // [FIX] Cek apakah folder uploads ada, jika tidak, BUAT FOLDERNYA
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Buat nama file unik
        $file_ext = strtolower(pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        // Validasi tipe file
        $allowed_types = array("jpg", "jpeg", "png", "gif", "webp");
        
        if (in_array($file_ext, $allowed_types)) {
            // Pindahkan file
            if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                $url_gambar = $target_file; // Simpan path ini ke database
            } else {
                $pesan_error = "Gagal memindahkan file. Pastikan folder 'uploads' memiliki izin tulis.";
            }
        } else {
            $pesan_error = "Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.";
        }
    } else {
        // Placeholder jika tidak ada gambar
        $url_gambar = "https://via.placeholder.com/400x300?text=No+Image"; 
    }

    // --- SIMPAN KE DATABASE ---
    if (empty($pesan_error)) {
        if (empty($nama) || empty($harga)) {
            $pesan_error = "Nama Produk dan Harga wajib diisi!";
        } else {
            $stmt = $koneksi->prepare("INSERT INTO equipment_products (category_id, name, description, rate_per_day, main_image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issds", $category_id, $nama, $deskripsi, $harga, $url_gambar);

            if ($stmt->execute()) {
                $pesan_sukses = "Produk berhasil ditambahkan!";
            } else {
                $pesan_error = "Error Database: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
        body {
            background-color: #f0f2f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 700px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .header h2 { color: #2c3e50; font-weight: 700; margin-bottom: 5px; }
        
        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; font-size: 14px; }
        
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 14px; transition: all 0.3s ease; background: #f9f9f9;
        }
        
        input:focus, textarea:focus {
            border-color: #e67e22; background: white; outline: none;
            box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.1);
        }

        /* Styling area upload foto yang keren */
        .upload-area {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            background: #fafafa;
            position: relative;
        }
        .upload-area:hover { border-color: #e67e22; background: #fff8f0; }
        
        .upload-area input[type="file"] {
            position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;
        }
        
        .preview-img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            margin-top: 15px;
            display: none; /* Sembunyi dulu */
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-submit {
            width: 100%; padding: 15px; background-color: #e67e22; color: white; border: none;
            border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;
            transition: 0.3s; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-submit:hover { background-color: #d35400; transform: translateY(-2px); }

        .back-link { display: block; text-align: center; margin-top: 20px; color: #888; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: #e67e22; }

        /* Alerts */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2><i class="fas fa-campground"></i> Tambah Produk Baru</h2>
            <p>Lengkapi data perlengkapan outdoor di bawah ini</p>
        </div>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        
        <?php if ($pesan_error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="tambah_produk.php" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" placeholder="Misal: Tenda Eiger 4 Orang" required>
            </div>

            <div class="form-group">
                <label>Harga Sewa (Rp / Hari)</label>
                <input type="number" name="harga_per_hari" placeholder="Misal: 50000" min="0" required>
            </div>

            <div class="form-group">
                <label>Foto Produk</label>
                <div class="upload-area">
                    <input type="file" name="foto_produk" id="fileInput" accept="image/*" onchange="previewImage(event)">
                    <div id="uploadText">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 30px; color: #ccc;"></i><br>
                        <span style="color: #777;">Klik atau seret foto ke sini</span>
                    </div>
                    <img id="imagePreview" class="preview-img">
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Detail</label>
                <textarea name="deskripsi" rows="4" placeholder="Jelaskan kondisi barang, warna, kelengkapan, dll..."></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Simpan Produk
            </button>
        </form>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('imagePreview');
                var text = document.getElementById('uploadText');
                
                output.src = reader.result;
                output.style.display = 'block'; // Tampilkan gambar
                text.style.display = 'none'; // Sembunyikan teks upload
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>
</html>