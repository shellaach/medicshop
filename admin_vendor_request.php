<?php
session_start();
include "config/koneksi.php";

// ==== CEK ROLE ADMIN ====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("<div class='alert alert-danger text-center mt-5'>
         ⚠️ Akses ditolak. Hanya admin yang bisa membuka halaman ini.
         </div>");
}

// ==== PROSES UBAH STATUS / DELETE ====
if (isset($_GET['action'], $_GET['vendor_id'])) {
    $vendor_id = intval($_GET['vendor_id']);
    $map = ['approve'=>'approved','reject'=>'rejected','pending'=>'pending'];
    $action = $_GET['action'];

    if (isset($map[$action])) {
        $newStatus = $map[$action];
        mysqli_query(
            $koneksi,
            "UPDATE vendors 
             SET status='".mysqli_real_escape_string($koneksi,$newStatus)."' 
             WHERE vendor_id=$vendor_id"
        ) or die("Update gagal: ".mysqli_error($koneksi));
        $_SESSION['flash'] = "Status vendor #$vendor_id → <b>$newStatus</b>";
    } elseif ($action === 'delete') {
        mysqli_query(
            $koneksi,
            "DELETE FROM vendors WHERE vendor_id=$vendor_id"
        ) or die("Delete gagal: ".mysqli_error($koneksi));
        $_SESSION['flash'] = "Vendor #$vendor_id telah dihapus.";
    }
    header("Location: admin_vendor_request.php");
    exit;
}

// ==== FILTER STATUS ====
$allowed = ['all','pending','approved','rejected'];
$status  = (isset($_GET['status']) && in_array($_GET['status'],$allowed))
            ? $_GET['status'] : 'all';
$where   = $status!=='all'
            ? "WHERE v.status='".mysqli_real_escape_string($koneksi,$status)."'"
            : "";

$sql = "SELECT v.*, u.username, u.email 
        FROM vendors v
        JOIN users u ON v.user_id = u.id
        $where
        ORDER BY v.created_at DESC";
$res = mysqli_query($koneksi,$sql) or die("Query gagal: ".mysqli_error($koneksi));

$vendors = [];
while($row = mysqli_fetch_assoc($res)){ $vendors[] = $row; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin – Pengajuan Vendor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">🏠 Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">📜 Pengajuan Vendor</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Daftar Pengajuan Toko</h4>
      <div class="btn-group btn-group-sm">
        <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v): ?>
          <a href="?status=<?= $k ?>" class="btn btn-light <?=($status==$k?'active':'')?>"><?= $v ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card-body">
      <?php if(!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
          <thead class="table-dark text-center">
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Nama Toko</th>
              <th>Status</th>
              <th>Tanggal Pengajuan</th>
              <th>Detail</th>
              <th>Ubah Status</th>
              <th>Hapus</th>
            </tr>
          </thead>
          <tbody class="text-center">
          <?php if($vendors): foreach($vendors as $v): ?>
            <tr>
              <td><?= $v['vendor_id'] ?></td>
              <td class="text-start"><?= htmlspecialchars($v['username']) ?></td>
              <td class="text-start"><?= htmlspecialchars($v['nama_toko']) ?></td>
              <td>
                <?php
                  $badge = ['pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger'];
                  $cls   = $badge[$v['status']] ?? 'secondary';
                  echo "<span class='badge bg-$cls'>".ucfirst($v['status'] ?: 'Unknown')."</span>";
                ?>
              </td>
              <td><?= htmlspecialchars($v['created_at']) ?></td>
              <td>
                <button class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#detail<?= $v['vendor_id'] ?>">Detail</button>
              </td>
              <td>
                <form method="get" class="d-flex justify-content-center">
                  <input type="hidden" name="vendor_id" value="<?= $v['vendor_id'] ?>">
                  <select name="action" class="form-select form-select-sm w-auto me-2">
                    <option value="">-- Pilih --</option>
                    <option value="approve" <?= $v['status']=='approved'?'selected':'' ?>>Approved</option>
                    <option value="reject" <?= $v['status']=='rejected'?'selected':'' ?>>Rejected</option>
                    <option value="pending" <?= $v['status']=='pending'?'selected':'' ?>>Pending</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary"
                          onclick="return confirm('Ubah status vendor ini?')">Simpan</button>
                </form>
              </td>
              <td>
                <a href="?action=delete&vendor_id=<?= $v['vendor_id'] ?>"
                   class="btn btn-outline-danger btn-sm"
                   onclick="return confirm('Hapus data vendor ini?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="8" class="text-muted">Tidak ada data</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php foreach($vendors as $v): ?>
<!-- Modal Detail -->
<div class="modal fade" id="detail<?= $v['vendor_id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Detail Pengajuan #<?= $v['vendor_id'] ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-borderless mb-0">
          <tr><th style="width:30%">Username</th><td><?= htmlspecialchars($v['username']) ?></td></tr>
          <tr><th>Email</th><td><?= htmlspecialchars($v['email']) ?></td></tr>
          <tr><th>Nama Toko</th><td><?= htmlspecialchars($v['nama_toko']) ?></td></tr>
          <tr><th>Deskripsi</th><td><?= nl2br(htmlspecialchars($v['deskripsi'] ?? '-')) ?></td></tr>
          <tr><th>Alamat</th><td><?= nl2br(htmlspecialchars($v['alamat'] ?? '-')) ?></td></tr>
          <tr><th>No. Telepon</th><td><?= htmlspecialchars($v['telepon'] ?? '-') ?></td></tr>
          <tr><th>Status</th><td><?= htmlspecialchars($v['status']) ?></td></tr>
          <tr><th>Tanggal Pengajuan</th><td><?= htmlspecialchars($v['created_at']) ?></td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
