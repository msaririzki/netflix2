<?php
session_start();
require_once 'config/database.php';

// Ambil kata kunci pencarian dari URL
$query = $_GET['q'] ?? '';

if (empty($query)) { header("Location: index.php"); exit; }

$page_title = 'Search Results for: ' . htmlspecialchars($query);
require_once 'templates/header.php';

// Cari film di database menggunakan LIKE dan prepared statement
$stmt = $connect->prepare("SELECT * FROM videos WHERE title LIKE ? ORDER BY release_year DESC");
$search_query = "%" . $query . "%";
$stmt->bind_param("s", $search_query);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5 main-content">
    <h1 class="mb-4 text-white" data-aos="fade-right">Search Results for: "<?= htmlspecialchars($query) ?>"</h1>
    <h4 class="text-secondary mb-5"><?= $result->num_rows ?> films found.</h4>

    <?php if ($result->num_rows > 0): ?>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
            <?php while ($movie = $result->fetch_assoc()): ?>
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
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>No films matching "<?= htmlspecialchars($query) ?>" were found.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
require_once 'templates/footer.php';
?>