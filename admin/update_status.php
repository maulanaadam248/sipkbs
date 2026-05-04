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

// Proses update status
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $laporan_id = $_POST['id'];
    $status = $_POST['status'];
    
    // Validasi input
    if(empty($laporan_id) || empty($status)) {
        $_SESSION['error'] = "Data tidak lengkap!";
        header("Location: detail_laporan.php?id=" . $laporan_id);
        exit();
    }
    
    // Validasi status
    $valid_status = ['menunggu', 'diproses', 'selesai'];
    if(!in_array($status, $valid_status)) {
        $_SESSION['error'] = "Status tidak valid!";
        header("Location: detail_laporan.php?id=" . $laporan_id);
        exit();
    }
    
    // Update status di database
    $query = "UPDATE laporan SET status = ?, updated_at = NOW() WHERE id_laporan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $laporan_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Status laporan berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate status laporan!";
    }
    
    header("Location: detail_laporan.php?id=" . $laporan_id);
    exit();
} else {
    // Jika bukan POST, redirect ke halaman semua laporan
    header("Location: semua_laporan.php");
    exit();
}
?>
