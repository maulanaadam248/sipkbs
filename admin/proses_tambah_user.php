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

// Proses tambah user
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $role = trim($_POST['role']);
    $balai_id = trim($_POST['balai_id']);
    
    // Validasi data
    if(empty($username) || empty($password) || empty($nama) || empty($role)) {
        $_SESSION['error'] = "Username, password, nama, dan role wajib harus diisi!";
        header("Location: tambah_user.php");
        exit();
    }
    
    // Cek username sudah ada
    $cek_username = "SELECT id_user FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'";
    $result_cek = mysqli_query($conn, $cek_username);
    
    if(mysqli_num_rows($result_cek) > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: tambah_user.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Query insert user - sesuai struktur database yang ada
    $query_insert = "INSERT INTO users (username, password, nama, role, balai_id) 
                    VALUES ('" . mysqli_real_escape_string($conn, $username) . "', 
                           '" . $hashed_password . "', 
                           '" . mysqli_real_escape_string($conn, $nama) . "', 
                           '" . mysqli_real_escape_string($conn, $role) . "', 
                           " . ($balai_id ? (int)$balai_id : "NULL") . ")";
    
    if(mysqli_query($conn, $query_insert)) {
        $_SESSION['success'] = "User berhasil ditambahkan!";
        header("Location: manajemen_user.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan user: " . mysqli_error($conn);
        header("Location: tambah_user.php");
        exit();
    }
} else {
    header("Location: tambah_user.php");
    exit();
}
?>
