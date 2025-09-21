<?php
include "config/koneksi.php";
session_start();

// Jika tombol submit ditekan
if (isset($_POST['register'])) {
    $username   = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password   = mysqli_real_escape_string($koneksi, $_POST['password']);
    $retype     = mysqli_real_escape_string($koneksi, $_POST['retype']);
    $email      = mysqli_real_escape_string($koneksi, $_POST['email']);
    $dob        = mysqli_real_escape_string($koneksi, $_POST['dob']);
    $gender     = mysqli_real_escape_string($koneksi, $_POST['gender']);
    $alamat     = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $city       = mysqli_real_escape_string($koneksi, $_POST['city']);
    $no_hp      = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $paypal_id  = mysqli_real_escape_string($koneksi, $_POST['paypal_id']);

    // Validasi password sama
    if ($password !== $retype) {
        $error = "Password dan Retype Password tidak sama!";
    } else {
        // Enkripsi password (hash MD5, bisa diganti password_hash())
        $hash = md5($password);

        // Simpan ke database
        $sql = "INSERT INTO users (username, password, email, dob, gender, alamat, city, no_hp, paypal_id, role) 
                VALUES ('$username', '$hash', '$email', '$dob', '$gender', '$alamat', '$city', '$no_hp', '$paypal_id', 'customer')";

        if (mysqli_query($koneksi, $sql)) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-scroll {
      max-height: 400px;   /* tinggi maksimal form */
      overflow-y: auto;    /* aktifkan scroll vertical */
      padding-right: 10px; /* biar scroll ga nutup input */
    }
  </style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
          <h4>Form Register</h4>
        </div>
        <div class="card-body">

          <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php } ?>
          <?php if (isset($success)) { ?>
            <div class="alert alert-success"><?= $success ?> <a href="login.php">Login di sini</a></div>
          <?php } ?>

          <div class="form-scroll">
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Retype Password</label>
                <input type="password" name="retype" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control" required>
                  <option value="">-- Pilih --</option>
                  <option value="male">Laki-laki</option>
                  <option value="female">Perempuan</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="alamat" class="form-control" required></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Contact No</label>
                <input type="text" name="no_hp" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Paypal ID</label>
                <input type="text" name="paypal_id" class="form-control">
              </div>

              <div class="d-flex justify-content-between mb-3">
                <button type="submit" name="register" class="btn btn-success">Submit</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
              </div>
            </form>
          </div><!-- form-scroll -->
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
