<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

// Validasi input
if (!isset($_SESSION['user_id']) || !isset($_POST['video_id']) || !isset($_POST['reaction'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$video_id = (int)$_POST['video_id'];
$reaction_type = $_POST['reaction']; // 'like' or 'dislike'

// Cek reaksi yang sudah ada dari user ini untuk video ini
$stmt_check = $connect->prepare("SELECT reaction_type FROM video_reactions WHERE user_id = ? AND video_id = ?");
$stmt_check->bind_param("ii", $user_id, $video_id);
$stmt_check->execute();
$existing = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if ($existing && $existing['reaction_type'] === $reaction_type) {
    // Jika user klik reaksi yang sama lagi, hapus reaksinya (unlike/undislike)
    $stmt_delete = $connect->prepare("DELETE FROM video_reactions WHERE user_id = ? AND video_id = ?");
    $stmt_delete->bind_param("ii", $user_id, $video_id);
    $stmt_delete->execute();
    $stmt_delete->close();
} else {
    // Jika tidak ada reaksi atau reaksinya beda, lakukan INSERT atau UPDATE
    $stmt_upsert = $connect->prepare(
        "INSERT INTO video_reactions (user_id, video_id, reaction_type) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE reaction_type = ?"
    );
    $stmt_upsert->bind_param("isss", $user_id, $video_id, $reaction_type, $reaction_type);
    $stmt_upsert->execute();
    $stmt_upsert->close();
}

// Ambil jumlah like/dislike terbaru untuk dikirim kembali ke browser
$stmt_like = $connect->prepare("SELECT COUNT(*) as count FROM video_reactions WHERE video_id = ? AND reaction_type = 'like'");
$stmt_like->bind_param("i", $video_id);
$stmt_like->execute();
$new_like_count = $stmt_like->get_result()->fetch_assoc()['count'];

// Kirim response berhasil
echo json_encode(['status' => 'success', 'new_like_count' => $new_like_count]);
$connect->close();