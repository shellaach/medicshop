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

$order_id = intval($_GET['id']);
$user_id  = intval($_SESSION['user']['id']); // supaya user hanya bisa lihat pesanan miliknya

// ===== Handle pembatalan (GET cancel) =====
if(isset($_GET['cancel'])){
    $cancel_id = intval($_GET['cancel']);
    if($cancel_id === $order_id){
        $stmt = mysqli_prepare($koneksi,
            "UPDATE orders 
             SET status='Dibatalkan' 
             WHERE id = ? 
               AND user_id = ? 
               AND LOWER(TRIM(status)) = 'diproses'"
        );
        mysqli_stmt_bind_param($stmt, "ii", $cancel_id, $user_id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if($affected > 0){
            echo "<script>alert('✅ Pesanan berhasil dibatalkan'); window.location='order_detail_user.php?id={$cancel_id}';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Gagal membatalkan pesanan. Pesanan mungkin sudah tidak berstatus Diproses.'); window.location='order_detail_user.php?id={$cancel_id}';</script>";
            exit;
        }
    }
}

// Ambil data order milik user
$stmt = mysqli_prepare($koneksi, "SELECT o.id, o.total, o.metode_bayar, o.tanggal, o.status FROM orders o WHERE o.id = ? AND o.user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if(!$order){
    die("❌ Pesanan tidak ditemukan atau bukan milik Anda.");
}

// helper badge
function badgeStatus($status){
    $s = trim($status);
    if(strcasecmp($s, "Diproses") === 0) return '<span class="badge bg-warning text-dark">Diproses</span>';
    if(strcasecmp($s, "Dikirim") === 0)  return '<span class="badge bg-info text-dark">Dikirim</span>';
    if(strcasecmp($s, "Selesai") === 0)  return '<span class="badge bg-success">Selesai</span>';
    if(strcasecmp($s, "Dibatalkan") === 0) return '<span class="badge bg-danger">Dibatalkan</span>';
    return '<span class="badge bg-secondary">Tidak diketahui</span>';
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

  <h2 class="mb-3">Detail Pesanan #<?= htmlspecialchars($order['id']); ?></h2>

  <div class="mb-3">
    <p><strong>Total:</strong> Rp <?= number_format($order['total'],0,',','.'); ?></p>
    <p><strong>Metode Bayar:</strong> <?= htmlspecialchars($order['metode_bayar']); ?></p>
    <p><strong>Tanggal:</strong> <?= htmlspecialchars($order['tanggal']); ?></p>
    <p><strong>Status:</strong> <?= badgeStatus($order['status']); ?></p>
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
      $stmt2 = mysqli_prepare($koneksi, "SELECT p.nama_produk, i.qty, i.harga FROM order_items i JOIN products p ON i.product_id=p.id WHERE i.order_id = ?");
      mysqli_stmt_bind_param($stmt2, "i", $order_id);
      mysqli_stmt_execute($stmt2);
      $res2 = mysqli_stmt_get_result($stmt2);
      while($item = mysqli_fetch_assoc($res2)){
          $subtotal = $item['qty'] * $item['harga'];
          echo "<tr>
                  <td>".htmlspecialchars($item['nama_produk'])."</td>
                  <td>".intval($item['qty'])."</td>
                  <td>Rp ".number_format($item['harga'],0,',','.')."</td>
                  <td>Rp ".number_format($subtotal,0,',','.')."</td>
                </tr>";
      }
      mysqli_stmt_close($stmt2);
      ?>
    </tbody>
  </table>

  <div class="mt-4 d-flex gap-2">
    <a href="my_orders.php" class="btn btn-secondary">⬅ Kembali ke Pesanan Saya</a>
    <!-- Download Nota sekarang kuning -->
    <a href="nota.php?id=<?= $order['id']; ?>" class="btn btn-warning" target="_blank">🖨  Download Nota</a>

    <?php if(strcasecmp(trim($order['status']), 'Diproses') === 0){ ?>
      <!-- Batalkan sekarang merah -->
      <a href="order_detail_user.php?id=<?= $order['id']; ?>&cancel=<?= $order['id']; ?>" 
         class="btn btn-danger"
         onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
         ❌ Batalkan Pesanan
      </a>
    <?php } ?>
  </div>

</body>
</html>
