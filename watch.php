<?php
session_start();
require_once 'config/database.php';

// Keamanan: Pastikan pengguna sudah login untuk mengakses halaman ini
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman login dengan membawa tujuan awal
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /auth/login.php?redirect=$redirect_url");
    exit;
}

// Validasi ID Film dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$film_id = (int)$_GET['id'];

// Ambil data film yang akan ditonton
$stmt = $connect->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php"); // Film tidak ditemukan
    exit;
}

$film = $result->fetch_assoc();
$stmt->close();

// Ambil data film terkait (genre yang sama, kecuali film yang sedang ditonton)
$genre = $film['genre']; // Asumsi ada kolom 'genre'
$related_stmt = $connect->prepare("SELECT * FROM videos WHERE genre = ? AND id != ? ORDER BY RAND() LIMIT 6");
$related_stmt->bind_param("si", $genre, $film_id);
$related_stmt->execute();
$related_films = $related_stmt->get_result();
$related_stmt->close();


// Atur judul halaman
$page_title = 'Watching: ' . htmlspecialchars($film['title']);
// Kita tidak akan memanggil header.php karena halaman ini punya layout sendiri
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-black">

<div class="watch-container">
    <video id="player" playsinline controls>
        <source src="<?= htmlspecialchars($film['file_path']) ?>" type="video/mp4" />
        Browser Anda tidak mendukung tag video.
    </video>
</div>

<div class="container my-5">
    <a href="detail.php?id=<?= $film_id ?>" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left me-2"></i> Back to Details</a>
    
    <h1 class="text-white"><?= htmlspecialchars($film['title']) ?></h1>
    <p class="text-secondary"><?= nl2br(htmlspecialchars($film['deskripsi'])) ?></p>
    <hr class="border-secondary">
    
    <h3 class="text-white mt-5 mb-4">Related Films</h3>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
       <?php while ($related = $related_films->fetch_assoc()): ?>
            <div class="col">
                <a href="detail.php?id=<?= $related['id'] ?>" class="text-decoration-none">
                    <div class="card movie-card h-100">
                        <img src="<?= htmlspecialchars($related['thumbnail_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related['title']) ?>">
                        <div class="card-body p-2 text-center">
                            <h5 class="card-title text-white mb-0"><?= htmlspecialchars($related['title']) ?></h5>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>


<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script src="/assets/js/main.js"></script>

</body>
</html>