<?php
$page_title = "Edit Film";
include 'templates/header.php';
require_once '../config/database.php';

// Validasi ID film dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: manage_films.php"); exit; }
$film_id = $_GET['id'];

// Ambil data film saat ini
$stmt = $connect->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { echo "<div class='alert alert-danger'>Film tidak ditemukan.</div>"; include 'templates/footer.php'; exit; }
$film = $result->fetch_assoc();
$stmt->close();

// Ambil semua genre untuk dropdown
$genres_result = $connect->query("SELECT * FROM genres ORDER BY nama ASC");

$error_messages = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $title = $_POST['title'] ?? $film['title'];
    $deskripsi = $_POST['deskripsi'] ?? $film['deskripsi'];
    $duration = $_POST['duration'] ?? $film['duration'];
    $release_year = $_POST['release_year'] ?? $film['release_year'];
    $genre_id = $_POST['genre_id'] ?? $film['genre_id'];
    $director = $_POST['director'] ?? $film['director'];
    $cast = $_POST['cast'] ?? $film['cast'];

    // Update data di database
    $update_stmt = $connect->prepare("UPDATE videos SET title = ?, deskripsi = ?, duration = ?, release_year = ?, genre_id = ?, director = ?, cast = ? WHERE id = ?");
    // Sesuaikan bind_param dengan tipe data yang benar (i untuk genre_id)
    $update_stmt->bind_param("sssiissi", $title, $deskripsi, $duration, $release_year, $genre_id, $director, $cast, $film_id);

    if ($update_stmt->execute()) {
        $success_message = "Film '$title' berhasil diperbarui! <a href='manage_films.php'>Kembali ke daftar film</a>";
        // Refresh data film setelah update
        $film = $connect->query("SELECT * FROM videos WHERE id = $film_id")->fetch_assoc();
    } else {
        $error_messages[] = "Database error: " . $update_stmt->error;
    }
    $update_stmt->close();
}
?>

<h1 class="mb-4">Edit Film: <?= htmlspecialchars($film['title']) ?></h1>

<?php if ($success_message): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>
<?php if (!empty($error_messages)): ?>
    <div class="alert alert-danger"><ul><?php foreach ($error_messages as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form action="edit_film.php?id=<?= $film_id ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3"><label for="title" class="form-label">Title</label><input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($film['title']) ?>" required></div>
            <div class="mb-3"><label for="deskripsi" class="form-label">Description</label><textarea class="form-control" name="deskripsi" id="deskripsi" rows="5" required><?= htmlspecialchars($film['deskripsi']) ?></textarea></div>
            <div class="mb-3"><label for="cast" class="form-label">Cast</label><input type="text" class="form-control" name="cast" id="cast" value="<?= htmlspecialchars($film['cast']) ?>"></div>
            <div class="mb-3"><label for="director" class="form-label">Director</label><input type="text" class="form-control" name="director" id="director" value="<?= htmlspecialchars($film['director']) ?>"></div>
        </div>
        <div class="col-md-4">
            <div class="mb-3"><label for="release_year" class="form-label">Release Year</label><input type="number" class="form-control" name="release_year" id="release_year" value="<?= htmlspecialchars($film['release_year']) ?>" required></div>
            <div class="mb-3">
                <label for="genre_id" class="form-label">Genre</label>
                <select class="form-select" name="genre_id" id="genre_id" required>
                    <?php mysqli_data_seek($genres_result, 0); ?>
                    <?php while($g = $genres_result->fetch_assoc()): ?>
                        <option value="<?= $g['id'] ?>" <?= ($film['genre_id'] == $g['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3"><label for="duration" class="form-label">Duration</label><input type="text" class="form-control" name="duration" id="duration" value="<?= htmlspecialchars($film['duration']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Current Thumbnail</label><br><img src="../<?= htmlspecialchars($film['thumbnail_path']) ?>" alt="Thumbnail" style="max-width: 150px; border-radius: 5px;"></div>
        </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-primary">Update Film</button>
    <a href="manage_films.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include 'templates/footer.php'; ?>