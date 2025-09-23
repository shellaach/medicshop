<?php
include "config/koneksi.php";
session_start();

// ✅ Cek login
if (!isset($_SESSION['user'])) {
  echo "<p>Anda belum login. <a href='login.php'>Login disini</a></p>";
  exit;
}

$user_id = (int)$_SESSION['user']['id'];

// ✅ Ambil data vendor
$qVendor = mysqli_query($koneksi, "SELECT * FROM vendors WHERE user_id='$user_id' LIMIT 1");
$vendor = mysqli_fetch_assoc($qVendor);

// ✅ Cek status vendor
if (!$vendor || $vendor['status'] !== 'approved') {
  echo "<p>Akses ditolak. Anda bukan vendor yang sudah disetujui.</p>";
  exit;
}

$username    = htmlspecialchars($_SESSION['user']['username']);
$vendor_name = htmlspecialchars($vendor['nama_toko']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Vendor Dashboard - <?= $vendor_name ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main {
      flex: 1;
    }
  </style>
</head>
<body class="bg-light">

  <!-- ✅ Navbar dengan Hamburger -->
  <nav class="navbar navbar-dark bg-success">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#vendorMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <span class="navbar-brand ms-2">🛍️ Vendor Panel</span>
      <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-light btn-sm me-2">Kembali ke Halaman Utama</a>
        <span class="text-white">👤 <?= $username ?></span>
      </div>
    </div>
  </nav>

  <!-- ✅ Offcanvas Menu -->
  <div class="offcanvas offcanvas-start bg-success text-white" tabindex="-1" id="vendorMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">🏬 <?= $vendor_name ?></h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a href="vendor_dashboard.php" class="nav-link text-white">🏠 Dashboard</a></li>
        <li class="nav-item mb-2"><a href="vendor_manage_products.php" class="nav-link text-white">📦 Kelola Produk</a></li>
        <li class="nav-item mb-2"><a href="vendor_manage_orders.php" class="nav-link text-white">📑 Kelola Pesanan</a></li>
        <li class="nav-item mb-2"><a href="vendor_report.php" class="nav-link text-white">📊 Cetak Laporan</a></li>
        <hr class="border-light">
        <li class="nav-item mb-2"><a href="user_detail.php" class="nav-link text-white">👤 Profil</a></li>
        <!-- ✅ Tambahan tombol kembali ke index -->
        <li class="nav-item mb-2"><a href="index.php" class="nav-link text-info">⬅️ Kembali ke Halaman Utama</a></li>
        <li class="nav-item"><a href="logout.php" class="nav-link text-warning">🚪 Logout</a></li>
      </ul>
    </div>
  </div>

  <!-- ✅ Konten Halaman -->
  <main class="container mt-4">
    <div class="alert alert-success">
      👋 Selamat datang di Dashboard Vendor <b><?= $vendor_name ?></b>!
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">📦 Produk</h5>
            <p class="card-text">Kelola semua produk toko Anda.</p>
            <a href="vendor_manage_products.php" class="btn btn-success w-100">Kelola Produk</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">📑 Pesanan</h5>
            <p class="card-text">Pantau & proses pesanan masuk.</p>
            <a href="vendor_manage_orders.php" class="btn btn-success w-100">Kelola Pesanan</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">📊 Laporan</h5>
            <p class="card-text">Lihat laporan transaksi toko Anda.</p>
            <a href="vendor_report.php" class="btn btn-success w-100">Cetak Laporan</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- ✅ Footer selalu di bawah -->
  <footer class="bg-success text-white text-center py-3">
    <p class="mb-1">&copy; <?= date("Y") ?> MedicShop Vendor Panel.</p>
    <small>Developed by ❤️ Shella Christanti</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
