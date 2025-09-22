<?php
session_start();
include "config/koneksi.php";

// ======== CEK LOGIN & ROLE ADMIN ========
if(!isset($_SESSION['user'])){
    die("<div class='alert alert-danger m-3'>
        ⚠️ Silakan login sebagai admin terlebih dahulu.
        <a href='login.php'>Login</a></div>");
}
if($_SESSION['user']['role'] !== 'admin'){
    die("<div class='alert alert-danger m-3'>
        🚫 Anda tidak memiliki akses ke halaman ini.</div>");
}

// ======== HAPUS USER ========
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    // cegah admin hapus dirinya sendiri
    if($id == $_SESSION['user']['id']){
        header("Location: guest_book.php?msg=cannot_delete_self");
        exit;
    }

    // prepared statement aman
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: guest_book.php?msg=deleted");
        exit;
    } else {
        // tampilkan error jika gagal (misal foreign key constraint)
        $err = mysqli_error($koneksi);
        header("Location: guest_book.php?msg=error&info=" . urlencode($err));
        exit;
    }
}

// ======== AMBIL DATA USER ========
$q = mysqli_query(
    $koneksi,
    "SELECT id, username, email, role, created_at
     FROM users ORDER BY id DESC"
) or die("Query Error: ".mysqli_error($koneksi));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Guest Book - Data User</title>
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
  <!-- ======== HEADER ======== -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📖 Guest Book - Daftar User Terdaftar</h2>
    <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  </div>

  <!-- ======== ALERT ======== -->
  <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'){ ?>
    <div class="alert alert-success">✅ Data user berhasil dihapus.</div>
  <?php } elseif(isset($_GET['msg']) && $_GET['msg']=='cannot_delete_self'){ ?>
    <div class="alert alert-warning">❗ Tidak bisa menghapus akun admin yang sedang login.</div>
  <?php } elseif(isset($_GET['msg']) && $_GET['msg']=='error'){ ?>
    <div class="alert alert-danger">
      ⚠️ Gagal menghapus user.<br>
      <small><?= htmlspecialchars($_GET['info']) ?></small>
    </div>
  <?php } ?>

  <!-- ======== TABEL USER ======== -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Tanggal Daftar</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($q)){ ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td>
          <?php if($row['role'] === 'admin'){ ?>
            <span class="badge bg-danger">Admin</span>
          <?php } else { ?>
            <span class="badge bg-primary">Customer</span>
          <?php } ?>
        </td>
        <td><?= $row['created_at'] ?></td>
        <td>
          <a href="user_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">
            Lihat Data
          </a>
          <?php if($row['role'] !== 'admin'){ ?>
            <a href="guest_book.php?delete=<?= $row['id'] ?>"
               onclick="return confirm('Yakin ingin menghapus user ini?')"
               class="btn btn-sm btn-danger">
               🗑 Hapus
            </a>
          <?php } else { ?>
            <span class="text-muted">-</span>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
