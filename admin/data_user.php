<?php
session_start();
require_once '../config/database.php';
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

$page_title = "Data User";
$current_page = 'data_user';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container-modern">
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Data User</h1>
                    <p class="page-subtitle">Kelola data pengguna sistem</p>
                </div>
                <div class="header-actions">
                    <a href="tambah_user.php" class="btn-modern btn-primary-modern">
                        <i class="fas fa-plus me-2"></i>
                        Tambah User
                    </a>
                </div>
            </div>
        </div>

        <!-- User Table -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-users me-2"></i>
                Data User
            </h3>
            
            <div class="table-container-modern">
                <table class="table-modern table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Balai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data user dengan join balai
                        $query = "SELECT u.*, b.nama_balai 
                                 FROM users u 
                                 LEFT JOIN balai b ON u.balai_id = b.id_balai 
                                 ORDER BY u.username";
                        $result = mysqli_query($conn, $query);
                        $no = 1;
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . $no . '</td>';
                                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                echo '<td><span class="badge bg-' . ($row['role'] == 'admin' ? 'danger' : 'primary') . '">' . ucfirst($row['role']) . '</span></td>';
                                echo '<td>' . htmlspecialchars($row['nama_balai']) . '</td>';
                                echo '<td><span class="badge bg-' . ($row['is_active'] ? 'success' : 'secondary') . '">' . ($row['is_active'] ? 'Aktif' : 'Tidak Aktif') . '</span></td>';
                                echo '<td>';
                                echo '<div class="table-actions">';
                                echo '<a href="edit_user.php?id=' . $row['id'] . '" class="btn-action-modern btn-action-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                                echo '<a href="hapus_user.php?id=' . $row['id'] . '" class="btn-action-modern btn-action-delete" onclick="return confirm(\'Yakin ingin menghapus user ini?\')">
                                        <i class="fas fa-trash"></i>
                                    </a>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                                $no++;
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center">Belum ada data user</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>