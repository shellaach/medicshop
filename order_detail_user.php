<?php
session_start();
include "config/koneksi.php";

// Cek login user
if(!isset($_SESSION['user'])){
    die("❌ Akses ditolak! Silakan login terlebih dahulu.");
}

if(!isset($_GET['id'])){
    die("ID Pesanan tidak ditemukan.");
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user']['id']; // supaya user hanya bisa lihat pesanan miliknya

// Ambil data order milik user
$q = mysqli_query($koneksi,"SELECT o.id, o.total, o.metode_bayar, o.tanggal, o.status
                            FROM orders o 
                            WHERE o.id='$order_id' AND o.user_id='$user_id'");
$order = mysqli_fetch_assoc($q);

if(!$order){
    die("❌ Pesanan tidak ditemukan atau bukan milik Anda.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pesanan Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <h2 class="mb-3">Detail Pesanan #<?= $order['id']; ?></h2>

  <div class="mb-3">
    <p><strong>Total:</strong> Rp <?= number_format($order['total'],0,',','.'); ?></p>
    <p><strong>Metode Bayar:</strong> <?= $order['metode_bayar']; ?></p>
    <p><strong>Tanggal:</strong> <?= $order['tanggal']; ?></p>
    <p><strong>Status:</strong> 
      <span class="badge 
        <?php if($order['status']=='Diproses') echo 'bg-warning';
              elseif($order['status']=='Dikirim') echo 'bg-primary';
              elseif($order['status']=='Selesai') echo 'bg-success'; ?>">
        <?= $order['status']; ?>
      </span>
    </p>
  </div>

  <h3>Daftar Produk</h3>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Produk</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $q_items = mysqli_query($koneksi,"SELECT p.nama_produk, i.qty, i.harga 
                                        FROM order_items i 
                                        JOIN products p ON i.product_id=p.id
                                        WHERE i.order_id='$order_id'");
      while($item = mysqli_fetch_assoc($q_items)){
          $subtotal = $item['qty'] * $item['harga'];
          echo "<tr>
                  <td>{$item['nama_produk']}</td>
                  <td>{$item['qty']}</td>
                  <td>Rp ".number_format($item['harga'],0,',','.')."</td>
                  <td>Rp ".number_format($subtotal,0,',','.')."</td>
                </tr>";
      }
      ?>
    </tbody>
  </table>

  <div class="mt-4">
    <a href="my_orders.php" class="btn btn-secondary">⬅ Kembali ke Pesanan Saya</a>
  </div>

</body>
</html>
