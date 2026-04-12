<?php
// admin_pesanan.php
session_start();
require_once 'koneksi.php';

// --- KODE AUTO-FIX (Hapus setelah dijalankan 1 kali) ---
$cek_kolom = $koneksi->query("SHOW COLUMNS FROM rentals LIKE 'fine_amount'");
if ($cek_kolom->num_rows == 0) {
    $koneksi->query("ALTER TABLE rentals ADD COLUMN fine_amount INT(11) DEFAULT 0");
    $koneksi->query("ALTER TABLE rentals ADD COLUMN actual_return_date DATETIME NULL");
    echo "<script>alert('Kolom denda berhasil ditambahkan otomatis ke database!');</script>";
}
// --------------------------------------------------------

// 1. CEK KEAMANAN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// 2. PROSES UPDATE STATUS & DENDA
if (isset($_POST['update_status'])) {
    $rental_id = intval($_POST['rental_id']);
    $new_status = $_POST['status'];
    
    // JIKA STATUS DIUBAH MENJADI RETURNED -> HITUNG DENDA
    if ($new_status === 'returned') {
        // Ambil end_date dari database
        $stmt_date = $koneksi->prepare("SELECT end_date FROM rentals WHERE id_rentals = ?");
        $stmt_date->bind_param("i", $rental_id);
        $stmt_date->execute();
        $res_date = $stmt_date->get_result();
        $row_date = $res_date->fetch_assoc();
        $end_date_str = $row_date['end_date'];
        $stmt_date->close();

        // Hitung Keterlambatan
        $tenggat_waktu = new DateTime($end_date_str);
        $waktu_aktual = new DateTime(); // Waktu saat ini (sekarang)
        
        $total_denda = 0;
        $hari_telat = 0;

        if ($waktu_aktual > $tenggat_waktu) {
            $selisih = $tenggat_waktu->diff($waktu_aktual);
            $hari_telat = $selisih->days;
            
            // Pembulatan hari jika ada selisih jam/menit
            if ($hari_telat == 0 && ($selisih->h > 0 || $selisih->i > 0)) {
                $hari_telat = 1;
            } else if ($hari_telat > 0 && ($selisih->h > 0 || $selisih->i > 0)) {
                $hari_telat++;
            }
            
            // Tarif Denda: Rp 20.000 / Hari
            $total_denda = $hari_telat * 20000;
        }

        // Update status, denda, dan tanggal kembali aktual
        $stmt = $koneksi->prepare("UPDATE rentals SET status = ?, fine_amount = ?, actual_return_date = NOW() WHERE id_rentals = ?");
        $stmt->bind_param("sii", $new_status, $total_denda, $rental_id);
        
        if ($stmt->execute()) {
            if ($total_denda > 0) {
                $pesan_sukses = "Pesanan #$rental_id Dikembalikan! Penyewa telat $hari_telat hari. Denda Rp " . number_format($total_denda, 0, ',', '.') . " tercatat otomatis.";
            } else {
                $pesan_sukses = "Pesanan #$rental_id Dikembalikan tepat waktu. (Denda: Rp 0).";
            }
        } else {
            $pesan_error = "Gagal memperbarui status.";
        }
        $stmt->close();
        
    } else {
        // UPDATE STATUS BIASA (Bukan returned)
        $stmt = $koneksi->prepare("UPDATE rentals SET status = ? WHERE id_rentals = ?");
        $stmt->bind_param("si", $new_status, $rental_id);
        
        if ($stmt->execute()) {
            $pesan_sukses = "Status pesanan #$rental_id berhasil diperbarui menjadi " . strtoupper($new_status) . "!";
        } else {
            $pesan_error = "Gagal memperbarui status.";
        }
        $stmt->close();
    }
}

// 3. PROSES HAPUS TRANSAKSI 
if (isset($_POST['delete_order'])) {
    $rental_id = intval($_POST['rental_id']);

    $stmt_get_item = $koneksi->prepare("SELECT item_id FROM rental_items WHERE rental_id = ?");
    $stmt_get_item->bind_param("i", $rental_id);
    $stmt_get_item->execute();
    $res_item = $stmt_get_item->get_result();

    while ($item = $res_item->fetch_assoc()) {
        $item_id = $item['item_id'];
        $koneksi->query("UPDATE equipment_items SET status = 'available' WHERE id = $item_id");
    }
    $stmt_get_item->close();

    $stmt_del = $koneksi->prepare("DELETE FROM rentals WHERE id_rentals = ?");
    $stmt_del->bind_param("i", $rental_id);

    if ($stmt_del->execute()) {
        $pesan_sukses = "Data transaksi #$rental_id berhasil dihapus permanen & stok dikembalikan!";
    } else {
        $pesan_error = "Gagal menghapus transaksi.";
    }
    $stmt_del->close();
}

// 4. AMBIL DATA PESANAN (Tambahkan kolom denda jika dibutuhkan)
$query = "SELECT rentals.*, users.full_name, users.phone_number, users.email 
          FROM rentals 
          JOIN users ON rentals.user_id = users.id_users 
          ORDER BY rentals.created_at DESC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS RESET & VARIABLES --- */
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
            width: 260px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column;
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            padding: 25px 20px; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 12px;
        }
        .sidebar-brand i { color: var(--primary); font-size: 1.5rem; }

        .sidebar-nav { padding: 20px 15px; flex: 1; list-style: none; }
        .sidebar-nav li { margin-bottom: 8px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #cbd5e1; text-decoration: none;
            border-radius: 10px; font-weight: 600; font-size: 0.95rem; transition: 0.3s;
        }
        .sidebar-nav a:hover { background-color: var(--sidebar-hover); color: white; }
        .sidebar-nav a.active { background-color: var(--primary); color: white; box-shadow: 0 4px 10px rgba(211,84,0,0.3); }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 1.1rem; }

        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-footer a { color: #ef4444; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px; }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: 260px; padding: 40px; width: calc(100% - 260px); transition: margin-left 0.3s, width 0.3s; }

        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); }
        .top-header p { color: var(--text-muted); margin-top: 5px; font-size: 0.95rem; }
        
        /* Alert Info */
        .alert-toast { padding: 15px 20px; border-radius: 10px; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        /* --- TABLE CARD MODERN --- */
        .card-table { background: var(--card-bg); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--border-soft); overflow: hidden; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        
        thead { background-color: #f8fafc; border-bottom: 2px solid var(--border-soft); }
        th { padding: 18px 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); white-space: nowrap; }
        
        td { padding: 20px; border-bottom: 1px solid var(--border-soft); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: 0.2s; }
        tbody tr:hover { background-color: #f8fafc; }

        .order-id { font-weight: 800; color: var(--text-dark); font-size: 1rem; }
        .user-name { font-weight: 700; color: var(--text-dark); font-size: 0.95rem; display: block; margin-bottom: 4px; }
        .user-address { font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: flex-start; gap: 5px; line-height: 1.4; max-width: 250px; }
        
        .date-block { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 5px; white-space: nowrap; }
        .date-block strong { color: var(--text-dark); font-weight: 600; }
        .price-tag { font-weight: 800; color: #059669; font-size: 1.1rem; }

        .btn-wa { display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-decoration: none; margin-top: 10px; border: 1px solid #a7f3d0; transition: 0.3s; white-space: nowrap; }
        .btn-wa:hover { background: #d1fae5; }

        /* --- SOFT BADGES --- */
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; white-space: nowrap; }
        .bg-pending { background: #fef3c7; color: #d97706; }
        .bg-approved { background: #e0f2fe; color: #0284c7; }
        .bg-on_rent { background: #f3e8ff; color: #9333ea; }
        .bg-returned { background: #dcfce7; color: #166534; }
        .bg-cancelled { background: #fee2e2; color: #dc2626; }

        /* --- MODERN ACTION FORM --- */
        .action-flex { display: flex; align-items: center; gap: 8px; }
        .select-modern {
            padding: 8px 12px; border: 1px solid var(--border-soft); border-radius: 8px;
            background: white; font-size: 0.85rem; font-weight: 600; color: var(--text-dark);
            cursor: pointer; outline: none; transition: 0.3s; font-family: inherit;
        }
        .select-modern:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(211,84,0,0.1); }
        
        .btn-save-status {
            background: var(--text-dark); color: white; border: none; width: 34px; height: 34px;
            border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; flex-shrink: 0;
        }
        .btn-save-status:hover { background: var(--primary); transform: translateY(-2px); box-shadow: 0 4px 6px rgba(211,84,0,0.2); }

        .btn-delete-status {
            background: #fee2e2; color: #dc2626; border: none; width: 34px; height: 34px;
            border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; flex-shrink: 0;
        }
        .btn-delete-status:hover { background: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(239,68,68,0.2); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 3rem; margin-bottom: 15px; color: #cbd5e1; }

        /* --- RESPONSIVE MOBILE --- */
        .mobile-admin-toggle {
            display: none; background: var(--primary); color: white; border: none;
            width: 40px; height: 40px; border-radius: 8px; font-size: 1.2rem; cursor: pointer;
        }
        .admin-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);
            z-index: 90; display: none; opacity: 0; transition: 0.3s;
        }
        
        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-brand span, .sidebar-nav span { display: none; }
            .sidebar-brand i { margin: 0 auto; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); padding: 20px; }
        }
        
        @media (max-width: 768px) {
            .mobile-admin-toggle { display: block; }
            .sidebar { transform: translateX(-100%); width: 260px !important; }
            .sidebar.active { transform: translateX(0); }
            .admin-overlay.active { display: block; opacity: 1; }
            .sidebar-brand span, .sidebar-nav span { display: inline-block !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 20px !important; }
            .top-header { flex-direction: row; align-items: center; justify-content: space-between; gap: 15px; }
            .top-header h1 { font-size: 1.4rem; margin-bottom: 0; }
            .top-header p { display: none; } 
            .user-address { max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="admin-overlay"></div>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-mountain"></i>
            <span>Se7en Admin</span>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_pesanan.php" class="active"><i class="fas fa-clipboard-list"></i> <span>Pesanan Masuk</span></a></li>
            <li><a href="admin_produk.php"><i class="fas fa-box-open"></i> <span>Katalog Gear</span></a></li>
            <li><a href="index.php" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Lihat Website</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar Sistem</span></a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="top-header">
            <div>
                <h1>Manajemen Pesanan</h1>
                <p>Pantau dan kelola seluruh transaksi penyewaan pelanggan di sini.</p>
            </div>
        </div>

        <?php if(isset($pesan_sukses)): ?>
            <div class="alert-toast alert-success"><i class="fas fa-check-circle"></i> <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        
        <?php if(isset($pesan_error)): ?>
            <div class="alert-toast alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <div class="card-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Informasi Penyewa</th>
                            <th>Jadwal & Tagihan</th>
                            <th>Status Saat Ini</th>
                            <th>Aksi Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="order-id">#RNT-<?php echo str_pad($row['id_rentals'], 4, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                
                                <td>
                                    <span class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    <div class="user-address">
                                        <i class="fas fa-map-marker-alt" style="margin-top: 3px; color: #94a3b8;"></i> 
                                        <?php echo htmlspecialchars($row['delivery_address']); ?>
                                    </div>
                                    
                                    <?php if(!empty($row['phone_number'])): ?>
                                        <a href="https://wa.me/<?php echo $row['phone_number']; ?>?text=Halo%20kak%20<?php echo urlencode($row['full_name']); ?>,%20saya%20Admin%20Se7en%20Summits%20mengonfirmasi%20pesanan%20%23RNT-<?php echo str_pad($row['id_rentals'], 4, '0', STR_PAD_LEFT); ?>" target="_blank" class="btn-wa">
                                            <i class="fab fa-whatsapp"></i> Chat WA
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="date-block">Ambil: <strong><?php echo date('d M Y', strtotime($row['start_date'])); ?></strong></div>
                                    <div class="date-block">Kembali: <strong><?php echo date('d M Y', strtotime($row['end_date'])); ?></strong></div>
                                    <div class="price-tag">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></div>
                                    
                                    <?php if (isset($row['fine_amount']) && $row['fine_amount'] > 0): ?>
                                        <div style="color:#dc2626; font-size:0.8rem; font-weight:bold; margin-top:4px;">
                                            + Denda: Rp <?php echo number_format($row['fine_amount'], 0, ',', '.'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?php echo $row['status']; ?>">
                                        <?php echo str_replace('_', ' ', $row['status']); ?>
                                    </span>
                                </td>

                                <td>
                                    <form method="POST" class="action-flex">
                                        <input type="hidden" name="rental_id" value="<?php echo $row['id_rentals']; ?>">
                                        
                                        <select name="status" class="select-modern">
                                            <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo ($row['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="on_rent" <?php echo ($row['status'] == 'on_rent') ? 'selected' : ''; ?>>On Rent</option>
                                            <option value="returned" <?php echo ($row['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                                            <option value="cancelled" <?php echo ($row['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>

                                        <button type="submit" name="update_status" class="btn-save-status" title="Simpan Perubahan">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        
                                        <button type="submit" name="delete_order" class="btn-delete-status" title="Hapus Transaksi Permanen" onclick="return confirm('Peringatan: Yakin ingin menghapus transaksi #RNT-<?php echo str_pad($row['id_rentals'], 4, '0', STR_PAD_LEFT); ?> secara permanen? Stok barang fisik otomatis akan kembali ke gudang.');">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>Belum Ada Transaksi</h3>
                                        <p>Saat ini belum ada pesanan yang masuk dari pelanggan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const topHeader = document.querySelector('.top-header');
            if (topHeader && window.innerWidth <= 768) {
                // Buat tombol burger
                const btn = document.createElement('button');
                btn.className = 'mobile-admin-toggle';
                btn.innerHTML = '<i class="fas fa-bars"></i>';
                topHeader.prepend(btn);

                const overlay = document.querySelector('.admin-overlay');
                const sidebar = document.querySelector('.sidebar');
                
                function toggleAdminSidebar() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                }

                btn.addEventListener('click', toggleAdminSidebar);
                overlay.addEventListener('click', toggleAdminSidebar);
            }
        });
    </script>

</body>
</html>