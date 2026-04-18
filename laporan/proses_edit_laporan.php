<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role - hanya operator yang bisa edit laporan
if($_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Proses update laporan
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_laporan = $_POST['id_laporan'];
    
    // Ambil data dari form dengan aman
    $komoditas = isset($_POST['komoditas']) ? trim($_POST['komoditas']) : '';
    $kelompok_komoditas = isset($_POST['kelompok_komoditas']) ? trim($_POST['kelompok_komoditas']) : '';
    $satuan = isset($_POST['satuan']) ? trim($_POST['satuan']) : '';
    $harga_satuan = isset($_POST['harga_satuan']) ? trim($_POST['harga_satuan']) : '';
    $varietas = isset($_POST['varietas']) ? trim($_POST['varietas']) : '';
    $kelas_benih = isset($_POST['kelas_benih']) ? trim($_POST['kelas_benih']) : '';
    $status_ketersediaan = isset($_POST['status_ketersediaan']) ? trim($_POST['status_ketersediaan']) : '';
    $jumlah_benih = isset($_POST['jumlah_benih']) ? trim($_POST['jumlah_benih']) : '';
    $bulan = isset($_POST['bulan']) ? trim($_POST['bulan']) : '';
    $tahun = isset($_POST['tahun']) ? trim($_POST['tahun']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    
    // Validasi data
    $errors = [];
    
    if(empty($komoditas)) {
        $errors[] = 'Komoditas wajib diisi';
    }
    if(empty($status_ketersediaan)) {
        $errors[] = 'Status ketersediaan wajib diisi';
    }
    if(empty($bulan)) {
        $errors[] = 'Bulan wajib diisi';
    }
    if(empty($tahun)) {
        $errors[] = 'Tahun wajib diisi';
    }
    
    // Jika ada error, kembali ke form dengan pesan error
    if(!empty($errors)) {
        $_SESSION['error'] = implode(', ', $errors);
        header("Location: edit_laporan.php?id=" . $id_laporan);
        exit();
    }
    
    // --- BLOK PROSES UPLOAD FOTO SUDAH DIHAPUS TOTAL ---
    
    // Build query update secara manual untuk menghindari error binding
    $query_update = "UPDATE laporan SET 
                    komoditas = '" . mysqli_real_escape_string($conn, $komoditas) . "',
                    kelompok_komoditas = '" . mysqli_real_escape_string($conn, $kelompok_komoditas) . "',
                    satuan = '" . mysqli_real_escape_string($conn, $satuan) . "',
                    harga_satuan = '" . mysqli_real_escape_string($conn, $harga_satuan) . "',
                    varietas = '" . mysqli_real_escape_string($conn, $varietas) . "',
                    kelas_benih = '" . mysqli_real_escape_string($conn, $kelas_benih) . "',
                    status_ketersediaan = '" . mysqli_real_escape_string($conn, $status_ketersediaan) . "',
                    jumlah_benih = '" . mysqli_real_escape_string($conn, $jumlah_benih) . "',
                    bulan = '" . mysqli_real_escape_string($conn, $bulan) . "',
                    tahun = '" . mysqli_real_escape_string($conn, $tahun) . "',
                    deskripsi = '" . mysqli_real_escape_string($conn, $deskripsi) . "'
                    WHERE id_laporan = " . (int)$id_laporan . " AND balai_id = " . (int)$_SESSION['balai_id'];
    
    // Execute query
    $result = mysqli_query($conn, $query_update);
    
    if($result) {
        // Pesan sukses disederhanakan karena sudah tidak ada foto
        $_SESSION['success'] = "Laporan berhasil diperbarui!";
        header("Location: riwayat_laporan.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui laporan: " . mysqli_error($conn);
        header("Location: edit_laporan.php?id=" . $id_laporan);
        exit();
    }
} else {
    header("Location: riwayat_laporan.php");
    exit();
}
?>