<?php
$page_title = "Manage Films";
include 'templates/header.php';
require_once '../config/database.php';

// Logika untuk menghapus film
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Hapus file fisik (opsional tapi direkomendasikan)
    $stmt_get_paths = $connect->prepare("SELECT file_path, thumbnail_path FROM videos WHERE id = ?");
    $stmt_get_paths->bind_param("i", $delete_id);
    $stmt_get_paths->execute();
    $paths = $stmt_get_paths->get_result()->fetch_assoc();
    if ($paths) {
        // Path di server menggunakan '../' karena kita berada di dalam folder /admin
        if (file_exists('../' . $paths['file_path'])) unlink('../' . $paths['file_path']);
        if (file_exists('../' . $paths['thumbnail_path'])) unlink('../' . $paths['thumbnail_path']);
    }
    $stmt_get_paths->close();

    // Hapus data dari database
    $stmt_delete = $connect->prepare("DELETE FROM videos WHERE id = ?");
    $stmt_delete->bind_param("i", $delete_id);
    if ($stmt_delete->execute()) {
        echo "<div class='alert alert-success'>Film berhasil dihapus.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menghapus film.</div>";
    }
    $stmt_delete->close();
}


// Ambil semua data film dan gabungkan dengan nama genre menggunakan LEFT JOIN
$query = "
    SELECT 
        v.id, 
        v.title, 
        v.release_year, 
        g.nama AS genre_name 
    FROM 
        videos v
    LEFT JOIN 
        genres g ON v.genre_id = g.id
    ORDER BY 
        v.created_at DESC
";
$result = $connect->query($query);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0">Manage Films</h1>
    <a href="add_film.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Add New Film</a>
</div>

<div class="table-responsive">
    <table class="table table-dark table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">#ID</th>
                <th scope="col">Title</th>
                <th scope="col">Genre</th>
                <th scope="col">Year</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($film = $result->fetch_assoc()): ?>
                    <tr>
                        <th scope="row"><?= $film['id'] ?></th>
                        <td><?= htmlspecialchars($film['title']) ?></td>
                        <td><?= htmlspecialchars($film['genre_name'] ?? 'Uncategorized') ?></td>
                        <td><?= htmlspecialchars($film['release_year'] ?? 'N/A') ?></td>
                        <td class="text-end">
                            <a href="edit_film.php?id=<?= $film['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <a href="manage_films.php?delete_id=<?= $film['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this film? This action cannot be undone.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-secondary">No films found. Add one to get started!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'templates/footer.php'; ?>