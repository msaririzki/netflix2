<?php
session_start();

// Keamanan: Cek jika pengguna adalah admin, jika tidak, tendang keluar.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= $page_title ?? 'Netflix' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="bg-dark text-white">

<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark bg-black">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Admin Dashboard</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="/" target="_blank" class="nav-link">Lihat Situs</a>
                        </li>
                        <li class="nav-item">
                            <a href="/auth/logout.php" class="nav-link">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="container-fluid p-4"></div>