<?php
session_start();
include "config/koneksi.php";

// cek login
if(!isset($_SESSION['user'])){
    die("❌ Harap login terlebih dahulu.");
}

$user_id = $_SESSION['user']['id'];

// ambil semua pesanan milik user
$q = mysqli_query($koneksi,"SELECT id, tanggal, total, status 
                            FROM orders 
                            WHERE user_id='$user_id' 
                            ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesanan Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <h2 class="mb-4">Pesanan Saya</h2>

  <!-- ✅ Tombol kembali dipindahkan ke atas tabel -->
  <div class="mb-3">
    <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID Pesanan</th>
        <th>Tanggal</th>
        <th>Total</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($order = mysqli_fetch_assoc($q)){ ?>
        <tr>
          <td>#<?= $order['id']; ?></td>
          <td><?= $order['tanggal']; ?></td>
          <td>Rp <?= number_format($order['total'],0,',','.'); ?></td>
          <td>
            <?php if($order['status']=="Diproses"){ ?>
              <span class="badge bg-warning text-dark"><?= $order['status']; ?></span>
            <?php } elseif($order['status']=="Dikirim"){ ?>
              <span class="badge bg-info text-dark"><?= $order['status']; ?></span>
            <?php } else { ?>
              <span class="badge bg-success"><?= $order['status']; ?></span>
            <?php } ?>
          </td>
          <td>
            <a href="order_detail_user.php?id=<?= $order['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

</body>
</html>
