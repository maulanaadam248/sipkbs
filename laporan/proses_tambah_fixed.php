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
    $komoditas = trim($_POST['komoditas']);
    $kelompok_komoditas = trim($_POST['kelompok_komoditas']);
    $satuan = trim($_POST['satuan']);
    $harga_satuan = trim($_POST['harga_satuan']);
    $varietas = trim($_POST['varietas']);
    $kelas_benih = trim($_POST['kelas_benih']);
    $status_ketersediaan = trim($_POST['status_ketersediaan']);
    $jumlah_benih = trim($_POST['jumlah_benih']);
    $bulan = trim($_POST['bulan']);
    $tahun = trim($_POST['tahun']);
    $deskripsi = trim($_POST['deskripsi']);
    
    // Proses upload foto
    $foto = null;
    $upload_error = '';
    
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 50 * 1024 * 1024; // 50MB
        
        // Debug info
        $file_type = $_FILES['foto']['type'];
        $file_size = $_FILES['foto']['size'];
        $file_name = $_FILES['foto']['name'];
        $file_tmp = $_FILES['foto']['tmp_name'];
        
        // Check file type dengan cara yang lebih fleksibel
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png'];
        
        $is_valid_type = in_array($file_type, $allowed_types) || in_array($file_extension, $valid_extensions);
        $is_valid_size = $file_size <= $max_size;
        
        if($is_valid_type && $is_valid_size) {
            $foto_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file_name);
            $upload_path = '../assets/uploads/' . $foto_name;
            
            if(move_uploaded_file($file_tmp, $upload_path)) {
                $foto = $foto_name;
            } else {
                $upload_error = 'Gagal memindahkan file ke folder uploads';
            }
        } else {
            if(!$is_valid_type) {
                $upload_error = 'Tipe file tidak valid. Hanya JPG, JPEG, PNG yang diperbolehkan. Tipe file: ' . $file_type;
            } else if(!$is_valid_size) {
                $upload_error = 'Ukuran file terlalu besar. Maksimal 50MB.';
            }
        }
    } elseif(isset($_FILES['foto']) && $_FILES['foto']['error'] != 0) {
        $upload_error = 'Error upload: ' . $_FILES['foto']['error'];
    }
    
    // Validasi data
    $errors = [];
    $field_labels = [
        'komoditas' => 'Komoditas',
        'status_ketersediaan' => 'Keterangan',
        'bulan' => 'Bulan',
        'tahun' => 'Tahun'
    ];
    
    // Field yang wajib diisi
    if(empty($komoditas)) {
        $errors[] = 'komoditas';
    }
    
    if(empty($status_ketersediaan)) {
        $errors[] = 'status_ketersediaan';
    }
    
    if(empty($bulan)) {
        $errors[] = 'bulan';
    }
    
    if(empty($tahun)) {
        $errors[] = 'tahun';
    }
    
    // Jika ada error, simpan data ke session dan kembali ke form
    if(!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        
        // Simpan semua data yang sudah diisi
        $_SESSION['form_data'] = [
            'komoditas' => $komoditas,
            'kelompok_komoditas' => $kelompok_komoditas,
            'satuan' => $satuan,
            'harga_satuan' => $harga_satuan,
            'varietas' => $varietas,
            'kelas_benih' => $kelas_benih,
            'status_ketersediaan' => $status_ketersediaan,
            'jumlah_benih' => $jumlah_benih,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'deskripsi' => $deskripsi
        ];
        
        // Buat pesan error yang lebih informatif
        $error_fields = [];
        foreach($errors as $error_field) {
            $error_fields[] = $field_labels[$error_field] ?? $error_field;
        }
        
        $error_message = "Field berikut wajib diisi: " . implode(', ', $error_fields);
        $_SESSION['error'] = $error_message;
        
        header("Location: tambah_laporan.php");
        exit();
    }
    
    // Query insert laporan
    $query_insert = "INSERT INTO laporan (balai_id, komoditas, kelompok_komoditas, satuan, harga_satuan, varietas, kelas_benih, status_ketersediaan, jumlah_benih, bulan, tahun, deskripsi, foto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query_insert);
    
    if($stmt === false) {
        $_SESSION['error'] = "Query preparation failed: " . mysqli_error($conn);
        header("Location: tambah_laporan.php");
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "issssssssssss", $balai_id, $komoditas, $kelompok_komoditas, $satuan, $harga_satuan, $varietas, $kelas_benih, $status_ketersediaan, $jumlah_benih, $bulan, $tahun, $deskripsi, $foto);
    
    if(mysqli_stmt_execute($stmt)) {
        $success_msg = "Laporan berhasil ditambahkan!";
        if(!empty($upload_error)) {
            $success_msg .= " (Note: Foto tidak terupload - " . $upload_error . ")";
        }
        $_SESSION['success'] = $success_msg;
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
