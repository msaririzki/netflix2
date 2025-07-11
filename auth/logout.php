<?php
session_start();
session_destroy(); // Hapus semua data session
header('Location: ../index.php');
exit;
