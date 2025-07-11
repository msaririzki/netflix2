<?php
session_start();
require_once 'config/database.php';

// Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Ambil data user saat ini untuk ditampilkan di form
$stmt = $connect->prepare("SELECT nama, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Proses form jika ada data yang dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses Ganti Nama
    if (isset($_POST['update_username'])) {
        $new_name = trim($_POST['nama']);
        if (!empty($new_name)) {
            $update_stmt = $connect->prepare("UPDATE users SET nama = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_name, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['user_name'] = $new_name; // Update session juga
                $message = "Nama berhasil diperbarui!";
                $user['nama'] = $new_name; // Update variabel lokal untuk tampilan
            } else {
                $error = "Gagal memperbarui nama.";
            }
            $update_stmt->close();
        } else {
            $error = "Nama tidak boleh kosong.";
        }
    }

    // Proses Ganti Password (tambahkan validasi lebih lanjut jika perlu)
    if (isset($_POST['update_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($new_password) < 8) {
            $error = "Password minimal harus 8 karakter.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $connect->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $message = "Password berhasil diubah!";
            } else {
                $error = "Gagal mengubah password.";
            }
            $update_stmt->close();
        }
    }

     // Proses Ganti Foto Profil
    if (isset($_POST['update_picture'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (in_array($file['type'], $allowed_types)) {
                $target_dir = "assets/images/profiles/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_name = uniqid() . '_' . basename($file['name']);
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $update_stmt = $connect->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $target_file, $user_id);
                    if ($update_stmt->execute()) {
                        $message = "Foto profil berhasil diubah!";
                        $user['profile_picture'] = $target_file; // Update untuk tampilan
                    } else {
                        $error = "Gagal menyimpan path gambar ke database.";
                    }
                    $update_stmt->close();
                } else {
                    $error = "Gagal mengunggah file gambar.";
                }
            } else {
                $error = "Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.";
            }
        } else {
            $error = "Pilih file gambar untuk diunggah.";
        }
    }
}

$page_title = 'My Profile';
require_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card profile-card text-center" data-aos="fade-right">
                <div class="card-body">
                    <img src="<?= htmlspecialchars($user['profile_picture'] ?: 'assets/images/profiles/default.png') ?>" alt="Profile Picture" class="profile-picture mb-3">
                    <h4 class="card-title"><?= htmlspecialchars($user['nama']) ?></h4>
                    <p class="text-secondary"><?= htmlspecialchars($user['email']) ?></p>
                    <hr>
                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        <label for="profile_picture" class="form-label">Ganti Foto Profil</label>
                        <input class="form-control form-control-sm bg-dark text-white mb-2" type="file" name="profile_picture" id="profile_picture" required>
                        <button type="submit" name="update_picture" class="btn btn-sm btn-outline-danger">Upload</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8" data-aos="fade-left">
            <h1 class="mb-4">Account Settings</h1>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="setting-block">
                <h5>Change Username</h5>
                <form action="profile.php" method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Username</label>
                        <input type="text" class="form-control bg-secondary text-white border-dark" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <button type="submit" name="update_username" class="btn btn-danger">Save Changes</button>
                </form>
            </div>

            <div class="setting-block">
                <h5>Change Password</h5>
                <form action="profile.php" method="POST">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control bg-secondary text-white border-dark" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control bg-secondary text-white border-dark" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-danger">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>