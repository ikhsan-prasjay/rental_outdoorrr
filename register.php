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
        $pesan_error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Cek Email
        $cek = $koneksi->prepare("SELECT id_users FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $pesan_error = "Email ini sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            // Insert
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $hash, $role);
            
            if ($stmt->execute()) {
                $pesan_sukses = "Akun berhasil dibuat! Mengalihkan ke halaman login...";
                // Redirect otomatis setelah 2 detik
                echo "<meta http-equiv='refresh' content='2;url=login.php'>";
            } else {
                $pesan_error = "Terjadi kesalahan sistem. Gagal mendaftar.";
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
    <title>Daftar - Se7en Summits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- INTERNAL CSS REGISTER (Mirip Login agar Konsisten) --- */
        :root {
            --primary: #d35400; 
            --primary-hover: #b04600; 
            --dark: #0f172a;
            --text-gray: #64748b; 
            --bg-input: #f8fafc; 
            --border-input: #e2e8f0;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .auth-layout { display: flex; min-height: 100vh; background: white; }

        /* --- SISI KIRI (BANNER) --- */
        .auth-banner {
            flex: 1.2;
            /* Menggunakan gambar panorama alam yang sedikit berbeda dari login */
            background: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            position: relative; display: flex; flex-direction: column; justify-content: flex-end; padding: 60px; color: white;
        }
        .auth-banner::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.1) 100%);
        }
        .banner-content { position: relative; z-index: 1; max-width: 500px; }
        .banner-content h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; line-height: 1.2; }
        .banner-content p { font-size: 1.1rem; color: #cbd5e1; line-height: 1.6; }

        /* --- SISI KANAN (FORM) --- */
        .auth-form-area {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 40px; position: relative;
        }
        
        .back-link { position: absolute; top: 40px; right: 40px; font-weight: 600; color: var(--text-gray); text-decoration: none; display: flex; align-items: center; gap: 8px; transition: var(--transition); }
        .back-link:hover { color: var(--dark); }

        .form-wrapper { width: 100%; max-width: 420px; } /* Sedikit lebih lebar untuk form daftar */
        
        .brand { font-size: 1.5rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
        .brand i { color: var(--primary); font-size: 1.8rem; }

        .form-header { margin-bottom: 30px; }
        .form-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .form-header p { color: var(--text-gray); font-size: 0.95rem; }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; transition: var(--transition); font-size: 1.1rem; }
        .input-group input {
            width: 100%; padding: 15px 15px 15px 50px; background: var(--bg-input); border: 1px solid var(--border-input);
            border-radius: 12px; font-size: 0.95rem; color: var(--dark); outline: none; transition: var(--transition);
        }
        .input-group input:focus { background: white; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(211,84,0,0.1); }
        .input-group input:focus + i, .input-group:focus-within i { color: var(--primary); }

        .btn-auth {
            width: 100%; padding: 16px; background: var(--primary); color: white; border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 700; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 12px rgba(211,84,0,0.2); margin-top: 10px;
        }
        .btn-auth:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(211,84,0,0.3); }

        .auth-footer { margin-top: 30px; text-align: center; font-size: 0.95rem; color: var(--text-gray); }
        .auth-footer a { color: var(--primary); font-weight: 700; text-decoration: none; transition: var(--transition); }
        .auth-footer a:hover { color: var(--primary-hover); text-decoration: underline; }

        /* Alerts */
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 15px; border-radius: 10px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 15px; border-radius: 10px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        @media (max-width: 900px) {
            .auth-banner { display: none; }
            .auth-form-area { padding: 30px 20px; }
            .back-link { top: 20px; right: 20px; font-size: 0.9rem; }
        }
    </style>
</head>
<body>

    <div class="auth-layout">
        <div class="auth-banner">
            <div class="banner-content">
                <h2>Bergabung Bersama Kami.</h2>
                <p>Jadilah bagian dari komunitas petualang dan dapatkan akses penuh untuk menyewa peralatan outdoor kelas dunia.</p>
            </div>
        </div>

        <div class="auth-form-area">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Beranda</a>
            
            <div class="form-wrapper">
                <div class="brand"><i class="fas fa-mountain"></i> Se7en Summits</div>
                
                <div class="form-header">
                    <h1>Buat Akun Baru 🚀</h1>
                    <p>Isi data diri Anda di bawah ini dengan benar.</p>
                </div>

                <?php if (!empty($pesan_error)): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $pesan_error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $pesan_sukses; ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="input-group">
                        <input type="text" name="full_name" placeholder="Nama Lengkap" required>
                        <i class="fas fa-user"></i>
                    </div>

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Alamat Email" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Buat Password (Min. 6 Karakter)" required>
                        <i class="fas fa-lock"></i>
                    </div>

                    <div class="input-group">
                        <input type="password" name="konfirmasi_password" placeholder="Ulangi Password" required>
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    
                    <button type="submit" class="btn-auth">Daftar Akun Sekarang</button>
                </form>

                <div class="auth-footer">
                    Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>