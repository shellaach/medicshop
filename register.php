<?php
include "config/koneksi.php";
session_start();

if(isset($_POST['register'])){
    $user = $_POST['username'];
    $pass = md5($_POST['password']); // simpan password dalam md5
    $role = "customer"; // default customer

    // Cek apakah username sudah ada
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user'");
    if(mysqli_num_rows($cek) > 0){
        $pesan = "<div class='alert alert-danger'>❌ Username sudah digunakan!</div>";
    } else {
        mysqli_query($koneksi, "INSERT INTO users(username,password,role) VALUES('$user','$pass','$role')");
        $pesan = "<div class='alert alert-success'>✅ Registrasi berhasil! <a href='login.php' class='alert-link'>Login di sini</a></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
          <h4>Buat Akun Baru</h4>
        </div>
        <div class="card-body">

          <?php if(isset($pesan)) echo $pesan; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" name="register" class="btn btn-success w-100">Daftar</button>
          </form>

          <hr>
          <p class="text-center">Sudah punya akun? <a href="login.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
