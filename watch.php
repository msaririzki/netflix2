<?php
session_start();
require_once 'config/database.php';

// (Seluruh logika PHP di atas sini tetap sama seperti yang terakhir Anda berikan)
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /auth/login.php?redirect=$redirect_url");
    exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$film_id = (int)$_GET['id'];
$stmt = $connect->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}
$film = $result->fetch_assoc();
$stmt->close();
$genres_stmt = $connect->prepare("SELECT g.id, g.nama FROM genres g JOIN video_genres vg ON g.id = vg.genre_id WHERE vg.video_id = ?");
$genres_stmt->bind_param("i", $film_id);
$genres_stmt->execute();
$genres_result = $genres_stmt->get_result();
$genres = [];
while($row = $genres_result->fetch_assoc()){
    $genres[] = $row;
}
$genres_stmt->close();
$related_films = null; 
if (!empty($genres)) {
    $first_genre_id = $genres[0]['id'];
    $related_stmt = $connect->prepare("SELECT v.* FROM videos v JOIN video_genres vg ON v.id = vg.video_id WHERE vg.genre_id = ? AND v.id != ? ORDER BY RAND() LIMIT 10");
    $related_stmt->bind_param("ii", $first_genre_id, $film_id);
    $related_stmt->execute();
    $related_films = $related_stmt->get_result();
    $related_stmt->close();
}
$page_title = 'Watching: ' . htmlspecialchars($film['title']);
require_once 'templates/header.php';
?>

<div class="container-fluid watch-page-yt mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="video-player-wrapper mb-3">
                <div class="plyr__video-embed" id="player">
                    <iframe
                        src="<?= htmlspecialchars($film['file_path']) ?>?origin=https-plyr-io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                        allowfullscreen allowtransparency allow="autoplay">
                    </iframe>
                </div>
            </div>
            <h1 class="video-title-yt"><?= htmlspecialchars($film['title']) ?></h1>
            <div class="d-flex justify-content-between align-items-center">
                <div class="video-meta-yt text-secondary">
                    <span><?= htmlspecialchars($film['release_year']) ?></span>
                    <span class="mx-2">&bull;</span>
                    <span><?= htmlspecialchars($film['duration']) ?></span>
                </div>
                <div class="genres-pills">
                    <?php foreach($genres as $g): ?>
                        <a href="/category.php?id=<?= $g['id'] ?>" class="badge bg-danger text-decoration-none me-1"><?= htmlspecialchars($g['nama']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <h4 class="text-white mb-3">Up Next</h4>
            <div class="related-videos-sidebar">
                <?php if ($related_films && $related_films->num_rows > 0): ?>
                    <?php while ($related = $related_films->fetch_assoc()): ?>
                        <a href="watch.php?id=<?= $related['id'] ?>" class="related-video-item">
                            <img src="/<?= htmlspecialchars($related['thumbnail_path']) ?>" class="related-video-thumb" alt="<?= htmlspecialchars($related['title']) ?>">
                            <div class="related-video-info">
                                <h6 class="related-video-title"><?= htmlspecialchars($related['title']) ?></h6>
                                <p class="related-video-meta text-secondary"><?= htmlspecialchars($related['release_year']) ?></p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-secondary">No related films found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inisialisasi player HANYA di halaman ini
        const player = new Plyr('#player');
        
        let hasBeenRecorded = false;
        player.on('play', () => {
            if (!hasBeenRecorded) {
                hasBeenRecorded = true;
                console.log('Mencatat riwayat untuk video ID: <?= $film_id ?>'); // DEBUG
                fetch('/record_history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `video_id=<?= $film_id ?>`
                });
            }
        });
    });
</script>