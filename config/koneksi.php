<?php
$host = "localhost";
$user = "root"; // default user XAMPP
$pass = "";
$db   = "db_toko"; // nama database yang kamu buat

$koneksi = mysqli_connect($host, $user, $pass, $db);

if(!$koneksi){
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
