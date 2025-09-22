<?php
session_start();
include "config/koneksi.php";

// cek login
if(!isset($_SESSION['user'])){
    die("❌ Harap login terlebih dahulu.");
}

$user_id = intval($_SESSION['user']['id']);

// ambil semua pesanan milik user beserta rating (LEFT JOIN)
$q = mysqli_query($koneksi,"
    SELECT o.id, o.tanggal, o.total, o.status, r.rating 
    FROM orders o
    LEFT JOIN ratings r ON o.id = r.order_id AND r.user_id='$user_id'
    WHERE o.user_id='$user_id'
    ORDER BY o.tanggal DESC
");

// helper badge
function badgeStatusSmall($status){
    $s = trim($status);
    if(strcasecmp($s, "Diproses") === 0) return '<span class="badge bg-warning text-dark">Diproses</span>';
    if(strcasecmp($s, "Dikirim") === 0)  return '<span class="badge bg-info text-dark">Dikirim</span>';
    if(strcasecmp($s, "Selesai") === 0)  return '<span class="badge bg-success">Selesai</span>';
    if(strcasecmp($s, "Dibatalkan") === 0) return '<span class="badge bg-danger">Dibatalkan</span>';
    return '<span class="badge bg-danger">Dibatalkan</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesanan Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .star-rating { display: inline-flex; flex-direction: row; }
    .star-rating input { display: none; }
    .star-rating label { font-size: 1.4rem; color: #ddd; cursor: pointer; transition: color 0.2s; }
    .star-rating .filled { color: #ffc107; }
    .star-rating.readonly label { cursor: default; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-warning" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">📦 Pesanan Saya</h2>
    <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-bordered table-striped mb-0">
      <thead class="table-dark">
        <tr>
          <th>ID Pesanan</th>
          <th>Tanggal</th>
          <th>Total</th>
          <th>Status</th>
          <th>Rating</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if(mysqli_num_rows($q) > 0){ ?>
          <?php while($order = mysqli_fetch_assoc($q)){ ?>
            <tr>
              <td>#<?= intval($order['id']); ?></td>
              <td><?= date('d M Y', strtotime($order['tanggal'])); ?></td>
              <td>Rp <?= number_format($order['total'],0,',','.'); ?></td>
              <td><?= badgeStatusSmall($order['status']); ?></td>
              <td>
                <?php if(strcasecmp(trim($order['status']), 'Selesai') === 0){ ?>
                  <?php if(!empty($order['rating']) && intval($order['rating']) > 0){ ?>
                    <div class="star-rating readonly">
                      <?php for($i=1;$i<=5;$i++){ ?>
                        <label class="<?= ($i <= intval($order['rating'])) ? 'filled' : '' ?>">★</label>
                      <?php } ?>
                    </div>
                  <?php } else { ?>
                    <form method="post" action="submit_rating.php" class="d-flex align-items-center mb-0">
                      <div class="star-rating">
                        <?php for($i=1;$i<=5;$i++){ ?>
                          <input type="radio" id="star<?= $i ?>_<?= $order['id']; ?>" name="rating" value="<?= $i; ?>">
                          <label for="star<?= $i ?>_<?= $order['id']; ?>">★</label>
                        <?php } ?>
                      </div>
                      <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                      <button class="btn btn-sm btn-primary ms-2" type="submit">Kirim</button>
                    </form>
                  <?php } ?>
                <?php } else { ?>
                  -
                <?php } ?>
              </td>
              <td>
                <a href="order_detail_user.php?id=<?= intval($order['id']); ?>" class="btn btn-primary btn-sm">Detail</a>
              </td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="6" class="text-center text-muted">Belum ada pesanan</td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- JS highlight rating -->
<script>
document.querySelectorAll('.star-rating').forEach(group => {
  const inputs = group.querySelectorAll('input');
  inputs.forEach(input => {
    input.addEventListener('change', () => {
      const val = parseInt(input.value);
      group.querySelectorAll('label').forEach((lbl, idx) => {
        lbl.style.color = (idx < val) ? '#ffc107' : '#ddd';
      });
    });
  });
});
</script>

</body>
</html>
