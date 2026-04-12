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

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_text = trim($_GET['search']);
    $safe_search = $koneksi->real_escape_string($search_text);
    $search_filter = " AND (name LIKE '%$safe_search%' OR description LIKE '%$safe_search%') ";
}

// Ambil Data Paket
$sql_paket = "SELECT id_equipment, name, rate_per_day, main_image_url, description FROM equipment_products WHERE rent_type = 'package' $search_filter ORDER BY created_at DESC";
$res_paket = $koneksi->query($sql_paket);

// Ambil Data Satuan
$sql_item = "SELECT id_equipment, name, rate_per_day, main_image_url FROM equipment_products WHERE rent_type = 'item' $search_filter ORDER BY created_at DESC";
$res_item = $koneksi->query($sql_item);

require_once 'includes/header.php';
?>

<style>
    /* --- BACKGROUND DECORATION --- */
    body {
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z' fill='%23d35400' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
        overflow-x: hidden;
    }

    .bg-shape { position: absolute; border-radius: 50%; filter: blur(100px); z-index: -1; opacity: 0.3; pointer-events: none;}
    .shape-1 { top: 15%; left: -10%; width: 400px; height: 400px; background: #d35400; }
    .shape-2 { top: 40%; right: -5%; width: 500px; height: 500px; background: #3498db; }

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
    .section-wrapper { max-width: 1400px; margin: 100px auto; padding: 0 30px; position: relative; z-index: 5; }
    .section-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
    .section-head h2 { font-size: 2.5rem; color: var(--dark); font-weight: 800; line-height: 1.2; }
    .section-head p { color: #64748b; font-size: 1.1rem; margin-top: 5px; }

    /* --- 🌟 SLIDER PAKET BUNDLING (NEW UI) 🌟 --- */
    .slider-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden; /* Sembunyikan scrollbar luar */
        padding: 20px 0 50px; /* Ruang bawah untuk bayangan */
    }
    
    /* Efek Gradasi (Fade) di Tepi Kiri & Kanan */
    .slider-wrapper::before, .slider-wrapper::after {
        content: ''; position: absolute; top: 0; width: 100px; height: 100%; z-index: 10; pointer-events: none;
    }
    .slider-wrapper::before { left: 0; background: linear-gradient(to right, var(--light) 0%, transparent 100%); }
    .slider-wrapper::after { right: 0; background: linear-gradient(to left, var(--light) 0%, transparent 100%); }

    /* Container yang bisa digeser */
    .package-slider {
        display: flex; gap: 30px; overflow-x: auto; scroll-behavior: auto;
        scrollbar-width: none; /* Sembunyikan scrollbar Firefox */
        -ms-overflow-style: none; /* Sembunyikan scrollbar IE/Edge */
        cursor: grab;
        padding: 10px 0; /* Beri ruang untuk hover animation */
    }
    .package-slider::-webkit-scrollbar { display: none; } /* Sembunyikan scrollbar Chrome */
    .package-slider.active { cursor: grabbing; } /* Kursor berubah saat ditarik */

    /* Desain Kartu Bundling Super Premium */
    .pkg-card {
        min-width: 480px; max-width: 480px; height: 230px; /* Ukuran tetap agar rapi berjejer */
        flex-shrink: 0; /* Mencegah kartu menyusut */
        background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        border-radius: 24px; display: flex; overflow: hidden; position: relative;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s;
        color: white; border: 1px solid rgba(255,255,255,0.05); user-select: none;
    }
    .pkg-card:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 25px 45px rgba(211, 84, 0, 0.2); border-color: rgba(211, 84, 0, 0.4); z-index: 5; }
    
    .pkg-img { width: 45%; position: relative; background: #2c3e50; overflow: hidden; }
    .pkg-img img { width: 100%; height: 100%; object-fit: cover; transition: 0.8s ease; pointer-events: none;}
    .pkg-card:hover .pkg-img img { transform: scale(1.15) rotate(2deg); } /* Efek zoom foto */
    .pkg-img::after { content:''; position:absolute; top:0; right:0; bottom:0; width:50%; background: linear-gradient(to right, transparent, #1e293b); }
    
    .pkg-badge { position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); color: white; padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.3); z-index: 2; letter-spacing: 1px;}
    
    .pkg-content { width: 55%; padding: 25px 20px; display: flex; flex-direction: column; justify-content: center; z-index: 2; }
    .pkg-content h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .pkg-content p { font-size: 0.9rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pkg-price { font-size: 1.7rem; font-weight: 800; color: #f59e0b; margin-bottom: 15px; }
    .pkg-price span { font-size: 0.9rem; color: #94a3b8; font-weight: 500; }
    
    .btn-pkg { background: white; color: var(--dark); text-align: center; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; transition: 0.3s; }
    .btn-pkg:hover { background: var(--primary); color: white; box-shadow: 0 5px 15px rgba(211,84,0,0.4); }

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

    /* Responsive untuk Slider */
    @media (max-width: 768px) {
        .slider-wrapper::before, .slider-wrapper::after { width: 30px; } /* Kurangi efek fade di HP */
        .pkg-card { min-width: 320px; max-width: 320px; height: auto; flex-direction: column; }
        .pkg-img { width: 100%; height: 180px; }
        .pkg-img::after { background: linear-gradient(to bottom, transparent, #1e293b); width: 100%; height: 40%; top: auto; }
        .pkg-content { width: 100%; padding: 20px; }
        .hero h1 { font-size: 3rem; }
    }
</style>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>

<?php if (empty($search_text)): ?>
    <section class="hero">
        <div class="hero-content">
            <h1>Bebaskan Jiwa<br>Petualangmu.</h1>
            <p>Rental perlengkapan outdoor premium yang dirawat layaknya milik sendiri. Bersih, aman, dan siap menemanimu menaklukkan puncak tertinggi Nusantara.</p>
            <a href="#paket" class="btn-cta">Eksplorasi Gear</a>
        </div>
    </section>
<?php else: ?>
    <div class="section-wrapper" style="text-align: center; margin-top: 50px;">
        <h2>Hasil Pencarian untuk: <span style="color:var(--primary);">"<?php echo htmlspecialchars($search_text); ?>"</span></h2>
        <a href="index.php" class="btn-item" style="display:inline-block; width:auto; padding:8px 20px; margin-top:10px;"><i class="fas fa-times"></i> Hapus Pencarian</a>
    </div>
<?php endif; ?>


<?php if ($res_paket && $res_paket->num_rows > 0): ?>
<section id="paket" class="section-wrapper" style="<?php echo !empty($search_text) ? 'margin-top: 20px;' : ''; ?>">
    <div class="section-head">
        <div>
            <h2>🔥 Bundling Super Hemat</h2>
            <p>Geser untuk melihat paket persiapan mendaki yang lebih praktis dan murah.</p>
        </div>
    </div>

    <div class="slider-wrapper">
        <div class="package-slider" id="packageSlider">
            
            <?php 
            // Simpan HTML card ke dalam array agar bisa diduplikasi oleh Javascript nanti
            $cards_html = "";
            while($row = $res_paket->fetch_assoc()): 
                $img = !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://via.placeholder.com/600x600?text=Paket+Sewa';
                $name = htmlspecialchars($row['name']);
                $desc = htmlspecialchars($row['description']);
                $price = number_format($row['rate_per_day'], 0, ',', '.');
                $id = $row['id_equipment'];

                $card = "
                <div class='pkg-card'>
                    <div class='pkg-img'>
                        <span class='pkg-badge'><i class='fas fa-star'></i> PAKET</span>
                        <img src='{$img}' draggable='false'>
                    </div>
                    <div class='pkg-content'>
                        <h3>{$name}</h3>
                        <p>{$desc}</p>
                        <div class='pkg-price'>Rp {$price}<span> / Hari</span></div>
                        <a href='detail_produk.php?id={$id}' class='btn-pkg'>Sewa Paket Ini</a>
                    </div>
                </div>";
                
                $cards_html .= $card;
                echo $card;
            endwhile; 
            ?>

        </div>
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
            echo "<p style='grid-column: 1/-1; text-align:center; color:#888;'>Tidak ada item yang tersedia.</p>";
        }
        ?>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const slider = document.getElementById('packageSlider');
    if (!slider) return;

    // 1. Duplikasi konten agar bisa looping terus menerus (Infinite Loop)
    const itemsHTML = slider.innerHTML;
    slider.innerHTML += itemsHTML + itemsHTML + itemsHTML; // Copy 3x agar mulus walaupun itemnya sedikit

    let isDown = false;
    let startX;
    let scrollLeft;
    let isHovered = false;

    // 2. Logika Auto-Scroll
    function autoScroll() {
        if (!isHovered && !isDown) {
            slider.scrollLeft += 1.5; // Kecepatan scroll (ubah angka ini jika ingin lebih cepat/lambat)
            
            // Jika sudah scroll setengah jalan, kembalikan ke awal secara diam-diam (seamless)
            if (slider.scrollLeft >= slider.scrollWidth / 2) {
                slider.scrollLeft = 0;
            }
        }
        requestAnimationFrame(autoScroll); // Looping animasi dengan sangat mulus (60fps)
    }

    // Mulai animasi
    requestAnimationFrame(autoScroll);

    // 3. Pause saat kursor diarahkan ke kartu (Hover)
    slider.addEventListener('mouseenter', () => isHovered = true);
    slider.addEventListener('mouseleave', () => { isHovered = false; isDown = false; });
    slider.addEventListener('touchstart', () => isHovered = true);
    slider.addEventListener('touchend', () => { setTimeout(() => isHovered = false, 1500); });

    // 4. Fitur Drag & Swipe Manual (Geser Kanan Kiri)
    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.classList.add('active');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });
    
    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.classList.remove('active');
    });
    
    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 2; // Angka 2 adalah kecepatan saat ditarik
        slider.scrollLeft = scrollLeft - walk;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>