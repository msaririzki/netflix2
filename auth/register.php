<?php
require_once '../config/database.php';
session_start();

// Jika user sudah login, langsung arahkan ke index.php atau halaman redirect
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_GET['redirect'] ?? '../index.php';
    header('Location: ' . $redirect_url);
    exit;
}

$errors = [];
$success_message = ''; // Untuk menampilkan pesan sukses

// Mengambil redirect URL dari GET parameter jika ada, untuk dilewatkan ke form
$initial_redirect_url = $_GET['redirect'] ?? '../index.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gunakan null coalescing operator untuk mencegah "Undefined index" jika input kosong
    $nama = trim($_POST["nama"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $konfirmasi = $_POST["konfirmasi"] ?? '';
    
    // Ambil juga redirect URL dari hidden input form
    $redirect_after_register = $_POST['redirect_url'] ?? '../index.php';

    // --- Validasi Input ---
    if (empty($nama) || empty($email) || empty($password) || empty($konfirmasi)) {
        $errors[] = "Semua kolom wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Kata Sandi minimal 8 karakter.";
    }

    if ($password !== $konfirmasi) {
        $errors[] = "Konfirmasi Kata Sandi tidak cocok.";
    }

    // Hanya lanjutkan jika tidak ada error validasi dasar
    if (empty($errors)) {
        // --- Cek Email Sudah Terdaftar (menggunakan Prepared Statement) ---
        $stmt_check = $connect->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt_check === false) {
            $errors[] = "Gagal mempersiapkan query cek email: " . $connect->error;
        } else {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result(); // Simpan hasil query

            if ($stmt_check->num_rows > 0) {
                $errors[] = "Email sudah terdaftar. Silakan gunakan email lain atau Login.";
            }
            $stmt_check->close(); // Tutup statement setelah selesai
        }

        // --- Jika tidak ada error dan email belum terdaftar, lakukan pendaftaran (menggunakan Prepared Statement) ---
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $connect->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
            
            if ($stmt_insert === false) {
                $errors[] = "Gagal mempersiapkan query pendaftaran: " . $connect->error;
            } else {
                $stmt_insert->bind_param("sss", $nama, $email, $hash);
                
                if ($stmt_insert->execute()) {
                    // Pendaftaran berhasil
                    // Langsung login user dan redirect ke halaman sebelumnya atau index
                    $_SESSION['user_id'] = $stmt_insert->insert_id; // Ambil ID user yang baru didaftarkan
                    header("Location: " . $redirect_after_register);
                    exit;
                } else {
                    $errors[] = "Gagal mendaftar akun: " . $stmt_insert->error;
                }
                $stmt_insert->close(); // Tutup statement setelah selesai
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Netflix Gadungan</title>
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

        .register-container {
            background-color: #1c1c1c; /* Slightly lighter dark for content area */
            padding: 40px;
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

        input[type="text"],
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

        input[type="text"]:focus,
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

        .message.success {
            background-color: #28a745; /* Green */
            color: white;
        }

        .message.error {
            background-color: #dc3545; /* Red */
            color: white;
        }
        .message.error ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .message.error li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Daftar Akun</h2>

    <?php if (!empty($success_message)): ?>
        <div class="message success">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($initial_redirect_url) ?>">

        <div class="form-group">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" placeholder="Masukkan nama lengkap Anda" required>
        </div>
        <div class="form-group">
            <label for="email">Alamat Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Masukkan alamat email Anda" required>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi:</label>
            <input type="password" name="password" id="password" placeholder="Minimal 8 karakter" required>
        </div>
        <div class="form-group">
            <label for="konfirmasi">Konfirmasi Kata Sandi:</label>
            <input type="password" name="konfirmasi" id="konfirmasi" placeholder="Ulangi kata sandi Anda" required>
        </div>
        <button type="submit">Daftar</button>
        <p class="text-link">
            Sudah punya akun? <a href="login.php?redirect=<?= urlencode($initial_redirect_url) ?>">Login di sini</a>
        </p>
    </form>
</div>

</body>
</html>