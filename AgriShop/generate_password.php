<?php
$password = 'admin_password'; // Replace this with your desired admin password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
