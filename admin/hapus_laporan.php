<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role - hanya admin yang bisa akses
if($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Ambil ID laporan
if(!isset($_GET['id'])) {
    $_SESSION['error'] = "ID laporan tidak ditemukan!";
    header("Location: semua_laporan.php");
    exit();
}

$laporan_id = (int)$_GET['id'];

// Langsung eksekusi hapus (Logika foto dihapus karena kolomnya sudah tidak ada)
$query_delete = "DELETE FROM laporan WHERE id_laporan = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, "i", $laporan_id);

if(mysqli_stmt_execute($stmt_delete)) {
    $_SESSION['success'] = "Data laporan telah berhasil dimusnahkan!";
} else {
    $_SESSION['error'] = "Aduh! Gagal menghapus data: " . mysqli_error($conn);
}

header("Location: semua_laporan.php");
exit();