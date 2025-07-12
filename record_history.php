<?php
// Selalu mulai session di bagian paling atas
session_start();

// Set header ke JSON agar browser tahu format responsenya
header('Content-Type: application/json');

// Panggil file koneksi database
require_once 'config/database.php';

// Cek apakah user sudah login dan ada video_id yang dikirim
if (!isset($_SESSION['user_id']) || !isset($_POST['video_id'])) {
    // Jika tidak, kirim pesan error yang jelas dan hentikan skrip
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid. User ID atau Video ID tidak ada.']);
    exit;
}

// Ambil data dan pastikan tipenya benar
$user_id = (int)$_SESSION['user_id'];
$video_id = (int)$_POST['video_id'];

// Logika baru: Selalu INSERT baris baru setiap kali skrip ini dipanggil
$stmt = $connect->prepare("INSERT INTO history (user_id, video_id) VALUES (?, ?)");

// Cek jika prepare statement gagal
if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Gagal mempersiapkan query database.']);
    exit;
}

$stmt->bind_param("ii", $user_id, $video_id);

// Eksekusi query dan kirim respons sesuai hasilnya
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Riwayat tontonan berhasil dicatat.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan riwayat: ' . $stmt->error]);
}

$stmt->close();
$connect->close();
?>