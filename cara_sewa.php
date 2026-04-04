<?php
// cara_sewa.php
session_start();
require_once 'koneksi.php';
require_once 'includes/header.php';
?>

<style>
    .page-wrapper { max-width: 1000px; margin: 60px auto; padding: 0 20px; min-height: 60vh; }
    .page-title { text-align: center; margin-bottom: 50px; }
    .page-title h1 { color: var(--dark); font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
    .page-title p { color: var(--text-gray); font-size: 1.1rem; max-width: 600px; margin: 0 auto; }

    /* Timeline Style */
    .timeline { position: relative; max-width: 800px; margin: 0 auto; }
    .timeline::after {
        content: ''; position: absolute; width: 4px; background-color: var(--primary);
        top: 0; bottom: 0; left: 50%; margin-left: -2px; border-radius: 10px;
    }

    .container-step { padding: 10px 40px; position: relative; background-color: inherit; width: 50%; }
    .container-step.left { left: 0; }
    .container-step.right { left: 50%; }

    /* Circles on timeline */
    .container-step::after {
        content: ''; position: absolute; width: 25px; height: 25px; right: -12px; background-color: white;
        border: 4px solid var(--primary); top: 15px; border-radius: 50%; z-index: 1;
    }
    .right::after { left: -12px; }

    /* Content Box */
    .content-step {
        padding: 30px; background-color: white; position: relative; border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: 1px solid #eee; transition: 0.3s;
    }
    .content-step:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(211, 84, 0, 0.1); border-color: var(--primary); }
    
    .content-step h3 { color: var(--dark); font-size: 1.4rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
    .content-step h3 i { color: var(--primary); font-size: 1.6rem; }
    .content-step p { color: #555; line-height: 1.6; }

    /* Responsive Timeline */
    @media screen and (max-width: 768px) {
        .timeline::after { left: 31px; }
        .container-step { width: 100%; padding-left: 70px; padding-right: 25px; }
        .container-step.right { left: 0; }
        .container-step::after { left: 18px; }
    }
</style>

<div class="page-wrapper">
    <div class="page-title">
        <h1>Cara Menyewa Gear</h1>
        <p>Hanya dengan 4 langkah mudah, kamu sudah siap memulai petualanganmu bersama Se7en Summits.</p>
    </div>

    <div class="timeline">
        <div class="container-step left">
            <div class="content-step">
                <h3><i class="fas fa-search"></i> 1. Pilih Gear Anda</h3>
                <p>Jelajahi <b>Katalog Gear</b> kami. Kami menyediakan berbagai macam peralatan camping dan pendakian kualitas terbaik yang rutin dirawat. Klik "Lihat Detail" untuk membaca spesifikasi barang.</p>
            </div>
        </div>
        <div class="container-step right">
            <div class="content-step">
                <h3><i class="fas fa-calendar-check"></i> 2. Tentukan Tanggal</h3>
                <p>Setelah menemukan gear yang cocok, masukkan tanggal mulai sewa dan tanggal pengembalian. Sistem akan otomatis menghitung total biaya sewa Anda. Klik <b>Lanjut ke Pembayaran</b>.</p>
            </div>
        </div>
        <div class="container-step left">
            <div class="content-step">
                <h3><i class="fas fa-wallet"></i> 3. Checkout & Bayar</h3>
                <p>Isi alamat pengiriman atau catatan (misal: "Ambil di toko"). Setelah checkout, Anda akan mendapatkan <b>Invoice</b>. Lakukan pembayaran melalui transfer bank dan konfirmasi via WhatsApp kami.</p>
            </div>
        </div>
        <div class="container-step right">
            <div class="content-step">
                <h3><i class="fas fa-mountain"></i> 4. Ambil & Berpetualang!</h3>
                <p>Setelah pembayaran dikonfirmasi Admin, status pesanan akan menjadi "Disetujui". Anda bisa mengambil barang di *basecamp* kami atau menunggunya dikirim. Selamat berpetualang!</p>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 50px;">
        <a href="index.php#katalog" class="btn-auth-primary" style="padding: 15px 40px; font-size: 1.1rem;"><i class="fas fa-compass"></i> Mulai Sewa Sekarang</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>