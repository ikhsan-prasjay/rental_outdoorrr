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

// 3. Ambil Data Produk & Hitung Harga
$stmt = $koneksi->prepare("SELECT id_equipment, name, rate_per_day, main_image_url FROM equipment_products WHERE id_equipment = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("<div style='text-align:center; padding:50px;'><h2>Produk tidak ditemukan.</h2><a href='index.php'>Kembali</a></div>");
}

// Hitung Durasi (Minimal 1 hari)
$date1 = new DateTime($start_date);
$date2 = new DateTime($end_date);
$durasi = max(1, $date1->diff($date2)->days);
$total_price = $durasi * $product['rate_per_day'];

// 4. Proses Transaksi Database (POST) dengan Sistem Keamanan Stok Fisik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $alamat = trim($_POST['delivery_address']);
    $catatan = trim($_POST['notes']);
    
    if (empty($alamat)) {
        $error_message = "Alamat pengiriman wajib diisi dengan lengkap!";
    } else {
        // Mulai Transaksi Relasional
        $koneksi->begin_transaction();
        try {
            // A. Kunci Stok
            $stmt_item = $koneksi->prepare("SELECT id FROM equipment_items WHERE product_id = ? AND status = 'available' LIMIT 1 FOR UPDATE");
            $stmt_item->bind_param("i", $product_id);
            $stmt_item->execute();
            $res_item = $stmt_item->get_result();
            
            if ($res_item->num_rows === 0) {
                throw new Exception("Mohon maaf, saat Anda melakukan proses checkout, stok fisik barang ini baru saja habis disewa orang lain.");
            }
            $item_id = $res_item->fetch_assoc()['id'];
            $stmt_item->close();

            // B. Buat Master Pesanan
            $status = 'pending';
            $stmt_rental = $koneksi->prepare("INSERT INTO rentals (user_id, start_date, end_date, delivery_address, total_price, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_rental->bind_param("isssdss", $user_id, $start_date, $end_date, $alamat, $total_price, $status, $catatan);
            $stmt_rental->execute();
            $rental_id = $koneksi->insert_id;

            // C. Masukkan Detail Barang
            $stmt_detail = $koneksi->prepare("INSERT INTO rental_items (rental_id, item_id, price_at_rental) VALUES (?, ?, ?)");
            $stmt_detail->bind_param("iid", $rental_id, $item_id, $product['rate_per_day']);
            $stmt_detail->execute();

            // D. Kurangi Stok
            $koneksi->query("UPDATE equipment_items SET status = 'rented' WHERE id = $item_id");

            // E. Selesai
            $koneksi->commit();
            header("Location: status_pemesanan.php?id=" . $rental_id);
            exit();

        } catch (Exception $e) {
            $koneksi->rollback();
            $error_message = $e->getMessage();
        }
    }
}

// Format Tanggal untuk Tampilan UI
$tgl_mulai_format = date('d M Y', strtotime($start_date));
$tgl_selesai_format = date('d M Y', strtotime($end_date));

require_once 'includes/header.php';
?>

<style>
    /* --- CSS MODERN E-COMMERCE CHECKOUT --- */
    :root {
        --brand-primary: #d35400;
        --brand-hover: #b04600;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-lg: 16px;
        --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    body { background-color: var(--bg-color); color: var(--text-dark); padding-bottom: 60px; }

    .checkout-layout {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        align-items: start;
    }

    /* --- BAGIAN KIRI (FORM) --- */
    .checkout-main {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 35px;
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--border-color);
    }

    .checkout-title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 25px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid var(--bg-color);
        padding-bottom: 15px;
    }
    .checkout-title i { color: var(--brand-primary); }

    .form-group { margin-bottom: 25px; }
    .form-group label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-dark);
    }
    .form-control {
        width: 100%;
        padding: 16px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 1rem;
        font-family: inherit;
        background-color: #fcfcfc;
        transition: all 0.3s ease;
        resize: vertical;
    }
    .form-control:focus {
        border-color: var(--brand-primary);
        outline: none;
        background-color: var(--card-bg);
        box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1);
    }

    /* Trust Badge */
    .trust-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 25px;
    }

    .btn-submit-order {
        width: 100%;
        padding: 18px;
        background: var(--brand-primary);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 800;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(211, 84, 0, 0.2);
    }
    .btn-submit-order:hover {
        background: var(--brand-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(211, 84, 0, 0.3);
    }

    /* --- BAGIAN KANAN (SUMMARY) --- */
    .checkout-sidebar {
        position: sticky;
        top: 100px;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 30px;
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--border-color);
    }

    .summary-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; }

    .product-preview {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    .product-preview img {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--border-color);
    }
    .product-preview .info h4 {
        font-size: 1.05rem;
        color: var(--text-dark);
        margin: 0 0 5px 0;
        line-height: 1.3;
    }
    .product-preview .info .badge {
        background: var(--bg-color);
        color: var(--text-muted);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid var(--border-color);
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 0.95rem;
        color: var(--text-muted);
    }
    .detail-row strong { color: var(--text-dark); font-weight: 600; }
    
    .divider {
        border-top: 2px dashed var(--border-color);
        margin: 20px 0;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .total-row span { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
    .total-row .price { font-size: 1.5rem; font-weight: 800; color: var(--brand-primary); }

    .alert-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 500;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
    }
    .back-link:hover { color: var(--text-dark); }

    @media (max-width: 900px) {
        .checkout-layout { grid-template-columns: 1fr; }
        .checkout-sidebar { position: static; order: -1; /* Pindahkan ringkasan ke atas di mobile */ }
    }
</style>

<div class="checkout-layout">
    
    <div class="checkout-main">
        <h2 class="checkout-title"><i class="fas fa-map-marked-alt"></i> Pengiriman & Konfirmasi</h2>
        
        <?php if($error_message): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Alamat Lengkap Pengiriman</label>
                <textarea name="delivery_address" class="form-control" rows="4" placeholder="Contoh: Jl. Slamet Riyadi No. 123, RT 01/RW 02, Kec. Banjarsari, Kota Surakarta. (Sertakan patokan jika ada)" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Tolong dikirim jam 9 pagi, atau dititipkan ke pos satpam..."></textarea>
            </div>

            <div class="trust-badge">
                <i class="fas fa-shield-check"></i>
                <span>Transaksi Anda diproses dengan sistem keamanan terenkripsi.</span>
            </div>

            <button type="submit" class="btn-submit-order">
                Selesaikan Pembayaran <i class="fas fa-arrow-right"></i>
            </button>
            
            <a href="detail_produk.php?id=<?php echo $product_id; ?>" class="back-link">Batal & Kembali ke Produk</a>
        </form>
    </div>

    <div class="checkout-sidebar">
        <h3 class="summary-title">Ringkasan Pesanan</h3>
        
        <div class="product-preview">
            <img src="<?php echo !empty($product['main_image_url']) ? htmlspecialchars($product['main_image_url']) : 'https://via.placeholder.com/150'; ?>" alt="Produk">
            <div class="info">
                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                <span class="badge"><i class="far fa-clock"></i> Sewa <?php echo $durasi; ?> Hari</span>
            </div>
        </div>

        <div class="detail-row">
            <span>Tanggal Pengambilan</span>
            <strong><?php echo $tgl_mulai_format; ?></strong>
        </div>
        <div class="detail-row">
            <span>Tanggal Pengembalian</span>
            <strong><?php echo $tgl_selesai_format; ?></strong>
        </div>
        <div class="detail-row">
            <span>Tarif Harian</span>
            <strong>Rp <?php echo number_format($product['rate_per_day'], 0, ',', '.'); ?></strong>
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>Total Tagihan</span>
            <div class="price">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>