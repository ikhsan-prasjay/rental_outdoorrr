<?php
// includes/header.php
// Logika PHP yang sama tetap dipertahankan
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : '';

// Mendapatkan nama file saat ini untuk menentukan class 'active' pada menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se7en Summits Outdoor - Gear for Adventure</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS VARS & RESET (PROFESIONAL) --- */
        :root {
            --primary: #d35400; /* Warna Oranye Petualang */
            --dark: #1e272e;    /* Warna Gelap Elegan */
            --light: #f4f6f8;   /* Warna Background Terang */
            --text-gray: #7f8c8d;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Montserrat', sans-serif; background-color: var(--light); color: var(--dark); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        ul { list-style: none; }

        /* --- STYLING HEADER --- */
        .main-header {
            background-color: var(--white);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 80px;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        /* --- LOGO --- */
        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -1.5px;
            text-transform: uppercase;
        }
        .logo i {
            color: var(--primary);
            font-size: 2rem;
            transform: rotate(-5deg);
        }

        /* --- MENU NAVIGASI --- */
        .nav-wrapper { display: flex; align-items: center; gap: 30px; }
        .nav-links { display: flex; gap: 10px; }
        .nav-links li a {
            padding: 10px 15px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }
        
        /* Animasi Garis Bawah di Hover */
        .nav-links li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
            transform: translateX(-50%);
        }
        .nav-links li a:hover { color: var(--primary); }
        .nav-links li a:hover::after,
        .nav-links li a.active::after { width: 80%; }
        .nav-links li a.active { color: var(--primary); font-weight: 700; }

        /* Panel Admin (Merah Pop) */
        .nav-links li .admin-link { color: #e74c3c; border: 1px solid transparent; }
        .nav-links li .admin-link:hover { background: #feebeb; border-color: #f5c6cb; color: #c0392b; border-radius: 4px; }

        /* --- SEARCH BAR --- */
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            background: #f0f2f5;
            border-radius: 50px;
            padding: 5px 15px;
            width: 250px;
            transition: var(--transition);
        }
        .search-box:focus-within { background: var(--white); box-shadow: 0 0 0 2px rgba(211, 84, 0, 0.2); width: 300px; }
        .search-box input {
            border: none;
            background: transparent;
            padding: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--dark);
            width: 100%;
        }
        .search-box input:focus { outline: none; }
        .search-box i { color: var(--text-gray); cursor: pointer; }
        .search-box i:hover { color: var(--primary); }

        /* --- USER CONTROLS (DROPDOWN) --- */
        .user-controls { display: flex; align-items: center; gap: 20px; }
        
        .btn-auth-primary {
            background: var(--primary);
            color: var(--white);
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(211, 84, 0, 0.2);
        }
        .btn-auth-primary:hover { background: #b04600; transform: translateY(-2px); }

        .user-dropdown-trigger {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50px;
            position: relative;
        }
        .user-dropdown-trigger:hover { background-color: #f8f9fa; }
        .avatar-circle {
            width: 45px;
            height: 45px;
            background: #bdc3c7;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            text-transform: uppercase;
        }
        .user-dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            width: 220px;
            padding: 15px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
        }
        .user-dropdown-trigger:hover .user-dropdown-content { opacity: 1; visibility: visible; transform: translateY(0); }
        .user-dropdown-content a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #555;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .user-dropdown-content a:hover { background: #f8f9fa; color: var(--primary); }
        .user-dropdown-content .divider { height: 1px; background: #eee; margin: 10px 0; }
        .logout-link:hover { color: #e74c3c !important; }

        /* --- MOBILE MENU (HIDDEN ON DESKTOP) --- */
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.8rem; color: var(--dark); cursor: pointer; }

        /* --- MEDIA QUERIES (RESPONSIVE) --- */
        @media (max-width: 1200px) { .search-box { width: 180px; } .search-box:focus-within { width: 220px; } }
        @media (max-width: 992px) {
            .nav-wrapper { gap: 15px; }
            .nav-links li a { font-size: 0.85rem; padding: 10px 8px; }
            .search-box { display: none; } /* Sembunyikan search di tablet */
        }
        @media (max-width: 768px) {
            .nav-wrapper { display: none; } /* Sembunyikan nav desktop di mobile */
            .mobile-menu-btn { display: block; } /* Munculkan hamburger menu */
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-container">
            
            <a href="index.php" class="logo">
                <i class="fas fa-mountain"></i> 
                <span>Se7en Summits</span>
            </a>

            <div class="nav-wrapper">
                <ul class="nav-links">
                    <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Beranda</a></li>
                    <li><a href="index.php#katalog">Katalog Gear</a></li>
                    <li><a href="cara_sewa.php" class="<?php echo ($current_page == 'cara_sewa.php') ? 'active' : ''; ?>">Cara Sewa</a></li>
                    <li><a href="kebijakan.php" class="<?php echo ($current_page == 'kebijakan.php') ? 'active' : ''; ?>">Kebijakan</a></li>
                    
                    <?php if ($user_role === 'admin'): ?>
                        <li><a href="admin_pesanan.php" class="admin-link"><i class="fas fa-shield-alt"></i> Panel Admin</a></li>
                    <?php endif; ?>
                </ul>

                <form class="search-box" action="index.php#katalog" method="GET">
                    <input type="text" name="search" placeholder="Cari tenda, cari tas..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <i class="fas fa-search"></i>
                </form>
            </div>

            <div class="user-controls">
                <?php if ($is_logged_in): ?>
                    <div class="user-dropdown-trigger">
                        <div class="avatar-circle">
                            <?php echo substr($user_name, 0, 1); ?>
                        </div>
                        <span style="font-weight: 600; font-size: 0.9rem;">Hai, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: #7f8c8d;"></i>
                        
                        <div class="user-dropdown-content">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
                            <a href="riwayat_sewa.php"><i class="fas fa-history"></i> Riwayat Sewa</a>
                            <div class="divider"></div>
                            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-auth-primary"><i class="fas fa-user"></i> Login / Daftar</a>
                <?php endif; ?>

                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

        </div>
    </header>