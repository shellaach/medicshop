<?php
session_start();
include "config/koneksi.php";
require("fpdf/fpdf.php");

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa membuat laporan.");
}

$nama_bulan = [
    "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
    "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
    "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

// Jika form sudah dipilih, buat laporan PDF
if(isset($_GET['bulan']) && isset($_GET['tahun'])){
    $bulan = $_GET['bulan'];
    $tahun = $_GET['tahun'];

    $q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal 
                                FROM orders o 
                                JOIN users u ON o.user_id=u.id
                                WHERE MONTH(o.tanggal)='$bulan' AND YEAR(o.tanggal)='$tahun'
                                ORDER BY o.tanggal ASC");

    $total_bulanan = 0;
    $data = [];
    while($row = mysqli_fetch_assoc($q)){
        $data[] = $row;
        $total_bulanan += $row['total'];
    }

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();

    if(file_exists("logo.png")){
        $pdf->Image("logo.png",10,8,25,25);
    }

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(190,10,'LAPORAN PESANAN BULANAN',0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','',12);
    $periode = $nama_bulan[$bulan]." ".$tahun;
    $pdf->Cell(190,10,'Periode: '.$periode,0,1,'C');
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
    $pdf->Cell(65,10,'TOTAL BULANAN',1,0,'C');
    $pdf->Cell(115,10,"Rp ".number_format($total_bulanan,0,',','.'),1,1,'R');

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
  <title>Cetak Laporan Bulanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">📝 Cetak Laporan Bulanan</h4>
    </div>
    <div class="card-body">
      <form method="GET" action="">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Pilih Bulan</label>
            <select name="bulan" class="form-select">
              <?php
              for($i=1;$i<=12;$i++){
                  $b = str_pad($i,2,"0",STR_PAD_LEFT);
                  echo "<option value='$b'>$nama_bulan[$b]</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Pilih Tahun</label>
            <select name="tahun" class="form-select">
              <?php
              for($t=2023;$t<=date('Y');$t++){
                  echo "<option value='$t'>$t</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Cetak Laporan 📄</button>
      </form>
    </div>
    <div class="card-footer text-center">
      <a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
    </div>
  </div>
</div>

</body>
</html>
