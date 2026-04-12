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

// Hitung total akhir dengan Denda
$denda = isset($invoice['fine_amount']) ? $invoice['fine_amount'] : 0;
$total_keseluruhan = $invoice['total_price'] + $denda;

// Status Badge Warna Modern
$status_colors = [
    'pending' => 'background-color: #fef5e7; color: #f39c12;',
    'approved' => 'background-color: #e0f2fe; color: #0284c7;',
    'on_rent' => 'background-color: #f3e8ff; color: #9333ea;',
    'returned' => 'background-color: #e9f7ef; color: #27ae60;',
    'cancelled' => 'background-color: #fdeaea; color: #e74c3c;'
];
$badge_style = $status_colors[$invoice['status']] ?? 'background-color: #f8f9fa; color: #6c757d;';

// Memanggil Header UI
require_once 'includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --inv-primary: #d35400; /* Warna Oranye Se7en Summits */
        --inv-light: #fff5eb;
        --inv-text-main: #2c3e50;
        --inv-text-light: #7f8c8d;
        --inv-border: #e9ecef;
    }

    body {
        background-color: #f8fafc;
    }

    .invoice-container {
        font-family: 'Poppins', sans-serif;
        max-width: 900px;
        margin: 40px auto;
        background-color: #ffffff;
        padding: 50px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        color: var(--inv-text-main);
        line-height: 1.6;
    }

    .invoice-container::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background-color: var(--inv-primary);
    }

    .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--inv-border); padding-bottom: 30px; margin-bottom: 40px; }
    .brand-logo { font-size: 32px; font-weight: 700; color: var(--inv-primary); text-transform: uppercase; letter-spacing: 1px; }
    .invoice-info { text-align: right; }
    .invoice-info h1 { margin: 0; font-size: 24px; font-weight: 600; color: var(--inv-text-main); }
    .invoice-info .id-num { font-size: 16px; color: var(--inv-primary); font-weight: 500; }
    
    .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 10px; }

    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; }
    .detail-group h3 { font-size: 16px; font-weight: 600; color: var(--inv-text-light); text-transform: uppercase; letter-spacing: 1px; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--inv-border); padding-bottom: 8px; }
    .detail-group p { margin: 5px 0; font-size: 15px; }
    .detail-group .name { font-weight: 600; font-size: 17px; color: var(--inv-text-main); }

    .invoice-table { width: 100%; border-collapse: collapse; font-size: 15px; margin-bottom: 40px; }
    .invoice-table thead th { text-align: left; padding: 15px; background-color: #f8fafc; font-weight: 600; color: var(--inv-text-main); text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
    .invoice-table tbody td { padding: 18px 15px; border-bottom: 1px solid var(--inv-border); }
    .invoice-table .numeric-col { text-align: right; }

    .summary-container { display: flex; justify-content: flex-end; margin-bottom: 40px; }
    .summary-box { width: 380px; text-align: right; font-size: 15px; }
    .summary-row { display: flex; justify-content: space-between; padding: 10px 0; color: var(--inv-text-light); }
    .summary-row.fine { color: #e74c3c; font-weight: 500; }
    .summary-total { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background-color: var(--inv-light); color: var(--inv-primary); border-radius: 8px; margin-top: 15px; font-weight: 700; font-size: 18px; }

    .payment-notes { margin-bottom: 40px; background-color: #fefdfb; padding: 20px; border-radius: 8px; border-left: 4px solid var(--inv-primary); }
    .payment-notes h4 { margin: 0 0 10px 0; color: var(--inv-text-main); font-family: 'Poppins', sans-serif;}
    .payment-notes p { margin: 0; font-size: 14px; color: var(--inv-text-light); }
    .payment-notes ul { margin-top: 10px; font-size: 14px; color: var(--inv-text-main); }

    .invoice-footer { text-align: center; border-top: 1px solid var(--inv-border); padding-top: 30px; font-size: 14px; color: var(--inv-text-light); }
    .btn-action { padding: 12px 25px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; font-family: 'Poppins', sans-serif;}
    .btn-print { background-color: var(--inv-text-main); color: white; border: none; }
    .btn-print:hover { background-color: #1a252f; }
    .btn-wa { background-color: #25d366; color: white; border: none; }
    .btn-wa:hover { background-color: #20bd5a; }

    @media print {
        body { background-color: #fff; padding: 0; }
        .main-header, footer, .float-wa, .no-print { display: none !important; }
        .invoice-container { box-shadow: none; max-width: 100%; padding: 0; margin: 0; }
    }
</style>

<div class="invoice-container">
    
    <header class="invoice-header">
        <div class="brand-logo">SE7EN SUMMITS</div>
        <div class="invoice-info">
            <h1>INVOICE</h1>
            <p class="id-num">#RNT-<?php echo str_pad($invoice['id_rentals'], 5, '0', STR_PAD_LEFT); ?></p>
            <p style="margin:0; font-size:13px;">Tanggal Terbit: <?php echo date('d M Y', strtotime($invoice['created_at'])); ?></p>
            <span class="status-badge" style="<?php echo $badge_style; ?>"><?php echo str_replace('_', ' ', $invoice['status']); ?></span>
        </div>
    </header>

    <section class="details-grid">
        <div class="detail-group">
            <h3>DITAGIHKAN KEPADA</h3>
            <p class="name"><?php echo htmlspecialchars($invoice['full_name'] ?? '-'); ?></p>
            <p><?php echo htmlspecialchars($invoice['email'] ?? '-'); ?></p>
            <p><?php echo htmlspecialchars($invoice['phone_number'] ?? 'Belum diisi'); ?></p>
            <p style="margin-top: 10px; font-size: 13px; color: var(--inv-text-light);">
                <strong>Alamat Pengiriman:</strong><br>
                <?php echo nl2br(htmlspecialchars($invoice['delivery_address'] ?? '-')); ?>
            </p>
        </div>
        <div class="detail-group">
            <h3>RINCIAN PENYEWAAN</h3>
            <p><strong>Tgl Pengambilan:</strong> <?php echo date('d M Y', strtotime($invoice['start_date'])); ?></p>
            <p><strong>Batas Pengembalian:</strong> <?php echo date('d M Y', strtotime($invoice['end_date'])); ?></p>
            <p><strong>Durasi Sepakat:</strong> <?php echo $durasi; ?> Hari</p>
            
            <?php if (!empty($invoice['actual_return_date'])): ?>
                <p style="color: #27ae60; margin-top:10px;"><strong>Dikembalikan Tgl:</strong> <?php echo date('d M Y H:i', strtotime($invoice['actual_return_date'])); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Item / Barang</th>
                <th class="numeric-col">Harga/Hari</th>
                <th class="numeric-col">Durasi</th>
                <th class="numeric-col">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong><?php echo htmlspecialchars($invoice['product_name']); ?></strong></td>
                <td class="numeric-col">Rp <?php echo number_format($invoice['rate_per_day'], 0, ',', '.'); ?></td>
                <td class="numeric-col"><?php echo $durasi; ?> Hari</td>
                <td class="numeric-col"><strong>Rp <?php echo number_format($invoice['total_price'], 0, ',', '.'); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <section class="summary-container">
        <div class="summary-box">
            <div class="summary-row">
                <span>Subtotal Sewa:</span>
                <span>Rp <?php echo number_format($invoice['total_price'], 0, ',', '.'); ?></span>
            </div>
            
            <?php if ($denda > 0): ?>
            <div class="summary-row fine">
                <span>Denda Keterlambatan:</span>
                <span>+ Rp <?php echo number_format($denda, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>

            <div class="summary-total">
                <span>TOTAL TAGIHAN</span>
                <span>Rp <?php echo number_format($total_keseluruhan, 0, ',', '.'); ?></span>
            </div>
        </div>
    </section>

    <?php if($invoice['status'] === 'pending' || ($denda > 0 && $invoice['status'] === 'returned')): ?>
    <section class="payment-notes no-print">
        <h4>Catatan Pembayaran & Tagihan</h4>
        <?php if($invoice['status'] === 'pending'): ?>
            <p>Selesaikan pembayaran Sewa sebesar <strong>Rp <?php echo number_format($total_keseluruhan, 0, ',', '.'); ?></strong> ke rekening di bawah ini untuk memproses pesanan Anda.</p>
        <?php elseif($denda > 0): ?>
            <p>Pesanan telah dikembalikan namun Anda memiliki <strong>Tagihan Denda Keterlambatan sebesar Rp <?php echo number_format($denda, 0, ',', '.'); ?></strong>. Mohon segera lunasi untuk menghindari penangguhan akun Anda.</p>
        <?php endif; ?>
        
        <ul style="list-style: none; padding-left: 0;">
            <li><i class="fas fa-money-check-alt" style="color:var(--inv-primary); margin-right:5px;"></i> <strong>BCA:</strong> 1234-567-890 a/n Se7en Summits Outdoor</li>
            <li><i class="fas fa-money-check-alt" style="color:var(--inv-primary); margin-right:5px;"></i> <strong>Mandiri:</strong> 0987-654-321 a/n Se7en Summits Outdoor</li>
        </ul>
    </section>
    <?php endif; ?>

    <footer class="invoice-footer">
        <p>Terima kasih telah mempercayakan petualangan Anda bersama Se7en Summits.</p>
        <p style="font-size: 12px; margin-top:10px;">Dokumen ini dihasilkan secara otomatis oleh sistem.</p>
        
        <div class="no-print" style="margin-top: 30px; display:flex; justify-content:center; gap:15px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
            <?php if($invoice['status'] === 'pending' || $denda > 0): ?>
                <a href="https://wa.me/6281234567890?text=Halo%20Se7en%20Summits,%20saya%20ingin%20konfirmasi%20pembayaran%20untuk%20Invoice%20%23RNT-<?php echo str_pad($invoice['id_rentals'], 5, '0', STR_PAD_LEFT); ?>" target="_blank" class="btn-action btn-wa">
                    <i class="fab fa-whatsapp"></i> Konfirmasi via WA
                </a>
            <?php endif; ?>
        </div>
    </footer>

</div>

<?php 
require_once 'includes/footer.php'; 
?>