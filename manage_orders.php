<?php
session_start();
include "config/koneksi.php";

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa melihat pesanan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Pesanan - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📑 Daftar Pesanan</h2>
    <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <?php
  $q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal 
                              FROM orders o 
                              JOIN users u ON o.user_id=u.id
                              ORDER BY o.tanggal DESC");

  if(mysqli_num_rows($q) > 0){ ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead class="table-primary">
            <tr>
              <th>ID Pesanan</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Metode Bayar</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = mysqli_fetch_assoc($q)){ ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['username'] ?></td>
              <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
              <td><?= $row['metode_bayar'] ?></td>
              <td><?= $row['tanggal'] ?></td>
              <td>
                <a href="order_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">🔍 Lihat</a>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php } else {
    echo "<div class='alert alert-warning'>⚠️ Belum ada pesanan.</div>";
  } ?>
</div>

</body>
</html>
