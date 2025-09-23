<?php
session_start();
include "config/koneksi.php";

// === CEK LOGIN ===
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$sql_cek = mysqli_query($koneksi, "SELECT * FROM vendors WHERE user_id='$user_id'");
$cek     = mysqli_fetch_assoc($sql_cek);

// jika klik ajukan ulang dan status ditolak
if (isset($_GET['ulang']) && $cek && $cek['status'] == 'rejected') {
    mysqli_query($koneksi, "DELETE FROM vendors WHERE user_id='$user_id'");
    header("Location: vendor_request.php");
    exit;
}

$error = $success = "";

if (isset($_POST['submit'])) {
    $nama_toko  = mysqli_real_escape_string($koneksi, $_POST['nama_toko']);
    $pemilik    = mysqli_real_escape_string($koneksi, $_POST['pemilik']);
    $email_toko = mysqli_real_escape_string($koneksi, $_POST['email_toko']);
    $telepon    = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat     = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    if ($cek) {
        $error = "⚠️ Pengajuan toko sudah pernah dilakukan.";
    } else {
        if (!$nama_toko || !$pemilik || !$email_toko || !$telepon || !$deskripsi) {
            $error = "Harap isi semua data yang wajib diisi.";
        } else {
            $insert = mysqli_query($koneksi, "
                INSERT INTO vendors 
                    (user_id, nama_toko, pemilik, email_toko, telepon, alamat, deskripsi, status)
                VALUES
                    ('$user_id','$nama_toko','$pemilik','$email_toko','$telepon','$alamat','$deskripsi','pending')
            ");
            if ($insert) {
                $success = "✅ Pengajuan toko berhasil. Menunggu persetujuan admin.";
            } else {
                $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Toko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Form Pengajuan Toko</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if (!$cek): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Nama Toko *</label>
                                <input type="text" name="nama_toko" class="form-control" placeholder="Contoh: Apotek Sehat" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Pemilik *</label>
                                <input type="text" name="pemilik" class="form-control" placeholder="Nama Lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Toko *</label>
                                <input type="email" name="email_toko" class="form-control" placeholder="email@contoh.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No. Telepon *</label>
                                <input type="text" name="telepon" class="form-control" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat (Opsional)</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Toko *</label>
                                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Ceritakan tentang toko Anda" required></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary w-100">Ajukan Toko</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Status pengajuan toko Anda: <strong><?= htmlspecialchars($cek['status']) ?></strong></p>
                            <?php if ($cek['status'] == 'pending'): ?>
                                <p>⏳ Menunggu persetujuan admin.</p>
                            <?php elseif ($cek['status'] == 'approved'): ?>
                                <p>✅ Toko Anda sudah disetujui! Silakan mulai menambahkan produk.</p>
                            <?php else: ?>
                                <p>❌ Pengajuan ditolak. Silakan hubungi admin jika ingin mengajukan ulang.</p>
                                <small>
                                    👉 <a href="vendor_request.php?ulang=1" class="text-decoration-none">
                                        Ajukan ulang toko
                                    </a>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
