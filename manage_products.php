<?php
include "config/koneksi.php";
session_start();

// Cek login & role
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
  echo "<p>Anda tidak punya akses! <a href='index.php'>Kembali</a></p>";
  exit;
}

// Tambah Produk
if(isset($_POST['tambah'])){
    $nama     = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];

    // Upload gambar
    $gambar = "";
    if(!empty($_FILES['gambar']['name'])){
        $gambar = time()."_".$_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "images/".$gambar);
    }

    mysqli_query($koneksi, "INSERT INTO products(nama_produk,kategori,harga,stok,gambar) 
                            VALUES('$nama','$kategori','$harga','$stok','$gambar')");
    header("Location: manage_products.php");
    exit;
}

// Hapus Produk
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    // hapus gambar juga
    $res = mysqli_query($koneksi,"SELECT gambar FROM products WHERE id='$id'");
    $row = mysqli_fetch_assoc($res);
    if($row && $row['gambar'] && file_exists("images/".$row['gambar'])){
        unlink("images/".$row['gambar']);
    }
    mysqli_query($koneksi,"DELETE FROM products WHERE id='$id'");
    header("Location: manage_products.php");
    exit;
}

// Update Produk
if(isset($_POST['update'])){
    $id       = $_POST['id'];
    $nama     = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];

    // Ambil gambar lama
    $res = mysqli_query($koneksi,"SELECT gambar FROM products WHERE id='$id'");
    $row = mysqli_fetch_assoc($res);
    $gambar = $row['gambar'];

    // Jika upload baru
    if(!empty($_FILES['gambar']['name'])){
        // hapus lama kalau ada
        if($gambar && file_exists("images/".$gambar)){
            unlink("images/".$gambar);
        }
        $gambar = time()."_".$_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "images/".$gambar);
    }

    mysqli_query($koneksi,"UPDATE products SET 
                nama_produk='$nama',
                kategori='$kategori',
                harga='$harga',
                stok='$stok',
                gambar='$gambar'
                WHERE id='$id'");
    header("Location: manage_products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Produk - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-3">📦 Kelola Produk</h2>
  <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
  
  <!-- Form Tambah -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Tambah Produk</div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-3 mb-2">
            <input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="text" name="kategori" class="form-control" placeholder="Kategori" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="number" name="harga" class="form-control" placeholder="Harga" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="number" name="stok" class="form-control" placeholder="Stok" required>
          </div>
          <div class="col-md-3 mb-2">
            <input type="file" name="gambar" class="form-control">
          </div>
        </div>
        <button type="submit" name="tambah" class="btn btn-success mt-2">Tambah</button>
      </form>
    </div>
  </div>

  <!-- Tabel Produk -->
  <table class="table table-bordered table-hover bg-white shadow-sm">
    <thead class="table-primary">
      <tr>
        <th>No</th>
        <th>Foto</th>
        <th>Nama</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $q = mysqli_query($koneksi,"SELECT * FROM products ORDER BY id DESC");
      $no = 1;
      while($row = mysqli_fetch_assoc($q)){ ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <?php if($row['gambar']){ ?>
              <img src="images/<?= $row['gambar'] ?>" width="80">
            <?php } else { ?>
              <img src="images/no-image.png" width="80">
            <?php } ?>
          </td>
          <td><?= $row['nama_produk'] ?></td>
          <td><?= $row['kategori'] ?></td>
          <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
          <td><?= $row['stok'] ?></td>
          <td>
            <!-- Tombol Edit -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $row['id'] ?>">Edit</button>
            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus produk ini?')" class="btn btn-sm btn-danger">Hapus</a>
          </td>
        </tr>

        <!-- Edit Produk -->
        <div class="modal fade" id="edit<?= $row['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Edit Produk</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <div class="mb-2">
                    <label>Nama Produk</label>
                    <input type="text" name="nama" class="form-control" value="<?= $row['nama_produk'] ?>" required>
                  </div>
                  <div class="mb-2">
                    <label>Kategori</label>
                    <input type="text" name="kategori" class="form-control" value="<?= $row['kategori'] ?>" required>
                  </div>
                  <div class="mb-2">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" value="<?= $row['harga'] ?>" required>
                  </div>
                  <div class="mb-2">
                    <label>Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $row['stok'] ?>" required>
                  </div>
                  <div class="mb-2">
                    <label>Gambar (biarkan kosong jika tidak ganti)</label>
                    <input type="file" name="gambar" class="form-control">
                    <?php if($row['gambar']){ ?>
                      <img src="images/<?= $row['gambar'] ?>" width="100" class="mt-2">
                    <?php } ?>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="update" class="btn btn-success">Simpan</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php } ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
