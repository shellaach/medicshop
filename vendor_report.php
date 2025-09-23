<?php
include "config/koneksi.php";
require("fpdf/fpdf.php");
session_start();

// ===================== CEK LOGIN & VENDOR =====================
if (!isset($_SESSION['user'])) {
    die("❌ Akses ditolak! <a href='login.php'>Login</a>");
}

$user_id = $_SESSION['user']['id'];

$cek_vendor = mysqli_query($koneksi,"
    SELECT * FROM vendors
    WHERE user_id='$user_id' AND status='approved'
    LIMIT 1
");
if (!$cek_vendor || mysqli_num_rows($cek_vendor) == 0) {
    die("❌ Anda bukan vendor atau belum disetujui. <a href='index.php'>Kembali</a>");
}
$vendor = mysqli_fetch_assoc($cek_vendor);
$vendor_id = $vendor['vendor_id'];

// 🔹 Nama bulan
$nama_bulan = [
    "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
    "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
    "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

// ===================== CETAK PDF =====================
if (isset($_GET['action']) && $_GET['action']=="cetak") {
    $filter = $_GET['filter'] ?? 'semua';
    $where  = "WHERE p.vendor_id='$vendor_id'";
    $judul  = "Laporan Semua Pesanan - ".$vendor['nama_toko'];

    if($filter=="tahun"){
        $tahun = $_GET['tahun_tahun'];
        $where .= " AND YEAR(o.tanggal)='$tahun'";
        $judul = "Laporan Pesanan Tahun $tahun - ".$vendor['nama_toko'];
    }
    if($filter=="bulan"){
        $bulan = $_GET['bulan'];
        $tahun = $_GET['tahun_bulan'];
        $where .= " AND MONTH(o.tanggal)='$bulan' AND YEAR(o.tanggal)='$tahun'";
        $judul = "Laporan Pesanan - ".$nama_bulan[$bulan]." $tahun - ".$vendor['nama_toko'];
    }
    if($filter=="tanggal"){
        $tgl1 = $_GET['tgl1'];
        $tgl2 = $_GET['tgl2'];
        $where .= " AND DATE(o.tanggal) BETWEEN '$tgl1' AND '$tgl2'";
        $judul = "Laporan Pesanan ($tgl1 s/d $tgl2) - ".$vendor['nama_toko'];
    }

    // 🔹 Ambil data order_items untuk vendor ini
    $q = mysqli_query($koneksi,"
        SELECT p.nama_produk, oi.qty, oi.harga, (oi.qty*oi.harga) as subtotal, o.tanggal
        FROM order_items oi
        JOIN products p ON oi.product_id=p.id
        JOIN orders o ON oi.order_id=o.id
        $where
        ORDER BY o.tanggal ASC
    ");

    $total_laporan = 0;
    $data = [];
    while($row = mysqli_fetch_assoc($q)){
        $data[] = $row;
        $total_laporan += $row['subtotal'];
    }

    // 🔹 Generate PDF
    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190,10,strtoupper($judul),0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(60,10,'Produk',1,0,'C');
    $pdf->Cell(20,10,'Qty',1,0,'C');
    $pdf->Cell(35,10,'Harga',1,0,'C');
    $pdf->Cell(35,10,'Subtotal',1,0,'C');
    $pdf->Cell(40,10,'Tanggal',1,1,'C');

    $pdf->SetFont('Arial','',11);
    foreach($data as $row){
        $pdf->Cell(60,10,$row['nama_produk'],1);
        $pdf->Cell(20,10,$row['qty'],1,0,'C');
        $pdf->Cell(35,10,"Rp ".number_format($row['harga'],0,',','.'),1,0,'R');
        $pdf->Cell(35,10,"Rp ".number_format($row['subtotal'],0,',','.'),1,0,'R');
        $pdf->Cell(40,10,$row['tanggal'],1,1,'C');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(80,10,'TOTAL',1,0,'C');
    $pdf->Cell(110,10,"Rp ".number_format($total_laporan,0,',','.'),1,1,'R');

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
  <title>Laporan Vendor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="vendor_dashboard.php">MedicShop Vendor</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-success text-white">
      <h4 class="mb-0">📝 Cetak Laporan Produk (<?= htmlspecialchars($vendor['nama_toko']) ?>)</h4>
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
            <?php for($t=2023;$t<=date('Y');$t++){ echo "<option value='$t'>$t</option>"; } ?>
          </select>
        </div>

        <!-- Filter Bulan -->
        <div id="filter_bulan" class="row mb-3" style="display:none;">
          <div class="col-md-6">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
              <?php foreach($nama_bulan as $key=>$val){ echo "<option value='$key'>$val</option>"; } ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tahun</label>
            <select name="tahun_bulan" class="form-select">
              <?php for($t=2023;$t<=date('Y');$t++){ echo "<option value='$t'>$t</option>"; } ?>
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
      <a href="vendor_dashboard.php" class="btn btn-secondary">⬅ Kembali</a>
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
