<?php
session_start();
include "config/koneksi.php";
require("fpdf/fpdf.php");

// ✅ Cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak!");
}

// 🔹 Nama bulan
$nama_bulan = [
    "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
    "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
    "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

// ✅ Cetak PDF jika dipanggil
if(isset($_GET['action']) && $_GET['action']=="cetak"){
    $filter = $_GET['filter'] ?? 'semua';
    $where  = "";
    $judul  = "Laporan Semua Pesanan";

    if($filter=="tahun"){
        $tahun = $_GET['tahun_tahun']; // ✅ gunakan name unik
        $where = "WHERE YEAR(o.tanggal)='$tahun'";
        $judul = "Laporan Pesanan Tahun $tahun";
    }
    if($filter=="bulan"){
        $bulan = $_GET['bulan'];
        $tahun = $_GET['tahun_bulan']; // ✅ gunakan name unik
        $where = "WHERE MONTH(o.tanggal)='$bulan' AND YEAR(o.tanggal)='$tahun'";
        $judul = "Laporan Pesanan - ".$nama_bulan[$bulan]." $tahun";
    }
    if($filter=="tanggal"){
        $tgl1 = $_GET['tgl1'];
        $tgl2 = $_GET['tgl2'];
        $where = "WHERE DATE(o.tanggal) BETWEEN '$tgl1' AND '$tgl2'";
        $judul = "Laporan Pesanan ($tgl1 s/d $tgl2)";
    }

    // 🔹 Ambil data
    $q = mysqli_query($koneksi,"SELECT o.id,u.username,o.total,o.metode_bayar,o.tanggal
                                FROM orders o
                                JOIN users u ON o.user_id=u.id
                                $where ORDER BY o.tanggal ASC");
    $total_laporan = 0;
    $data = [];
    while($row = mysqli_fetch_assoc($q)){
        $data[] = $row;
        $total_laporan += $row['total'];
    }

    // 🔹 Generate PDF
    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    if(file_exists("logo.png")){
        $pdf->Image("logo.png",10,8,25,25);
    }

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(190,10,strtoupper($judul),0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(20,10,'ID',1,0,'C');
    $pdf->Cell(45,10,'Customer',1,0,'C');
    $pdf->Cell(40,10,'Total',1,0,'C');
    $pdf->Cell(35,10,'Metode',1,0,'C');
    $pdf->Cell(50,10,'Tanggal',1,1,'C');

    $pdf->SetFont('Arial','',12);
    foreach($data as $row){
        $pdf->Cell(20,10,$row['id'],1,0,'C');
        $pdf->Cell(45,10,$row['username'],1);
        $pdf->Cell(40,10,"Rp ".number_format($row['total'],0,',','.'),1,0,'R');
        $pdf->Cell(35,10,$row['metode_bayar'],1,0,'C');
        $pdf->Cell(50,10,$row['tanggal'],1,1,'C');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(65,10,'TOTAL',1,0,'C');
    $pdf->Cell(125,10,"Rp ".number_format($total_laporan,0,',','.'),1,1,'R');

    $pdf->Ln(10);
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,10,'Dicetak pada: '.date("d-m-Y H:i:s"),0,1,'R');
    $pdf->Output();
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Pesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <!-- Logo MedicShop diarahkan ke index.php -->
    <a class="navbar-brand fw-bold" href="index.php">MedicShop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <!-- Tombol logout -->
        <li class="nav-item">
          <a class="nav-link text-warning" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">📝 Cetak Laporan Pesanan</h4>
    </div>
    <div class="card-body">
      <form method="GET" action="">
        <input type="hidden" name="action" value="cetak">

        <div class="mb-3">
          <label class="form-label">Pilih Jenis Laporan</label>
          <select name="filter" id="filter" class="form-select" required>
            <option value="semua">Semua Pesanan</option>
            <option value="tahun">Berdasarkan Tahun</option>
            <option value="bulan">Berdasarkan Bulan</option>
            <option value="tanggal">Berdasarkan Tanggal</option>
          </select>
        </div>

        <!-- Filter Tahun -->
        <div id="filter_tahun" class="mb-3" style="display:none;">
          <label class="form-label">Tahun</label>
          <select name="tahun_tahun" class="form-select">
            <?php
            for($t=2023;$t<=date('Y');$t++){
                echo "<option value='$t'>$t</option>";
            }
            ?>
          </select>
        </div>

        <!-- Filter Bulan -->
        <div id="filter_bulan" class="row mb-3" style="display:none;">
          <div class="col-md-6">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
              <?php
              foreach($nama_bulan as $key=>$val){
                  echo "<option value='$key'>$val</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tahun</label>
            <select name="tahun_bulan" class="form-select">
              <?php
              for($t=2023;$t<=date('Y');$t++){
                  echo "<option value='$t'>$t</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <!-- Filter Tanggal -->
        <div id="filter_tanggal" class="row mb-3" style="display:none;">
          <div class="col-md-6">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="tgl1" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="tgl2" class="form-control">
          </div>
        </div>

        <button type="submit" class="btn btn-success w-100">📄 Cetak Laporan</button>
      </form>
    </div>
    <div class="card-footer text-center">
      <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
    </div>
  </div>
</div>

<script>
const filterSelect = document.getElementById('filter');
const fTahun   = document.getElementById('filter_tahun');
const fBulan   = document.getElementById('filter_bulan');
const fTanggal = document.getElementById('filter_tanggal');

filterSelect.addEventListener('change', function(){
    fTahun.style.display   = this.value === 'tahun' ? 'block' : 'none';
    fBulan.style.display   = this.value === 'bulan' ? 'flex' : 'none';
    fTanggal.style.display = this.value === 'tanggal' ? 'flex' : 'none';
});
</script>

</body>
</html>
