<?php
// index.php
session_start(); 
require_once 'koneksi.php'; 

// Cek Login untuk Navigasi
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : '';

// Ambil Produk Terbaru
$sql = "SELECT id_equipment, name, rate_per_day, main_image_url FROM equipment_products ORDER BY created_at DESC LIMIT 8";
$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se7en Summits Outdoor - Gear for Adventure</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS VARIABLE & RESET --- */
        :root {
            --primary: #d35400; /* Warna Oranye Gunung */
            --dark: #2c3e50;
            --light: #f4f6f8;
            --text-gray: #7f8c8d;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--light); color: var(--dark); }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* --- HEADER --- */
        .main-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky; /* Membuat header melayang saat scroll */
            top: 0;
            z-index: 1000;
        }
        .logo { font-size: 1.5rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 10px; letter-spacing: -1px; }
        .logo i { color: var(--primary); font-size: 1.8rem; }
        .logo span { color: var(--primary); }
        
        .nav-links a { margin-left: 25px; font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }
        .btn-auth { background: var(--primary); color: white; padding: 8px 20px; border-radius: 50px; }
        .btn-auth:hover { background: #a04000; color: white; }

        /* --- HERO BANNER (GAMBAR BESAR) --- */
        .hero {
            /* Placeholder gambar gunung */
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            height: 600px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero h1 { font-size: 4rem; margin-bottom: 10px; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); font-weight: 800; }
        .hero p { font-size: 1.3rem; margin-bottom: 30px; opacity: 0.9; max-width: 700px; line-height: 1.6; }
        .btn-cta { background: var(--primary); color: white; padding: 15px 40px; border-radius: 50px; font-weight: bold; font-size: 1.1rem; transition: transform 0.3s; border: 2px solid var(--primary); }
        .btn-cta:hover { transform: scale(1.05); background: transparent; color: var(--primary); border-color: var(--primary); background: rgba(0,0,0,0.5); }

        /* --- KATALOG SECTION --- */
        .container { max-width: 1200px; margin: 60px auto; padding: 0 20px; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 2.2rem; color: var(--dark); margin-bottom: 10px; }
        .section-title p { color: var(--text-gray); font-size: 1.1rem; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 30px; }
        .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; border: 1px solid #eee; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .product-img { width: 100%; height: 220px; object-fit: cover; }
        .product-info { padding: 25px; }
        .product-info h3 { font-size: 1.2rem; margin-bottom: 8px; font-weight: 700; }
        .price { color: var(--primary); font-weight: bold; font-size: 1.1rem; margin-bottom: 20px; display: block; }
        .btn-detail { display: block; width: 100%; text-align: center; padding: 12px; border: 1px solid var(--primary); color: var(--primary); border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-detail:hover { background: var(--primary); color: white; }

        /* --- FOOTER (PROFESIONAL) --- */
        footer { background: #1a252f; color: #bbb; padding: 70px 0 20px; margin-top: 100px; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .footer-col h4 { color: white; margin-bottom: 25px; font-size: 1.2rem; letter-spacing: 1px; }
        .footer-col ul li { margin-bottom: 12px; }
        .footer-col ul li a:hover { color: var(--primary); text-decoration: underline; }
        .social-links a { display: inline-block; width: 40px; height: 40px; background: #333; color: white; text-align: center; line-height: 40px; border-radius: 50%; margin-right: 10px; transition: 0.3s; }
        .social-links a:hover { background: var(--primary); }
        .copyright { text-align: center; margin-top: 60px; padding-top: 25px; border-top: 1px solid #333; font-size: 0.9rem; }

        /* --- TOMBOL WA MELAYANG --- */
        .float-wa { position: fixed; bottom: 30px; right: 30px; background: #25d366; color: white; width: 60px; height: 60px; border-radius: 50%; text-align: center; line-height: 60px; font-size: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 999; transition: 0.3s; }
        .float-wa:hover { transform: scale(1.1); background: #128c7e; }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .main-header { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <i class="fas fa-mountain"></i> Se7en Summits
        </div>
        <nav class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="#katalog">Katalog Gear</a>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="admin_pesanan.php" style="color: #e74c3c; font-weight: bold;">Panel Admin</a>
            <?php endif; ?>

            <?php if ($is_logged_in): ?>
                <span style="margin-left: 20px; color: #7f8c8d;">Hai, <?php echo htmlspecialchars($user_name); ?></span>
                <a href="logout.php" class="btn-auth" style="margin-left: 10px; background: #333;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-auth" style="margin-left: 20px;">Login / Daftar</a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Taklukkan Puncakmu</h1>
            <p>Se7en Summits menyediakan peralatan pendakian kelas dunia untuk petualangan tanpa batas.</p>
            <a href="#katalog" class="btn-cta">Sewa Sekarang</a>
        </div>
    </section>

    <section id="katalog" class="container">
        <div class="section-title">
            <h2>Pilihan Gear Terbaik</h2>
            <p>Lengkapi kebutuhan ekspedisi Anda dengan peralatan terawat dan higienis</p>
        </div>

        <div class="product-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <div class="product-card">
                    <img src="<?php echo !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://source.unsplash.com/400x300/?camping,hiking,mountain'; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-img">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <span class="price">Rp <?php echo number_format($row['rate_per_day'], 0, ',', '.'); ?> / Hari</span>
                        <a href="detail_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-detail">Lihat Detail</a>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center; width:100%;'>Belum ada produk tersedia.</p>";
            }
            ?>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h4>Se7en Summits Outdoor</h4>
                <p>Mitra terpercaya pendaki Indonesia. Kami menyewakan peralatan hiking, camping, dan climbing dengan standar keamanan tinggi.</p>
            </div>
            <div class="footer-col">
                <h4>Layanan Pelanggan</h4>
                <ul>
                    <li><a href="#">Cara Penyewaan</a></li>
                    <li><a href="#">Syarat & Ketentuan</a></li>
                    <li><a href="#">Kebijakan Denda</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> Basecamp Se7en, Jl. Merapi No. 7</li>
                    <li><i class="fas fa-phone"></i> +62 812-3456-7890</li>
                    <li><i class="fas fa-envelope"></i> admin@se7ensummits.com</li>
                </ul>
                <div class="social-links" style="margin-top: 15px;">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Se7en Summits Outdoor. All Rights Reserved.</p>
        </div>
    </footer>

    <a href="https://wa.me/6281234567890" target="_blank" class="float-wa">
        <i class="fab fa-whatsapp"></i>
    </a>

</body>
</html>