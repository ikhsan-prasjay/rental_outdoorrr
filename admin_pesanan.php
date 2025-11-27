<?php
// admin_pesanan.php
session_start();
require_once 'koneksi.php';

// 1. CEK KEAMANAN: Pastikan yang akses adalah ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang ke login
    header('Location: login.php');
    exit();
}

// 2. PROSES UPDATE STATUS (Jika tombol 'Simpan' ditekan)
if (isset($_POST['update_status'])) {
    $rental_id = intval($_POST['rental_id']);
    $new_status = $_POST['status'];
    
    // Query Update
    $stmt = $koneksi->prepare("UPDATE rentals SET status = ? WHERE id_rentals = ?");
    $stmt->bind_param("si", $new_status, $rental_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location='admin_pesanan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status.');</script>";
    }
    $stmt->close();
}

// 3. AMBIL DATA PESANAN (JOIN dengan tabel USERS untuk dapat nama & no HP)
// Kita mengurutkan berdasarkan 'created_at' DESC (Terbaru di atas)
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
    <title>Kelola Pesanan - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS Reset & Basic Style */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f6f8; color: #333; }

        /* Header / Navbar Admin */
        .admin-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header h2 { font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .admin-nav a {
            color: #bdc3c7;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
            transition: 0.3s;
        }
        .admin-nav a:hover, .admin-nav a.active { color: #f39c12; font-weight: bold; }

        /* Container Utama */
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden; /* Agar sudut tabel tumpul */
            padding: 20px;
        }

        h3.page-title { margin-bottom: 20px; color: #2c3e50; border-left: 5px solid #f39c12; padding-left: 15px; }

        /* Styling Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background-color: #34495e; color: white; font-weight: 500; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        tr:hover { background-color: #f9f9f9; }

        /* Status Badges */
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: white; display: inline-block; }
        .bg-pending { background-color: #f39c12; }   /* Kuning */
        .bg-approved { background-color: #3498db; }  /* Biru Muda */
        .bg-on_rent { background-color: #9b59b6; }   /* Ungu */
        .bg-returned { background-color: #27ae60; }  /* Hijau */
        .bg-cancelled { background-color: #e74c3c; } /* Merah */

        /* Form Update Status */
        .action-form { display: flex; gap: 8px; align-items: center; }
        select.status-select {
            padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px; cursor: pointer;
        }
        .btn-update {
            background: #27ae60; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; transition: 0.3s;
        }
        .btn-update:hover { background: #219150; }

        /* Link WhatsApp */
        .wa-btn {
            color: #25d366; font-weight: 600; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; margin-top: 5px;
        }
        .wa-btn:hover { text-decoration: underline; }

        /* Responsive Table */
        @media (max-width: 768px) {
            .admin-header { flex-direction: column; gap: 15px; }
            th, td { padding: 10px; font-size: 12px; }
            .action-form { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h2><i class="fas fa-clipboard-list"></i> Admin Pesanan</h2>
        <nav class="admin-nav">
            <a href="admin_pesanan.php" class="active">Pesanan</a>
            <a href="admin_produk.php">Kelola Produk</a> <a href="index.php" target="_blank">Lihat Website</a>
            <a href="logout.php" style="color: #e74c3c;">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h3 class="page-title">Daftar Transaksi Masuk</h3>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Penyewa Info</th>
                        <th>Jadwal & Biaya</th>
                        <th>Status Saat Ini</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id_rentals']; ?></strong></td>
                            
                            <td>
                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                <span style="font-size: 12px; color: #777;">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['delivery_address']); ?>
                                </span><br>
                                
                                <?php if(!empty($row['phone_number'])): ?>
                                    <a href="https://wa.me/<?php echo $row['phone_number']; ?>?text=Halo%20kak,%20saya%20Admin%20Rental%20Outdoor%20mau%20konfirmasi%20pesanan%20#<?php echo $row['id_rentals']; ?>" target="_blank" class="wa-btn">
                                        <i class="fab fa-whatsapp"></i> Hubungi WA
                                    </a>
                                <?php else: ?>
                                    <span style="font-size:12px; color:red;">No HP Kosong</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div style="font-size: 13px;">
                                    Mulai: <b><?php echo date('d M Y', strtotime($row['start_date'])); ?></b><br>
                                    Selesai: <b><?php echo date('d M Y', strtotime($row['end_date'])); ?></b>
                                </div>
                                <div style="margin-top: 5px; color: #27ae60; font-weight: bold;">
                                    Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-<?php echo $row['status']; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>

                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="rental_id" value="<?php echo $row['id_rentals']; ?>">
                                    
                                    <select name="status" class="status-select">
                                        <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo ($row['status'] == 'approved') ? 'selected' : ''; ?>>Setujui</option>
                                        <option value="on_rent" <?php echo ($row['status'] == 'on_rent') ? 'selected' : ''; ?>>Sedang Disewa</option>
                                        <option value="returned" <?php echo ($row['status'] == 'returned') ? 'selected' : ''; ?>>Selesai/Kembali</option>
                                        <option value="cancelled" <?php echo ($row['status'] == 'cancelled') ? 'selected' : ''; ?>>Batalkan</option>
                                    </select>

                                    <button type="submit" name="update_status" class="btn-update" title="Simpan">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #777;">
                                Belum ada pesanan masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>