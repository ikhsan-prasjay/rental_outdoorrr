<?php
// detail_produk.php
session_start();
require_once 'koneksi.php';

// 1. Validasi ID Produk di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// 2. Ambil Data Produk dari Database
$stmt = $koneksi->prepare("SELECT * FROM equipment_products WHERE id_equipment = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

// Jika produk tidak ditemukan
if (!$produk) {
    echo "Produk tidak ditemukan!";
    exit();
}

// Cek Login untuk tombol sewa
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail - <?php echo htmlspecialchars($produk['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS Reset & Variables --- */
        :root { --primary: #e67e22; --dark: #2c3e50; --light: #f4f6f8; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { background-color: var(--light); color: var(--dark); padding-bottom: 50px; }
        
        /* Navbar Sederhana */
        .navbar { background: white; padding: 15px 5%; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; }
        .navbar a { text-decoration: none; color: var(--dark); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .navbar .logo { font-size: 1.2rem; color: var(--primary); }

        /* Container Utama */
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap; /* Agar responsif di HP */
        }

        /* Bagian Kiri: Gambar */
        .img-section {
            flex: 1;
            min-width: 350px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .img-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            min-height: 400px;
        }

        /* Bagian Kanan: Info */
        .info-section {
            flex: 1;
            padding: 40px;
            min-width: 350px;
        }

        .category-badge {
            background: #ffe0b2;
            color: #e65100;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .product-title { font-size: 2rem; margin: 15px 0 10px; color: var(--dark); }
        .product-price { font-size: 1.5rem; color: var(--primary); font-weight: 700; margin-bottom: 20px; }
        .product-desc { color: #666; line-height: 1.6; margin-bottom: 30px; font-size: 14px; }

        /* Form Sewa */
        .rental-form {
            background: #fafafa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #eee;
        }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }

        .btn-sewa {
            display: block;
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-sewa:hover { background: #d35400; }
        
        .btn-disabled { background: #ccc; cursor: not-allowed; }

        .total-price {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: var(--dark);
            display: none; /* Sembunyikan dulu sampai dihitung */
        }

        /* Responsif HP */
        @media (max-width: 768px) {
            .detail-card { flex-direction: column; }
            .img-section img { min-height: 250px; max-height: 300px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><i class="fas fa-campground"></i> Rental Outdoor</a>
        <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali</a>
    </nav>

    <div class="container">
        <div class="detail-card">
            <div class="img-section">
                <img src="<?php echo !empty($produk['main_image_url']) ? $produk['main_image_url'] : 'https://via.placeholder.com/500x500?text=No+Image'; ?>" alt="Foto Produk">
            </div>

            <div class="info-section">
                <span class="category-badge">Outdoor Gear</span>
                <h1 class="product-title"><?php echo htmlspecialchars($produk['name']); ?></h1>
                <div class="product-price">Rp <?php echo number_format($produk['rate_per_day'], 0, ',', '.'); ?> <span style="font-size: 14px; color: #888; font-weight: normal;">/ Hari</span></div>
                
                <h3>Deskripsi</h3>
                <p class="product-desc">
                    <?php echo nl2br(htmlspecialchars($produk['description'])); ?>
                </p>

                <div class="rental-form">
                    <?php if ($is_logged_in): ?>
                        <form action="checkout.php" method="GET">
                            <input type="hidden" name="product" value="<?php echo $id; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Mulai Sewa</label>
                                    <input type="date" id="start_date" name="start" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Selesai Sewa</label>
                                    <input type="date" id="end_date" name="end" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div id="estimasi" class="total-price">
                                Total: Rp <span id="total_nominal">0</span>
                            </div>

                            <button type="submit" class="btn-sewa" style="margin-top: 15px;">
                                <i class="fas fa-shopping-cart"></i> Lanjut ke Pembayaran
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center;">
                            <p style="margin-bottom: 10px; color: red;">Login untuk menyewa barang ini.</p>
                            <a href="login.php" class="btn-sewa">Login Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ratePerDay = <?php echo $produk['rate_per_day']; ?>;
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const totalDisplay = document.getElementById('total_nominal');
        const estimasiBox = document.getElementById('estimasi');

        function hitungTotal() {
            if(startInput.value && endInput.value) {
                const start = new Date(startInput.value);
                const end = new Date(endInput.value);
                
                // Hitung selisih waktu
                const diffTime = Math.abs(end - start);
                // Ubah ke hari (minimal 1 hari)
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                
                if (diffDays < 1) diffDays = 1; // Jika hari sama, dihitung 1 hari
                
                // Validasi tanggal
                if (end < start) {
                    estimasiBox.style.display = 'none';
                    return;
                }

                const total = diffDays * ratePerDay;
                
                // Format Rupiah
                totalDisplay.innerText = new Intl.NumberFormat('id-ID').format(total);
                estimasiBox.style.display = 'block';
            }
        }

        if(startInput && endInput) {
            startInput.addEventListener('change', hitungTotal);
            endInput.addEventListener('change', hitungTotal);
        }
    </script>

</body>
</html>