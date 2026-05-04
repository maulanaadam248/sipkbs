<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

// Enable error reporting for debugging (bisa dimatikan nanti jika sudah produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. BUKA GEMBOK: Izinkan Admin DAN Operator mengakses file proses ini
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Tentukan URL kembali berdasarkan role (Cerdas Redirect)
$url_kembali_sukses = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

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
    
    // Keamanan ID Balai berdasarkan Role
    if($_SESSION['role'] == 'operator') {
        // Paksakan menggunakan ID Balai milik operator
        $balai_id = $_SESSION['balai_id'];
    } else {
        // Jika admin, biarkan balai_id sesuai data aslinya di database
        $query_b = mysqli_query($conn, "SELECT balai_id FROM laporan WHERE id_laporan = " . (int)$id_laporan);
        $row_b = mysqli_fetch_assoc($query_b);
        $balai_id = $row_b['balai_id'];
    }
    
    // Validasi data wajib
    $errors = [];
    if(empty($komoditas)) $errors[] = 'Komoditas wajib diisi';
    if(empty($status_ketersediaan)) $errors[] = 'Status ketersediaan wajib diisi';
    if(empty($bulan)) $errors[] = 'Bulan wajib diisi';
    if(empty($tahun)) $errors[] = 'Tahun wajib diisi';
    
    // --- PENANGANAN KOLOM ANGKA AGAR TIDAK CRASH ---
    if($jumlah_benih === '') {
        $jumlah_benih = 0;
    }
    if($harga_satuan === '') {
        $harga_satuan = 0;
    }
    
    // Jika ada error, kembali ke form edit
    if(!empty($errors)) {
        $_SESSION['error'] = implode(', ', $errors);
        header("Location: edit_laporan.php?id=" . $id_laporan);
        exit();
    }
    
    // Build query update
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
                    WHERE id_laporan = " . (int)$id_laporan;
    
    // Jika operator, tambahkan filter keamanan ekstra!
    if($_SESSION['role'] == 'operator') {
        $query_update .= " AND balai_id = " . (int)$balai_id;
    }
    
    // Execute query
    $result = mysqli_query($conn, $query_update);
    
    if($result) {
        $_SESSION['success'] = "Laporan berhasil diperbarui!";
        header("Location: " . $url_kembali_sukses);
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui laporan: " . mysqli_error($conn);
        header("Location: edit_laporan.php?id=" . $id_laporan);
        exit();
    }
} else {
    // Jika ada yang mencoba mengakses file ini langsung dari URL
    header("Location: " . $url_kembali_sukses);
    exit();
}
?>