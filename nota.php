<?php
session_start();
include "config/koneksi.php";
require('fpdf/fpdf.php');

// Pastikan ada id order
if(!isset($_GET['id'])){
    die("ID Order tidak ditemukan.");
}
$order_id = intval($_GET['id']);

// Ambil data order
$order_q = mysqli_query($koneksi, "SELECT o.*, u.username 
                                   FROM orders o 
                                   JOIN users u ON o.user_id=u.id 
                                   WHERE o.id='$order_id'");
$order = mysqli_fetch_assoc($order_q);
if(!$order){
    die("Order tidak ditemukan.");
}

// Ambil detail produk
$item_q = mysqli_query($koneksi, "SELECT oi.*, p.nama_produk 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id=p.id 
                                  WHERE oi.order_id='$order_id'");

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Nota Pembelian - MedicShop',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(100,8,'ID Order : '.$order['id'],0,1);
$pdf->Cell(100,8,'Pelanggan : '.$order['username'],0,1);
$pdf->Cell(100,8,'Tanggal : '.$order['tanggal'],0,1);
$pdf->Cell(100,8,'Metode Bayar : '.$order['metode_bayar'],0,1);
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,8,'Produk',1);
$pdf->Cell(30,8,'Qty',1,0,'C');
$pdf->Cell(40,8,'Harga',1,0,'R');
$pdf->Cell(40,8,'Subtotal',1,1,'R');

// Data item
$pdf->SetFont('Arial','',12);
$total = 0;
while($it = mysqli_fetch_assoc($item_q)){
    $subtotal = $it['qty'] * $it['harga'];
    $total += $subtotal;
    $pdf->Cell(80,8,$it['nama_produk'],1);
    $pdf->Cell(30,8,$it['qty'],1,0,'C');
    $pdf->Cell(40,8,'Rp '.number_format($it['harga'],0,',','.'),1,0,'R');
    $pdf->Cell(40,8,'Rp '.number_format($subtotal,0,',','.'),1,1,'R');
}

// Total
$pdf->SetFont('Arial','B',12);
$pdf->Cell(150,8,'Total',1,0,'R');
$pdf->Cell(40,8,'Rp '.number_format($total,0,',','.'),1,1,'R');

$pdf->Output('D', 'nota_'.$order['id'].'.pdf'); // D = download langsung
?>
