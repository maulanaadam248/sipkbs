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

<!-- Main Content -->
<main class="main-content">
    <div class="container-modern">
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Edit User</h1>
                    <p class="page-subtitle">Edit data pengguna: <?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                <div class="header-actions">
                    <a href="manajemen_user.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Edit User -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-user-edit me-2"></i>
                Form Edit User
            </h3>
            <?php
            // Tampilkan pesan error
            if(isset($_SESSION['error'])) {
                echo '<div class="alert-modern alert-danger-modern">
                        <i class="fas fa-exclamation-circle"></i>
                        ' . $_SESSION['error'] . '
                    </div>';
                unset($_SESSION['error']);
            }
            
            // Tampilkan pesan sukses
            if(isset($_SESSION['success'])) {
                echo '<div class="alert-modern alert-success-modern">
                        <i class="fas fa-check-circle"></i>
                        ' . $_SESSION['success'] . '
                    </div>';
                unset($_SESSION['success']);
            }
            ?>

            <form method="POST" action="proses_edit_user.php" class="form-modern">
                <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
                
                <div class="form-row">
                    <div class="form-group-modern">
                        <label for="username" class="form-label-modern">Username *</label>
                        <input type="text" class="form-control-modern" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="nama" class="form-label-modern">Nama Lengkap *</label>
                        <input type="text" class="form-control-modern" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group-modern">
                    <label for="password" class="form-label-modern">Password</label>
                    <input type="password" class="form-control-modern" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                    <small class="form-text-modern">Biarkan kosong jika tidak ingin mengubah password</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group-modern">
                        <label for="role" class="form-label-modern">Role *</label>
                        <select class="form-control-modern" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="operator" <?php echo $user['role'] == 'operator' ? 'selected' : ''; ?>>Operator</option>
                        </select>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="balai_id" class="form-label-modern">Balai</label>
                        <select class="form-control-modern" id="balai_id" name="balai_id">
                            <option value="">Pilih Balai (Opsional)</option>
                            <?php 
                            mysqli_data_seek($result_balai, 0);
                            while($balai = mysqli_fetch_assoc($result_balai)): 
                            ?>
                                <option value="<?php echo $balai['id_balai']; ?>" <?php echo ($user['balai_id'] == $balai['id_balai']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($balai['nama_balai']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text-modern">Balai hanya diperlukan untuk role Operator</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="manajemen_user.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-times me-2"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save me-2"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>

<style>
/* Form Layout Styles */
.form-modern {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group-modern {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label-modern {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.875rem;
}

.form-control-modern {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    outline: none;
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.form-text-modern {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .form-actions .btn-modern {
        width: 100%;
        justify-content: center;
    }
}

/* Alert styles untuk edit user */
.alert-modern {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success-modern {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-danger-modern {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
</style>
