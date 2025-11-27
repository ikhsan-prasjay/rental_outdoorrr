<?php
// register.php
require_once 'koneksi.php';

$pesan_error = "";
$pesan_sukses = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $role = 'customer';

    if (empty($nama) || empty($email) || empty($password)) {
        $pesan_error = "Semua kolom wajib diisi!";
    } elseif ($password !== $konfirmasi) {
        $pesan_error = "Password tidak cocok!";
    } else {
        // Cek Email
        $cek = $koneksi->prepare("SELECT id_users FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $pesan_error = "Email sudah digunakan!";
        } else {
            // Insert
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $hash, $role);
            
            if ($stmt->execute()) {
                $pesan_sukses = "Akun berhasil dibuat! Silakan Login.";
            } else {
                $pesan_error = "Gagal mendaftar.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Rental Outdoor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Menggunakan CSS yang sama dengan Login agar konsisten */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            /* Background berbeda sedikit untuk variasi, tapi tetap tema alam */
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            width: 100%;
            max-width: 450px; /* Lebih lebar sedikit karena isian banyak */
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-card h2 { color: #2c3e50; margin-bottom: 10px; font-weight: 600; }
        .login-card p { color: #7f8c8d; font-size: 14px; margin-bottom: 25px; }

        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; color: #2c3e50; font-weight: 500; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; transition: 0.3s; }
        .form-group input:focus { border-color: #e67e22; outline: none; box-shadow: 0 0 5px rgba(230, 126, 34, 0.3); }

        .btn-login {
            width: 100%; padding: 12px; background-color: #e67e22; color: white; border: none; border-radius: 8px;
            font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .btn-login:hover { background-color: #d35400; }

        .error-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; border: 1px solid #ffcdd2; }
        .success-msg { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; border: 1px solid #c8e6c9; }

        .links { margin-top: 20px; font-size: 14px; }
        .links a { color: #e67e22; text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Buat Akun Baru</h2>
        <p>Bergabunglah dengan komunitas petualang kami.</p>

        <?php if (!empty($pesan_error)): ?>
            <div class="error-msg"><?php echo $pesan_error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($pesan_sukses)): ?>
            <div class="success-msg"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="full_name" required placeholder="Contoh: Budi Santoso">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="nama@email.com">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter">
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" required placeholder="Ulangi password">
            </div>
            
            <button type="submit" class="btn-login">Daftar Sekarang</button>
        </form>

        <div class="links">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

</body>
</html>