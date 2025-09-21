<?php
session_start();
include "config/koneksi.php";

// cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa melihat detail pesanan.");
}

if(!isset($_GET['id'])){
    die("ID Pesanan tidak ditemukan.");
}

$order_id = $_GET['id'];

// jika admin update status
if(isset($_POST['update_status'])){
    $status_baru = $_POST['status'];
    mysqli_query($koneksi,"UPDATE orders SET status='$status_baru' WHERE id='$order_id'");
    echo "<div class='alert alert-success'>✅ Status pesanan berhasil diperbarui menjadi <b>$status_baru</b>.</div>";
}

// ambil data order
$q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal, o.status 
                            FROM orders o 
                            JOIN users u ON o.user_id=u.id
                            WHERE o.id='$order_id'");
$order = mysqli_fetch_assoc($q);

if(!$order){
    die("Pesanan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <!-- tombol kembali -->
  <div class="mb-3">
    <a href="manage_orders.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <h2 class="mb-3">Detail Pesanan #<?= $order['id']; ?></h2>

  <div class="mb-3">
    <p><strong>Customer:</strong> <?= $order['username']; ?></p>
    <p><strong>Total:</strong> Rp <?= number_format($order['total'],0,',','.'); ?></p>
    <p><strong>Metode Bayar:</strong> <?= $order['metode_bayar']; ?></p>
    <p><strong>Tanggal:</strong> <?= $order['tanggal']; ?></p>
    <p><strong>Status:</strong> <span class="badge bg-info"><?= $order['status']; ?></span></p>
  </div>

  <!-- form update status -->
  <form method="post" class="mb-4">
    <label for="status" class="form-label"><strong>Ubah Status Pesanan:</strong></label>
    <select name="status" id="status" class="form-select" required>
      <option value="Diproses" <?= ($order['status']=="Diproses"?"selected":""); ?>>Diproses</option>
      <option value="Dikirim" <?= ($order['status']=="Dikirim"?"selected":""); ?>>Dikirim</option>
      <option value="Selesai" <?= ($order['status']=="Selesai"?"selected":""); ?>>Selesai</option>
    </select>
    <button type="submit" name="update_status" class="btn btn-success mt-2">💾 Simpan</button>
  </form>

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

  <!-- tombol cetak PDF -->
  <div class="mt-4">
    <a href="order_detail_pdf.php?id=<?= $order['id']; ?>" target="_blank" class="btn btn-primary">📝 Cetak PDF</a>
  </div>

</body>
</html>
