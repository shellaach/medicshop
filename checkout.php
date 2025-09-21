<?php
session_start();
include "config/koneksi.php";

// Cek login
if(!isset($_SESSION['user'])){
    echo "<div class='alert alert-danger'>Anda harus login dulu. <a href='login.php'>Login</a></div>";
    exit;
}

$user_id = $_SESSION['user']['id'];

// Ambil data keranjang
$q = mysqli_query($koneksi,"SELECT c.id, c.product_id, c.qty, p.nama_produk, p.harga 
                            FROM cart c 
                            JOIN products p ON c.product_id=p.id 
                            WHERE c.user_id='$user_id'");

if(mysqli_num_rows($q) == 0){
    echo "<div class='alert alert-warning'>Keranjang kosong. <a href='index.php'>Belanja dulu</a></div>";
    exit;
}

$total = 0;
$items = [];
while($row = mysqli_fetch_assoc($q)){
    $subtotal = $row['qty'] * $row['harga'];
    $total += $subtotal;
    $items[] = $row;
}

$order_id = null;
$metode = null;

// Proses checkout
if(isset($_POST['checkout'])){
    $metode = $_POST['metode'];

    mysqli_query($koneksi, "INSERT INTO orders(user_id,total,metode_bayar,tanggal) 
                            VALUES('$user_id','$total','$metode',NOW())");
    $order_id = mysqli_insert_id($koneksi);

    foreach($items as $it){
        mysqli_query($koneksi, "INSERT INTO order_items(order_id,product_id,qty,harga)
                                VALUES('$order_id','".$it['product_id']."','".$it['qty']."','".$it['harga']."')");
        mysqli_query($koneksi, "UPDATE products 
                                SET stok = stok - ".$it['qty']." 
                                WHERE id = ".$it['product_id']);
    }

    mysqli_query($koneksi,"DELETE FROM cart WHERE user_id='$user_id'");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Checkout - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>🛒 Checkout</h4>
    </div>
    <div class="card-body">

      <?php if($order_id){ ?>
        <div class="alert alert-success">
          <h5>✅ Pesanan Berhasil!</h5>
          <p>Pesanan dengan ID <b>#<?= $order_id ?></b> berhasil dibuat menggunakan metode <b><?= $metode ?></b>.</p>
          <a href="nota.php?id=<?= $order_id ?>" class="btn btn-danger" target="_blank">📄 Download Nota (PDF)</a>
          <a href="index.php" class="btn btn-primary">⬅ Kembali ke Produk</a>
        </div>
      <?php } else { ?>

      <h5>Ringkasan Belanja</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $it){ ?>
            <tr>
              <td><?= $it['nama_produk'] ?></td>
              <td>Rp <?= number_format($it['harga'],0,',','.') ?></td>
              <td><?= $it['qty'] ?></td>
              <td>Rp <?= number_format($it['qty'] * $it['harga'],0,',','.') ?></td>
            </tr>
          <?php } ?>
          <tr class="table-success">
            <th colspan="3" class="text-end">Total</th>
            <th>Rp <?= number_format($total,0,',','.') ?></th>
          </tr>
        </tbody>
      </table>

      <form method="POST" class="mt-3">
        <label class="form-label">Pilih metode pembayaran:</label>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="metode" value="Transfer Bank" required>
          <label class="form-check-label">Transfer Bank</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="metode" value="Kartu Kredit">
          <label class="form-check-label">Kartu Kredit</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="metode" value="COD">
          <label class="form-check-label">Bayar di Tempat (COD)</label>
        </div>

        <button type="submit" name="checkout" class="btn btn-success mt-3">✅ Konfirmasi Checkout</button>
        <a href="cart.php" class="btn btn-secondary mt-3">⬅ Kembali ke Keranjang</a>
      </form>

      <?php } ?>

    </div>
  </div>
</div>

</body>
</html>
