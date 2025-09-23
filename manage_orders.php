<?php
session_start();
include "config/koneksi.php";

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa melihat pesanan.");
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Pesanan - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
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
    <h2>📑 Daftar Pesanan (Produk Admin)</h2>
    <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <!-- Search -->
  <div class="d-flex justify-content-end mb-3">
    <form method="GET" class="d-flex">
      <input type="text" name="search" class="form-control form-control-sm me-2" 
             style="max-width: 220px;" 
             placeholder="Cari pesanan..." 
             value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-sm btn-primary" type="submit">Cari</button>
      <?php if($search != ""){ ?>
        <a href="manage_orders.php" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
      <?php } ?>
    </form>
  </div>

  <?php
  // 🔹 Ambil hanya pesanan yang ada produk admin (vendor_id IS NULL)
  $sql = "SELECT DISTINCT o.id, u.username, o.total, o.metode_bayar, o.tanggal, o.status
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN order_items oi ON oi.order_id = o.id
          JOIN products p ON oi.product_id = p.id
          WHERE p.vendor_id IS NULL";

  if($search != ""){
      $sql .= " AND (
                  o.id LIKE '%$search%' 
                  OR u.username LIKE '%$search%'
                  OR o.metode_bayar LIKE '%$search%'
                  OR o.status LIKE '%$search%'
                )";
  }

  $sql .= " ORDER BY o.tanggal DESC";
  $q = mysqli_query($koneksi, $sql);

  if(mysqli_num_rows($q) > 0){ ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead class="table-primary">
            <tr>
              <th>ID Pesanan</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Metode Bayar</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = mysqli_fetch_assoc($q)){ ?>
            <tr>
              <td>#<?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
              <td><?= htmlspecialchars($row['metode_bayar']) ?></td>
              <td><?= date("d M Y", strtotime($row['tanggal'])) ?></td>
              <td>
                <?php 
                  switch($row['status']){
                    case "Diproses":
                      echo '<span class="badge bg-warning text-dark">Diproses</span>'; break;
                    case "Dikirim":
                      echo '<span class="badge bg-info text-dark">Dikirim</span>'; break;
                    case "Selesai":
                      echo '<span class="badge bg-success">Selesai</span>'; break;
                    case "Dibatalkan":
                      echo '<span class="badge bg-danger">Dibatalkan</span>'; break;
                    default:
                      echo '<span class="badge bg-secondary">Tidak Diketahui</span>';
                  }
                ?>
              </td>
              <td>
                <a href="order_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php } else {
    echo "<div class='alert alert-warning'>⚠️ Tidak ada hasil untuk <b>".htmlspecialchars($search)."</b>.</div>";
  } ?>
</div>

</body>
</html>
