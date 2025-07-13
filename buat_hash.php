<?php
// Ganti 'PasswordSuperAman123' dengan password yang Anda inginkan
$password_untuk_admin = 'admin123'; 

$hash = password_hash($password_untuk_admin, PASSWORD_DEFAULT);

echo "Password Anda: " . $password_untuk_admin . "<br>";
echo "Hasil Hash (salin ini): " . $hash;
?>