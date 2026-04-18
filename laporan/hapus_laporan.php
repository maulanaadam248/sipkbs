<?php
session_start();
require_once '../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role - hanya operator yang bisa hapus laporan
if($_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Ambil ID laporan dari URL
$id_laporan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id_laporan == 0) {
    $_SESSION['error'] = "ID laporan tidak valid!";
    header("Location: riwayat_laporan.php");
    exit();
}

// Cek apakah laporan ada dan milik user ini
$query = "SELECT * FROM laporan WHERE id_laporan = ? AND balai_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $id_laporan, $_SESSION['balai_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Laporan tidak ditemukan!";
    header("Location: riwayat_laporan.php");
    exit();
}

// Ambil data laporan untuk hapus foto
$laporan = mysqli_fetch_assoc($result);

// Hapus laporan dari database
$delete_query = "DELETE FROM laporan WHERE id_laporan = ? AND balai_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "ii", $id_laporan, $_SESSION['balai_id']);

if(mysqli_stmt_execute($delete_stmt)) {
    $_SESSION['success'] = "Laporan berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus laporan: " . mysqli_error($conn);
}

header("Location: riwayat_laporan.php");
exit();
?>
