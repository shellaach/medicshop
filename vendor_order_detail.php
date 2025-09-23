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

// ================= CEK ORDER =================
if (!isset($_GET['id'])) {
    die("<p>❌ ID pesanan tidak valid. <a href='vendor_manage_orders.php'>Kembali</a></p>");
}
$order_id = (int) $_GET['id'];

// cek apakah order memang ada produk vendor ini
$qOrder = mysqli_query($koneksi, "
    SELECT o.*, u.username 
    FROM orders o
    JOIN users u ON o.user_id=u.id
    JOIN order_items oi ON oi.order_id=o.id
    JOIN products p ON oi.product_id=p.id
    WHERE o.id='$order_id' AND p.vendor_id='$vendor_id'
    LIMIT 1
");
if (!$qOrder || mysqli_num_rows($qOrder) == 0) {
    die("<p>❌ Pesanan tidak ditemukan atau bukan milik Anda. 
         <a href='vendor_manage_orders.php'>Kembali</a></p>");
}
$order = mysqli_fetch_assoc($qOrder);

// ================= UPDATE STATUS PRODUK =================
if (isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // update semua produk vendor ini di order tsb
    mysqli_query($koneksi, "
        UPDATE order_items oi
        JOIN products p ON oi.product_id=p.id
        SET oi.status='$status'
        WHERE oi.order_id='$order_id' AND p.vendor_id='$vendor_id'
    ");

    // sinkronisasi status ke tabel orders
    $qCheck = mysqli_query($koneksi, "SELECT status FROM order_items WHERE order_id='$order_id'");
    $statuses = [];
    while($r = mysqli_fetch_assoc($qCheck)){
        $statuses[] = strtolower($r['status']);
    }

    $finalStatus = "Diproses"; // default
    if(count($statuses) > 0){
        if(count(array_unique($statuses)) === 1){
            switch($statuses[0]){
                case "selesai": $finalStatus = "Selesai"; break;
                case "dibatalkan": $finalStatus = "Dibatalkan"; break;
                case "dikirim": $finalStatus = "Dikirim"; break;
                default: $finalStatus = "Diproses";
            }
        } else {
            if(in_array("dikirim",$statuses)) $finalStatus="Dikirim";
            elseif(in_array("diproses",$statuses)) $finalStatus="Diproses";
        }
    }

    mysqli_query($koneksi, "UPDATE orders SET status='$finalStatus' WHERE id='$order_id'");

    header("Location: vendor_order_detail.php?id=$order_id");
    exit;
}

// ================= QUERY PRODUK PESANAN =================
$qItems = mysqli_query($koneksi, "
    SELECT oi.id AS order_item_id, oi.qty, oi.harga, oi.status, 
           p.nama_produk 
    FROM order_items oi
    JOIN products p ON oi.product_id=p.id
    WHERE oi.order_id='$order_id' AND p.vendor_id='$vendor_id'
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pesanan Vendor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="vendor_dashboard.php">MedicShop Vendor</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>📦 Detail Pesanan #<?= $order['id'] ?> (<?= htmlspecialchars($vendor['nama_toko']) ?>)</h2>
  <div class="d-flex gap-2">
    <a href="vendor_manage_orders.php" class="btn btn-secondary">⬅ Kembali</a>
    <a href="order_detail_pdf.php?id=<?= $order['id']; ?>&vendor=<?= $vendor_id ?>" 
       target="_blank" class="btn btn-primary">
       🖨 Cetak PDF
    </a>
  </div>
</div>

  <!-- Info Order -->
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <p><strong>Pemesan:</strong> <?= htmlspecialchars($order['username']) ?></p>
      <p><strong>Tanggal:</strong> <?= date("d M Y H:i", strtotime($order['tanggal'])) ?></p>
      <p><strong>Metode Bayar:</strong> <?= htmlspecialchars($order['metode_bayar']) ?></p>
    </div>
  </div>

  <!-- Kotak Update Status -->
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5 class="fw-bold mb-3">Update Status Pesanan</h5>
      <form method="POST" class="d-flex gap-2">
        <select name="status" class="form-select w-auto">
          <option value="diproses">Diproses</option>
          <option value="dikirim">Dikirim</option>
          <option value="selesai">Selesai</option>
          <option value="dibatalkan">Dibatalkan</option>
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
        <thead class="table-success">
          <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($qItems && mysqli_num_rows($qItems) > 0) { 
          while ($item = mysqli_fetch_assoc($qItems)) {
            $badge = "secondary";
            if ($item['status']=="diproses") $badge="warning text-dark";
            elseif ($item['status']=="dikirim") $badge="info text-dark";
            elseif ($item['status']=="selesai") $badge="success";
            elseif ($item['status']=="dibatalkan") $badge="danger";
        ?>
          <tr>
            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
            <td><?= (int)$item['qty'] ?></td>
            <td>Rp <?= number_format($item['harga'],0,',','.') ?></td>
            <td>Rp <?= number_format($item['qty']*$item['harga'],0,',','.') ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($item['status']) ?></span></td>
          </tr>
        <?php } } else { ?>
          <tr><td colspan="5" class="text-muted text-center">Tidak ada produk di pesanan ini.</td></tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
