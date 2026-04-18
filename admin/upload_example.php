<?php
session_start();
require_once '../config/database.php';
require_once '../includes/upload_handler.php';

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

$page_title = "Upload Foto Example";
$current_page = 'upload';
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
                    <h1 class="page-title">Upload Foto Example</h1>
                    <p class="page-subtitle">Contoh implementasi upload foto ke folder uploads</p>
                </div>
                <div class="header-actions">
                    <a href="semua_laporan.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($_SESSION['upload_message'])): ?>
            <div class="alert-modern alert-<?php echo $_SESSION['upload_type'] ?? 'info'; ?>-modern">
                <i class="fas fa-<?php echo $_SESSION['upload_icon'] ?? 'info'; ?>"></i>
                <?php echo htmlspecialchars($_SESSION['upload_message']); unset($_SESSION['upload_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-upload me-2"></i>
                Upload Foto Benih
            </h3>
            
            <form method="POST" action="" enctype="multipart/form-data" class="form-modern">
                <div class="form-group-modern">
                    <label for="foto" class="form-label-modern">Pilih Foto</label>
                    <input type="file" class="form-control-modern" id="foto" name="foto" accept="image/*" required>
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF (Maks: 5MB)</small>
                </div>
                
                <div class="form-group-modern">
                    <label for="deskripsi" class="form-label-modern">Deskripsi</label>
                    <textarea class="form-control-modern" id="deskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi foto..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="upload" value="upload" class="btn-modern btn-primary-modern">
                        <i class="fas fa-upload me-2"></i>
                        Upload Foto
                    </button>
                    <button type="reset" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-times me-2"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar File yang Sudah Diupload -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-images me-2"></i>
                File yang Sudah Diupload
            </h3>
            
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tipe</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $uploadsDir = '../uploads/';
                        if (is_dir($uploadsDir)) {
                            $files = scandir($uploadsDir);
                            $files = array_diff($files, ['.', '..']);
                            
                            foreach ($files as $file) {
                                $filePath = $uploadsDir . $file;
                                if (file_exists($filePath)) {
                                    $fileInfo = getUploadedFileInfo($file);
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($fileInfo['name']) . '</td>';
                                    echo '<td>' . number_format($fileInfo['size'] / 1024, 2) . ' KB</td>';
                                    echo '<td>' . htmlspecialchars($fileInfo['type']) . '</td>';
                                    echo '<td>' . date('d/m/Y H:i', $fileInfo['modified']) . '</td>';
                                    echo '<td>';
                                    echo '<button onclick="if(confirm(\'Hapus file ini?\')) { window.location.href=\'?delete=' . urlencode($file) . '\'; }" class="btn-modern btn-danger-modern btn-sm">';
                                    echo '<i class="fas fa-trash"></i> Hapus';
                                    echo '</button>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                        }
                        
                        if (empty($files)) {
                            echo '<tr><td colspan="5" class="text-center">Belum ada file yang diupload</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php 
// Handle upload
if(isset($_POST['upload'])) {
    $result = handleFileUpload($_FILES['foto']);
    
    if ($result['success']) {
        $_SESSION['upload_message'] = $result['message'];
        $_SESSION['upload_type'] = 'success';
        $_SESSION['upload_icon'] = 'check';
        
        // Simpan info ke database jika perlu
        // $query = "UPDATE laporan SET foto = '" . $result['file_path'] . "' WHERE id_laporan = [id_laporan]";
        // mysqli_query($conn, $query);
        
    } else {
        $_SESSION['upload_message'] = $result['message'];
        $_SESSION['upload_type'] = 'danger';
        $_SESSION['upload_icon'] = 'exclamation';
    }
    
    header("Location: upload_example.php");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $fileName = $_GET['delete'];
    if (deleteUploadedFile($fileName)) {
        $_SESSION['upload_message'] = 'File berhasil dihapus';
        $_SESSION['upload_type'] = 'success';
        $_SESSION['upload_icon'] = 'check';
    } else {
        $_SESSION['upload_message'] = 'File gagal dihapus';
        $_SESSION['upload_type'] = 'danger';
        $_SESSION['upload_icon'] = 'exclamation';
    }
    
    header("Location: upload_example.php");
    exit();
}

require_once '../templates/footer.php'; 
?>
