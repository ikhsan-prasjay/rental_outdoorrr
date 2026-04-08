<?php
// includes/header.php

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : '';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Se7en Summits Outdoor - Gear for Adventure</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS VARS & RESET --- */
        :root {
            --primary: #d35400; 
            --dark: #0f172a;    
            --light: #f8fafc;   
            --text-gray: #64748b;
            --white: #ffffff;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Montserrat', sans-serif; background-color: var(--light); color: var(--dark); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        ul { list-style: none; }

        /* --- STYLING HEADER DESKTOP --- */
        .main-header {
            background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; height: 80px;
        }

        .header-container {
            max-width: 1400px; margin: 0 auto; padding: 0 30px;
            display: flex; justify-content: space-between; align-items: center; height: 100%;
        }

        .logo { font-size: 1.5rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: -0.5px; z-index: 1001; position: relative;}
        .logo i { color: var(--primary); font-size: 1.8rem; transform: rotate(-5deg); }

        /* --- MENU NAVIGASI DESKTOP --- */
        .nav-wrapper { display: flex; align-items: center; gap: 30px; }
        .nav-links { display: flex; gap: 5px; }
        .nav-links li a {
            padding: 10px 15px; font-weight: 600; font-size: 0.9rem; color: var(--dark); text-transform: uppercase; position: relative; border-radius: 8px; transition: var(--transition);
        }
        .nav-links li a:hover { background: #f1f5f9; color: var(--primary); }
        .nav-links li a.active { color: var(--primary); font-weight: 800; }
        
        .admin-badge { background: #fee2e2; color: #dc2626 !important; padding: 5px 12px !important; border-radius: 50px !important; font-size: 0.8rem !important; margin-left: 10px; }
        .admin-badge:hover { background: #fecaca !important; }

        /* --- SEARCH BAR DESKTOP --- */
        .search-box {
            display: flex; align-items: center; background: #f1f5f9; border-radius: 50px; padding: 6px 15px; width: 220px; transition: var(--transition); border: 1px solid transparent;
        }
        .search-box:focus-within { background: var(--white); border-color: var(--primary); width: 280px; box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1); }
        .search-box input { border: none; background: transparent; padding: 8px; font-family: inherit; font-size: 0.9rem; width: 100%; outline: none; }
        .search-box i { color: var(--text-gray); }

        /* --- USER CONTROLS DESKTOP --- */
        .user-controls { display: flex; align-items: center; gap: 15px; }
        .btn-auth-primary { background: var(--primary); color: var(--white); padding: 10px 24px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(211, 84, 0, 0.2); }
        .btn-auth-primary:hover { background: #b04600; transform: translateY(-2px); }

        .user-dropdown-trigger { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 5px 10px; border-radius: 50px; position: relative; transition: 0.3s; border: 1px solid transparent; }
        .user-dropdown-trigger:hover { background: #f8fafc; border-color: #e2e8f0; }
        .avatar-circle { width: 38px; height: 38px; background: linear-gradient(135deg, var(--dark), #334155); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; text-transform: uppercase; }
        
        .user-dropdown-content {
            position: absolute; top: 110%; right: 0; background: var(--white); box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 16px; width: 220px; padding: 10px 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: var(--transition); border: 1px solid #f1f5f9; z-index: 1000;
        }
        .user-dropdown-trigger:hover .user-dropdown-content { opacity: 1; visibility: visible; transform: translateY(0); }
        .user-dropdown-content a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #475569; font-size: 0.9rem; font-weight: 600; transition: 0.2s; }
        .user-dropdown-content a:hover { background: #f8fafc; color: var(--primary); padding-left: 25px; }
        .user-dropdown-content .divider { height: 1px; background: #f1f5f9; margin: 5px 0; }
        .logout-link { color: #ef4444 !important; } .logout-link:hover { background: #fef2f2 !important; }

        /* --- MOBILE HAMBURGER BUTTON --- */
        .mobile-toggle { display: none; background: transparent; border: none; font-size: 1.8rem; color: var(--dark); cursor: pointer; z-index: 1002; transition: var(--transition); }
        .mobile-toggle:focus { outline: none; }

        /* --- OFF-CANVAS MOBILE MENU (APP-LIKE DESIGN) --- */
        .mobile-nav-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
            z-index: 998; opacity: 0; visibility: hidden; transition: var(--transition);
        }
        .mobile-nav-overlay.active { opacity: 1; visibility: visible; }

        .mobile-nav-menu {
            position: fixed; top: 0; right: -100%; width: 300px; max-width: 85%; height: 100vh; background: var(--white);
            z-index: 999; display: flex; flex-direction: column; box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto;
        }
        .mobile-nav-menu.active { right: 0; }

        /* Header Menu Mobile */
        .mobile-menu-header { display: flex; justify-content: space-between; align-items: center; padding: 25px 25px 15px; border-bottom: 1px solid #f1f5f9; }
        .mobile-menu-header h3 { font-size: 1.2rem; font-weight: 800; color: var(--dark); }
        .mobile-close-btn { background: #f1f5f9; border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 1.2rem; color: var(--dark); cursor: pointer; display: flex; justify-content: center; align-items: center; transition: 0.3s; }
        .mobile-close-btn:hover { background: #e2e8f0; color: #ef4444; }

        .mobile-menu-body { padding: 25px; flex: 1; display: flex; flex-direction: column; }

        /* Search Mobile */
        .mobile-search-box { display: flex; align-items: center; background: #f1f5f9; border-radius: 12px; padding: 12px 15px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .mobile-search-box input { border: none; background: transparent; font-family: inherit; font-size: 0.95rem; width: 100%; outline: none; margin-right: 10px; }
        .mobile-search-box i { color: var(--text-gray); }

        /* Links Mobile */
        .mobile-nav-menu .nav-links { flex-direction: column; gap: 10px; width: 100%; margin-bottom: 30px; }
        .mobile-nav-menu .nav-links li a { 
            display: flex; align-items: center; padding: 14px 18px; font-size: 1.05rem; font-weight: 700; color: var(--text-gray); background: var(--white); border-radius: 12px; transition: 0.3s; border: 1px solid transparent;
        }
        .mobile-nav-menu .nav-links li a i { width: 30px; font-size: 1.2rem; }
        .mobile-nav-menu .nav-links li a:hover { background: #f8fafc; color: var(--dark); }
        .mobile-nav-menu .nav-links li a.active { background: #fff7ed; color: var(--primary); border-color: #ffedd5; }
        
        .divider-mobile { height: 1px; background: #e2e8f0; margin: 10px 0 25px; }

        /* User Section Mobile */
        .mobile-user-section { margin-top: auto; padding-bottom: 20px; }
        
        .mobile-user-card { display: flex; align-items: center; gap: 15px; padding: 15px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
        .mobile-user-card .avatar-circle { width: 50px; height: 50px; font-size: 1.5rem; }
        .mobile-user-card .user-info h4 { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin-bottom: 2px; }
        .mobile-user-card .user-info p { font-size: 0.8rem; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }

        .mobile-action-link { display: flex; align-items: center; gap: 12px; padding: 14px 18px; font-size: 1rem; font-weight: 700; color: var(--dark); border-radius: 12px; transition: 0.3s; }
        .mobile-action-link i { color: var(--text-gray); font-size: 1.2rem; width: 25px; text-align: center; }
        .mobile-action-link:hover { background: #f1f5f9; }

        .mobile-btn-logout { display: flex; justify-content: center; align-items: center; gap: 10px; width: 100%; padding: 15px; margin-top: 15px; background: #fee2e2; color: #dc2626; border-radius: 12px; font-size: 1rem; font-weight: 800; transition: 0.3s; }
        .mobile-btn-logout:hover { background: #fecaca; }

        .mobile-guest-card { text-align: center; padding: 20px 15px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; }
        .mobile-guest-card p { font-size: 0.9rem; color: var(--text-gray); margin-bottom: 15px; line-height: 1.5; font-weight: 500;}
        .mobile-btn-login { display: flex; justify-content: center; align-items: center; gap: 10px; width: 100%; padding: 15px; background: var(--primary); color: white; border-radius: 12px; font-size: 1rem; font-weight: 800; box-shadow: 0 4px 15px rgba(211,84,0,0.2); }

        /* --- RESPONSIVE BREAKPOINTS --- */
        @media (max-width: 1024px) {
            .nav-wrapper { display: none; } 
            .user-controls { display: none; } 
            .mobile-toggle { display: block; } 
            .header-container { padding: 0 20px; }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Logika Highlight Menu Katalog
            function updateNavActiveState() {
                let hash = window.location.hash;
                let path = window.location.pathname;
                
                if (path.endsWith("index.php") || path.endsWith("/")) {
                    let navBeranda = document.querySelectorAll(".nav-beranda");
                    let navKatalog = document.querySelectorAll(".nav-katalog");
                    
                    navBeranda.forEach(el => el.classList.remove("active"));
                    navKatalog.forEach(el => el.classList.remove("active"));

                    if (hash === "#katalog" || hash === "#paket") {
                        navKatalog.forEach(el => el.classList.add("active"));
                    } else {
                        navBeranda.forEach(el => el.classList.add("active"));
                    }
                }
            }
            updateNavActiveState();
            window.addEventListener("hashchange", updateNavActiveState);

            // 2. Logika Mobile Menu Hamburger
            const mobileBtn = document.querySelector('.mobile-toggle');
            const closeBtn = document.querySelector('.mobile-close-btn');
            const mobileMenu = document.querySelector('.mobile-nav-menu');
            const mobileOverlay = document.querySelector('.mobile-nav-overlay');
            const mobileLinks = document.querySelectorAll('.mobile-nav-menu a');

            function openMenu() {
                mobileMenu.classList.add('active');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden'; 
            }

            function closeMenu() {
                mobileMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto'; 
            }

            if(mobileBtn) {
                mobileBtn.addEventListener('click', openMenu);
                closeBtn.addEventListener('click', closeMenu);
                mobileOverlay.addEventListener('click', closeMenu); 
                
                mobileLinks.forEach(link => {
                    link.addEventListener('click', closeMenu);
                });
            }
        });
    </script>
</head>
<body>

    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-mountain"></i> <span>Se7en Summits</span>
            </a>

            <div class="nav-wrapper">
                <ul class="nav-links">
                    <li><a href="index.php" class="nav-beranda <?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Beranda</a></li>
                    <li><a href="index.php#katalog" class="nav-katalog">Katalog Gear</a></li>
                    <li><a href="cara_sewa.php" class="<?php echo ($current_page == 'cara_sewa.php') ? 'active' : ''; ?>">Cara Sewa</a></li>
                    <li><a href="kebijakan.php" class="<?php echo ($current_page == 'kebijakan.php') ? 'active' : ''; ?>">Kebijakan</a></li>
                    <?php if ($user_role === 'admin'): ?><li><a href="admin_pesanan.php" class="admin-badge"><i class="fas fa-shield-alt"></i> Panel Admin</a></li><?php endif; ?>
                </ul>
                <form class="search-box" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Cari tenda, tas...">
                    <button type="submit" style="background:none; border:none; cursor:pointer;"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="user-controls">
                <?php if ($is_logged_in): ?>
                    <div class="user-dropdown-trigger">
                        <div class="avatar-circle"><?php echo substr($user_name, 0, 1); ?></div>
                        <span style="font-weight: 700; font-size: 0.9rem; color: #1e293b;"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: #94a3b8;"></i>
                        <div class="user-dropdown-content">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
                            <a href="riwayat_sewa.php"><i class="fas fa-history"></i> Riwayat Sewa</a>
                            <div class="divider"></div>
                            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-auth-primary"><i class="fas fa-user"></i> Login / Daftar</a>
                <?php endif; ?>
            </div>

            <button class="mobile-toggle"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <div class="mobile-nav-overlay"></div>
    <div class="mobile-nav-menu">
        
        <div class="mobile-menu-header">
            <h3>Menu Se7en</h3>
            <button class="mobile-close-btn"><i class="fas fa-times"></i></button>
        </div>

        <div class="mobile-menu-body">
            <form class="mobile-search-box" action="index.php" method="GET">
                <input type="text" name="search" placeholder="Cari alat pendakian...">
                <button type="submit" style="background:none; border:none; cursor:pointer;"><i class="fas fa-search"></i></button>
            </form>

            <ul class="nav-links">
                <li><a href="index.php" class="nav-beranda <?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="index.php#katalog" class="nav-katalog"><i class="fas fa-box-open"></i> Katalog Gear</a></li>
                <li><a href="cara_sewa.php" class="<?php echo ($current_page == 'cara_sewa.php') ? 'active' : ''; ?>"><i class="fas fa-map-signs"></i> Cara Sewa</a></li>
                <li><a href="kebijakan.php" class="<?php echo ($current_page == 'kebijakan.php') ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Kebijakan</a></li>
                <?php if ($user_role === 'admin'): ?>
                    <li><a href="admin_pesanan.php" style="color: #ef4444; background: #fef2f2; border-color: #fecaca;"><i class="fas fa-shield-alt"></i> Panel Admin</a></li>
                <?php endif; ?>
            </ul>

            <div class="divider-mobile"></div>

            <div class="mobile-user-section">
                <?php if ($is_logged_in): ?>
                    <div class="mobile-user-card">
                        <div class="avatar-circle"><?php echo substr($user_name, 0, 1); ?></div>
                        <div class="user-info">
                            <h4><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></h4>
                            <p>Member Area</p>
                        </div>
                    </div>
                    
                    <a href="profile.php" class="mobile-action-link"><i class="fas fa-user-edit"></i> Pengaturan Profil</a>
                    <a href="riwayat_sewa.php" class="mobile-action-link"><i class="fas fa-history"></i> Riwayat Sewa</a>
                    
                    <a href="logout.php" class="mobile-btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Keluar Sistem
                    </a>

                <?php else: ?>
                    <div class="mobile-guest-card">
                        <p>Masuk ke akunmu untuk mempermudah proses penyewaan dan melihat riwayat pesanan.</p>
                        <a href="login.php" class="mobile-btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login / Daftar Akun
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>