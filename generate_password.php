<?php
/**
 * Script untuk generate password hash
 * Jalankan file ini di browser untuk mendapatkan hash password yang benar
 */

$password = "12345678";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p>Password: <strong>$password</strong></p>";
echo "<p>Hash: <strong>$hash</strong></p>";
echo "<hr>";
echo "<h3>SQL Update Query:</h3>";
echo "<pre>";
echo "UPDATE tb_user SET password = '$hash' WHERE username = 'admin';\n";
echo "UPDATE tb_user SET password = '$hash' WHERE username = 'petugas';\n";
echo "UPDATE tb_user SET password = '$hash' WHERE username = 'owner';";
echo "</pre>";
?>
