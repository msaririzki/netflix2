<?php
session_start();
require_once 'config/database.php';

$film_id = (int)$_GET['id'];


// Pastikan user_id diambil dari session
$user_id = (int)$_SESSION['user_id'];

// =======================================================
// ===== AMBIL DATA LIKE/DISLIKE & STATUS PENGGUNA =====
// =======================================================
$like_count = 0;
$dislike_count = 0;
$user_reaction = null; // null, 'like', atau 'dislike'

// Ambil jumlah total like
$stmt_like = $connect->prepare("SELECT COUNT(*) as count FROM video_reactions WHERE video_id = ? AND reaction_type = 'like'");
$stmt_like->bind_param("i", $film_id);
$stmt_like->execute();
$like_count = $stmt_like->get_result()->fetch_assoc()['count'];
$stmt_like->close();

// Ambil jumlah total dislike
$stmt_dislike = $connect->prepare("SELECT COUNT(*) as count FROM video_reactions WHERE video_id = ? AND reaction_type = 'dislike'");
$stmt_dislike->bind_param("i", $film_id);
$stmt_dislike->execute();
$dislike_count = $stmt_dislike->get_result()->fetch_assoc()['count'];
$stmt_dislike->close();

// Cek reaksi user yang sedang login
if (isset($_SESSION['user_id'])) {
    $stmt_user = $connect->prepare("SELECT reaction_type FROM video_reactions WHERE video_id = ? AND user_id = ?");
    $stmt_user->bind_param("ii", $film_id, $_SESSION['user_id']);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows > 0) {
        $user_reaction = $result_user->fetch_assoc()['reaction_type'];
    }
    $stmt_user->close();
}
// =======================================================

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


// Cukup INSERT baris baru setiap kali halaman diakses.
// Kolom 'terakhir_nonton' akan terisi otomatis oleh database.
$history_stmt = $connect->prepare(
    "INSERT INTO history (user_id, video_id) VALUES (?, ?)"
);

if ($history_stmt) {
    $history_stmt->bind_param("ii", $user_id, $film_id);
    $history_stmt->execute();
    $history_stmt->close();
}


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



// logika lama untuk up next dimana dia cuma mengambil 1 genre saja
// $related_films = null; 
// if (!empty($genres)) {
//     $first_genre_id = $genres[0]['id'];
//     $related_stmt = $connect->prepare("SELECT v.* FROM videos v JOIN video_genres vg ON v.id = vg.video_id WHERE vg.genre_id = ? AND v.id != ? ORDER BY RAND() LIMIT 10");
//     $related_stmt->bind_param("ii", $first_genre_id, $film_id);
//     $related_stmt->execute();
//     $related_films = $related_stmt->get_result();
//     $related_stmt->close();
// }

// =======================================================
// ===== LOGIKA BARU "UP NEXT" DENGAN PERBAIKAN ERROR ====
// =======================================================
$related_films = null; 
if (!empty($genres)) {
    // 1. Ambil semua ID genre dari film saat ini
    $genre_ids = array_column($genres, 'id');
    
    // 2. Buat placeholder '?' sebanyak jumlah genre untuk query SQL
    $placeholders = implode(',', array_fill(0, count($genre_ids), '?'));

    // 3. Query baru yang lebih cerdas (tidak ada perubahan di sini)
    $sql = "
        SELECT 
            v.*, 
            COUNT(v.id) as shared_genres_count
        FROM 
            videos v
        JOIN 
            video_genres vg ON v.id = vg.video_id
        WHERE 
            vg.genre_id IN ($placeholders)
            AND v.id != ?
        GROUP BY 
            v.id
        ORDER BY 
            shared_genres_count DESC,
            RAND()
        LIMIT 10
    ";

    // 4. Mempersiapkan statement
    $related_stmt = $connect->prepare($sql);
    
    // 5. Membuat tipe data untuk bind_param
    $types = str_repeat('i', count($genre_ids)) . 'i';

    // ===== BAGIAN YANG DIPERBAIKI =====
    // 6. Gabungkan semua parameter ke dalam satu array
    $params = $genre_ids;
    $params[] = $film_id;

    // 7. Bind parameter menggunakan array yang sudah digabung
    $related_stmt->bind_param($types, ...$params);
    // ===== AKHIR BAGIAN YANG DIPERBAIKI =====
    
    // 8. Eksekusi dan ambil hasilnya
    $related_stmt->execute();
    $related_films = $related_stmt->get_result();
    $related_stmt->close();
}
// =======================================================
// ============= AKHIR DARI LOGIKA BARU ==================
// =======================================================


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
                <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
    <?php
    $max_genres_visible = 10;
    $i = 0;
    foreach($genres as $g):
        if ($i < $max_genres_visible): ?>
            <a href="/category.php?id=<?= $g['id'] ?>" class="badge bg-danger text-decoration-none"><?= htmlspecialchars($g['nama']) ?></a>
        <?php endif;
        $i++;
    endforeach;

    if (count($genres) > $max_genres_visible): ?>
        <div class="dropdown">
            <button class="badge bg-secondary dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Sisa Genrenya
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
                <?php
                $i = 0;
                foreach($genres as $g):
                    if ($i >= $max_genres_visible): ?>
                        <li><a class="dropdown-item" href="/category.php?id=<?= $g['id'] ?>"><?= htmlspecialchars($g['nama']) ?></a></li>
                    <?php endif;
                    $i++;
                endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="reaction-group ms-auto">
        <button type="button" class="btn btn-sm btn-outline-secondary reaction-btn <?= ($user_reaction === 'like') ? 'active' : '' ?>" data-reaction="like" data-video-id="<?= $film_id ?>">
            <i class="fas fa-thumbs-up"></i>
            <span class="like-count ms-2"><?= $like_count ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary reaction-btn <?= ($user_reaction === 'dislike') ? 'active' : '' ?>" data-reaction="dislike" data-video-id="<?= $film_id ?>">
            <i class="fas fa-thumbs-down"></i>
        </button>
    </div>
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
        console.log('Penanda 1: Skrip dimulai.'); // PENANDA 1

        try {
            const player = new Plyr('#player');
            console.log('Penanda 2: Objek Plyr berhasil dibuat.', player); // PENANDA 2

            player.on('ready', () => {
                console.log('Penanda 3: Player siap digunakan.'); // PENANDA 3
            });

            let hasBeenRecorded = false;
            player.on('play', () => {
                console.log('Penanda 4: Tombol PLAY diklik!'); // PENANDA 4
                if (!hasBeenRecorded) {
                    hasBeenRecorded = true;
                    fetch('/record_history.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `video_id=<?= $film_id ?>`
                    });
                }
            });
        } catch (e) {
            console.error('ERROR PENTING:', e); // PENANDA JIKA ADA ERROR
        }
    });


    // Letakkan di dalam tag <script> di bawah watch.php

document.querySelectorAll('.reaction-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Cek jika user sudah login (jika tombol ada untuk non-user)
        <?php if (!isset($_SESSION['user_id'])): ?>
            // Jika Anda punya modal login, tampilkan di sini. Contoh:
            // var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            // loginModal.show();
            alert("Anda harus login untuk memberikan reaksi!");
            return;
        <?php endif; ?>
        
        const videoId = this.dataset.videoId;
        const reactionType = this.dataset.reaction;

        fetch('/handle_reaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `video_id=${videoId}&reaction=${reactionType}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update jumlah like secara visual
                document.querySelector('.like-count').textContent = data.new_like_count;
                
                const likeBtn = document.querySelector('.reaction-btn[data-reaction="like"]');
                const dislikeBtn = document.querySelector('.reaction-btn[data-reaction="dislike"]');

                // Logika untuk highlight tombol yang aktif
                if (this.classList.contains('active')) {
                    // Jika tombol yang diklik sudah aktif, nonaktifkan
                    this.classList.remove('active');
                } else {
                    // Jika tidak, nonaktifkan semua tombol lalu aktifkan yang diklik
                    likeBtn.classList.remove('active');
                    dislikeBtn.classList.remove('active');
                    this.classList.add('active');
                }
            } else {
                alert(data.message || 'Terjadi kesalahan.');
            }
        });
    });
});
</script>