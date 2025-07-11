<?php
$page_title = "Analytics";
include 'templates/header.php';
require_once '../config/database.php';

// Query untuk mendapatkan film paling banyak ditonton
// Kita menghitung berapa kali setiap video_id muncul di tabel history,
// lalu menggabungkannya dengan tabel videos untuk mendapatkan judulnya.
$query = "
    SELECT 
        v.title,
        COUNT(h.video_id) AS view_count
    FROM 
        history h
    JOIN 
        videos v ON h.video_id = v.id
    GROUP BY 
        h.video_id, v.title
    ORDER BY 
        view_count DESC
    LIMIT 10;
";

$result = $connect->query($query);

// Temukan jumlah tontonan tertinggi untuk kalkulasi persentase progress bar
$max_views = 0;
if ($result->num_rows > 0) {
    // Clone result untuk iterasi terpisah tanpa mengganggu pointer utama
    $clone_result = clone $result;
    $first_row = $clone_result->fetch_assoc();
    $max_views = $first_row['view_count'] ?? 1; // Default 1 untuk hindari pembagian oleh nol
}

?>

<h1 class="mb-4">Film Analytics</h1>

<div class="card bg-dark border-secondary">
    <div class="card-header">
        <h5 class="m-0">Top 10 Most Watched Films</h5>
    </div>
    <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
            <ul class="list-group list-group-flush">
                <?php while ($film = $result->fetch_assoc()): ?>
                    <?php
                        // Hitung persentase untuk lebar progress bar
                        $percentage = ($film['view_count'] / $max_views) * 100;
                    ?>
                    <li class="list-group-item bg-transparent text-white">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <strong><?= htmlspecialchars($film['title']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $film['view_count'] ?>" aria-valuemin="0" aria-valuemax="<?= $max_views ?>">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <strong><?= $film['view_count'] ?></strong> views
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-secondary text-center">No watch history data available yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>