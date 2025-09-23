<?php
session_start();
include "config/koneksi.php";

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin.");
}

if(!isset($_GET['id'])){
    die("ID Pesanan tidak ditemukan.");
}

$order_id = intval($_GET['id']);

// Ambil data order
$q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal, o.status
                            FROM orders o 
                            JOIN users u ON o.user_id=u.id
                            WHERE o.id='$order_id'");
$order = mysqli_fetch_assoc($q);

if(!$order){
    die("Pesanan tidak ditemukan.");
}

// Jika tombol update status ditekan
if(isset($_POST['update_status'])){
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    mysqli_query($koneksi,"UPDATE orders SET status='$status' WHERE id='$order_id'");
    header("Location: order_detail.php?id=".$order_id);
    exit;
}

// Jika cetak PDF
if(isset($_GET['pdf'])){
    require("fpdf/fpdf.php");

    $pdf = new FPDF();
    $pdf->AddPage();

    // Header toko
    $pdf->SetFont("Arial","B",16);
    $pdf->Cell(0,10,"MedicShop - Invoice",0,1,"C");

    $pdf->SetFont("Arial","",10);
    $pdf->Cell(0,6,"Jl. Contoh No.123, Jakarta",0,1,"C");
    $pdf->Cell(0,6,"Telp: 0812-3456-7890 | Email: support@medicshop.com",0,1,"C");
    $pdf->Ln(8);

    // Judul invoice
    $pdf->SetFont("Arial","B",14);
    $pdf->Cell(0,10,"Detail Pesanan #".$order['id'],0,1,"C");
    $pdf->Ln(5);

    // Info order
    $pdf->SetFont("Arial","",12);
    $pdf->Cell(50,8,"Customer",0,0);
    $pdf->Cell(0,8,$order['username'],0,1);

    $pdf->Cell(50,8,"Tanggal",0,0);
    $pdf->Cell(0,8,date("d M Y H:i", strtotime($order['tanggal'])),0,1);

    $pdf->Cell(50,8,"Metode Bayar",0,0);
    $pdf->Cell(0,8,$order['metode_bayar'],0,1);

    $pdf->Cell(50,8,"Status",0,0);
    $pdf->Cell(0,8,$order['status'],0,1);

    $pdf->Cell(50,8,"Total",0,0);
    $pdf->Cell(0,8,"Rp ".number_format($order['total'],0,',','.'),0,1);

    $pdf->Ln(5);
    $pdf->SetFont("Arial","B",12);
    $pdf->Cell(0,8,"Produk Pesanan:",0,1);

    // Produk pesanan
    $pdf->SetFont("Arial","",11);
    $q_items = mysqli_query($koneksi,"SELECT p.nama_produk, i.qty, i.harga 
                                      FROM order_items i 
                                      JOIN products p ON i.product_id=p.id
                                      WHERE i.order_id='$order_id'");
    while($item = mysqli_fetch_assoc($q_items)){
        $subtotal = $item['qty'] * $item['harga'];
        $pdf->Cell(0,7,"- ".$item['nama_produk']." (".$item['qty']." x Rp ".number_format($item['harga'],0,',','.').") = Rp ".number_format($subtotal,0,',','.'),0,1);
    }

    $pdf->Ln(8);
    $pdf->SetFont("Arial","I",10);
    $pdf->Cell(0,6,"Terima kasih telah berbelanja di MedicShop!",0,1,"C");

    $pdf->Output("I","pesanan_".$order['id'].".pdf");
    exit;
}

// fungsi badge warna status
function badgeStatus($status){
    switch($status){
        case "Diproses":   return "badge bg-warning text-dark";
        case "Dikirim":    return "badge bg-info text-dark";
        case "Selesai":    return "badge bg-success";
        case "Dibatalkan": return "badge bg-danger";
        default:           return "badge bg-warning text-dark";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pesanan Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar biru admin -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">MedicShop Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="manage_orders.php">Pesanan</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📋 Detail Pesanan #<?= $order['id'] ?></h2>
    <div>
      <a href="manage_orders.php" class="btn btn-secondary">⬅ Kembali</a>
      <a href="order_detail_pdf.php?id=<?= $order['id']; ?>" target="_blank" class="btn btn-primary">
        🖨 Cetak PDF
      </a>
    </div>
  </div>

  <!-- Info Pesanan -->
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
      <p><strong>Tanggal:</strong> <?= date("d M Y H:i", strtotime($order['tanggal'])) ?></p>
      <p><strong>Metode Bayar:</strong> <?= htmlspecialchars($order['metode_bayar']) ?></p>
      <p><strong>Total:</strong> Rp <?= number_format($order['total'],0,',','.') ?></p>
      <p><strong>Status Saat Ini:</strong> 
        <span class="<?= badgeStatus($order['status']) ?>">
          <?= htmlspecialchars($order['status']) ?>
        </span>
      </p>
    </div>
  </div>

  <!-- Kotak Update Status -->
  <div class="card mb-3 shadow-sm border-primary">
    <div class="card-body">
      <h5 class="fw-bold mb-3 text-primary">🔄 Ubah Status Pesanan</h5>
      <form method="POST" class="d-flex">
        <select name="status" class="form-select me-2" style="max-width:200px;">
          <option value="Diproses"   <?= ($order['status']=="Diproses"?"selected":"") ?>>Diproses</option>
          <option value="Dikirim"    <?= ($order['status']=="Dikirim"?"selected":"") ?>>Dikirim</option>
          <option value="Selesai"    <?= ($order['status']=="Selesai"?"selected":"") ?>>Selesai</option>
          <option value="Dibatalkan" <?= ($order['status']=="Dibatalkan"?"selected":"") ?>>Dibatalkan</option>
        </select>
        <button type="submit" name="update_status" class="btn btn-success">✔ Update</button>
      </form>
    </div>
  </div>

  <!-- Produk Pesanan -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="fw-bold mb-3">Produk Pesanan</h5>
      <table class="table table-bordered align-middle text-center">
        <thead class="table-primary">
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
        $hasItems = false;
        while($item = mysqli_fetch_assoc($q_items)){
            $hasItems = true;
            $subtotal = $item['qty'] * $item['harga'];
            echo "<tr>
                    <td>".htmlspecialchars($item['nama_produk'])."</td>
                    <td>".intval($item['qty'])."</td>
                    <td>Rp ".number_format($item['harga'],0,',','.')."</td>
                    <td>Rp ".number_format($subtotal,0,',','.')."</td>
                  </tr>";
        }
        if(!$hasItems){
            echo "<tr><td colspan='4' class='text-muted'>Tidak ada produk</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
