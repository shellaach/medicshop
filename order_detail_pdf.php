<?php
session_start();
include "config/koneksi.php";
require("fpdf/fpdf.php");

// Cek login & role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    die("❌ Akses ditolak! Hanya admin yang bisa mencetak PDF.");
}

if(!isset($_GET['id'])){
    die("ID Pesanan tidak ditemukan.");
}

$order_id = $_GET['id'];

// Ambil data order
$q = mysqli_query($koneksi,"SELECT o.id, u.username, o.total, o.metode_bayar, o.tanggal 
                            FROM orders o 
                            JOIN users u ON o.user_id=u.id
                            WHERE o.id='$order_id'");
$order = mysqli_fetch_assoc($q);

if(!$order){
    die("Pesanan tidak ditemukan.");
}

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();

// Judul
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Detail Pesanan #'.$order['id'],0,1,'C');
$pdf->Ln(5);

// Info Customer
$pdf->SetFont('Arial','',12);
$pdf->Cell(50,8,'Customer',1);
$pdf->Cell(0,8,$order['username'],1,1);
$pdf->Cell(50,8,'Tanggal',1);
$pdf->Cell(0,8,$order['tanggal'],1,1);
$pdf->Cell(50,8,'Metode Bayar',1);
$pdf->Cell(0,8,$order['metode_bayar'],1,1);
$pdf->Cell(50,8,'Total',1);
$pdf->Cell(0,8,'Rp '.number_format($order['total'],0,',','.'),1,1);

$pdf->Ln(8);

// Daftar Produk
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,8,'Produk',1);
$pdf->Cell(30,8,'Qty',1);
$pdf->Cell(40,8,'Harga',1);
$pdf->Cell(40,8,'Subtotal',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);

$q_items = mysqli_query($koneksi,"SELECT p.nama_produk, i.qty, i.harga 
                                  FROM order_items i 
                                  JOIN products p ON i.product_id=p.id
                                  WHERE i.order_id='$order_id'");
while($item = mysqli_fetch_assoc($q_items)){
    $subtotal = $item['qty'] * $item['harga'];
    $pdf->Cell(80,8,$item['nama_produk'],1);
    $pdf->Cell(30,8,$item['qty'],1,0,'C');
    $pdf->Cell(40,8,'Rp '.number_format($item['harga'],0,',','.'),1,0,'R');
    $pdf->Cell(40,8,'Rp '.number_format($subtotal,0,',','.'),1,1,'R');
}

// Output PDF
$pdf->Output("I","Pesanan_".$order['id'].".pdf");
?>
