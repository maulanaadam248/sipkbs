<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$url_kembali_sukses = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_laporan = (int)$_POST['id_laporan'];
    
    // Ambil data dasar
    $komoditas = isset($_POST['komoditas']) ? trim($_POST['komoditas']) : '';
    $kelompok_komoditas = isset($_POST['kelompok_komoditas']) ? trim($_POST['kelompok_komoditas']) : '';
    $satuan = isset($_POST['satuan']) ? trim($_POST['satuan']) : '';
    $varietas = isset($_POST['varietas']) ? trim($_POST['varietas']) : '';
    $kelas_benih = isset($_POST['kelas_benih']) ? trim($_POST['kelas_benih']) : '';
    $status_ketersediaan = isset($_POST['status_ketersediaan']) ? trim($_POST['status_ketersediaan']) : '';
    $bulan = isset($_POST['bulan']) ? trim($_POST['bulan']) : '';
    $tahun = isset($_POST['tahun']) ? trim($_POST['tahun']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    
    $jumlah_benih = (!empty($_POST['jumlah_benih'])) ? $_POST['jumlah_benih'] : 0;
    $harga_satuan = (!empty($_POST['harga_satuan'])) ? $_POST['harga_satuan'] : 0;

    // --- LOGIKA MULTI-DISTRIBUSI & LOKASI ---
    $dist_qtys = $_POST['dist_qty'] ?? [];
    $dist_targets = $_POST['dist_target'] ?? [];
    $dist_lokasis = $_POST['dist_lokasi'] ?? [];
    $list_penerima = [];
    $total_volume = 0;
    $kumpulan_lokasi = [];

    foreach ($dist_qtys as $index => $qty) {
        $target = trim($dist_targets[$index] ?? '');
        $lokasi = trim($dist_lokasis[$index] ?? '');

        if (!empty($qty) || !empty($target) || !empty($lokasi)) {
            $list_penerima[] = [
                'qty' => $qty ?: 0,
                'target' => $target ?: '-',
                'lokasi' => $lokasi ?: '-'
            ];
            $total_volume += (int)$qty;
            if(!empty($lokasi)) {
                $kumpulan_lokasi[] = $lokasi;
            }
        }
    }

    $penerima_manfaat_json = json_encode($list_penerima);
    $volume_penyaluran = $total_volume;
    $lokasi_final = implode(', ', array_unique($kumpulan_lokasi)); // Gabungkan jadi teks
    // ---------------------------------------------

    // Query Update tanpa Validasi Wajib Isi (Tambahkan update lokasi_distribusi)
    $query_update = "UPDATE laporan SET 
                    komoditas = '" . mysqli_real_escape_string($conn, $komoditas) . "',
                    kelompok_komoditas = '" . mysqli_real_escape_string($conn, $kelompok_komoditas) . "',
                    satuan = '" . mysqli_real_escape_string($conn, $satuan) . "',
                    harga_satuan = '" . mysqli_real_escape_string($conn, $harga_satuan) . "',
                    varietas = '" . mysqli_real_escape_string($conn, $varietas) . "',
                    kelas_benih = '" . mysqli_real_escape_string($conn, $kelas_benih) . "',
                    status_ketersediaan = '" . mysqli_real_escape_string($conn, $status_ketersediaan) . "',
                    jumlah_benih = '" . mysqli_real_escape_string($conn, $jumlah_benih) . "',
                    volume_penyaluran = '" . mysqli_real_escape_string($conn, $volume_penyaluran) . "',
                    penerima_manfaat = '" . mysqli_real_escape_string($conn, $penerima_manfaat_json) . "',
                    lokasi_distribusi = '" . mysqli_real_escape_string($conn, $lokasi_final) . "',
                    bulan = '" . mysqli_real_escape_string($conn, $bulan) . "',
                    tahun = '" . mysqli_real_escape_string($conn, $tahun) . "',
                    deskripsi = '" . mysqli_real_escape_string($conn, $deskripsi) . "'
                    WHERE id_laporan = " . $id_laporan;
    
    if($_SESSION['role'] == 'operator') {
        $balai_id = $_SESSION['balai_id'];
        $query_update .= " AND balai_id = " . (int)$balai_id;
    }
    
    $result = mysqli_query($conn, $query_update);
    
    if($result) {
        $_SESSION['success'] = "Perubahan berhasil disimpan!";
        header("Location: " . $url_kembali_sukses);
        exit();
    } else {
        $_SESSION['error'] = "Gagal menyimpan: " . mysqli_error($conn);
        header("Location: edit_laporan.php?id=" . $id_laporan);
        exit();
    }
} else {
    header("Location: " . $url_kembali_sukses);
    exit();
}
?>