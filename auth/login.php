<?php
session_start();
require_once '../config/database.php';

// Cek jika sudah login, langsung redirect ke halaman utama atau redirect_url
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_GET['redirect'] ?? '../index.php'; // Ambil dari GET atau default ke index
    header('Location: ' . $redirect_url);
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect_after_login = $_POST['redirect_url'] ?? '../index.php'; // Ambil dari hidden input

    // Validasi input dan query ke database
    // Menggunakan Prepared Statement untuk keamanan
    $stmt = $connect->prepare("SELECT id, password FROM users WHERE email = ?");

    if ($stmt === false) {
        $error_message = "Gagal mempersiapkan query database: " . $connect->error;
    } else {
        $stmt = $connect->prepare("SELECT id, password, nama, role FROM users WHERE email = ?"); // Ambil kolom 'role' dan 'nama'
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
        // Login berhasil
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama']; // Simpan nama user untuk ditampilkan
        $_SESSION['role'] = $user['role']; // Simpan role user

        // Logika redirect baru: jika admin, arahkan ke dashboard admin
        if ($user['role'] === 'admin') {
            header('Location: /admin/index.php');
        } else {
            header('Location: ' . $redirect_after_login);
        }
        exit;   
        } else {
            $error_message = "Email atau password salah.";
        }
    }
}

// Ambil redirect URL dari GET untuk dimasukkan ke hidden input
$initial_redirect_url = $_GET['redirect'] ?? '../index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Netflix Gadungan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #141414; /* Dark background like Netflix */
            color: #e5e5e5; /* Light text color */
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-container {
            background-color: #1c1c1c; /* Slightly lighter dark for content area */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px; /* Lebar yang lebih compact untuk form login */
            box-sizing: border-box;
        }

        h2 {
            color: #e50914; /* Netflix red */
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #b3b3b3;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px); /* Adjust for padding */
            padding: 12px 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #333;
            color: #e5e5e5;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #e50914;
            outline: none;
            box-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
        }

        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #e50914; /* Netflix Red */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background-color: #ff0000;
            transform: translateY(-2px);
        }

        .text-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95em;
        }

        .text-link a {
            color: #007bff; /* Blue for links */
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .text-link a:hover {
            color: #56aaff;
        }

        /* Message Styling */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .message.error {
            background-color: #dc3545; /* Red */
            color: white;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($error_message): ?>
        <div class="message error">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($initial_redirect_url) ?>">

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Masukkan alamat email Anda" required>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi:</label>
            <input type="password" name="password" id="password" placeholder="Masukkan kata sandi Anda" required>
        </div>
        <button type="submit">Login</button>
        <p class="text-link">
            Belum punya akun? <a href="register.php?redirect=<?= urlencode($initial_redirect_url) ?>">Daftar di sini</a>
        </p>
    </form>
</div>

</body>
</html>