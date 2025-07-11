<?php
$page_title = "Manage Users";
include 'templates/header.php';
require_once '../config/database.php';

// (Logika untuk Hapus atau Edit user bisa ditambahkan di sini nanti)
// Contoh:
// if (isset($_GET['delete_id'])) { ... }

// Ambil semua data pengguna, urutkan berdasarkan yang terbaru mendaftar
$result = $connect->query("SELECT id, nama, email, role, waktu_pembuatan FROM users ORDER BY waktu_pembuatan DESC");

?>

<h1 class="mb-4">Manage Users</h1>

<div class="table-responsive">
    <table class="table table-dark table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">#ID</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Registered On</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <th scope="row"><?= $user['id'] ?></th>
                        <td><?= htmlspecialchars($user['nama']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-success">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y, H:i', strtotime($user['waktu_pembuatan'])) ?></td>
                        <td class="text-end">
                            <button class="btn btn-primary btn-sm" disabled><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn btn-danger btn-sm" disabled><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-secondary">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'templates/footer.php'; ?>