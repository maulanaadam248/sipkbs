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

// Ambil data user berdasarkan ID
if(!isset($_GET['id'])) {
    header("Location: manajemen_user.php");
    exit();
}

$user_id = $_GET['id'];
$query = "SELECT * FROM users WHERE id_user = " . (int)$user_id;
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "User tidak ditemukan!";
    header("Location: manajemen_user.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Ambil data balai untuk dropdown
$query_balai = "SELECT * FROM balai ORDER BY nama_balai";
$result_balai = mysqli_query($conn, $query_balai);

$page_title = "Edit User";
$current_page = 'manajemen_user';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    /* Styling khusus form input agar terlihat premium (Sama dengan Tambah User) */
    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        background-color: #ffffff !important;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15) !important;
    }

    /* Mematikan border default bg-light agar lebih clean */
    .bg-light-input {
        background-color: #f8fafc !important;
    }
</style>

<main class="bg-dashboard min-vh-100 py-4 w-100" style="background-color: #f8f9fc;">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Edit User</h2>
                <p class="text-muted mb-0">Edit data pengguna: <strong><?= htmlspecialchars($user['username']); ?></strong></p>
            </div>
            <div>
                <a href="manajemen_user.php" class="btn btn-white border px-4 py-2 rounded-3 shadow-sm fw-medium">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-user-edit me-2"></i>Form Perubahan Data User</h6>
            </div>
            
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="proses_edit_user.php">
                    <input type="hidden" name="id_user" value="<?= $user['id_user']; ?>">
                    
                    <div class="row g-4">
                        
                        <div class="col-12">
                            <label for="nama" class="form-label text-muted small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-input border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control px-3 py-2 bg-light-input border-start-0 ps-0" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" required>
                            </div>
                        </div>

                        <div class="col-12 border-bottom py-2 my-2"></div> <div class="col-md-6">
                            <label for="username" class="form-label text-muted small fw-bold">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-input border-end-0 text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control px-3 py-2 bg-light-input border-start-0 ps-0" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label text-muted small fw-bold">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-input border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control px-3 py-2 bg-light-input border-start-0 ps-0" id="password" name="password" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Biarkan kosong jika tidak ingin mengubah password.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label text-muted small fw-bold">Hak Akses (Role) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-input border-end-0 text-muted"><i class="fas fa-user-shield"></i></span>
                                <select class="form-select px-3 py-2 bg-light-input border-start-0 ps-0" id="role" name="role" required onchange="toggleBalai()">
                                    <option value="" disabled>Pilih Role</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin Pusat</option>
                                    <option value="operator" <?= $user['role'] == 'operator' ? 'selected' : ''; ?>>Operator Balai</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="balai_id" class="form-label text-muted small fw-bold">Pilih Instansi / Balai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-input border-end-0 text-muted"><i class="fas fa-building"></i></span>
                                <select class="form-select px-3 py-2 bg-light-input border-start-0 ps-0" id="balai_id" name="balai_id">
                                    <option value="">Tidak terikat instansi (Semua Akses)</option>
                                    <?php 
                                    mysqli_data_seek($result_balai, 0);
                                    while($balai = mysqli_fetch_assoc($result_balai)): 
                                    ?>
                                        <option value="<?= $balai['id_balai']; ?>" <?= ($user['balai_id'] == $balai['id_balai']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($balai['nama_balai']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Wajib diisi jika role adalah Operator.</small>
                        </div>

                    </div>
                    
                    <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                        <a href="manajemen_user.php" class="btn btn-light border px-4 py-2 rounded-3 me-3 fw-medium">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success px-4 py-2 rounded-3 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</main>

<script>
    function toggleBalai() {
        const role = document.getElementById('role').value;
        const balaiSelect = document.getElementById('balai_id');
        
        if (role === 'admin') {
            balaiSelect.value = "";
        }
    }
</script>

<?php require_once '../templates/footer.php'; ?>