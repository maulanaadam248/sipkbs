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

// Ambil data balai untuk dropdown
$query_balai = "SELECT * FROM balai ORDER BY nama_balai";
$result_balai = mysqli_query($conn, $query_balai);

$page_title = "Tambah User";
$current_page = 'manajemen_user';
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
                    <h1 class="page-title">Tambah User</h1>
                    <p class="page-subtitle">Tambahkan pengguna baru ke sistem</p>
                </div>
                <div class="header-actions">
                    <a href="manajemen_user.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form User -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-user-plus me-2"></i>
                Form Tambah User
            </h3>
            <form method="POST" action="proses_tambah_user.php" class="filter-form-modern">
                <?php
                // Tampilkan pesan error
                if(isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>' . $_SESSION['error'] . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
                    unset($_SESSION['error']);
                }
                
                // Tampilkan pesan sukses
                if(isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>' . $_SESSION['success'] . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
                    unset($_SESSION['success']);
                }
                ?>
                
                <div class="filter-grid">
                    <div class="form-group-modern">
                        <label for="username" class="form-label-modern">Username *</label>
                        <input type="text" class="form-control-modern" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="password" class="form-label-modern">Password *</label>
                        <input type="password" class="form-control-modern" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="nama" class="form-label-modern">Nama Lengkap *</label>
                        <input type="text" class="form-control-modern" id="nama" name="nama" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="email" class="form-label-modern">Email</label>
                        <input type="email" class="form-control-modern" id="email" name="email">
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="role" class="form-label-modern">Role *</label>
                        <select class="form-control-modern" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="balai_id" class="form-label-modern">Balai</label>
                        <select class="form-control-modern" id="balai_id" name="balai_id">
                            <option value="">Pilih Balai</option>
                            <?php while($balai = mysqli_fetch_assoc($result_balai)): ?>
                                <option value="<?php echo $balai['id_balai']; ?>"><?php echo htmlspecialchars($balai['nama_balai']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <a href="manajemen_user.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-times me-2"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save me-2"></i>
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>
