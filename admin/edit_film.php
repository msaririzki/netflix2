<?php
$page_title = "Edit Film";
include 'templates/header.php';
require_once '../config/database.php';

// Validasi ID film dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_films.php");
    exit;
}
$film_id = $_GET['id'];

// Ambil data film saat ini
$stmt = $connect->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Film tidak ditemukan.</div>";
    include 'templates/footer.php';
    exit;
}
$film = $result->fetch_assoc();
$stmt->close();

// Ambil semua genre yang tersedia untuk ditampilkan sebagai checkbox
$genres_result = $connect->query("SELECT * FROM genres ORDER BY nama ASC");

// Ambil semua ID genre yang saat ini dimiliki oleh film ini
$current_genres_stmt = $connect->prepare("SELECT genre_id FROM video_genres WHERE video_id = ?");
$current_genres_stmt->bind_param("i", $film_id);
$current_genres_stmt->execute();
$current_genres_result = $current_genres_stmt->get_result();
$current_genre_ids = [];
while ($row = $current_genres_result->fetch_assoc()) {
    $current_genre_ids[] = $row['genre_id'];
}
$current_genres_stmt->close();

$error_messages = [];
$success_message = '';

// Proses form jika ada data yang dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $title = $_POST['title'] ?? $film['title'];
    $deskripsi = $_POST['deskripsi'] ?? $film['deskripsi'];
    $duration = $_POST['duration'] ?? $film['duration'];
    $release_year = $_POST['release_year'] ?? $film['release_year'];
    $director = $_POST['director'] ?? $film['director'];
    $cast = $_POST['cast'] ?? $film['cast'];
    $selected_genres = $_POST['genres'] ?? [];

    // 1. Update data utama di tabel 'videos' (tanpa genre)
    $update_video_stmt = $connect->prepare("UPDATE videos SET title = ?, deskripsi = ?, duration = ?, release_year = ?, director = ?, cast = ? WHERE id = ?");
    
    // ===== BARIS YANG DIPERBAIKI =====
    $update_video_stmt->bind_param("sssissi", $title, $deskripsi, $duration, $release_year, $director, $cast, $film_id);
    
    if ($update_video_stmt->execute()) {
        // 2. Hapus semua genre lama dari film ini di tabel 'video_genres'
        $delete_genres_stmt = $connect->prepare("DELETE FROM video_genres WHERE video_id = ?");
        $delete_genres_stmt->bind_param("i", $film_id);
        $delete_genres_stmt->execute();
        $delete_genres_stmt->close();

        // 3. Masukkan kembali genre yang baru dipilih
        if (!empty($selected_genres)) {
            $insert_genres_stmt = $connect->prepare("INSERT INTO video_genres (video_id, genre_id) VALUES (?, ?)");
            foreach ($selected_genres as $genre_id) {
                $insert_genres_stmt->bind_param("ii", $film_id, $genre_id);
                $insert_genres_stmt->execute();
            }
            $insert_genres_stmt->close();
        }
        
        $success_message = "Film '$title' berhasil diperbarui! <a href='manage_films.php'>Kembali ke daftar film</a>";
        
        // Refresh data untuk ditampilkan kembali di form
        $film = $connect->query("SELECT * FROM videos WHERE id = $film_id")->fetch_assoc();
        $current_genre_ids = $selected_genres;

    } else {
        $error_messages[] = "Database error: " . $update_video_stmt->error;
    }
    $update_video_stmt->close();
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
                <label class="form-label">Genres</label>
                <div class="genre-checkbox-container">
                    <?php mysqli_data_seek($genres_result, 0); ?>
                    <?php while($g = $genres_result->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="genres[]" value="<?= $g['id'] ?>" id="genre_<?= $g['id'] ?>"
                                <?php if (in_array($g['id'], $current_genre_ids)) echo 'checked'; ?>
                            >
                            <label class="form-check-label" for="genre_<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nama']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="mb-3"><label for="duration" class="form-label">Duration</label><input type="text" class="form-control" name="duration" id="duration" value="<?= htmlspecialchars($film['duration']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Current Thumbnail</label><br><img src="/<?= htmlspecialchars($film['thumbnail_path']) ?>" alt="Thumbnail" style="max-width: 150px; border-radius: 5px;"></div>
        </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-primary">Update Film</button>
    <a href="manage_films.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include 'templates/footer.php'; ?>