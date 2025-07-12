<?php
$page_title = "Analytics";
include 'templates/header.php';
require_once '../config/database.php';

// Query untuk mendapatkan film paling banyak ditonton
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

// Ambil semua hasil ke dalam sebuah array
$films = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $films[] = $row;
    }
}

// Temukan jumlah tontonan tertinggi dari array
$max_views = 0;
if (!empty($films)) {
    // Nilai tertinggi adalah view_count dari elemen pertama
    $max_views = $films[0]['view_count'];
}

?>

<h1 class="mb-4">Film Analytics</h1>

<div class="card bg-dark border-secondary">
    <div class="card-header">
        <h5 class="m-0">Top 10 Most Watched Films</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($films)): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($films as $film): ?>
                    <?php
                        // Hitung persentase untuk lebar progress bar
                        // Pastikan max_views tidak nol untuk menghindari pembagian oleh nol
                        $percentage = ($max_views > 0) ? ($film['view_count'] / $max_views) * 100 : 0;
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
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-secondary text-center">No watch history data available yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>