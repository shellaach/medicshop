<?php
session_start();
include "config/koneksi.php";

// Cek login
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Tambah produk ke cart dari index
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $product_id = intval($_POST['id']);

    // cek apakah produk ada
    $cek_produk = mysqli_query($koneksi,"SELECT * FROM products WHERE id='$product_id'");
    if ($cek_produk && mysqli_num_rows($cek_produk) > 0) {
        $produk = mysqli_fetch_assoc($cek_produk);

        if ($produk['stok'] > 0) {
            // cek apakah produk sudah ada di cart
            $cek_cart = mysqli_query($koneksi,"SELECT * FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
            if (mysqli_num_rows($cek_cart) > 0) {
                // update qty +1
                mysqli_query($koneksi,"UPDATE cart SET qty = qty + 1 WHERE user_id='$user_id' AND product_id='$product_id'");
            } else {
                // insert baru
                mysqli_query($koneksi,"INSERT INTO cart(user_id, product_id, qty) VALUES('$user_id','$product_id',1)");
            }
        }
    }

    // redirect agar tidak resubmit
    header("Location: cart.php");
    exit;
}

// Update qty otomatis
if(isset($_POST['qty']) && isset($_POST['cart_id'])){
    $cart_id = $_POST['cart_id'];
    $qty     = (int) $_POST['qty'];

    // Ambil stok produk
    $cek = mysqli_query($koneksi,"SELECT p.stok 
                                  FROM cart c 
                                  JOIN products p ON c.product_id=p.id 
                                  WHERE c.id='$cart_id' AND c.user_id='$user_id'");
    $produk = mysqli_fetch_assoc($cek);

    if($produk){
        if($qty > 0 && $qty <= $produk['stok']){
            mysqli_query($koneksi,"UPDATE cart SET qty='$qty' WHERE id='$cart_id' AND user_id='$user_id'");
        } elseif($qty == 0){
            // Kalau qty = 0 → hapus
            mysqli_query($koneksi,"DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
        } else {
            echo "<script>alert('Jumlah tidak valid atau stok kurang!');</script>";
        }
    }
}


// Hapus item
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    mysqli_query($koneksi,"DELETE FROM cart WHERE id='$id' AND user_id='$user_id'");
}

// Ambil data keranjang
$q = mysqli_query($koneksi,"SELECT c.id, c.product_id, c.qty, p.nama_produk, p.harga, p.stok 
                            FROM cart c 
                            JOIN products p ON c.product_id=p.id 
                            WHERE c.user_id='$user_id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keranjang - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>🛒 Keranjang Belanja</h4>
    </div>
    <div class="card-body">
      <?php if(mysqli_num_rows($q) == 0){ ?>
        <div class="alert alert-warning">Keranjang kosong. <a href="index.php">Belanja dulu</a></div>
      <?php } else { ?>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Harga</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $total = 0;
            while($row = mysqli_fetch_assoc($q)){ 
              $subtotal = $row['harga'] * $row['qty'];
              $total += $subtotal;
            ?>
              <tr>
                <td><?= $row['nama_produk'] ?></td>
                <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="cart_id" value="<?= $row['id'] ?>">
                    <input type="number" name="qty" value="<?= $row['qty'] ?>" min="0" max="<?= $row['stok'] ?>" 
                           class="form-control" style="width:80px;" onchange="this.form.submit()">
                  </form>
                </td>
                <td>Rp <?= number_format($subtotal,0,',','.') ?></td>
                <td>
                  <a href="cart.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                </td>
              </tr>
            <?php } ?>
            <tr class="table-success">
              <th colspan="3" class="text-end">Total</th>
              <th colspan="2">Rp <?= number_format($total,0,',','.') ?></th>
            </tr>
          </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary">⬅ Lanjut Belanja</a>
        <a href="checkout.php" class="btn btn-success">✅ Checkout</a>
      <?php } ?>
    </div>
  </div>
</div>

</body>
</html>
