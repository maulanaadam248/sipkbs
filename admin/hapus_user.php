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

// Ambil ID user yang akan dihapus
if(!isset($_GET['id'])) {
    header("Location: manajemen_user.php");
    exit();
}

$user_id = $_GET['id'];

// Cek apakah user yang akan dihapus bukan user yang sedang login
if($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "Tidak dapat menghapus akun yang sedang digunakan!";
    header("Location: manajemen_user.php");
    exit();
}

// Cek apakah user ada
$query = "SELECT username FROM users WHERE id_user = " . (int)$user_id;
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "User tidak ditemukan!";
    header("Location: manajemen_user.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Hapus user
$delete_query = "DELETE FROM users WHERE id_user = " . (int)$user_id;

if(mysqli_query($conn, $delete_query)) {
    $_SESSION['success'] = "User '" . htmlspecialchars($user['username']) . "' berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus user: " . mysqli_error($conn);
}

header("Location: manajemen_user.php");
exit();
?>
