<?php
session_start();
include "config/koneksi.php";

// cek login
if(!isset($_SESSION['user'])){
    die("❌ Harap login terlebih dahulu.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_SESSION['user']['id'];
    $order_id = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);

    // validasi rating
    if($rating < 1 || $rating > 5){
        die("❌ Rating tidak valid.");
    }

    // cek apakah pesanan milik user dan sudah selesai
    $cek = mysqli_query($koneksi, "SELECT * FROM orders WHERE id='$order_id' AND user_id='$user_id' AND status='Selesai'");
    if(mysqli_num_rows($cek) == 0){
        die("❌ Pesanan tidak valid atau belum selesai.");
    }

    // cek apakah user sudah memberi rating sebelumnya
    $cek_rating = mysqli_query($koneksi, "SELECT * FROM ratings WHERE order_id='$order_id' AND user_id='$user_id'");
    if(mysqli_num_rows($cek_rating) > 0){
        die("❌ Anda sudah memberi rating untuk pesanan ini.");
    }

    // simpan rating
    $insert = mysqli_query($koneksi, "INSERT INTO ratings (order_id, user_id, rating) VALUES ('$order_id', '$user_id', '$rating')");
    if($insert){
        header("Location: my_orders.php?msg=Rating berhasil dikirim!");
        exit;
    } else {
        die("❌ Gagal menyimpan rating. Silakan coba lagi.");
    }
} else {
    die("❌ Akses tidak valid.");
}
