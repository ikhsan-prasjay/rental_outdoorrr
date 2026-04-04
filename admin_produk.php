<?php
// admin_produk.php
session_start();
require_once 'koneksi.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Ambil semua data produk beserta jumlah stok fisik yang statusnya 'available'
// Menggunakan Subquery agar aman dari error strict mode
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM equipment_items WHERE product_id = p.id_equipment AND status = 'available') as stok_tersedia 
          FROM equipment_products p 
          ORDER BY p.id_equipment DESC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Gear - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS RESET & VARIABLES (Sama dengan admin_pesanan) --- */
        :root {
            --sidebar-bg: #0f172a; 
            --sidebar-hover: #1e293b; 
            --bg-main: #f8fafc; 
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --primary: #d35400; 
            --border-soft: #e2e8f0;
            --radius-lg: 16px;
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-dark); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* --- SIDEBAR LAYOUT --- */
        .sidebar {
            width: 260px; background-color: var(--sidebar-bg); color: white;
            display: flex; flex-direction: column; position: fixed;
            top: 0; bottom: 0; left: 0; z-index: 100;
        }

        .sidebar-brand {
            padding: 25px 20px; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 12px;
        }
        .sidebar-brand i { color: var(--primary); font-size: 1.5rem; }

        .sidebar-nav { padding: 20px 15px; flex: 1; list-style: none; }
        .sidebar-nav li { margin-bottom: 8px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #cbd5e1; 
            text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 0.95rem; transition: 0.3s;
        }
        .sidebar-nav a:hover { background-color: var(--sidebar-hover); color: white; }
        .sidebar-nav a.active { background-color: var(--primary); color: white; box-shadow: 0 4px 10px rgba(211,84,0,0.3); }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 1.1rem; }

        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-footer a { color: #ef4444; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px; }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1; margin-left: 260px; padding: 40px; width: calc(100% - 260px);
        }

        .top-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .top-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); }
        .top-header p { color: var(--text-muted); margin-top: 5px; font-size: 0.95rem; }
        
        .btn-add-primary {
            background: var(--primary); color: white; padding: 12px 24px; border-radius: 10px;
            font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 8px;
            transition: 0.3s; box-shadow: 0 4px 12px rgba(211,84,0,0.2);
        }
        .btn-add-primary:hover { background: #b04600; transform: translateY(-2px); }

        /* --- TABLE CARD MODERN --- */
        .card-table { background: var(--card-bg); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--border-soft); overflow: hidden; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        
        thead { background-color: #f8fafc; border-bottom: 2px solid var(--border-soft); }
        th { padding: 18px 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
        td { padding: 20px; border-bottom: 1px solid var(--border-soft); vertical-align: middle; }
        tbody tr { transition: 0.2s; }
        tbody tr:hover { background-color: #f8fafc; }

        /* Product Display in Table */
        .product-cell { display: flex; align-items: center; gap: 15px; }
        .img-thumb { width: 65px; height: 65px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border-soft); background: #f0f0f0; }
        .product-info h4 { font-size: 1rem; color: var(--text-dark); font-weight: 700; margin-bottom: 4px; }
        .product-info .meta { font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 10px; align-items: center;}
        
        /* Badges */
        .type-badge { padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .type-item { background: #e0f2fe; color: #0284c7; }
        .type-package { background: #fef3c7; color: #d97706; }

        .stock-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .stock-ready { background: #dcfce7; color: #166534; }
        .stock-empty { background: #fee2e2; color: #dc2626; }

        .price-tag { font-weight: 800; color: var(--text-dark); font-size: 1.05rem; }
        .price-tag span { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

        /* Action Buttons */
        .action-flex { display: flex; gap: 8px; }
        .btn-icon { width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: 0.3s; }
        .btn-edit { background: #f59e0b; }
        .btn-edit:hover { background: #d97706; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 3rem; margin-bottom: 15px; color: #cbd5e1; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-brand span, .sidebar-nav span { display: none; }
            .sidebar-brand i { margin: 0 auto; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); padding: 20px; }
            .top-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-mountain"></i>
            <span>Se7en Admin</span>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_pesanan.php"><i class="fas fa-clipboard-list"></i> <span>Pesanan Masuk</span></a></li>
            <li><a href="admin_produk.php" class="active"><i class="fas fa-box-open"></i> <span>Katalog Gear</span></a></li>
            <li><a href="index.php" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Lihat Website</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar Sistem</span></a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="top-header">
            <div>
                <h1>Katalog Gear & Stok</h1>
                <p>Kelola etalase produk satuan maupun paket penyewaan.</p>
            </div>
            <a href="tambah_produk.php" class="btn-add-primary">
                <i class="fas fa-plus"></i> Tambah Gear
            </a>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Detail Produk</th>
                            <th>Tarif Sewa</th>
                            <th>Ketersediaan Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img src="<?php echo !empty($row['main_image_url']) ? htmlspecialchars($row['main_image_url']) : 'https://via.placeholder.com/150?text=No+Image'; ?>" class="img-thumb" alt="Foto Gear">
                                        <div class="product-info">
                                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                            <div class="meta">
                                                <span>ID: #<?php echo str_pad($row['id_equipment'], 3, '0', STR_PAD_LEFT); ?></span>
                                                <span class="type-badge <?php echo (isset($row['rent_type']) && $row['rent_type'] == 'package') ? 'type-package' : 'type-item'; ?>">
                                                    <?php echo (isset($row['rent_type']) && $row['rent_type'] == 'package') ? 'Paket Sewa' : 'Item Satuan'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="price-tag">Rp <?php echo number_format($row['rate_per_day'], 0, ',', '.'); ?> <span>/ hr</span></div>
                                </td>

                                <td>
                                    <?php if(isset($row['stok_tersedia']) && $row['stok_tersedia'] > 0): ?>
                                        <span class="stock-badge stock-ready">
                                            <i class="fas fa-check-circle"></i> <?php echo $row['stok_tersedia']; ?> Unit Siap
                                        </span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-empty">
                                            <i class="fas fa-times-circle"></i> Kosong / Disewa
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="action-flex">
                                        <a href="edit_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-icon btn-edit" title="Edit Produk">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="hapus_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-icon btn-delete" title="Hapus Produk" onclick="return confirm('Peringatan: Menghapus produk ini akan menghapus data riwayat sewanya juga. Yakin ingin menghapus?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>Katalog Masih Kosong</h3>
                                        <p>Silakan klik tombol "Tambah Gear" untuk mulai mengisi katalog penyewaan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>