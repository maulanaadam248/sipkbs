<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
// 1. Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. BUKA GEMBOK: Izinkan Admin DAN Operator masuk
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Tentukan arah kembali (redirect) setelah menghapus
$redirect = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

// 3. Pastikan ada ID yang dikirim
if(!isset($_GET['id'])) {
    $_SESSION['error'] = "Pilih data yang ingin dihapus!";
    header("Location: $redirect");
    exit();
}

$laporan_id = $_GET['id'];

// 4. LOGIKA KEAMANAN PENGHAPUSAN (Mencegah Operator menghapus data balai lain)
if($_SESSION['role'] == 'admin') {
    // Admin bisa menghapus data apa saja
    $query = "DELETE FROM laporan WHERE id_laporan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $laporan_id);
} else {
    $balai_id_operator = $_SESSION['balai_id'];
    $query = "DELETE FROM laporan WHERE id_laporan = ? AND balai_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $laporan_id, $balai_id_operator);
}

if(mysqli_stmt_execute($stmt)) {
    if(mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success'] = "Data laporan berhasil dihapus permanen!";
    } else {
        // Jika tidak ada baris yang terhapus (mungkin ID tidak ada, atau Operator mencoba hapus data balai lain)
        $_SESSION['error'] = "Data tidak ditemukan atau Anda tidak berhak menghapus data ini.";
    }
} else {
    $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
}

header("Location: $redirect");
exit();
?>