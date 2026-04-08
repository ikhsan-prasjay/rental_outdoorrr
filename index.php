<?php
// index.php
session_start(); 
require_once 'koneksi.php'; 

// Cek Login untuk Navigasi
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : '';

// --- LOGIKA PENCARIAN (SEARCH) ---
$search_filter = "";
$search_text = "";

// Jika ada kata kunci di URL (misal: index.php?search=tenda)
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_text = trim($_GET['search']);
    // Mengamankan input dari SQL Injection
    $safe_search = $koneksi->real_escape_string($search_text);
    // Tambahkan filter query: Cari di nama produk ATAU di deskripsinya
    $search_filter = " AND (name LIKE '%$safe_search%' OR description LIKE '%$safe_search%') ";
}

// Ambil Data Paket (Ditambah filter pencarian jika ada)
$sql_paket = "SELECT id_equipment, name, rate_per_day, main_image_url, description FROM equipment_products WHERE rent_type = 'package' $search_filter ORDER BY created_at DESC";
$res_paket = $koneksi->query($sql_paket);

// Ambil Data Satuan (Ditambah filter pencarian jika ada)
$sql_item = "SELECT id_equipment, name, rate_per_day, main_image_url FROM equipment_products WHERE rent_type = 'item' $search_filter ORDER BY created_at DESC";
$res_item = $koneksi->query($sql_item);

require_once 'includes/header.php';
?>

<style>
    /* --- BACKGROUND DECORATION (TOPOGRAFI & GLOWING BLOBS) --- */
    body {
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23d35400' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
        position: relative;
        overflow-x: hidden;
    }

    .bg-shape { position: absolute; border-radius: 50%; filter: blur(100px); z-index: -1; opacity: 0.3; }
    .shape-1 { top: 15%; left: -10%; width: 400px; height: 400px; background: #d35400; }
    .shape-2 { top: 40%; right: -5%; width: 500px; height: 500px; background: #3498db; }
    .shape-3 { bottom: 10%; left: 20%; width: 600px; height: 600px; background: #f39c12; }

    /* --- HERO --- */
    .hero {
        background: linear-gradient(rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1920&q=80');
        height: 85vh; min-height: 500px; background-size: cover; background-position: center; background-attachment: fixed; display: flex; align-items: center; justify-content: center; text-align: center; color: white;
    }
    .hero h1 { font-size: 4.5rem; font-weight: 800; letter-spacing: -2px; margin-bottom: 15px; }
    .hero p { font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto 35px; line-height: 1.6; }
    .btn-cta { background: var(--primary); color: white; padding: 16px 45px; border-radius: 50px; font-weight: 700; font-size: 1.1rem; transition: 0.3s; box-shadow: 0 10px 20px rgba(211,84,0,0.3); display: inline-block; }
    .btn-cta:hover { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(211,84,0,0.4); }

    /* --- SECTION CONTAINERS --- */
    .section-wrapper { max-width: 1300px; margin: 100px auto; padding: 0 30px; position: relative; z-index: 5; }
    .section-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
    .section-head h2 { font-size: 2.5rem; color: var(--dark); font-weight: 800; line-height: 1.2; }
    .section-head p { color: #64748b; font-size: 1.1rem; margin-top: 5px; }

    /* Search Results Indicator */
    .search-indicator {
        text-align: center; margin: 60px auto 40px; padding: 30px; background: white; border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 800px; border: 1px solid var(--border-color);
        position: relative; z-index: 10;
    }
    .search-indicator h2 { color: var(--dark); font-size: 1.8rem; margin-bottom: 10px; }
    .search-indicator span { color: var(--primary); }
    .btn-clear-search { display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-weight: 600; padding: 8px 20px; border-radius: 50px; border: 1px solid #cbd5e1; transition: 0.3s; margin-top: 15px; }
    .btn-clear-search:hover { background: #f1f5f9; color: var(--dark); }

    /* --- PREMIUM PACKAGE UI --- */
    .package-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; }
    .pkg-card { background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%); border-radius: 24px; display: flex; overflow: hidden; position: relative; box-shadow: 0 15px 35px rgba(0,0,0,0.1); transition: 0.4s; color: white; border: 1px solid rgba(255,255,255,0.05); }
    .pkg-card:hover { transform: translateY(-10px); box-shadow: 0 25px 45px rgba(211, 84, 0, 0.15); border-color: rgba(211, 84, 0, 0.3); }
    .pkg-img { width: 45%; position: relative; background: #2c3e50; }
    .pkg-img img { width: 100%; height: 100%; object-fit: cover; }
    .pkg-img::after { content:''; position:absolute; top:0; right:0; bottom:0; width:30%; background: linear-gradient(to right, transparent, #1e293b); }
    .pkg-badge { position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); color: white; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(255,255,255,0.3); z-index: 2; }
    .pkg-content { width: 55%; padding: 30px; display: flex; flex-direction: column; justify-content: center; z-index: 2; }
    .pkg-content h3 { font-size: 1.6rem; font-weight: 800; margin-bottom: 10px; }
    .pkg-content p { font-size: 0.9rem; color: #94a3b8; line-height: 1.6; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pkg-price { font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-bottom: 20px; }
    .pkg-price span { font-size: 1rem; color: #64748b; font-weight: 500; }
    .btn-pkg { background: white; color: var(--dark); text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; transition: 0.3s; }
    .btn-pkg:hover { background: var(--primary); color: white; }

    /* --- ITEM SATUAN UI --- */
    .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; }
    .item-card { background: white; border-radius: 20px; overflow: hidden; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; border: 1px solid #f0f2f5; position: relative; z-index: 2; }
    .item-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border-color: #e2e8f0; }
    .item-img { width: 100%; height: 220px; border-radius: 12px; overflow: hidden; margin-bottom: 15px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;}
    .item-img img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .item-card:hover .item-img img { transform: scale(1.05); }
    .item-info h3 { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .item-info .price { color: var(--primary); font-weight: 800; font-size: 1.1rem; margin-bottom: 15px; display: block; }
    .btn-item { display: block; width: 100%; text-align: center; padding: 12px; border: 1px solid #e2e8f0; color: var(--dark); border-radius: 10px; font-weight: 600; transition: 0.3s; background: #f8fafc; }
    .btn-item:hover { background: var(--primary); border-color: var(--primary); color: white; }

    /* --- KEUNGGULAN KAMI --- */
    .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px; }
    .feature-card { background: white; padding: 40px 30px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); text-align: center; border: 1px solid #f0f2f5; transition: 0.4s; position: relative; overflow: hidden; z-index: 1; }
    .feature-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--primary); transform: scaleX(0); transition: 0.4s; transform-origin: left; }
    .feature-card:hover::before { transform: scaleX(1); }
    .feature-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .feature-icon-wrapper { width: 80px; height: 80px; margin: 0 auto 25px; background: #fff8f0; color: var(--primary); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; transition: 0.4s; }
    .feature-card:hover .feature-icon-wrapper { background: var(--primary); color: white; transform: scale(1.1); }
    .feature-card h3 { font-size: 1.3rem; color: var(--dark); font-weight: 800; margin-bottom: 15px; }
    .feature-card p { font-size: 0.95rem; color: #64748b; line-height: 1.6; }

    @media (max-width: 992px) { .package-grid { grid-template-columns: 1fr; } }
    @media (max-width: 768px) {
        .hero h1 { font-size: 3rem; }
        .section-head { flex-direction: column; align-items: flex-start; }
        .pkg-card { flex-direction: column; }
        .pkg-img, .pkg-content { width: 100%; }
        .pkg-img { height: 200px; }
        .pkg-img::after { background: linear-gradient(to bottom, transparent, #1e293b); width: 100%; height: 30%; top: auto; }
    }
</style>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>
<div class="bg-shape shape-3"></div>

<?php if (empty($search_text)): ?>
    <section class="hero">
        <div class="hero-content">
            <h1>Bebaskan Jiwa<br>Petualangmu.</h1>
            <p>Rental perlengkapan outdoor premium yang dirawat layaknya milik sendiri. Bersih, aman, dan siap menemanimu menaklukkan puncak tertinggi Nusantara.</p>
            <a href="#paket" class="btn-cta">Eksplorasi Gear</a>
        </div>
    </section>
<?php else: ?>
    <div class="search-indicator">
        <h2>Hasil Pencarian untuk: <span>"<?php echo htmlspecialchars($search_text); ?>"</span></h2>
        <p>Menampilkan gear dan paket yang sesuai dengan kata kunci Anda.</p>
        <a href="index.php" class="btn-clear-search"><i class="fas fa-times"></i> Hapus Pencarian</a>
    </div>
<?php endif; ?>


<?php if ($res_paket && $res_paket->num_rows > 0): ?>
<section id="paket" class="section-wrapper" style="<?php echo !empty($search_text) ? 'margin-top: 20px;' : ''; ?>">
    <div class="section-head">
        <div>
            <h2>🔥 Bundling Super Hemat</h2>
            <p>Pilih paket agar persiapan mendaki lebih praktis dan murah.</p>
        </div>
    </div>

    <div class="package-grid">
        <?php while($row = $res_paket->fetch_assoc()): ?>
            <div class="pkg-card">
                <div class="pkg-img">
                    <span class="pkg-badge"><i class="fas fa-star"></i> PAKET</span>
                    <img src="<?php echo !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://via.placeholder.com/600x600?text=Paket+Sewa'; ?>">
                </div>
                <div class="pkg-content">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="pkg-price">
                        Rp <?php echo number_format($row['rate_per_day'], 0, ',', '.'); ?><span> / Hari</span>
                    </div>
                    <a href="detail_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-pkg">Sewa Paket Ini</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>

<section id="katalog" class="section-wrapper" style="margin-top: <?php echo ($res_paket && $res_paket->num_rows > 0) ? '0' : '80px'; ?>">
    <div class="section-head">
        <div>
            <h2>🎒 Sewa Eceran</h2>
            <p>Butuh tambahan barang? Pilih peralatan satuan dengan kualitas terjamin.</p>
        </div>
    </div>

    <div class="item-grid">
        <?php
        if ($res_item && $res_item->num_rows > 0) {
            while($row = $res_item->fetch_assoc()) {
        ?>
            <div class="item-card">
                <div class="item-img">
                    <img src="<?php echo !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://via.placeholder.com/400x400?text=Gear'; ?>">
                </div>
                <div class="item-info">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <span class="price">Rp <?php echo number_format($row['rate_per_day'], 0, ',', '.'); ?> <span style="font-size:0.8rem; color:#888;">/ hr</span></span>
                    <a href="detail_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-item">Detail Gear</a>
                </div>
            </div>
        <?php
            }
        } else {
            echo "<p style='grid-column: 1/-1; text-align:center; color:#888;'>Tidak ada item yang sesuai dengan pencarian Anda.</p>";
        }
        ?>
    </div>
</section>

<?php if (empty($search_text)): ?>
<section class="section-wrapper" style="margin-bottom: 120px;">
    <div class="section-head" style="text-align: center; display: block; margin-bottom: 50px;">
        <h2>✨ Kenapa Memilih Se7en Summits?</h2>
        <p style="margin: 0 auto; max-width: 600px;">Kami tidak hanya menyewakan barang, tapi memberikan pengalaman dan keamanan ekstra untuk setiap petualanganmu.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-wrapper"><i class="fas fa-shield-alt"></i></div>
            <h3>Peralatan Terawat</h3>
            <p>Setiap gear selalu dicuci, disterilisasi, dan dicek kelayakannya setelah dan sebelum disewakan demi keamanan dan kenyamananmu di alam bebas.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper"><i class="fas fa-tags"></i></div>
            <h3>Harga Kompetitif</h3>
            <p>Sewa satuan atau paket, kami menawarkan harga terbaik yang ramah di kantong pendaki mahasiswa maupun penjelajah profesional.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper"><i class="fas fa-headset"></i></div>
            <h3>Layanan Responsif</h3>
            <p>Butuh bantuan dadakan, konsultasi alat, atau jadwal ambil gear di malam hari? Admin kami selalu siap membantu menjadwalkan perjalananmu.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>