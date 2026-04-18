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

// Cek role - hanya operator yang bisa tambah laporan
if($_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$balai_id = $_SESSION['balai_id'];

// Proses tambah laporan
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $komoditas = trim($_POST['komoditas'] ?? '');
    $kelompok_komoditas = trim($_POST['kelompok_komoditas'] ?? '');
    $satuan = trim($_POST['satuan'] ?? '');
    $harga_satuan = trim($_POST['harga_satuan'] ?? '');
    $varietas = trim($_POST['varietas'] ?? '');
    $kelas_benih = trim($_POST['kelas_benih'] ?? '');
    $status_ketersediaan = trim($_POST['status_ketersediaan'] ?? '');
    $jumlah_benih = trim($_POST['jumlah_benih'] ?? '');
    $bulan = trim($_POST['bulan'] ?? '');
    $tahun = trim($_POST['tahun'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Validasi data
    $errors = [];
    $field_labels = [
        'komoditas' => 'Komoditas',
        'kelas_benih' => 'Kelas Benih',
        'jumlah_benih' => 'Jumlah Stok',
        'satuan' => 'Satuan',
        'status_ketersediaan' => 'Keterangan',
        'bulan' => 'Bulan',
        'tahun' => 'Tahun'
    ];
    
    // Pengecekan field wajib
    if($status_ketersediaan === '') $errors[] = 'status_ketersediaan';
    if($bulan === '') $errors[] = 'bulan';
    if($tahun === '') $errors[] = 'tahun';
    if($kelas_benih === '') $errors[] = 'kelas_benih';
    if($satuan === '') $errors[] = 'satuan';
    
    // Komoditas opsional - hanya wajib jika varietas kosong
    if($komoditas === '' && $varietas === '') {
        $errors[] = 'komoditas';
    }

    // --- KUNCI ANTI FATAL ERROR (Cegah Teks Kosong di Kolom Angka) ---
    if($jumlah_benih === '') {
        $errors[] = 'jumlah_benih';
        $jumlah_benih = 0; 
    }
    
    if($harga_satuan === '') {
        $harga_satuan = 0; // Harga boleh 0 jika memang kosong (tidak masuk errors wajib)
    }
    
    // Jika ada error, simpan data ke session dan kembali ke form
    if(!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        
        // Simpan semua data yang sudah diisi agar user tidak capek ngetik ulang
        $_SESSION['form_data'] = [
            'komoditas' => $komoditas,
            'kelompok_komoditas' => $kelompok_komoditas,
            'satuan' => $satuan,
            'harga_satuan' => ($harga_satuan == 0) ? '' : $harga_satuan, 
            'varietas' => $varietas,
            'kelas_benih' => $kelas_benih,
            'status_ketersediaan' => $status_ketersediaan,
            'jumlah_benih' => ($jumlah_benih == 0 && in_array('jumlah_benih', $errors)) ? '' : $jumlah_benih,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'deskripsi' => $deskripsi
        ];
        
        // Buat pesan error yang lebih informatif
        $error_fields = [];
        foreach($errors as $error_field) {
            $error_fields[] = $field_labels[$error_field] ?? $error_field;
        }
        
        $error_message = "Harap lengkapi data berikut: " . implode(', ', $error_fields);
        $_SESSION['error'] = $error_message;
        
        header("Location: tambah_laporan.php");
        exit();
    }
    
    // Query insert laporan
    $query_insert = "INSERT INTO laporan (balai_id, komoditas, kelompok_komoditas, satuan, harga_satuan, varietas, kelas_benih, status_ketersediaan, jumlah_benih, bulan, tahun, deskripsi) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query_insert);
    $komoditas_value = empty($komoditas) ? '' : $komoditas;
    
    mysqli_stmt_bind_param($stmt, "isssssssssss", $balai_id, $komoditas_value, $kelompok_komoditas, $satuan, $harga_satuan, $varietas, $kelas_benih, $status_ketersediaan, $jumlah_benih, $bulan, $tahun, $deskripsi);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Laporan berhasil ditambahkan!";
        header("Location: riwayat_laporan.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan laporan: " . mysqli_error($conn);
        header("Location: tambah_laporan.php");
        exit();
    }
} else {
    header("Location: tambah_laporan.php");
    exit();
}
?>