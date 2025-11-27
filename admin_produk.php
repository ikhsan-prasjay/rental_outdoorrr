<?php
// admin_produk.php
session_start();
require_once 'koneksi.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Ambil semua data produk
$query = "SELECT * FROM equipment_products ORDER BY id_equipment DESC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; margin: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #ecf0f1; text-decoration: none; margin-left: 20px; font-size: 14px; }
        
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; }
        
        .btn-add { display: inline-block; background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .btn-add:hover { background: #219150; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        img.thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }

        .btn-action { padding: 5px 10px; border-radius: 4px; color: white; text-decoration: none; font-size: 12px; margin-right: 5px; }
        .btn-edit { background: #f39c12; }
        .btn-delete { background: #c0392b; }
    </style>
</head>
<body>

<div class="header">
    <h2><i class="fas fa-boxes"></i> Kelola Produk</h2>
    <nav>
        <a href="admin_pesanan.php">Kelola Pesanan</a>
        <a href="index.php">Lihat Web</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="container">
    <a href="tambah_produk.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Produk Baru</a>
    
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Produk</th>
                    <th>Harga / Hari</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="<?php echo !empty($row['main_image_url']) ? $row['main_image_url'] : 'https://via.placeholder.com/60'; ?>" class="thumb">
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                        <small style="color: #777;">ID: <?php echo $row['id_equipment']; ?></small>
                    </td>
                    <td>Rp <?php echo number_format($row['rate_per_day'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="edit_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="hapus_produk.php?id=<?php echo $row['id_equipment']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?');"><i class="fas fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>