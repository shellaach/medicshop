<?php
session_start();
include "config/koneksi.php";
require("fpdf/fpdf.php");

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa membuat laporan.");
}

// Buat PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// Judul
$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Laporan Pesanan',0,1,'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial','B',12);
$pdf->Cell(20,10,'ID',1);
$pdf->Cell(50,10,'Customer',1);
$pdf->Cell(40,10,'Total',1);
$pdf->Cell(40,10,'Metode',1);
$pdf->Cell(40,10,'Tanggal',1);
$pdf->Ln();

// Isi data
$pdf->SetFont('Arial','',12);
$q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal 
                            FROM orders o 
                            JOIN users u ON o.user_id=u.id
                            ORDER BY o.tanggal DESC");
while($row = mysqli_fetch_assoc($q)){
    $pdf->Cell(20,10,$row['id'],1);
    $pdf->Cell(50,10,$row['username'],1);
    $pdf->Cell(40,10,"Rp ".number_format($row['total'],0,',','.'),1);
    $pdf->Cell(40,10,$row['metode_bayar'],1);
    $pdf->Cell(40,10,$row['tanggal'],1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output();
?>
