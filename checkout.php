<?php
session_start();
include "config/koneksi.php";
require('fpdf/fpdf.php');

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

    // ==== Generate PDF langsung setelah checkout ====
    $o = mysqli_query($koneksi,"SELECT * FROM orders WHERE id='$order_id' AND user_id='$user_id'");
    $order = mysqli_fetch_assoc($o);

    $oi = mysqli_query($koneksi,"SELECT oi.*, p.nama_produk 
                                 FROM order_items oi 
                                 JOIN products p ON oi.product_id=p.id 
                                 WHERE order_id='$order_id'");

    class PDF extends FPDF {
        function Header(){
            $this->SetFont('Arial','B',14);
            $this->Cell(0,10,'Nota Pembelian - MedicShop',0,1,'C');
            $this->Ln(5);
        }
        function Footer(){
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Halaman '.$this->PageNo(),0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);

    $pdf->Cell(0,10,'ID Order : '.$order['id'],0,1);
    $pdf->Cell(0,10,'User ID  : '.$order['user_id'],0,1);
    $pdf->Cell(0,10,'Tanggal  : '.$order['tanggal'],0,1);
    $pdf->Cell(0,10,'Metode   : '.$order['metode_bayar'],0,1);
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(70,10,'Produk',1);
    $pdf->Cell(30,10,'Qty',1);
    $pdf->Cell(40,10,'Harga',1);
    $pdf->Cell(40,10,'Subtotal',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',12);
    $totalNota = 0;
    while($r = mysqli_fetch_assoc($oi)){
        $sub = $r['qty'] * $r['harga'];
        $totalNota += $sub;
        $pdf->Cell(70,10,$r['nama_produk'],1);
        $pdf->Cell(30,10,$r['qty'],1);
        $pdf->Cell(40,10,'Rp '.number_format($r['harga'],0,',','.'),1);
        $pdf->Cell(40,10,'Rp '.number_format($sub,0,',','.'),1);
        $pdf->Ln();
    }

    $pdf->Cell(140,10,'Total',1);
    $pdf->Cell(40,10,'Rp '.number_format($totalNota,0,',','.'),1);

    // Bersihkan buffer agar file tidak corrupt
    ob_end_clean();

    // Download PDF
    $pdf->Output('D','nota-'.$order['id'].'.pdf');

    // Redirect ke halaman index setelah download
    header("Location: index.php");
    exit;
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

    </div>
  </div>
</div>

</body>
</html>
