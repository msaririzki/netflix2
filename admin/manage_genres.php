<?php
$page_title = "Manage Genres";
include 'templates/header.php';
require_once '../config/database.php';

// Logika tambah genre baru
if (isset($_POST['add_genre'])) {
    $new_genre_name = trim($_POST['nama']);
    if (!empty($new_genre_name)) {
        $stmt = $connect->prepare("INSERT INTO genres (nama) VALUES (?)");
        $stmt->bind_param("s", $new_genre_name);
        $stmt->execute();
        header("Location: manage_genres.php"); // Refresh halaman
        exit;
    }
}

// Logika hapus genre
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $connect->prepare("DELETE FROM genres WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_genres.php"); // Refresh halaman
    exit;
}

$genres = $connect->query("SELECT * FROM genres ORDER BY nama ASC");
?>

<h1 class="mb-4">Manage Genres</h1>
<div class="row">
    <div class="col-md-8">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Genre Name</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php while($g = $genres->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['nama']) ?></td>
                            <td class="text-end">
                                <a href="manage_genres.php?delete_id=<?= $g['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <h5 class="card-title">Add New Genre</h5>
                <form method="POST" action="manage_genres.php">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Genre Name</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>
                    <button type="submit" name="add_genre" class="btn btn-success w-100">Add Genre</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>