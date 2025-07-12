<?php
session_start();
require_once 'config/database.php';

// Validasi ID Genre dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: index.php"); exit; }
$genre_id = (int)$_GET['id'];

// Ambil nama genre untuk ditampilkan sebagai judul
$genre_info_stmt = $connect->prepare("SELECT nama FROM genres WHERE id = ?");
$genre_info_stmt->bind_param("i", $genre_id);
$genre_info_stmt->execute();
$genre_info_result = $genre_info_stmt->get_result();
$genre_name = 'Unknown Genre';
if ($genre_info_result->num_rows > 0) {
    $genre_name = $genre_info_result->fetch_assoc()['nama'];
}
$genre_info_stmt->close();

$page_title = 'Genre: ' . htmlspecialchars($genre_name);
require_once 'templates/header.php';

// Query baru untuk memfilter film berdasarkan genre_id melalui tabel penghubung
$stmt = $connect->prepare("
    SELECT v.* FROM videos v
    JOIN video_genres vg ON v.id = vg.video_id
    WHERE vg.genre_id = ?
    ORDER BY v.release_year DESC
");
$stmt->bind_param("i", $genre_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5 main-content">
    <h1 class="mb-4 text-white" data-aos="fade-right">Genre: <?= htmlspecialchars($genre_name) ?></h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
            <?php while ($movie = $result->fetch_assoc()): ?>
                <div class="col" data-aos="fade-up">
                    <a href="detail.php?id=<?= $movie['id'] ?>" class="text-decoration-none">
                        <div class="card movie-card h-100">
                            <img src="/<?= htmlspecialchars($movie['thumbnail_path'] ?: 'assets/images/placeholder.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
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
                <i class="fas fa-film fa-3x mb-3"></i>
                <p>No films found in this category.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
require_once 'templates/footer.php';
?>