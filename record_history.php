<?php
session_start();
require_once 'config/database.php';

// Hanya proses jika user login dan ada video_id yang dikirim
if (isset($_SESSION['user_id']) && isset($_POST['video_id'])) {
    $user_id = $_SESSION['user_id'];
    $video_id = (int)$_POST['video_id'];

    // Cek apakah riwayat untuk video ini sudah ada
    $check_stmt = $connect->prepare("SELECT id FROM history WHERE user_id = ? AND video_id = ?");
    $check_stmt->bind_param("ii", $user_id, $video_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada, update waktu terakhir nonton
        $update_stmt = $connect->prepare("UPDATE history SET terakhir_nonton = CURRENT_TIMESTAMP WHERE user_id = ? AND video_id = ?");
        $update_stmt->bind_param("ii", $user_id, $video_id);
        $update_stmt->execute();
    } else {
        // Jika belum ada, buat entri baru
        $insert_stmt = $connect->prepare("INSERT INTO history (user_id, video_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $video_id);
        $insert_stmt->execute();
    }
    
    // Kirim respons sukses (opsional)
    echo json_encode(['status' => 'success']);
} else {
    // Kirim respons error
    echo json_encode(['status' => 'error']);
}
?>