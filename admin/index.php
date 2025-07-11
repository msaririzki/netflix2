<?php
$page_title = "Dashboard";
include 'templates/header.php';
require_once '../config/database.php';

// Ambil data statistik sederhana
$total_films = $connect->query("SELECT COUNT(*) as total FROM videos")->fetch_assoc()['total'];
$total_users = $connect->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
// Statistik lain bisa ditambahkan di sini

?>

<h1 class="mb-4">Dashboard</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Total Films</div>
            <div class="card-body">
                <h5 class="card-title"><?= $total_films ?></h5>
                <p class="card-text">films currently in the database.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Total Users</div>
            <div class="card-body">
                <h5 class="card-title"><?= $total_users ?></h5>
                <p class="card-text">users registered in the system.</p>
            </div>
        </div>
    </div>
    </div>

<?php include 'templates/footer.php'; ?>