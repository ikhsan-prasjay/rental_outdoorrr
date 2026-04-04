<?php
// profile.php
session_start();
require_once 'koneksi.php';

// Proteksi Halaman Profesional
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan_sukses = "";
$pesan_error = "";

// Proses Update Profil & Foto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['full_name']);
    $telepon = trim($_POST['phone_number']);

    if (empty($nama)) {
        $pesan_error = "Nama Lengkap tidak boleh kosong!";
    } else {
        // 1. Update Data Teks (Nama & Telepon)
        $stmt_update = $koneksi->prepare("UPDATE users SET full_name = ?, phone_number = ? WHERE id_users = ?");
        $stmt_update->bind_param("ssi", $nama, $telepon, $user_id);
        $stmt_update->execute();
        $_SESSION['full_name'] = $nama;

        // 2. Proses Upload Foto Profil (Jika ada file yang dipilih)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = "uploads/avatars/";
            // Buat folder jika belum ada
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            // Validasi Keamanan (MIME Type)
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $_FILES["avatar"]["tmp_name"]);
            finfo_close($file_info);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (in_array($mime_type, $allowed_mimes)) {
                $file_ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
                // Nama file unik: id_user + waktu saat ini
                $new_file_name = "user_" . $user_id . "_" . time() . '.' . $file_ext;
                $target_file = $target_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    // Update URL Foto di Database
                    $stmt_avatar = $koneksi->prepare("UPDATE users SET avatar_url = ? WHERE id_users = ?");
                    $stmt_avatar->bind_param("si", $target_file, $user_id);
                    $stmt_avatar->execute();
                    $stmt_avatar->close();
                    
                    // Note: Idealnya hapus file avatar lama pakai unlink() di sini jika mau hemat storage
                } else {
                    $pesan_error = "Gagal mengunggah foto profil.";
                }
            } else {
                $pesan_error = "Format foto ditolak! Gunakan JPG, PNG, atau WEBP.";
            }
        }

        if (empty($pesan_error)) {
            $pesan_sukses = "Profil Anda berhasil diperbarui!";
        }
        $stmt_update->close();
    }
}

// Ambil Data User (Termasuk avatar_url) + Statistik Sewa
$stmt = $koneksi->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM rentals WHERE user_id = u.id_users AND status != 'cancelled') as total_rentals,
    (SELECT COUNT(*) FROM rentals WHERE user_id = u.id_users AND status = 'on_rent') as current_rentals,
    (SELECT IFNULL(SUM(total_price), 0) FROM rentals WHERE user_id = u.id_users AND status != 'cancelled') as total_spent
    FROM users u 
    WHERE u.id_users = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once 'includes/header.php';
?>

<style>
    /* --- CSS PREMIUM & KREATIF --- */
    :root {
        --primary: #d35400; 
        --dark: #1e272e;
        --light: #f4f7f6;
        --white: #ffffff;
        --border: #e2e8f0;
        --subtle-bg: #f1f3f5; /* Warna background pengganti gambar cover */
        --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.05);
        --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
        --radius: 20px;
    }

    body { background-color: var(--light); padding-bottom: 50px; }
    .profile-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

    /* --- COVER SECTION (Tampilan Minimalis Tanpa Gambar) --- */
    .profile-header-card {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 30px;
        position: relative;
    }

    .cover-photo {
        height: 150px; /* Sedikit diperpendek agar lebih minimalis */
        background-color: var(--subtle-bg); /* Menggunakan warna solid color */
        border-bottom: 1px solid var(--border);
    }

    .profile-info-bar {
        padding: 0 40px 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: -75px; /* Menarik konten ke atas menimpa cover area */
    }

    /* Interactive Avatar Wrapper */
    .avatar-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid var(--white);
        background: var(--white);
        box-shadow: var(--shadow-lg);
        cursor: pointer;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-wrapper img, .avatar-fallback {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .avatar-fallback {
        background: linear-gradient(135deg, var(--primary), #ff9f43);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    /* Hover Overlay untuk Ubah Foto */
    .avatar-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.3s;
        border-radius: 50%;
    }
    .avatar-wrapper:hover .avatar-overlay { opacity: 1; }
    .avatar-overlay i { font-size: 1.8rem; margin-bottom: 5px; }
    .avatar-overlay span { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }

    .user-titles { margin-left: 25px; flex-grow: 1; padding-bottom: 10px; }
    .user-titles h1 { font-size: 2rem; color: var(--dark); font-weight: 800; margin-bottom: 5px; }
    .user-titles p { color: #7f8c8d; font-weight: 500; }
    .user-badge { display: inline-block; background: rgba(211, 84, 0, 0.1); color: var(--primary); padding: 5px 15px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; margin-top: 10px; }

    /* --- LAYOUT BAWAH: STATS & FORM --- */
    .profile-body { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }

    .stats-sidebar { display: flex; flex-direction: column; gap: 20px; }
    .stat-box { background: var(--white); border-radius: var(--radius); padding: 25px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px; border: 1px solid var(--border); transition: 0.3s; }
    .stat-box:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .stat-icon { width: 50px; height: 50px; border-radius: 15px; background: #f0f4f8; color: var(--dark); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .stat-box:hover .stat-icon { background: var(--primary); color: white; }
    .stat-details h4 { color: #a0aec0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .stat-details span { font-size: 1.5rem; font-weight: 800; color: var(--dark); }

    .settings-card { background: var(--white); border-radius: var(--radius); padding: 40px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
    .settings-card h3 { font-size: 1.4rem; color: var(--dark); margin-bottom: 30px; border-bottom: 2px solid #f0f4f8; padding-bottom: 15px; display: flex; align-items: center; gap: 10px; }

    /* --- FORM KREATIF --- */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .form-group.full-width { grid-column: span 2; }
    .form-group label { display: block; font-weight: 600; color: #4a5568; margin-bottom: 8px; font-size: 0.95rem; }
    .input-modern { width: 100%; padding: 15px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 1rem; color: #2d3748; transition: 0.3s; font-family: inherit; }
    .input-modern:focus { background: var(--white); border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(211, 84, 0, 0.1); }
    .input-modern[readonly] { background: #edf2f7; color: #a0aec0; cursor: not-allowed; }

    .btn-save { background: var(--primary); color: white; border: none; padding: 16px 30px; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; }
    .btn-save:hover { background: #b04600; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(211, 84, 0, 0.2); }

    .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; text-align: center; }
    .alert-success { background: #def7ec; color: #03543f; }
    .alert-error { background: #fde8e8; color: #9b1c1c; }

    @media (max-width: 900px) {
        .profile-body { grid-template-columns: 1fr; }
        .profile-info-bar { flex-direction: column; align-items: center; text-align: center; }
        .user-titles { margin-left: 0; margin-top: 15px; }
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
    }
</style>

<div class="profile-container">
    
    <?php if ($pesan_sukses): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $pesan_sukses; ?></div><?php endif; ?>
    <?php if ($pesan_error): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $pesan_error; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="profile-header-card">
            <div class="cover-photo"></div>
            <div class="profile-info-bar">
                
                <label for="avatar_upload" class="avatar-wrapper" title="Klik untuk ganti foto">
                    <?php if (!empty($user['avatar_url']) && file_exists($user['avatar_url'])): ?>
                        <img id="avatar_preview" src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <div id="avatar_preview_container" style="width: 100%; height: 100%;">
                            <div class="avatar-fallback"><?php echo substr($user['full_name'], 0, 1); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="avatar-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Ubah Foto</span>
                    </div>
                </label>
                <input type="file" name="avatar" id="avatar_upload" accept="image/*" style="display: none;" onchange="previewAvatar(event)">

                <div class="user-titles">
                    <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="user-badge"><i class="fas fa-crown"></i> Petualang Aktif</div>
                </div>
                
                <div style="padding-bottom: 10px;">
                    <a href="riwayat_sewa.php" class="btn-save" style="margin-top:0; padding: 12px 25px; background: var(--dark); width: auto;">
                        <i class="fas fa-history"></i> Cek Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="profile-body">
            
            <div class="stats-sidebar">
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-campground"></i></div>
                    <div class="stat-details">
                        <h4>Total Disewa</h4>
                        <span><?php echo number_format($user['total_rentals'], 0, ',', '.'); ?>x</span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                    <div class="stat-details">
                        <h4>Status Aktif</h4>
                        <span><?php echo number_format($user['current_rentals'], 0, ',', '.'); ?> Unit</span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-details">
                        <h4>Total Belanja</h4>
                        <span style="color: var(--primary); font-size: 1.2rem;">Rp <?php echo number_format($user['total_spent'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3><i class="fas fa-user-edit"></i> Detail Personal</h3>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="input-modern" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="input-modern" required>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon / WhatsApp</label>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" placeholder="0812xxxxxx" class="input-modern">
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-check-circle"></i> Simpan Perubahan Profil
                </button>
            </div>
        </div>

    </form>
</div>

<script>
function previewAvatar(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Cek apakah elemen img dengan id avatar_preview sudah ada
            let previewImg = document.getElementById('avatar_preview');
            
            if (previewImg) {
                previewImg.src = e.target.result;
            } else {
                // Jika sebelumnya pakai inisial nama (fallback), ganti dengan elemen img
                const container = document.getElementById('avatar_preview_container');
                container.innerHTML = `<img id="avatar_preview" src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="New Profile Photo">`;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>