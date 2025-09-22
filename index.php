<?php
include "config/koneksi.php";
session_start();

// Cek login
if(!isset($_SESSION['user'])){
  echo "<p>Anda belum login. <a href='login.php'>Login disini</a></p>";
  exit;
}

// Ambil data user
$username = $_SESSION['user']['username'];
$role     = $_SESSION['user']['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>MedicShop - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if($role == 'admin'){ ?>
          <li class="nav-item"><a class="nav-link" href="manage_products.php">Kelola Produk</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_orders.php">Kelola Pesanan</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php" target="_blank">Cetak Laporan</a></li>
          <li class="nav-item"><a class="nav-link" href="guest_book.php">Guest Book</a></li>
          <!-- Admin bisa klik user_detail sesuai ID login -->
          <li class="nav-item"><a class="nav-link" href="user_detail.php?id=<?= $_SESSION['user']['id'] ?>">👤 Profil Saya</a></li>
        <?php } else { ?>
          <li class="nav-item"><a class="nav-link" href="cart.php">Keranjang Belanja</a></li>
          <li class="nav-item"><a class="nav-link" href="my_orders.php">Lihat Status Pesanan</a></li>
          <!-- Customer langsung diarahkan ke profilnya -->
          <li class="nav-item"><a class="nav-link" href="user_detail.php">👤 Profil Saya</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>


<!-- Welcome -->
<div class="container mt-4">
  <div class="alert alert-success">
    👋 Selamat datang, <b><?= $username ?></b>!
  </div>

  <!-- Judul + Search sejajar -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🛍️ Daftar Produk</h2>
    <form method="get" class="d-flex" style="width: 300px;">
      <input type="text" name="search" class="form-control me-2" placeholder="Cari produk atau kategori..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button class="btn btn-primary" type="submit">Cari</button>
    </form>
  </div>

  <div class="row">
    <?php
    // Tangkap kata kunci pencarian
    $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
    $query = "SELECT * FROM products";
    if($search){
        $query .= " WHERE nama_produk LIKE '%$search%' OR kategori LIKE '%$search%'";
    }
    $q = mysqli_query($koneksi, $query);

    if(mysqli_num_rows($q) > 0){
      while($row = mysqli_fetch_assoc($q)){ ?>
        <div class="col-md-3 mb-4">
          <div class="card h-100 shadow-sm">
            <!-- Foto Produk -->
            <?php if(!empty($row['gambar'])){ ?>
              <img src="images/<?= $row['gambar'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } else { ?>
              <img src="images/no-image.png" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } ?>

            <div class="card-body">
              <h5 class="card-title"><?= $row['nama_produk'] ?></h5>
              <p class="card-text text-muted">Kategori: <?= $row['kategori'] ?></p>
              <p class="card-text fw-bold">Rp <?= number_format($row['harga'],0,',','.') ?></p>
              <p class="card-text">Stok: <?= $row['stok'] ?></p>
            </div>

            <?php if($role == 'customer'){ ?>
              <div class="card-footer text-center">
                <?php if($row['stok'] > 0){ ?>
                  <form method="post" action="cart.php">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-success w-100">🛒 Beli</button>
                  </form>
                <?php } else { ?>
                  <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                <?php } ?>
              </div>
            <?php } ?>
          </div>
        </div>
      <?php }
    } else {
      echo "<p class='text-danger'>Tidak ada produk tersedia.</p>";
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

</body>
</html>
