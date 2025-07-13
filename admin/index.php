<?php
$page_title = "Dashboard";
include 'templates/header.php';
require_once '../config/database.php';

// Data statistik dasar
$total_films = $connect->query("SELECT COUNT(*) as total FROM videos")->fetch_assoc()['total'];
$total_users = $connect->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_genres = $connect->query("SELECT COUNT(*) as total FROM genres")->fetch_assoc()['total'];

// Data analitik film terpopuler
$query_analytics = "
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
$result_analytics = $connect->query($query_analytics);
$top_films = [];
if ($result_analytics && $result_analytics->num_rows > 0) {
    while ($row = $result_analytics->fetch_assoc()) {
        $top_films[] = $row;
    }
}
$max_views = 0;
if (!empty($top_films)) {
    $max_views = $top_films[0]['view_count'];
}
?>

<h1 class="mb-4">Dashboard</h1>

<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card text-white bg-danger h-100">
            <div class="card-header"><i class="fas fa-film me-2"></i>Total Films</div>
            <div class="card-body text-center d-flex align-items-center justify-content-center">
                <h5 class="card-title display-5"><?= $total_films ?></h5>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-header"><i class="fas fa-users me-2"></i>Total Users</div>
            <div class="card-body text-center d-flex align-items-center justify-content-center">
                <h5 class="card-title display-5"><?= $total_users ?></h5>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-header"><i class="fas fa-tags me-2"></i>Total Genres</div>
            <div class="card-body text-center d-flex align-items-center justify-content-center">
                <h5 class="card-title display-5"><?= $total_genres ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bg-dark border-secondary mt-2">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-chart-bar me-2"></i>Top 10 Most Watched Films</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($top_films)): ?>
                    <table class="table table-dark table-borderless align-middle mb-0">
                        <tbody>
                            <?php foreach ($top_films as $film): ?>
                                <?php
                                    $percentage = ($max_views > 0) ? ($film['view_count'] / $max_views) * 100 : 0;
                                ?>
                                <tr>
                                    <td style="width: 33%;"><strong><?= htmlspecialchars($film['title']) ?></strong></td>
                                    <td style="width: 50%;">
                                        <div class="progress" style="height: 20px; background-color: #333;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $percentage ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-end" style="width: 17%;"><strong><?= $film['view_count'] ?></strong> views</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-secondary text-center">No watch history data available yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include 'templates/footer.php'; ?>