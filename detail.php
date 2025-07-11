<?php
session_start();
require_once 'config/database.php';

// Validasi ID Film dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: index.php"); exit; }
$film_id = (int)$_GET['id'];

// Ambil data film dari database menggunakan JOIN untuk mendapatkan nama genre
$stmt = $connect->prepare("
    SELECT v.*, g.nama AS genre_name 
    FROM videos v 
    LEFT JOIN genres g ON v.genre_id = g.id 
    WHERE v.id = ?
");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { header("Location: index.php"); exit; }
$film = $result->fetch_assoc();
$stmt->close();

$page_title = htmlspecialchars($film['title']);
require_once 'templates/header.php';
?>

<div class="container-fluid movie-detail-container" style="background-image: linear-gradient(to top, rgba(0,0,0,1) 20%, rgba(0,0,0,0.5)), url('<?= htmlspecialchars($film['thumbnail_path']) ?>');">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-lg-3 text-center" data-aos="fade-right">
                <img src="<?= htmlspecialchars($film['thumbnail_path']) ?>" class="img-fluid rounded shadow-lg movie-poster-detail" alt="<?= htmlspecialchars($film['title']) ?>">
            </div>
            <div class="col-md-8 col-lg-9" data-aos="fade-left">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($film['title']) ?></h1>
                <div class="d-flex align-items-center my-3 text-secondary">
                    <span><?= htmlspecialchars($film['release_year'] ?? '2024') ?></span>
                    <span class="mx-2">|</span>
                    <span><?= htmlspecialchars($film['genre_name'] ?? 'Movie') ?></span>
                    <span class="mx-2">|</span>
                    <span><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($film['duration'] ?? 'N/A') ?></span>
                </div>
                <p class="lead"><?= nl2br(htmlspecialchars($film['deskripsi'])) ?></p>
                <p class="mb-1"><strong class="text-white">Director:</strong> <?= htmlspecialchars($film['director'] ?? 'N/A') ?></p>
                <p><strong class="text-white">Cast:</strong> <?= htmlspecialchars($film['cast'] ?? 'N/A') ?></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="watch.php?id=<?= $film['id'] ?>" class="btn btn-danger btn-lg mt-3"><i class="fas fa-play me-2"></i> Watch Now</a>
                <?php else: ?>
                    <button type="button" class="btn btn-danger btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-play me-2"></i> Watch Now</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-header border-secondary"><h5 class="modal-title" id="loginModalLabel">Login Required</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="text-secondary">You need to log in or register to watch this movie.</p>
        <form action="/auth/login.php" method="POST">
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            <div class="mb-3"><label for="email" class="form-label">Email address</label><input type="email" class="form-control bg-secondary text-white border-dark" id="email" name="email" required></div>
            <div class="mb-3"><label for="password" class="form-label">Password</label><input type="password" class="form-control bg-secondary text-white border-dark" id="password" name="password" required></div>
            <button type="submit" class="btn btn-danger w-100">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="/auth/register.php" class="text-danger">Register here</a></p>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>