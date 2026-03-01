<?php
$h1 = password_hash('admin123', PASSWORD_DEFAULT);
$h2 = password_hash('petugas123', PASSWORD_DEFAULT);
$h3 = password_hash('owner123', PASSWORD_DEFAULT);
file_put_contents('clean_hashes.txt', "admin123: $h1\npetugas123: $h2\nowner123: $h3\n");
?>
