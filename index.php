<?php
include "config/koneksi.php";
session_start();

// === CEK LOGIN ===
if(!isset($_SESSION['user'])){
  echo "<p>Anda belum login. <a href='login.php'>Login disini</a></p>";
  exit;
}

// Ambil data user
$username = htmlspecialchars($_SESSION['user']['username']);
$role     = $_SESSION['user']['role'];
$user_id  = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;

// Cek apakah user sudah mengajukan / memiliki vendor
$vendor = null;
$vendor_status = null;
$vendor_name = null;
if ($user_id) {
    $vQ = mysqli_query($koneksi, "SELECT * FROM vendors WHERE user_id='$user_id' LIMIT 1");
    if ($vQ && mysqli_num_rows($vQ) > 0) {
        $vendor = mysqli_fetch_assoc($vQ);
        $vendor_status = $vendor['status'];
        $vendor_name = $vendor['nama_toko'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>MedicShop - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if($role == 'admin'){ ?>
          <!-- Menu khusus admin -->
          <li class="nav-item"><a class="nav-link" href="manage_products.php">Kelola Produk</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_orders.php">Kelola Pesanan</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php" target="_blank">Cetak Laporan</a></li>
          <li class="nav-item"><a class="nav-link" href="guest_book.php">Guest Book</a></li>
          <!-- ✅ Tambahan menu Approval Vendor -->
          <li class="nav-item">
            <a class="nav-link" href="admin_vendor_request.php">
              <span class="badge bg-warning text-dark">Approval Vendor</span>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="user_detail.php?id=<?= $_SESSION['user']['id'] ?>">👤 Profil Saya</a></li>
        <?php } else { ?>
          <!-- Menu untuk customer/vendor -->
          <li class="nav-item"><a class="nav-link" href="cart.php">Keranjang Belanja</a></li>
          <li class="nav-item"><a class="nav-link" href="my_orders.php">Lihat Status Pesanan</a></li>

          <!-- ✅ Vendor / Ajukan Toko -->
          <?php if(!$vendor): ?>
            <li class="nav-item"><a class="nav-link" href="vendor_request.php">Ajukan Toko</a></li>
          <?php else: ?>
            <?php if($vendor_status === 'pending'): ?>
              <li class="nav-item">
                <a class="nav-link" href="vendor_request.php">
                  <span class="badge bg-warning text-dark">Pengajuan: Pending</span>
                </a>
              </li>
            <?php elseif($vendor_status === 'approved'): ?>
              <li class="nav-item"><a class="nav-link" href="vendor_dashboard.php">Kelola Toko</a></li>
            <?php elseif($vendor_status === 'rejected'): ?>
              <li class="nav-item"><a class="nav-link" href="vendor_request.php">Ajukan Ulang</a></li>
            <?php endif; ?>
          <?php endif; ?>

          <li class="nav-item"><a class="nav-link" href="user_detail.php">👤 Profil Saya</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Welcome -->
<div class="container mt-4">
  <div class="alert alert-success">
    👋 Selamat datang, <b><?= $username ?></b>!
    <?php if($role != 'admin' && $vendor_name && $vendor_status): ?>
      <br><small>Toko: <strong><?= htmlspecialchars($vendor_name) ?></strong> — 
      Status: <span class="badge <?= $vendor_status == 'approved' ? 'bg-success' : ($vendor_status=='pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
      <?= htmlspecialchars(ucfirst($vendor_status)) ?></span></small>
    <?php endif; ?>
  </div>

  <!-- Judul + Search sejajar -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🛍️ Daftar Produk</h2>
    <form method="get" class="d-flex" style="width: 300px;">
      <input type="text" name="search" class="form-control me-2"
             placeholder="Cari produk atau kategori..."
             value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button class="btn btn-light border" type="submit">Cari</button>
    </form>
  </div>

  <div class="row">
    <?php
    // Pencarian produk
    $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
    $searchCondition = $search ? " AND (p.nama_produk LIKE '%$search%' OR p.kategori LIKE '%$search%')" : "";

    // Filter hanya vendor approved (jika ada kolom vendor_id)
    $query1 = "SELECT p.*, v.nama_toko 
               FROM products p 
               LEFT JOIN vendors v ON p.vendor_id = v.vendor_id
               WHERE (p.vendor_id IS NULL OR v.status='approved') $searchCondition
               ORDER BY p.id DESC";
    $q = @mysqli_query($koneksi, $query1);

    if (!$q) {
        // fallback jika tabel lama
        $query2 = "SELECT * FROM products";
        if ($search) {
            $query2 .= " WHERE nama_produk LIKE '%$search%' OR kategori LIKE '%$search%'";
        }
        $query2 .= " ORDER BY id DESC";
        $q = mysqli_query($koneksi, $query2);
    }

    if($q && mysqli_num_rows($q) > 0){
      while($row = mysqli_fetch_assoc($q)){
        $shopName = isset($row['nama_toko']) ? $row['nama_toko'] : null;
    ?>
        <div class="col-md-3 mb-4">
          <div class="card h-100 shadow-sm">
            <?php if(!empty($row['gambar'])){ ?>
              <img src="images/<?= htmlspecialchars($row['gambar']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } else { ?>
              <img src="images/no-image.png" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php } ?>

            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['nama_produk']) ?></h5>
              <p class="card-text text-muted">Kategori: <?= htmlspecialchars($row['kategori']) ?></p>
              <?php if($shopName){ ?>
                <p class="card-text"><small class="text-muted">Penjual: <?= htmlspecialchars($shopName) ?></small></p>
              <?php } ?>
              <p class="card-text fw-bold">Rp <?= number_format($row['harga'],0,',','.') ?></p>
              <p class="card-text">Stok: <?= htmlspecialchars($row['stok']) ?></p>
            </div>

            <?php if($role == 'customer'){ ?>
              <div class="card-footer text-center">
                <?php if($row['stok'] > 0){ ?>
                  <form method="post" action="cart.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
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

<!-- ✅ Footer -->
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
