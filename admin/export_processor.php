<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
if(!isset($_POST['btn_export'])) {
    header("Location: export.php");
    exit();
}

$type = $_POST['export_type'];
$where = [];

if(!empty($_POST['balai_id'])) $where[] = "l.balai_id = " . (int)$_POST['balai_id'];
if(!empty($_POST['bulan'])) $where[] = "l.bulan = '" . mysqli_real_escape_string($conn, $_POST['bulan']) . "'";
if(!empty($_POST['tahun'])) $where[] = "l.tahun = " . (int)$_POST['tahun'];
if(!empty($_POST['status'])) $where[] = "l.status_ketersediaan = '" . mysqli_real_escape_string($conn, $_POST['status']) . "'";

$sql_where = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT l.*, b.nama_balai FROM laporan l JOIN balai b ON l.balai_id = b.id_balai $sql_where ORDER BY b.nama_balai ASC, l.komoditas ASC";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Data tidak ditemukan untuk filter tersebut!";
    header("Location: export.php");
    exit();
}

$filename = "Laporan_SIPKBS_" . date('Ymd_His');

// Set Header berdasarkan tipe
if($type == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
} elseif($type == 'word') {
    header("Content-Type: application/msword");
    header("Content-Disposition: attachment; filename=$filename.doc");
} elseif($type == 'pdf') {
    header("Content-Type: text/html");
}

// Panggil tampilan "Klasik"
include 'export_view.php';
exit();