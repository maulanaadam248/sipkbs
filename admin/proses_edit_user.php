<?php
session_start();
require_once '../config/database.php';

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

// Proses edit user
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $user_id = trim($_POST['id_user']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $role = trim($_POST['role']);
    $balai_id = trim($_POST['balai_id']);
    
    // Validasi data
    if(empty($username) || empty($nama) || empty($role)) {
        $_SESSION['error'] = "Username, nama, dan role wajib harus diisi!";
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }
    
    // Cek username sudah ada (kecuali username user yang sedang diedit)
    $cek_username = "SELECT id_user FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' AND id_user != " . (int)$user_id;
    $result_cek = mysqli_query($conn, $cek_username);
    
    if(mysqli_num_rows($result_cek) > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }
    
    // Build query update
    $query_update = "UPDATE users SET username = '" . mysqli_real_escape_string($conn, $username) . "', 
                                       nama = '" . mysqli_real_escape_string($conn, $nama) . "', 
                                       role = '" . mysqli_real_escape_string($conn, $role) . "'";
    
    // Tambahkan balai_id jika diisi
    if(!empty($balai_id)) {
        $query_update .= ", balai_id = " . (int)$balai_id;
    } else {
        $query_update .= ", balai_id = NULL";
    }
    
    // Tambahkan password jika diisi
    if(!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query_update .= ", password = '" . $hashed_password . "'";
    }
    
    $query_update .= " WHERE id_user = " . (int)$user_id;
    
    if(mysqli_query($conn, $query_update)) {
        $_SESSION['success'] = "User berhasil diperbarui!";
        header("Location: manajemen_user.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui user: " . mysqli_error($conn);
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }
} else {
    header("Location: edit_user.php");
    exit();
}
?>
