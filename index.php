<?php
session_start();
// Atur judul halaman
$page_title = 'Home - Daftar Film'; 

// Include file koneksi dan header
require_once 'config/database.php';
require_once 'templates/header.php';

// Ambil data film dari database
$result = mysqli_query($connect, "SELECT * FROM videos ORDER BY created_at DESC LIMIT 12");
?>

<div class="container my-5 main-content">
    <h1 class="mb-4 text-white" data-aos="fade-right">Top Movies</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
            <?php while ($movie = mysqli_fetch_assoc($result)): ?>
                <div class="col" data-aos="fade-up">
                    <a href="detail.php?id=<?= $movie['id'] ?>" class="text-decoration-none">
                        <div class="card movie-card h-100">
                            <img src="<?= htmlspecialchars($movie['thumbnail_path'] ?: 'assets/images/placeholder.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                            <div class="card-body p-2 text-center">
                                <h5 class="card-title text-white mb-0"><?= htmlspecialchars($movie['title']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="placeholder-container">
            <div>
                <i class="fas fa-video-slash fa-3x mb-3"></i>
                <p>Belum ada film yang diunggah.</p>
                <p class="fs-6">Login sebagai admin untuk mulai menambahkan film.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once 'templates/footer.php';
?>