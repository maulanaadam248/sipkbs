<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
// Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = mysqli_real_escape_string($conn, $_POST['bulan']);
    $tahun = mysqli_real_escape_string($conn, $_POST['tahun']);
    
    // Default redirect jika gagal
    $redirect = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

    // Validasi input kosong
    if(empty($bulan) || empty($tahun)) {
        $_SESSION['error'] = "Bulan dan Tahun wajib dipilih!";
        header("Location: $redirect");
        exit();
    }

    // LOGIKA KEAMANAN BERDASARKAN ROLE
    if ($_SESSION['role'] == 'operator') {
        // Operator HANYA bisa menghapus data balainya sendiri
        $balai_id = (int)$_SESSION['balai_id'];
        $query = "DELETE FROM laporan WHERE bulan = '$bulan' AND tahun = '$tahun' AND balai_id = $balai_id";
        
    } else if ($_SESSION['role'] == 'admin') {
        // Admin bisa memilih balai tertentu, atau mengosongkan SEMUA balai
        $balai_id = isset($_POST['balai_id']) ? (int)$_POST['balai_id'] : 0;

        if ($balai_id > 0) {
            $query = "DELETE FROM laporan WHERE bulan = '$bulan' AND tahun = '$tahun' AND balai_id = $balai_id";
        } else {
            // Bahaya: Ini akan menghapus laporan bulan tersebut dari SELURUH BALAI
            $query = "DELETE FROM laporan WHERE bulan = '$bulan' AND tahun = '$tahun'";
        }
    } else {
        header("Location: ../index.php");
        exit();
    }

    // Eksekusi Penghapusan
    if(mysqli_query($conn, $query)) {
        $jumlah_terhapus = mysqli_affected_rows($conn); // Cek berapa baris yang terhapus
        
        if ($jumlah_terhapus > 0) {
            $_SESSION['success'] = "Berhasil! Sebanyak <strong>$jumlah_terhapus data</strong> pada periode $bulan $tahun telah dihapus permanen.";
        } else {
            $_SESSION['error'] = "Tidak ada data yang ditemukan pada periode $bulan $tahun untuk dihapus.";
        }
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
    }

    header("Location: $redirect");
    exit();
} else {
    header("Location: ../dashboard/dashboard.php");
    exit();
}
?>