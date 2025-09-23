<?php
session_start();
include "config/koneksi.php";

// ================= CEK LOGIN & VENDOR =================
if (!isset($_SESSION['user'])) {
    die("<p>❌ Akses ditolak! <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user']['id'];

// cek vendor terdaftar & approved
$qVendor = mysqli_query($koneksi, "
    SELECT * FROM vendors 
    WHERE user_id='$user_id' AND status='approved' 
    LIMIT 1
");
if (!$qVendor || mysqli_num_rows($qVendor) == 0) {
    die("<p>❌ Anda belum terdaftar / belum disetujui sebagai vendor. 
         <a href='index.php'>Kembali</a></p>");
}
$vendor = mysqli_fetch_assoc($qVendor);
$vendor_id = $vendor['vendor_id'];

// ================= PENCARIAN =================
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : "";

// ================= QUERY PESANAN UNTUK VENDOR =================
$sql = "SELECT DISTINCT o.id, u.username, o.tanggal, o.metode_bayar, 
               SUM(oi.qty * oi.harga) as total, 
               MIN(oi.status) as status
        FROM orders o
        JOIN users u ON o.user_id=u.id
        JOIN order_items oi ON oi.order_id=o.id
        JOIN products p ON oi.product_id=p.id
        WHERE p.vendor_id='$vendor_id'";

if ($search != "") {
    $sql .= " AND (o.id LIKE '%$search%' 
                OR u.username LIKE '%$search%'
                OR o.metode_bayar LIKE '%$search%'
                OR oi.status LIKE '%$search%')";
}

$sql .= " GROUP BY o.id, u.username, o.tanggal, o.metode_bayar 
          ORDER BY o.tanggal DESC";

$q = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Pesanan Vendor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="vendor_dashboard.php">MedicShop Vendor</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-warning" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2>📑 Daftar Pesanan (<?= htmlspecialchars($vendor['nama_toko']); ?>)</h2>
    <a href="vendor_dashboard.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <!-- Search -->
  <div class="d-flex justify-content-end mb-3">
    <form method="GET" class="d-flex">
      <input type="text" name="search" class="form-control form-control-sm me-2" 
             style="max-width: 220px;" 
             placeholder="Cari pesanan..." 
             value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-sm btn-success" type="submit">Cari</button>
      <?php if ($search != "") { ?>
        <a href="vendor_manage_orders.php" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
      <?php } ?>
    </form>
  </div>

  <?php if ($q && mysqli_num_rows($q) > 0) { ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead class="table-success">
            <tr>
              <th>ID Pesanan</th>
              <th>Customer</th>
              <th>Total (Produk Anda)</th>
              <th>Metode Bayar</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q)) { ?>
            <tr>
              <td>#<?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
              <td><?= htmlspecialchars($row['metode_bayar']) ?></td>
              <td><?= date("d M Y", strtotime($row['tanggal'])) ?></td>
              <td>
                <?php 
                  switch($row['status']){
                    case "diproses":  echo '<span class="badge bg-warning text-dark">Diproses</span>'; break;
                    case "dikirim":   echo '<span class="badge bg-info text-dark">Dikirim</span>'; break;
                    case "selesai":   echo '<span class="badge bg-success">Selesai</span>'; break;
                    case "dibatalkan":echo '<span class="badge bg-danger">Dibatalkan</span>'; break;
                    default:          echo '<span class="badge bg-secondary">-</span>';
                  }
                ?>
              </td>
              <td>
                <a href="vendor_order_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php } else {
    echo "<div class='alert alert-warning'>⚠️ Tidak ada pesanan untuk toko ini.</div>";
  } ?>
</div>
</body>
</html>
