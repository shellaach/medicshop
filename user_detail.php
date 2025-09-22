<?php
session_start();
include "config/koneksi.php";

// ======== CEK LOGIN ========
if(!isset($_SESSION['user'])){
    die("<div class='alert alert-danger m-3'>
        ⚠️ Silakan login terlebih dahulu.
        <a href='login.php'>Login</a></div>");
}

// ======== TENTUKAN ID USER ========
$id = 0;
if($_SESSION['user']['role'] === 'admin'){
    // Admin bisa akses data user lain lewat GET id
    if(isset($_GET['id'])){
        $id = intval($_GET['id']);
    } else {
        die("<div class='alert alert-danger m-3'>ID user tidak ditemukan.</div>");
    }
} else {
    // Customer hanya bisa lihat data dirinya sendiri
    $id = intval($_SESSION['user']['id']);
}

// ======== AMBIL DATA USER ========
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if(!$user){
    die("<div class='alert alert-danger m-3'>User tidak ditemukan.</div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- ======== NAVBAR ======== -->
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

<div class="container mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>👤 Detail User</h2>
    <?php if($_SESSION['user']['role'] === 'admin'){ ?>
      <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
    <?php } else { ?>
      <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
    <?php } ?>
  </div>

  <table class="table table-bordered table-striped">
    <tr>
      <th>ID</th>
      <td><?= $user['id'] ?></td>
    </tr>
    <tr>
      <th>Username</th>
      <td><?= htmlspecialchars($user['username']) ?></td>
    </tr>
    <tr>
      <th>Email</th>
      <td><?= htmlspecialchars($user['email']) ?></td>
    </tr>
    <tr>
      <th>PayPal ID</th>
      <td><?= htmlspecialchars($user['paypal_id'] ?? '-') ?></td>
    </tr>

    <?php if(!empty($user['nama_lengkap'])){ ?>
    <tr>
      <th>Nama Lengkap</th>
      <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
    </tr>
    <?php } ?>

    <?php if(!empty($user['alamat'])){ ?>
    <tr>
      <th>Alamat</th>
      <td><?= nl2br(htmlspecialchars($user['alamat'])) ?></td>
    </tr>
    <?php } ?>

    <?php if(!empty($user['no_hp'])){ ?>
    <tr>
      <th>No. HP</th>
      <td><?= htmlspecialchars($user['no_hp']) ?></td>
    </tr>
    <?php } ?>

    <tr>
      <th>Role</th>
      <td><?= htmlspecialchars($user['role']) ?></td>
    </tr>
    <tr>
      <th>Tanggal Daftar</th>
      <td><?= $user['created_at'] ?></td>
    </tr>
  </table>
</div>

</body>
</html>
