<?php
include "config/koneksi.php"; // hanya koneksi DB
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>MedicShop - Visitor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">MedicShop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVisitor">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarVisitor">
      <ul class="navbar-nav ms-auto d-flex align-items-center gap-2">
        <li class="nav-item">
          <a class="btn btn-light text-primary px-3" href="register.php">Create Account</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-outline-light px-3" href="login.php">Login</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Info -->
<div class="container mt-4">
  <div class="alert alert-info text-center">
    👋 Selamat datang di <b>MedicShop</b>!  
    Anda dapat melihat produk, tetapi untuk berbelanja silakan
    <a href="register.php" class="fw-bold text-decoration-none">Create Account</a> atau
    <a href="login.php" class="fw-bold text-decoration-none">Login</a> terlebih dahulu.
  </div>

  <h2 class="mb-3 text-center">🛍️ Daftar Produk</h2>

  <div class="row">
    <?php
    $q = mysqli_query($koneksi,"SELECT * FROM products");
    if(mysqli_num_rows($q) > 0){
      while($row = mysqli_fetch_assoc($q)){ ?>
        <div class="col-md-3 mb-4">
          <div class="card h-100 shadow-sm">
            <?php if(!empty($row['gambar'])){ ?>
              <img src="images/<?= $row['gambar'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } else { ?>
              <img src="images/no-image.png" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['nama_produk']) ?></h5>
              <p class="card-text text-muted">Kategori: <?= htmlspecialchars($row['kategori']) ?></p>
              <p class="card-text fw-bold">Rp <?= number_format($row['harga'],0,',','.') ?></p>
              <p class="card-text">Stok: <?= $row['stok'] ?></p>
            </div>
          </div>
        </div>
      <?php }
    } else {
      echo "<p class='text-danger text-center'>Tidak ada produk tersedia.</p>";
    }
    ?>
  </div>
</div>

<!-- Footer -->
<footer class="bg-primary text-white mt-5">
  <div class="container text-center py-3">
    <p class="mb-1">&copy; <?= date("Y") ?> MedicShop. All rights reserved.</p>
    <small>Developed by ❤️ Shella Christanti</small>
    <hr class="border-light my-3">
    <p class="mb-1">📞 0812-3495-2364 | ✉️ admin@medicshop.com</p>
    <p class="mb-0">🏬 Jl. Katamso No. 10, Sidoarjo</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
