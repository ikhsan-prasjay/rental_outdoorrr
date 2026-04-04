<?php
// status_pemesanan.php
session_start();
require_once 'koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$rental_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil Data Transaksi Kompleks (JOIN antar tabel)
$sql = "SELECT r.*, u.full_name, u.email, u.phone_number, p.name AS product_name, p.rate_per_day 
        FROM rentals r
        JOIN users u ON r.user_id = u.id_users
        JOIN rental_items ri ON ri.rental_id = r.id_rentals
        JOIN equipment_items ei ON ri.item_id = ei.id
        JOIN equipment_products p ON ei.product_id = p.id_equipment
        WHERE r.id_rentals = ? AND r.user_id = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $rental_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Invoice Tidak Ditemukan</h2>
            <p>Transaksi ini tidak ada atau bukan milik Anda.</p>
            <a href='index.php'>Kembali ke Beranda</a>
         </div>");
}

$invoice = $result->fetch_assoc();

// Hitung Durasi
$date1 = new DateTime($invoice['start_date']);
$date2 = new DateTime($invoice['end_date']);
$durasi = max(1, $date1->diff($date2)->days);

// Status Badge
$status_colors = [
    'pending' => '#f39c12',
    'approved' => '#3498db',
    'on_rent' => '#9b59b6',
    'returned' => '#2ecc71',
    'cancelled' => '#e74c3c'
];
$color = $status_colors[$invoice['status']] ?? '#95a5a6';

// Memanggil Header UI
require_once 'includes/header.php';
?>

<style>
    .invoice-container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 30px; }
    .invoice-header h2 { color: var(--primary); font-size: 2rem; margin-bottom: 5px; }
    .invoice-id { font-size: 1.1rem; color: #7f8c8d; }
    .status-badge { display: inline-block; padding: 8px 15px; color: white; border-radius: 50px; font-weight: bold; font-size: 0.9rem; text-transform: uppercase; background-color: <?php echo $color; ?>; }
    
    .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
    .detail-box { flex: 1; }
    .detail-box h4 { color: var(--dark); margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; display: inline-block; }
    .detail-box p { color: #555; margin-bottom: 5px; font-size: 0.95rem; }
    
    .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    .item-table th { background: #f8f9fa; text-align: left; padding: 15px; color: var(--dark); border-bottom: 2px solid #ddd; }
    .item-table td { padding: 15px; border-bottom: 1px solid #eee; color: #555; }
    .total-row td { font-weight: bold; font-size: 1.2rem; color: var(--dark); border-top: 2px solid #ddd; }
    .total-price { color: var(--primary) !important; font-size: 1.4rem; }
    
    .payment-info { background: #fff8f0; border-left: 4px solid var(--primary); padding: 20px; margin-bottom: 30px; border-radius: 4px; }
    .btn-print { background: #2c3e50; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: 0.3s; display: inline-block; }
    .btn-print:hover { background: #1a252f; }

    /* Mode Cetak (Sembunyikan elemen yang tidak perlu saat di-print) */
    @media print {
        .main-header, footer, .float-wa, .btn-print { display: none !important; }
        .invoice-container { box-shadow: none; margin: 0; padding: 0; }
        body { background: white; }
    }
</style>

<div class="invoice-container">
    <div class="invoice-header">
        <div>
            <h2>INVOICE</h2>
            <div class="invoice-id">No. Referensi: <strong>#RNT-<?php echo str_pad($invoice['id_rentals'], 5, '0', STR_PAD_LEFT); ?></strong></div>
            <div style="margin-top: 10px;">Tanggal Terbit: <?php echo date('d M Y, H:i', strtotime($invoice['created_at'])); ?></div>
        </div>
        <div style="text-align: right;">
            <div style="margin-bottom: 10px;">Status Pembayaran:</div>
            <span class="status-badge"><?php echo $invoice['status']; ?></span>
        </div>
    </div>

    <div class="invoice-details">
        <div class="detail-box">
            <h4>Informasi Pelanggan</h4>
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($invoice['full_name'] ?? '-'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($invoice['email'] ?? '-'); ?></p>
            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($invoice['phone_number'] ?? 'Belum diisi'); ?></p>
            <p><strong>Alamat Pengiriman:</strong> <?php echo nl2br(htmlspecialchars($invoice['delivery_address'] ?? '-')); ?></p>
        </div>
        <div class="detail-box">
            <h4>Detail Sewa</h4>
            <p><strong>Tgl Mulai:</strong> <?php echo date('d M Y', strtotime($invoice['start_date'])); ?></p>
            <p><strong>Tgl Selesai:</strong> <?php echo date('d M Y', strtotime($invoice['end_date'])); ?></p>
            <p><strong>Durasi:</strong> <?php echo $durasi; ?> Hari</p>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th>Deskripsi Produk</th>
                <th>Harga / Hari</th>
                <th>Durasi</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                <td>Rp <?php echo number_format($invoice['rate_per_day'], 0, ',', '.'); ?></td>
                <td><?php echo $durasi; ?> Hari</td>
                <td style="text-align: right;">Rp <?php echo number_format($invoice['total_price'], 0, ',', '.'); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAL TAGIHAN</td>
                <td class="total-price" style="text-align: right;">Rp <?php echo number_format($invoice['total_price'], 0, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if($invoice['status'] === 'pending'): ?>
    <div class="payment-info">
        <h4 style="margin-bottom: 10px; color: var(--primary);">Instruksi Pembayaran</h4>
        <p>Silakan lakukan pembayaran sebesar <strong>Rp <?php echo number_format($invoice['total_price'], 0, ',', '.'); ?></strong> ke rekening berikut:</p>
        <ul style="list-style: none; padding-left: 0; margin-top: 10px;">
            <li><strong>BCA:</strong> 1234-567-890 a/n Se7en Summits Outdoor</li>
            <li><strong>Mandiri:</strong> 0987-654-321 a/n Se7en Summits Outdoor</li>
        </ul>
        <p style="margin-top: 10px; font-size: 0.9rem;">* Lakukan konfirmasi pembayaran melalui WhatsApp dengan melampirkan bukti transfer dan Nomor Referensi Invoice ini.</p>
    </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 40px;">
        <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
        <?php if($invoice['status'] === 'pending'): ?>
            <a href="https://wa.me/6281234567890?text=Halo%20Se7en%20Summits,%20saya%20ingin%20konfirmasi%20pembayaran%20untuk%20Invoice%20%23RNT-<?php echo str_pad($invoice['id_rentals'], 5, '0', STR_PAD_LEFT); ?>" target="_blank" class="btn-print" style="background: #25d366; margin-left: 10px;"><i class="fab fa-whatsapp"></i> Konfirmasi ke WA</a>
        <?php endif; ?>
    </div>
</div>

<?php 
require_once 'includes/footer.php'; 
?>