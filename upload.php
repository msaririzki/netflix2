<?php
session_start();
include './config/konek.php'; // Pastikan path ini benar ke file koneksi database Anda

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$upload_success_message = '';
$upload_error_messages = [];

// Proses form upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $genre_id = isset($_POST['genre_id']) && is_numeric($_POST['genre_id']) ? (int)$_POST['genre_id'] : null;
    $user_id = $_SESSION['user_id'];

    $video = $_FILES['video'] ?? null;
    $thumb = $_FILES['thumbnail'] ?? null;

    $videoPath = null;
    $thumbPath = null;

    // --- Validasi dan Proses Upload Video ---
    if ($video && $video['error'] === UPLOAD_ERR_OK) {
        if ($video['type'] === 'video/mp4') {
            $videoName = uniqid() . "_" . basename($video['name']);
            $targetVideoDir = "assets/video/";
            if (!is_dir($targetVideoDir)) {
                mkdir($targetVideoDir, 0777, true);
            }
            $videoPath = $targetVideoDir . $videoName;
            if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
                $upload_error_messages[] = "Gagal menyimpan file video ke folder. Periksa izin direktori: " . $targetVideoDir;
            }
        } else {
            $upload_error_messages[] = "Format video harus MP4. Format terdeteksi: " . ($video['type'] ?: 'Tidak diketahui');
        }
    } elseif ($video && $video['error'] !== UPLOAD_ERR_NO_FILE) {
        switch ($video['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $upload_error_messages[] = "Ukuran file video melebihi batas yang diizinkan di server (" . ini_get('upload_max_filesize') . ").";
                break;
            case UPLOAD_ERR_PARTIAL:
                $upload_error_messages[] = "File video hanya terunggah sebagian. Coba lagi.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $upload_error_messages[] = "Direktori temporary upload tidak ada di server.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $upload_error_messages[] = "Gagal menulis file video ke disk di server.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $upload_error_messages[] = "Upload video dihentikan oleh ekstensi PHP.";
                break;
            default:
                $upload_error_messages[] = "Terjadi kesalahan tidak dikenal saat mengunggah video. Kode error: " . $video['error'];
        }
    } else {
        $upload_error_messages[] = "Tidak ada file video yang diunggah. Silakan pilih file video.";
    }

    // --- Validasi dan Proses Upload Thumbnail (opsional) ---
    if ($thumb && $thumb['error'] === UPLOAD_ERR_OK) {
        if (in_array($thumb['type'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            $thumbName = uniqid() . "_" . basename($thumb['name']);
            $targetThumbDir = "assets/thumb/";
            if (!is_dir($targetThumbDir)) {
                mkdir($targetThumbDir, 0777, true);
            }
            $thumbPath = $targetThumbDir . $thumbName;
            if (!move_uploaded_file($thumb['tmp_name'], $thumbPath)) {
                $upload_error_messages[] = "Gagal menyimpan thumbnail ke folder. Periksa izin direktori: " . $targetThumbDir;
            }
        } else {
            $upload_error_messages[] = "Format thumbnail harus JPG, PNG, WEBP, atau GIF. Format terdeteksi: " . ($thumb['type'] ?: 'Tidak diketahui');
        }
    } elseif ($thumb && $thumb['error'] !== UPLOAD_ERR_NO_FILE) {
        switch ($thumb['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $upload_error_messages[] = "Ukuran file thumbnail melebihi batas yang diizinkan di server (" . ini_get('upload_max_filesize') . ").";
                break;
            case UPLOAD_ERR_PARTIAL:
                $upload_error_messages[] = "File thumbnail hanya terunggah sebagian.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $upload_error_messages[] = "Direktori temporary upload thumbnail tidak ada.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $upload_error_messages[] = "Gagal menulis file thumbnail ke disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $upload_error_messages[] = "Upload thumbnail dihentikan oleh ekstensi PHP.";
                break;
            default:
                $upload_error_messages[] = "Terjadi kesalahan tidak dikenal saat mengunggah thumbnail. Kode error: " . $thumb['error'];
        }
    }

    // --- Simpan ke database jika tidak ada error dan file video berhasil diupload ---
    if (empty($upload_error_messages) && $videoPath !== null) {
        $query = "INSERT INTO videos (user_id, title, deskripsi, file_path, thumbnail_path, genre_id, duration)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($query);

        if ($stmt === false) {
            $upload_error_messages[] = "Gagal mempersiapkan query database: " . $connect->error;
        } else {
            $stmt->bind_param("issssis", $user_id, $title, $deskripsi, $videoPath, $thumbPath, $genre_id, $duration);

            if ($stmt->execute()) {
                $upload_success_message = "✅ Video berhasil diupload!";
                // Opsional: Clear form fields after successful upload (refresh halaman)
                // header('Location: upload.php?status=success');
                // exit;
            } else {
                if (file_exists($videoPath)) {
                    unlink($videoPath);
                }
                if ($thumbPath && file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
                $upload_error_messages[] = "❌ Gagal menyimpan video ke database: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        // Jika upload video gagal, tapi thumbnail berhasil, hapus thumbnail juga
        if (!empty($upload_error_messages) && $thumbPath && file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - Netflix Gadungan</title>
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

        .upload-container {
            background-color: #1c1c1c; /* Slightly lighter dark for content area */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 600px;
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
        input[type="number"],
        textarea,
        input[type="file"] {
            width: calc(100% - 20px); /* Adjust for padding */
            padding: 12px 10px;
            margin-bottom: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #333;
            color: #e5e5e5;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
        }

        input[type="file"] {
            padding: 10px; /* Adjust padding for file input */
            cursor: pointer;
        }

        textarea {
            resize: vertical; /* Allow vertical resizing */
            min-height: 100px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        input[type="file"]:focus {
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
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background-color: #ff0000;
            transform: translateY(-2px);
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

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #b3b3b3;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #e50914;
        }
    </style>
</head>
<body>

<div class="upload-container">
    <h2>Upload Video Baru</h2>

    <?php if (!empty($upload_success_message)): ?>
        <div class="message success">
            <?= htmlspecialchars($upload_success_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($upload_error_messages)): ?>
        <div class="message error">
            <?php foreach ($upload_error_messages as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Judul Video:</label>
            <input type="text" name="title" id="title" placeholder="Masukkan judul video" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" placeholder="Tulis deskripsi video di sini..." required></textarea>
        </div>

        <div class="form-group">
            <label for="duration">Durasi (format: 1:30 atau 90s):</label>
            <input type="text" name="duration" id="duration" placeholder="Contoh: 1:30 atau 90s" required>
        </div>

        <div class="form-group">
            <label for="genre_id">Genre ID (opsional):</label>
            <input type="number" name="genre_id" id="genre_id" placeholder="Masukkan ID genre jika ada (misal: 1)" min="0">
        </div>

        <div class="form-group">
            <label for="video">File Video (MP4):</label>
            <input type="file" name="video" id="video" accept="video/mp4" required>
        </div>

        <div class="form-group">
            <label for="thumbnail">File Thumbnail (opsional, JPG/PNG/WEBP/GIF):</label>
            <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
        </div>

        <button type="submit">Upload Video</button>
    </form>

    <a href="index.php" class="back-link">Kembali ke Beranda</a>
</div>

</body>
</html>