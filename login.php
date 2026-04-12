<?php
// login.php
session_start();
require_once 'koneksi.php';

// Jika sudah login, lempar ke index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT id_users, password_hash, full_name, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id_users'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $pesan_error = "Password yang Anda masukkan salah.";
        }
    } else {
        $pesan_error = "Email tidak terdaftar di sistem kami.";
    }
    $stmt->close();
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Se7en Summits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- INTERNAL CSS LOGIN --- */
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
            /* Menggunakan gambar Bromo dari Wikimedia agar tidak diblokir localhost */
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Mount_Bromo_at_sunrise%2C_showing_its_volcanoes_and_Mount_Semeru_%28background%29.jpg/1024px-Mount_Bromo_at_sunrise%2C_showing_its_volcanoes_and_Mount_Semeru_%28background%29.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
            color: white;
        }
        .auth-banner::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(15,23,42,0.9) 0%, rgba(15,23,42,0.2) 100%);
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

        .form-wrapper { width: 100%; max-width: 400px; }
        
        .brand { font-size: 1.5rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .brand i { color: var(--primary); font-size: 1.8rem; }

        .form-header { margin-bottom: 30px; }
        .form-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .form-header p { color: var(--text-gray); font-size: 0.95rem; }

        /* Modern Input Styling */
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

        /* Error Message Alert */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 15px;
            border-radius: 10px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }

        /* Responsive Mobile */
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
                <h2>Penjelajahan Dimulai dari Sini.</h2>
                <p>Siapkan dirimu untuk menaklukkan puncak-puncak tertinggi Nusantara dengan peralatan outdoor terbaik dan terawat dari kami.</p>
            </div>
        </div>

        <div class="auth-form-area">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Beranda</a>
            
            <div class="form-wrapper">
                <div class="brand"><i class="fas fa-mountain"></i> Se7en Summits</div>
                
                <div class="form-header">
                    <h1>Selamat Datang Kembali 👋</h1>
                    <p>Silakan masukkan email dan password untuk melanjutkan.</p>
                </div>

                <?php if (!empty($pesan_error)): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $pesan_error; ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Alamat Email" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                        <a href="#" style="color: var(--primary); font-size: 0.85rem; font-weight: 600; text-decoration: none;">Lupa Password?</a>
                    </div>
                    
                    <button type="submit" class="btn-auth">Masuk ke Akun</button>
                </form>

                <div class="auth-footer">
                    Belum memiliki akun? <a href="register.php">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>