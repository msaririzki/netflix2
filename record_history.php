<?php
session_start();
require_once 'config/database.php';

// 1. Pastikan user sudah login dan video_id dikirim
if (!isset($_SESSION['user_id']) || !isset($_POST['video_id'])) {
    // Keluar jika tidak ada data yang diperlukan, bisa juga kirim response error
    http_response_code(400); // Bad Request
    exit('User not logged in or video ID not provided.');
}

// 2. Ambil data
$user_id = (int)$_SESSION['user_id'];
$video_id = (int)$_POST['video_id'];
$watch_date = date('Y-m-d H:i:s'); // Tanggal dan waktu saat ini

// 3. Gunakan "UPSERT" (UPDATE atau INSERT)
// Ini akan mencoba INSERT. Jika sudah ada (berdasarkan UNIQUE key), maka akan UPDATE.
$stmt = $connect->prepare("
    INSERT INTO history (user_id, video_id, terakhir_nonton)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE terakhir_nonton = ?
");

// Bind parameter: integer, integer, string, string
$stmt->bind_param("isss", $user_id, $video_id, $watch_date, $watch_date);

// 4. Eksekusi query
if ($stmt->execute()) {
    http_response_code(200); // OK
    echo "History recorded.";
} else {
    http_response_code(500); // Internal Server Error
    echo "Failed to record history.";
}

$stmt->close();
$connect->close();