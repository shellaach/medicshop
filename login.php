<?php
include "config/koneksi.php";
session_start();

if(isset($_POST['login'])){
  $user = $_POST['username'];
  $pass = md5($_POST['password']); // password di DB sudah pakai MD5

  $q = mysqli_query($koneksi,"SELECT * FROM users WHERE username='$user' AND password='$pass'");
  if(mysqli_num_rows($q) > 0){
    $_SESSION['user'] = mysqli_fetch_assoc($q);

    // Semua role diarahkan ke index.php
    header("Location: index.php");
    exit;

  } else {
    $pesan = "<div class='alert alert-danger'>❌ Username atau password salah!</div>";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - MedicShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
          <h4>Login</h4>
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

            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
          </form>

          <hr>
          <p class="text-center">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
