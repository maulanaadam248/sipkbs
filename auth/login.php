<?php
session_start();
require_once '../config/database.php';

// Cek jika user sudah login
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Ambil data balai dari URL
$balai_id = isset($_GET['balai']) ? (int)$_GET['balai'] : 0;

// Validasi balai
$query_balai = "SELECT * FROM balai WHERE id_balai = ?";
$stmt = mysqli_prepare($conn, $query_balai);
mysqli_stmt_bind_param($stmt, "i", $balai_id);
mysqli_stmt_execute($stmt);
$result_balai = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result_balai) == 0) {
    header("Location: ../index.php");
    exit();
}

$balai = mysqli_fetch_assoc($result_balai);

// Proses login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Query untuk login HANYA mencari berdasarkan username
    $query_login = "SELECT u.*, b.nama_balai FROM users u 
                   LEFT JOIN balai b ON u.balai_id = b.id_balai 
                   WHERE u.username = ?";
    $stmt = mysqli_prepare($conn, $query_login);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result_login = mysqli_stmt_get_result($stmt);
    
    // Jika username ditemukan
    if(mysqli_num_rows($result_login) == 1) {
        $user = mysqli_fetch_assoc($result_login);
        
        // Cek kecocokan password: 
        // 1. password_verify (untuk password baru yang sudah di-hash)
        // 2. === (fallback untuk akun lama yang password-nya belum di-hash)
        if(password_verify($password, $user['password']) || $password === $user['password']) {
            
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['balai_id'] = $user['balai_id'];
            $_SESSION['nama_balai'] = $user['nama_balai'];
            
            header("Location: ../dashboard/dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}

$page_title = "Login - " . htmlspecialchars($balai['nama_balai']);
$no_layout = true;
$current_page = 'login';
$css_path = '../assets/css/modern-ui.css?v=' . time();
$js_path = '../assets/js/script.js';
require_once '../templates/header.php';
?>

<style>
    /* Sedikit CSS untuk menyempurnakan tampilan input Bootstrap */
    body { background-color: #f8f9fc; }
    
    .form-control:focus {
        border-color: #198754;
        box-shadow: none;
    }
    
    /* Mengubah warna border saat input fokus (untuk input-group) */
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: #198754;
    }
    
    .hover-link:hover { color: #198754 !important; }
</style>

<div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container px-3" style="max-width: 420px;">
        
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 70px; height: 70px; background-color: #eefdf4; color: #16a34a; border-radius: 18px;">
                    <i class="fas fa-building fa-2x"></i>
                </div>
                <h2 class="h4 fw-bold text-dark mb-1"><?php echo htmlspecialchars($balai['nama_balai']); ?></h2>
                <p class="text-muted small">Autentikasi Dasbor SIPKBS</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger rounded-3 py-2 text-sm d-flex align-items-center" role="alert" style="font-size: 0.9rem;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="mb-3">
                    <label class="form-label text-dark fw-medium small mb-1">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user px-1"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light px-2 py-2" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-dark fw-medium small mb-1">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock px-1"></i></span>
                        <input type="password" class="form-control border-start-0 border-end-0 bg-light px-2 py-2" id="password" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="input-group-text bg-light text-muted border-start-0" onclick="togglePassword('password', 'toggleIcon')" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold mb-4 shadow-sm">
                    Masuk <i class="fas fa-sign-in-alt ms-1"></i>
                </button>
                
            </form>

            <div class="text-center">
                <a href="/sipkbs2/login.php" class="text-decoration-none text-muted small fw-medium hover-link transition">
                    <i class="fas fa-arrow-left me-1"></i> Kembali 
                </a>
            </div>
            
        </div>

        <div class="text-center mt-4">
            <p class="text-muted small mb-0"><i class="fas fa-shield-alt me-1"></i> Sistem Terenkripsi & Aman</p>
        </div>

    </div>
</div>

<script>
function togglePassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}
</script>

<?php require_once '../templates/footer.php'; ?>