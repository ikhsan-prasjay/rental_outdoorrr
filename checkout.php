<?php
// checkout.php
session_start();
require_once 'koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

// 2. Validasi Data dari URL (GET)
if (!isset($_GET['product']) || !isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: index.php');
    exit();
}

$product_id = intval($_GET['product']);
$start_date = $_GET['start'];
$end_date = $_GET['end'];

// 3. Ambil Data Produk & Hitung Harga (Validasi Ulang di Server)
$stmt = $koneksi->prepare("SELECT name, rate_per_day, main_image_url FROM equipment_products WHERE id_equipment = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Hitung Durasi
$date1 = new DateTime($start_date);
$date2 = new DateTime($end_date);
$diff = $date1->diff($date2);
$durasi = $diff->days;
if ($durasi < 1) $durasi = 1; // Minimal 1 hari

$total_price = $durasi * $product['rate_per_day'];

// 4. Proses Form Checkout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $alamat = trim($_POST['delivery_address']);
    $catatan = trim($_POST['notes']);
    
    if (empty($alamat)) {
        $error_message = "Alamat pengiriman wajib diisi!";
    } else {
        // INSERT ke tabel rentals
        // Sesuai skema: id_rentals, user_id, start_date, end_date, delivery_address, total_price, status, notes
        $status = 'pending';
        
        $stmt_insert = $koneksi->prepare("INSERT INTO rentals (user_id, start_date, end_date, delivery_address, total_price, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("isssdss", $user_id, $start_date, $end_date, $alamat, $total_price, $status, $catatan);
        
        if ($stmt_insert->execute()) {
            // Berhasil! Ambil ID transaksi terakhir
            $rental_id = $koneksi->insert_id;
            
            // Redirect ke halaman Sukses/Status
            header("Location: status_pemesanan.php?id=" . $rental_id);
            exit();
        } else {
            $error_message = "Gagal memproses transaksi: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Rental Outdoor</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .checkout-container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; gap: 30px; }
        .summary-section { flex: 1; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .form-section { flex: 1.5; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; }
        textarea, input[type="text"] { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .price-tag { font-size: 24px; color: #28a745; font-weight: bold; margin: 10px 0; }
        .btn-confirm { width: 100%; padding: 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-confirm:hover { background-color: #0056b3; }
        .error-msg { background: #ffdede; color: red; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .product-img { width: 100%; border-radius: 5px; margin-bottom: 15px; }
        
        @media (max-width: 768px) { .checkout-container { flex-direction: column; } }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="summary-section">
        <h3>Ringkasan Pesanan</h3>
        <img src="<?php echo !empty($product['main_image_url']) ? $product['main_image_url'] : 'https://source.unsplash.com/400x300/?camping,tent'; ?>" alt="Produk" class="product-img">
        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
        <p>Tgl Sewa: <b><?php echo $start_date; ?></b> s/d <b><?php echo $end_date; ?></b></p>
        <p>Durasi: <?php echo $durasi; ?> Hari</p>
        <hr>
        <p>Total Biaya:</p>
        <div class="price-tag">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></div>
    </div>

    <div class="form-section">
        <h2>Konfirmasi & Pengiriman</h2>
        <?php if($error_message) echo "<div class='error-msg'>$error_message</div>"; ?>
        
        <form method="POST">
            <label for="delivery_address">Alamat Pengiriman Lengkap</label>
            <textarea name="delivery_address" id="delivery_address" rows="4" placeholder="Jl. Merpati No. 10, Surakarta..." required></textarea>
            
            <label for="notes">Catatan Tambahan (Opsional)</label>
            <textarea name="notes" id="notes" rows="2" placeholder="Misal: Tolong dikirim pagi hari..."></textarea>
            
            <div style="margin-top: 20px; font-size: 14px; color: #666;">
                * Dengan menekan tombol di bawah, Anda menyetujui syarat & ketentuan sewa.
            </div>

            <button type="submit" class="btn-confirm">Bayar & Konfirmasi Pesanan</button>
            <a href="index.php" style="display:block; text-align:center; margin-top:15px; color:#555; text-decoration:none;">Batal</a>
        </form>
    </div>
</div>

</body>
</html>