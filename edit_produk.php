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

// Default tipe (jika kolom rent_type kosong/null di data lama)
$current_type = isset($data['rent_type']) ? $data['rent_type'] : 'item';

// PROSES UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga_per_hari'];
    $rent_type = $_POST['rent_type']; // Ambil update tipe
    $url_gambar = $data['main_image_url']; // Default gambar lama

    // Cek jika ada upload gambar BARU
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_ext = strtolower(pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        $allowed = array("jpg", "jpeg", "png", "webp");
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                // Hapus gambar lama jika ada dan bukan placeholder
                if (!empty($data['main_image_url']) && file_exists($data['main_image_url']) && strpos($data['main_image_url'], 'placeholder') === false) {
                    unlink($data['main_image_url']);
                }
                $url_gambar = $target_file; // Update path gambar
            }
        }
    }

    // Update Database (Sertakan rent_type)
    $update = $koneksi->prepare("UPDATE equipment_products SET rent_type=?, name=?, description=?, rate_per_day=?, main_image_url=? WHERE id_equipment=?");
    $update->bind_param("sssdsi", $rent_type, $nama, $deskripsi, $harga, $url_gambar, $id);

    if ($update->execute()) {
        echo "<script>alert('Produk berhasil diperbarui!'); window.location='admin_produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui produk.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS MODERN & KONSISTEN --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #f8fafc; color: #0f172a; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        
        .container {
            background: white; width: 100%; max-width: 750px; padding: 40px;
            border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { font-size: 1.8rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .header h2 i { color: #d35400; }
        .header p { color: #64748b; font-size: 0.95rem; margin-top: 5px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #334155; }
        
        input[type="text"], input[type="number"], textarea {
            width: 100%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px;
            font-size: 14px; background: #f8fafc; transition: 0.3s; color: #0f172a;
        }
        input:focus, textarea:focus { border-color: #d35400; background: white; outline: none; box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1); }
        
        /* Custom Radio for Rent Type (Persis seperti tambah_produk) */
        .type-selector { display: flex; gap: 15px; }
        .type-option { flex: 1; position: relative; }
        .type-option input { position: absolute; opacity: 0; cursor: pointer; }
        .type-card {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px;
            cursor: pointer; transition: 0.3s; text-align: center;
        }
        .type-card i { font-size: 1.5rem; margin-bottom: 8px; color: #94a3b8; }
        .type-card span { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .type-option input:checked ~ .type-card { border-color: #d35400; background: #fff8f0; }
        .type-option input:checked ~ .type-card i, .type-option input:checked ~ .type-card span { color: #d35400; }

        /* Upload Area Keren */
        .upload-area {
            border: 2px dashed #cbd5e1; padding: 20px; text-align: center; border-radius: 15px;
            cursor: pointer; background: #f8fafc; position: relative; transition: 0.3s;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .upload-area:hover { border-color: #d35400; background: #fff8f0; }
        .upload-area input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10; }
        
        .img-preview-container { margin-top: 10px; text-align: center; position: relative; z-index: 5; }
        .img-preview-container img { max-width: 100%; max-height: 250px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); object-fit: cover; }
        .upload-instruction { color: #64748b; font-size: 0.9rem; margin-top: 10px; font-weight: 500; }

        /* Buttons */
        .btn-submit {
            width: 100%; padding: 18px; background: #d35400; color: white; border: none;
            border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s;
            display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;
        }
        .btn-submit:hover { background: #b04600; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(211,84,0,0.2); }
        
        .btn-cancel {
            display: block; text-align: center; margin-top: 20px; color: #64748b;
            text-decoration: none; font-weight: 600; transition: 0.3s;
        }
        .btn-cancel:hover { color: #0f172a; }

        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2><i class="fas fa-pen-square"></i> Edit Produk</h2>
            <p>Perbarui informasi gear atau paket penyewaan</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Jenis Penyewaan</label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="rent_type" value="item" <?php echo ($current_type == 'item') ? 'checked' : ''; ?>>
                        <div class="type-card">
                            <i class="fas fa-toolbox"></i>
                            <span>Item Satuan</span>
                        </div>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="rent_type" value="package" <?php echo ($current_type == 'package') ? 'checked' : ''; ?>>
                        <div class="type-card">
                            <i class="fas fa-layer-group"></i>
                            <span>Paket Hemat</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Produk / Paket</label>
                <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Harga Sewa (Rp / Hari)</label>
                <input type="number" name="harga_per_hari" value="<?php echo $data['rate_per_day']; ?>" required>
            </div>

            <div class="form-group">
                <label>Foto Produk (Kosongkan jika tidak ingin mengganti)</label>
                <div class="upload-area">
                    <input type="file" name="foto_produk" accept="image/*" onchange="previewImage(event)">
                    
                    <div class="img-preview-container">
                        <img id="imgPreview" src="<?php echo !empty($data['main_image_url']) ? htmlspecialchars($data['main_image_url']) : 'https://via.placeholder.com/400x300?text=No+Image'; ?>" alt="Preview">
                    </div>
                    
                    <div class="upload-instruction">
                        <i class="fas fa-cloud-upload-alt"></i> Klik/Seret untuk mengganti foto
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Detail</label>
                <textarea name="deskripsi" rows="5" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            
            <a href="admin_produk.php" class="btn-cancel">Batalkan & Kembali</a>
        </form>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('imgPreview');
                output.src = reader.result;
            };
            // Pastikan file dipilih sebelum mengubah src
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>

</body>
</html>