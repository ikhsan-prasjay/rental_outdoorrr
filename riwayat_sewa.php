<?php
// riwayat_sewa.php
session_start();
require_once 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Menggunakan Subquery untuk menghindari error ONLY_FULL_GROUP_BY dari MySQL Strict Mode
$query = "SELECT r.*, 
            (SELECT p.name 
             FROM rental_items ri 
             JOIN equipment_items ei ON ri.item_id = ei.id 
             JOIN equipment_products p ON ei.product_id = p.id_equipment 
             WHERE ri.rental_id = r.id_rentals LIMIT 1) AS product_name,
            (SELECT p.main_image_url 
             FROM rental_items ri 
             JOIN equipment_items ei ON ri.item_id = ei.id 
             JOIN equipment_products p ON ei.product_id = p.id_equipment 
             WHERE ri.rental_id = r.id_rentals LIMIT 1) AS main_image_url
          FROM rentals r
          WHERE r.user_id = ?
          ORDER BY r.created_at DESC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Memanggil Header UI
require_once 'includes/header.php';
?>

<style>
    .history-container { max-width: 1000px; margin: 50px auto; padding: 0 20px; min-height: 60vh; }
    .page-header { border-bottom: 2px solid var(--primary); padding-bottom: 15px; margin-bottom: 30px; }
    .page-header h2 { color: var(--dark); font-size: 2rem; }
    
    .history-card { 
        background: white; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        margin-bottom: 20px; 
        display: flex; 
        overflow: hidden;
        border: 1px solid #eee;
        transition: transform 0.3s ease;
    }
    .history-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    
    .history-img { width: 150px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; }
    .history-img img { width: 100%; height: 100%; object-fit: cover; }
    
    .history-content { padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .history-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    
    .rent-id { font-size: 0.9rem; color: var(--text-gray); font-weight: 600; margin-bottom: 5px; }
    .product-title { font-size: 1.3rem; color: var(--dark); margin-bottom: 5px; }
    .rent-date { font-size: 0.9rem; color: #555; }
    
    .history-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; }
    .total-price { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
    
    .badge-status { padding: 6px 15px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: white; display: inline-block; text-align: center;}
    .status-pending { background-color: #f39c12; }
    .status-approved { background-color: #3498db; }
    .status-on_rent { background-color: #9b59b6; }
    .status-returned { background-color: #2ecc71; }
    .status-cancelled { background-color: #e74c3c; }
    
    .btn-invoice { background: var(--dark); color: white; padding: 8px 20px; border-radius: 6px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; }
    .btn-invoice:hover { background: var(--primary); }

    .empty-state { text-align: center; padding: 50px 20px; color: var(--text-gray); }
    .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }

    @media (max-width: 768px) {
        .history-card { flex-direction: column; }
        .history-img { width: 100%; height: 200px; }
        .history-bottom { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<div class="history-container">
    <div class="page-header">
        <h2><i class="fas fa-history"></i> Riwayat Sewa Saya</h2>
        <p style="color: var(--text-gray); margin-top: 5px;">Pantau status pesanan dan riwayat petualangan Anda di sini.</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="history-card">
                <div class="history-img">
                    <img src="<?php echo !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://via.placeholder.com/150?text=No+Image'; ?>" alt="Product">
                </div>
                <div class="history-content">
                    <div>
                        <div class="history-top">
                            <div>
                                <div class="rent-id">Order #RNT-<?php echo str_pad($row['id_rentals'], 5, '0', STR_PAD_LEFT); ?></div>
                                <h3 class="product-title"><?php echo htmlspecialchars($row['product_name'] ?? 'Produk Tidak Ditemukan'); ?></h3>
                            </div>
                            <span class="badge-status status-<?php echo $row['status']; ?>">
                                <?php echo str_replace('_', ' ', $row['status']); ?>
                            </span>
                        </div>
                        <div class="rent-date">
                            <i class="far fa-calendar-alt"></i> Sewa: <strong><?php echo date('d M Y', strtotime($row['start_date'])); ?></strong> s/d <strong><?php echo date('d M Y', strtotime($row['end_date'])); ?></strong>
                        </div>
                    </div>
                    
                    <div class="history-bottom">
                        <div class="total-price">
                            Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                        </div>
                        <a href="status_pemesanan.php?id=<?php echo $row['id_rentals']; ?>" class="btn-invoice">Lihat Invoice <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Belum ada riwayat sewa</h3>
            <p>Anda belum pernah melakukan penyewaan alat. Yuk, mulai petualanganmu sekarang!</p>
            <br>
            <a href="index.php#katalog" class="btn-invoice" style="padding: 12px 25px;">Lihat Katalog</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>