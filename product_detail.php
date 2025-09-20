<?php
session_start();
include "config/koneksi.php";

// Cek login customer
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer'){
    echo "❌ Anda harus login sebagai customer. <a href='login.php'>Login</a>";
    exit;
}

// Ambil ID produk
if(!isset($_GET['id'])){
    die("Produk tidak ditemukan!");
}
$id = $_GET['id'];

// Query produk
$q = mysqli_query($koneksi,"SELECT * FROM products WHERE id='$id'");
if(mysqli_num_rows($q) == 0){
    die("Produk tidak ada!");
}
$produk = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <a href="index.php" class="btn btn-secondary btn-sm mb-3">⬅ Kembali ke Produk</a>

  <div class="card shadow">
    <div class="row g-0">
      <div class="col-md-5 text-center p-3">
        <?php if(!empty($produk['gambar'])): ?>
          <img src="uploads/<?= $produk['gambar'] ?>" class="img-fluid rounded" alt="<?= $produk['nama_produk'] ?>">
        <?php else: ?>
          <img src="https://via.placeholder.com/400x400?text=No+Image" class="img-fluid rounded">
        <?php endif; ?>
      </div>
      <div class="col-md-7">
        <div class="card-body">
          <h2 class="card-title"><?= $produk['nama_produk'] ?></h2>
          <h4 class="text-success">Rp <?= number_format($produk['harga'],0,',','.') ?></h4>
          <p class="mb-1"><b>Kategori:</b> <?= $produk['kategori'] ?></p>
          <p class="mb-1"><b>Stok:</b> <?= $produk['stok'] ?></p>
          <hr>
          <a href="cart.php?id=<?= $produk['id'] ?>" class="btn btn-primary btn-lg">🛒 Tambah ke Keranjang</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
