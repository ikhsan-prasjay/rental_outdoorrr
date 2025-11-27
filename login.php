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
            $pesan_error = "Password salah.";
        }
    } else {
        $pesan_error = "Email tidak ditemukan.";
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
    <title>Login - Rental Outdoor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            /* Background DISAMAKAN dengan Register.php */
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95); /* Putih sedikit transparan */
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .login-card p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #e67e22; /* Warna Oranye */
            outline: none;
            box-shadow: 0 0 5px rgba(230, 126, 34, 0.3);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #e67e22; /* Warna Oranye Petualang */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #d35400;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
        }

        .links {
            margin-top: 20px;
            font-size: 14px;
        }

        .links a {
            color: #e67e22;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover { text-decoration: underline; }

        .back-home {
            display: block;
            margin-top: 15px;
            color: #fff; /* Ubah jadi putih agar terlihat di background gelap */
            font-size: 12px;
            text-decoration: none;
            opacity: 0.8;
        }
        .back-home:hover { opacity: 1; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Selamat Datang</h2>
        <p>Silakan login untuk mulai bertualang.</p>

        <?php if (!empty($pesan_error)): ?>
            <div class="error-msg"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="nama@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="********" required>
            </div>
            
            <button type="submit" class="btn-login">Masuk Sekarang</button>
        </form>

        <div class="links">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
        <a href="index.php" class="back-home">← Kembali ke Beranda</a>
    </div>

</body>
</html>