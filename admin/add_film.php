<?php
$page_title = "Add New Film";
include 'templates/header.php';
require_once '../config/database.php';

// Ambil daftar genre untuk ditampilkan sebagai checkboxes
$genres_result = $connect->query("SELECT * FROM genres ORDER BY nama ASC");

$error_messages = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $title = $_POST['title'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $release_year = $_POST['release_year'] ?? null;
    $director = $_POST['director'] ?? null;
    $cast = $_POST['cast'] ?? null;
    $user_id = $_SESSION['user_id'];
    $selected_genres = $_POST['genres'] ?? [];

    // Inisialisasi path file
    $video_path = null;
    $thumbnail_path = null;

    // 1. Validasi & Upload Video
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir_video = "../assets/videos/";
        if (!is_dir($target_dir_video)) { mkdir($target_dir_video, 0777, true); }
        $video_name = uniqid() . '_' . basename($_FILES['video_file']['name']);
        $target_video_file = $target_dir_video . $video_name;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target_video_file)) {
            $video_path = $target_video_file;
        } else {
            $error_messages[] = "Gagal memindahkan file video.";
        }
    } else {
        $error_messages[] = "File video wajib diunggah.";
    }

    // 2. Validasi & Upload Thumbnail
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir_thumb = "../assets/images/";
        if (!is_dir($target_dir_thumb)) { mkdir($target_dir_thumb, 0777, true); }
        $thumb_name = uniqid() . '_' . basename($_FILES['thumbnail_file']['name']);
        $target_thumb_file = $target_dir_thumb . $thumb_name;
        if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $target_thumb_file)) {
            $thumbnail_path = $target_thumb_file;
        } else {
            $error_messages[] = "Gagal memindahkan file thumbnail.";
        }
    } else {
        $error_messages[] = "File thumbnail wajib diunggah.";
    }

    // 3. Hanya lanjutkan jika tidak ada error sama sekali
    if (empty($error_messages)) {
        // Masukkan data ke tabel 'videos'
        $stmt_video = $connect->prepare("INSERT INTO videos (user_id, title, deskripsi, file_path, thumbnail_path, duration, release_year, director, cast) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $clean_video_path = str_replace('../', '', $video_path);
        $clean_thumbnail_path = str_replace('../', '', $thumbnail_path);
        $stmt_video->bind_param("isssssiss", $user_id, $title, $deskripsi, $clean_video_path, $clean_thumbnail_path, $duration, $release_year, $director, $cast);

        if ($stmt_video->execute()) {
            $new_video_id = $stmt_video->insert_id;

            // Masukkan genre yang dipilih ke tabel 'video_genres'
            if (!empty($selected_genres)) {
                $stmt_genre = $connect->prepare("INSERT INTO video_genres (video_id, genre_id) VALUES (?, ?)");
                foreach ($selected_genres as $genre_id) {
                    $stmt_genre->bind_param("ii", $new_video_id, $genre_id);
                    $stmt_genre->execute();
                }
                $stmt_genre->close();
            }
            $success_message = "Film '$title' berhasil ditambahkan! <a href='manage_films.php'>Lihat semua film</a>";
        } else {
            $error_messages[] = "Database error: " . $stmt_video->error;
        }
        $stmt_video->close();
    }
}
?>

<h1 class="mb-4">Add New Film</h1>

<?php if ($success_message): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>
<?php if (!empty($error_messages)): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($error_messages as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form action="add_film.php" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title" id="title" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="cast" class="form-label">Cast</label>
                <input type="text" class="form-control" name="cast" id="cast" placeholder="e.g., Tom Holland, Zendaya, ...">
            </div>
             <div class="mb-3">
                <label for="director" class="form-label">Director</label>
                <input type="text" class="form-control" name="director" id="director" placeholder="e.g., Jon Watts">
            </div>
        </div>
        <div class="col-md-4">
             <div class="mb-3">
                <label for="release_year" class="form-label">Release Year</label>
                <input type="number" class="form-control" name="release_year" id="release_year" placeholder="e.g., 2021" required>
            </div>
             <div class="mb-3">
                <label class="form-label">Genres</label>
                <div class="genre-checkbox-container">
                    <?php mysqli_data_seek($genres_result, 0); ?>
                    <?php while($g = $genres_result->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="genres[]" value="<?= $g['id'] ?>" id="genre_<?= $g['id'] ?>">
                            <label class="form-check-label" for="genre_<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nama']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
             <div class="mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="text" class="form-control" name="duration" id="duration" placeholder="e.g., 2h 28m" required>
            </div>
             <div class="mb-3">
                <label for="video_file" class="form-label">Video File (MP4)</label>
                <input type="file" class="form-control" name="video_file" id="video_file" accept="video/mp4" required>
            </div>
             <div class="mb-3">
                <label for="thumbnail_file" class="form-label">Thumbnail (JPG, PNG)</label>
                <input type="file" class="form-control" name="thumbnail_file" id="thumbnail_file" accept="image/*" required>
            </div>
        </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-success">Add Film</button>
    <a href="manage_films.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include 'templates/footer.php'; ?>