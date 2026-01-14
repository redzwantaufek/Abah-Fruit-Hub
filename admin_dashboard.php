<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
echo "<h1>Selamat Datang, " . $_SESSION['user_name'] . "</h1>";
echo "Anda log masuk sebagai: " . $_SESSION['user_role'];
echo "<br><a href='logout.php'>Log Keluar</a>";
?>