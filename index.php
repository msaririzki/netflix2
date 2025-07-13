<?php
session_start();
$page_title = 'Home - Netflix Gadungan'; 
require_once 'config/database.php';
require_once 'templates/header.php';

// =======================================================
// ===== LOGIKA BARU UNTUK CAROUSEL TRENDING =============
// =======================================================
// Ambil 5 film "trending" berdasarkan jumlah like terbanyak.
// Jika semua like 0, akan otomatis menampilkan 5 film terbaru.
$query_trending = "
    SELECT
        v.*,
        COUNT(vr.id) AS like_count
    FROM
        videos v
    LEFT JOIN
        video_reactions vr ON v.id = vr.video_id AND vr.reaction_type = 'like'
    GROUP BY
        v.id
    ORDER BY
        like_count DESC, v.created_at DESC
    LIMIT 5;
";
$featured_films = $connect->query($query_trending);
// =======================================================


// Ambil semua genre yang memiliki film
$genres_with_films = $connect->query("
    SELECT g.id, g.nama 
    FROM genres g
    JOIN video_genres vg ON g.id = vg.genre_id
    GROUP BY g.id, g.nama
    ORDER BY g.nama ASC
");
?>

<?php
// Cek apakah ada film untuk ditampilkan
if ($featured_films && $featured_films->num_rows > 0):
    // Reset pointer query setelah cek num_rows agar bisa di-loop lagi
    mysqli_data_seek($featured_films, 0); 
?>

    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php $i = 0; while($film = $featured_films->fetch_assoc()): ?>
            <div class="carousel-item <?= ($i === 0) ? 'active' : '' ?>">
                <div class="hero-slide" style="background-image: url('/<?= htmlspecialchars($film['thumbnail_path']) ?>');">
                    <div class="hero-overlay"></div>
                    <div class="container hero-caption">
                        
                        <div class="badge bg-danger text-uppercase mb-2" data-aos="fade-right">
                            <i class="fas fa-fire me-1"></i> Trending
                        </div>

                        <h1 class="display-3 fw-bold" data-aos="fade-right" data-aos-delay="100"><?= htmlspecialchars($film['title']) ?></h1>
                        <p class="lead col-md-8" data-aos="fade-up" data-aos-delay="200"><?= htmlspecialchars(mb_strimwidth($film['deskripsi'], 0, 150, "...")) ?></p>
                        <a href="detail.php?id=<?= $film['id'] ?>" class="btn btn-danger btn-lg mt-2" data-aos="fade-up" data-aos-delay="300">Watch Now <i class="fas fa-play ms-2"></i></a>
                    </div>
                </div>
            </div>
            <?php $i++; endwhile; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="container-fluid my-5">
        <?php while($genre = $genres_with_films->fetch_assoc()): ?>
            <div class="genre-row mb-4">
                <h3 class="text-white mb-3 ms-4"><?= htmlspecialchars($genre['nama']) ?></h3>
                <div class="movie-row">
                    <?php
                    // Ambil 10 film untuk genre ini
                    $film_stmt = $connect->prepare("
                        SELECT v.* FROM videos v
                        JOIN video_genres vg ON v.id = vg.video_id
                        WHERE vg.genre_id = ?
                        ORDER BY v.release_year DESC
                        LIMIT 10
                    ");
                    $film_stmt->bind_param("i", $genre['id']);
                    $film_stmt->execute();
                    $films_in_genre = $film_stmt->get_result();
                    ?>
                    <?php while($film = $films_in_genre->fetch_assoc()): ?>
                        <div class="movie-card-wrapper">
                            <a href="detail.php?id=<?= $film['id'] ?>">
                                <div class="movie-card-new">
                                    <img src="/<?= htmlspecialchars($film['thumbnail_path']) ?>" alt="<?= htmlspecialchars($film['title']) ?>">
                                    <div class="movie-card-overlay">
                                        <div class="movie-card-title"><?= htmlspecialchars($film['title']) ?></div>
                                        <i class="fas fa-play-circle fa-3x"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; $film_stmt->close(); ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

<?php
else:
    // Tampilan halaman kosong jika tidak ada film sama sekali
?>
    <div class="container d-flex align-items-center justify-content-center text-center" style="min-height: 80vh;">
        <div class="text-white-50">
            <i class="fas fa-video-slash fa-5x mb-4"></i>
            <h1 class="display-5 fw-bold">Belum Ada Film yang Tersedia</h1>
            <p class="lead col-lg-8 mx-auto">Situs ini masih dalam tahap pengembangan. Silakan kembali lagi nanti untuk melihat koleksi film kami.</p>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <hr class="w-25 mx-auto my-4">
                <p class="mb-3">Kamu adminnya co,tambahin video sana</p>
                <a href="/admin/add_film.php" class="btn btn-danger btn-lg">
                    <i class="fas fa-plus me-2"></i>Tambah Film
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php
endif;
require_once 'templates/footer.php';
?>