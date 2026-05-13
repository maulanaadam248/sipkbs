<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

if(!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit(); }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $balai_id = $_POST['target_balai_id'];
    $komoditas = trim($_POST['komoditas'] ?? '');
    $kelompok_komoditas = trim($_POST['kelompok_komoditas'] ?? '');
    $satuan = trim($_POST['satuan'] ?? '');
    $harga_satuan = (!empty($_POST['harga_satuan'])) ? $_POST['harga_satuan'] : 0;
    $varietas = trim($_POST['varietas'] ?? '');
    $kelas_benih = trim($_POST['kelas_benih'] ?? '');
    $status_ketersediaan = trim($_POST['status_ketersediaan'] ?? '');
    $jumlah_benih = (!empty($_POST['jumlah_benih'])) ? $_POST['jumlah_benih'] : 0;
    $bulan = trim($_POST['bulan'] ?? '');
    $tahun = trim($_POST['tahun'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // --- LOGIKA MULTI-DISTRIBUSI & LOKASI ---
    $dist_qtys = $_POST['dist_qty'] ?? [];
    $dist_targets = $_POST['dist_target'] ?? [];
    $dist_lokasis = $_POST['dist_lokasi'] ?? []; // Tangkap Lokasi
    $list_penerima = [];
    $total_volume = 0;
    $kumpulan_lokasi = [];

    foreach ($dist_qtys as $index => $qty) {
        $target = trim($dist_targets[$index] ?? '');
        $lokasi = trim($dist_lokasis[$index] ?? '');
        
        // Simpan ke array jika salah satu form diisi
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
    
    // Bungkus data untuk masuk database
    $penerima_manfaat_json = json_encode($list_penerima);
    $volume_penyaluran = $total_volume;
    // Gabungkan lokasi (contoh: Bogor, Sukabumi)
    $lokasi_final = implode(', ', array_unique($kumpulan_lokasi));

    // 15 KOLOM (Termasuk lokasi_distribusi)
    $query_insert = "INSERT INTO laporan (balai_id, komoditas, kelompok_komoditas, satuan, harga_satuan, varietas, kelas_benih, status_ketersediaan, jumlah_benih, volume_penyaluran, penerima_manfaat, lokasi_distribusi, bulan, tahun, deskripsi) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query_insert);
    
    // Binding 15 Parameter (i = integer, s = string)
    mysqli_stmt_bind_param($stmt, "issssssssssssss", 
        $balai_id, $komoditas, $kelompok_komoditas, $satuan, $harga_satuan, 
        $varietas, $kelas_benih, $status_ketersediaan, $jumlah_benih, 
        $volume_penyaluran, $penerima_manfaat_json, $lokasi_final, $bulan, $tahun, $deskripsi
    );
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Laporan berhasil ditambahkan!";
        header("Location: riwayat_laporan.php");
    } else {
        $_SESSION['error'] = "Gagal: " . mysqli_error($conn);
        header("Location: tambah_laporan.php");
    }
    exit();
}
?>