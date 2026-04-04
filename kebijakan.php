<?php
// kebijakan.php
session_start();
require_once 'koneksi.php';
require_once 'includes/header.php';
?>

<style>
    .policy-wrapper { max-width: 1100px; margin: 60px auto; padding: 0 20px; min-height: 60vh; }
    .policy-header { text-align: center; margin-bottom: 50px; }
    .policy-header h1 { color: var(--dark); font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
    .policy-header p { color: var(--text-gray); font-size: 1.1rem; }

    .policy-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
    
    .policy-card {
        background: white; border-radius: 16px; padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 5px solid var(--primary);
        transition: transform 0.3s;
    }
    .policy-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    
    .policy-icon {
        width: 60px; height: 60px; background: #fff0e6; color: var(--primary);
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; margin-bottom: 20px;
    }
    
    .policy-card h3 { color: var(--dark); font-size: 1.3rem; margin-bottom: 15px; font-weight: 700; }
    .policy-card ul { list-style: none; padding: 0; }
    .policy-card ul li { margin-bottom: 12px; color: #555; line-height: 1.5; display: flex; align-items: flex-start; gap: 10px; }
    .policy-card ul li i { color: var(--primary); margin-top: 4px; font-size: 0.9rem; }

    .policy-warning {
        background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 12px;
        padding: 20px; margin-top: 40px; display: flex; gap: 20px; align-items: center;
    }
    .policy-warning i { font-size: 2.5rem; color: #e74c3c; }
    .policy-warning div h4 { color: #c0392b; margin-bottom: 5px; font-size: 1.2rem; }
    .policy-warning div p { color: #842029; margin: 0; font-size: 0.95rem; line-height: 1.5; }

    @media (max-width: 768px) {
        .policy-warning { flex-direction: column; text-align: center; }
    }
</style>

<div class="policy-wrapper">
    <div class="policy-header">
        <h1>Syarat & Kebijakan</h1>
        <p>Harap membaca dengan seksama aturan penyewaan di Se7en Summits demi kenyamanan bersama.</p>
    </div>

    <div class="policy-grid">
        <div class="policy-card">
            <div class="policy-icon"><i class="fas fa-id-card"></i></div>
            <h3>Syarat Identitas</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Penyewa wajib meninggalkan jaminan berupa identitas asli (KTP / SIM / Kartu Pelajar yang masih aktif).</li>
                <li><i class="fas fa-check-circle"></i> Nama pemesan di website harus sama dengan nama di kartu identitas jaminan.</li>
                <li><i class="fas fa-check-circle"></i> Identitas akan dikembalikan saat barang sewaan dikembalikan secara utuh.</li>
            </ul>
        </div>

        <div class="policy-card">
            <div class="policy-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <h3>Pembayaran & DP</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Pembayaran dapat dilakukan via Transfer Bank atau Cash saat mengambil barang.</li>
                <li><i class="fas fa-check-circle"></i> Untuk *booking* lebih dari 3 hari sebelum pengambilan, wajib menyertakan DP (Down Payment) minimal 50%.</li>
                <li><i class="fas fa-check-circle"></i> Pembatalan sewa H-1 akan dikenakan potongan 50% dari total transaksi.</li>
            </ul>
        </div>

        <div class="policy-card">
            <div class="policy-icon"><i class="fas fa-clock"></i></div>
            <h3>Waktu & Keterlambatan</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Hitungan 1 hari sewa adalah 24 Jam sejak barang diambil.</li>
                <li><i class="fas fa-check-circle"></i> Toleransi keterlambatan pengembalian adalah 2 Jam.</li>
                <li><i class="fas fa-check-circle"></i> <b>Denda Keterlambatan:</b> Dikenakan biaya tambahan sebesar Rp 10.000 / Jam, atau dihitung sewa 1 hari penuh jika lewat dari 12 jam.</li>
            </ul>
        </div>
    </div>

    <div class="policy-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <h4>Kebijakan Kerusakan & Kehilangan Barang</h4>
            <p>Penyewa bertanggung jawab penuh atas barang yang disewa. Jika terjadi kerusakan (tenda bolong, frame patah, resleting rusak), penyewa wajib membayar biaya *service*. Jika barang hilang atau rusak total (tidak bisa dipakai lagi), penyewa <b>wajib mengganti dengan barang yang sama atau membayar senilai harga beli barang tersebut</b>.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
