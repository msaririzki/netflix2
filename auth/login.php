<?php
session_start();
require_once '../config/database.php';

// Cek jika sudah login, langsung redirect
if (isset($_SESSION['user_id'])) {
    $redirect_url = ($_SESSION['role'] === 'admin') ? '/admin/index.php' : '/index.php';
    header('Location: ' . $redirect_url);
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect_after_login = $_POST['redirect_url'] ?? '/index.php';

    $stmt = $connect->prepare("SELECT id, password, nama, role, profile_picture FROM users WHERE email = ?");

    if ($stmt === false) {
        $error_message = "Gagal mempersiapkan query database: " . $connect->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_profile_picture'] = $user['profile_picture'];

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

$initial_redirect_url = $_GET['redirect'] ?? '/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Netflix Gadungan</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Mengadopsi gaya dari halaman register Anda */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #141414;
            color: #e5e5e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container { /* Ganti nama class agar sesuai */
            background-color: #1c1c1c;
            padding: 60px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
        }
        h2 {
            color: #e50914; /* Netflix red */
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #b3b3b3; font-weight: bold; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #333;
            color: #e5e5e5;
            font-size: 1em;
            box-sizing: border-box;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #e50914;
            outline: none;
            box-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
        }
        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #e50914;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        button[type="submit"]:hover { background-color: #f6121d; }
        .text-link { text-align: center; margin-top: 25px; font-size: 0.95em; color: #b3b3b3; }
        .text-link a { color: white; text-decoration: none; font-weight: 500; }
        .text-link a:hover { text-decoration: underline; }
        .message.error {
            background-color: #e87c03;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
            <input type="email" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" id="password" placeholder="Password" required>
        </div>
        
        <button type="submit">Login</button>
        
        <div class="text-link mt-4">
            Belum punya akun? <a href="register.php?redirect=<?= urlencode($initial_redirect_url) ?>">Daftar sekarang.</a>
        </div>
    </form>
</div>

</body>
</html>