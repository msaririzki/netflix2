<?php
// Kita perlu koneksi database di sini untuk mengambil daftar genre
// __DIR__ . '/../' memastikan path selalu benar tidak peduli dari mana file ini dipanggil
require_once __DIR__ . '/../config/database.php';

// Ambil semua genre dari database untuk ditampilkan di menu
$genres_menu_result = $connect->query("SELECT id, nama FROM genres ORDER BY nama ASC");

// Logika baru untuk memastikan path gambar selalu benar
$profile_pic_path = '/assets/images/profiles/default.png'; // Path default
if (isset($_SESSION['user_profile_picture']) && !empty($_SESSION['user_profile_picture'])) {
    $user_pic = $_SESSION['user_profile_picture'];
    // Tambahkan slash '/' di depan jika belum ada
    if (substr($user_pic, 0, 1) !== '/') {
        $profile_pic_path = '/' . $user_pic;
    } else {
        $profile_pic_path = $user_pic;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Netflix Gadungan' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-dark text-white">

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-danger" href="/index.php">NETFLIX</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Home</a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Genres
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <?php mysqli_data_seek($genres_menu_result, 0); ?>
                        <?php while($genre = $genres_menu_result->fetch_assoc()): ?>
                            <li>
                                <a class="dropdown-item" href="/category.php?id=<?= $genre['id'] ?>">
                                    <?= htmlspecialchars($genre['nama']) ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </li>
            </ul>
            
            <form class="d-flex me-3" role="search" action="/search.php" method="GET">
                <input class="form-control form-control-sm me-2 bg-dark text-white border-secondary" type="search" name="q" placeholder="Search title..." aria-label="Search">
            </form>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= htmlspecialchars($profile_pic_path) ?>" alt="Avatar" class="navbar-avatar me-2">
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile.php">My Profile</a></li>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="/admin/index.php">Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/auth/login.php" class="btn btn-danger btn-sm">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>