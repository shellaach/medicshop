<?php
session_start();
include "config/koneksi.php";

// cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak!");
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']); 

    // update status jadi Dibatalkan
    $sql = "UPDATE orders SET status='Dibatalkan' WHERE id=$id AND status='Diproses'";
    mysqli_query($koneksi, $sql);
}

// kembali ke halaman manage_orders
header("Location: manage_orders.php");
exit;
?>
