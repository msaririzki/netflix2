<?php
$page_title = "Add New Film";
include 'templates/header.php';
require_once '../config/database.php';

// Ambil daftar genre dari database untuk ditampilkan di dropdown
$genres_result = $connect->query("SELECT * FROM genres ORDER BY nama ASC");

$error_messages = [];
$success_message = '';

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $title = $_POST['title'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $release_year = $_POST['release_year'] ?? null;
    $genre_id = $_POST['genre_id'] ?? null; // Ambil genre_id
    $director = $_POST['director'] ?? null;
    $cast = $_POST['cast'] ?? null;
    $user_id = $_SESSION['user_id'];

    $video_file = $_FILES['video_file'] ?? null;
    $thumbnail_file = $_FILES['thumbnail_file'] ?? null;

    $video_path = '';
    $thumbnail_path = '';

    // Validasi & Upload Video
    if ($video_file && $video_file['error'] === UPLOAD_ERR_OK) {
        $target_dir_video = "../assets/videos/";
        if (!is_dir($target_dir_video)) { mkdir($target_dir_video, 0777, true); }
        $video_name = uniqid() . '_' . basename($video_file['name']);
        $video_path = $target_dir_video . $video_name;
        if (!move_uploaded_file($video_file['tmp_name'], $video_path)) {
            $error_messages[] = "Failed to upload video file.";
        }
    } else {
        $error_messages[] = "Video file is required.";
    }

    // Validasi & Upload Thumbnail
    if ($thumbnail_file && $thumbnail_file['error'] === UPLOAD_ERR_OK) {
        $target_dir_thumb = "../assets/images/";
        if (!is_dir($target_dir_thumb)) { mkdir($target_dir_thumb, 0777, true); }
        $thumb_name = uniqid() . '_' . basename($thumbnail_file['name']);
        $thumbnail_path = $target_dir_thumb . $thumb_name;
        if (!move_uploaded_file($thumbnail_file['tmp_name'], $thumbnail_path)) {
            $error_messages[] = "Failed to upload thumbnail file.";
        }
    } else {
        $error_messages[] = "Thumbnail file is required.";
    }

    // Jika tidak ada error, simpan ke database
    if (empty($error_messages)) {
        $clean_video_path = str_replace('../', '', $video_path);
        $clean_thumbnail_path = str_replace('../', '', $thumbnail_path);

        $stmt = $connect->prepare("INSERT INTO videos (user_id, title, deskripsi, file_path, thumbnail_path, genre_id, duration, release_year, director, cast) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // Sesuaikan bind_param dengan tipe data yang benar (i untuk genre_id)
        $stmt->bind_param("issssisiss", $user_id, $title, $deskripsi, $clean_video_path, $clean_thumbnail_path, $genre_id, $duration, $release_year, $director, $cast);

        if ($stmt->execute()) {
            $success_message = "Film '$title' has been successfully added! <a href='manage_films.php'>View all films</a>";
        } else {
            $error_messages[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
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
            <div class="mb-3"><label for="title" class="form-label">Title</label><input type="text" class="form-control" name="title" id="title" required></div>
            <div class="mb-3"><label for="deskripsi" class="form-label">Description</label><textarea class="form-control" name="deskripsi" id="deskripsi" rows="5" required></textarea></div>
            <div class="mb-3"><label for="cast" class="form-label">Cast</label><input type="text" class="form-control" name="cast" id="cast" placeholder="e.g., Tom Holland, Zendaya, ..."></div>
            <div class="mb-3"><label for="director" class="form-label">Director</label><input type="text" class="form-control" name="director" id="director" placeholder="e.g., Jon Watts"></div>
        </div>
        <div class="col-md-4">
            <div class="mb-3"><label for="release_year" class="form-label">Release Year</label><input type="number" class="form-control" name="release_year" id="release_year" placeholder="e.g., 2021" required></div>
            <div class="mb-3">
                <label for="genre_id" class="form-label">Genre</label>
                <select class="form-select" name="genre_id" id="genre_id" required>
                    <option value="" disabled selected>Choose a genre...</option>
                    <?php mysqli_data_seek($genres_result, 0); ?>
                    <?php while($g = $genres_result->fetch_assoc()): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3"><label for="duration" class="form-label">Duration</label><input type="text" class="form-control" name="duration" id="duration" placeholder="e.g., 2h 28m" required></div>
            <div class="mb-3"><label for="video_file" class="form-label">Video File (MP4)</label><input type="file" class="form-control" name="video_file" id="video_file" accept="video/mp4" required></div>
            <div class="mb-3"><label for="thumbnail_file" class="form-label">Thumbnail (JPG, PNG)</label><input type="file" class="form-control" name="thumbnail_file" id="thumbnail_file" accept="image/*" required></div>
        </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-success">Add Film</button>
    <a href="manage_films.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include 'templates/footer.php'; ?>