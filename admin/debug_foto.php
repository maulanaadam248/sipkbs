<?php
session_start();
require_once '../config/database.php';
require_once '../includes/file_helper.php';

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

$page_title = "Debug Foto";
$current_page = 'debug';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container-modern">
        <div class="header-section">
            <div>
                <h1 class="page-title">Debug Foto Path</h1>
                <p class="page-subtitle">Testing path foto dan URL generation</p>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Path Information</h3>
            <div class="alert-modern alert-info-modern">
                <h5>Server Information:</h5>
                <ul>
                    <li><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
                    <li><strong>HTTP Host:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></li>
                    <li><strong>Protocol:</strong> <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'); ?></li>
                    <li><strong>Base URL:</strong> <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/sipkbs2/'; ?></li>
                </ul>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Folder Structure Check</h3>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Folder</th>
                            <th>Path</th>
                            <th>Exists</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $folders = [
                            'assets/uploads' => $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/assets/uploads/',
                            'uploads' => $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/uploads/',
                            'assets/images' => $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/assets/images/',
                            'root' => $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/'
                        ];
                        
                        foreach ($folders as $name => $path) {
                            $exists = is_dir($path);
                            $files = '';
                            if ($exists) {
                                $fileList = scandir($path);
                                $fileList = array_diff($fileList, ['.', '..']);
                                $files = implode(', ', array_slice($fileList, 0, 5));
                                if (count($fileList) > 5) {
                                    $files .= '... (' . count($fileList) . ' total)';
                                }
                            }
                            
                            echo '<tr>';
                            echo '<td>' . $name . '</td>';
                            echo '<td><code>' . $path . '</code></td>';
                            echo '<td>' . ($exists ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>') . '</td>';
                            echo '<td><small>' . ($files ?: '-') . '</small></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Database Foto Test</h3>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Komoditas</th>
                            <th>Database Path</th>
                            <th>Valid URL</th>
                            <th>Full URL</th>
                            <th>Test Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id_laporan, komoditas, foto FROM laporan WHERE foto IS NOT NULL AND foto != '' LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0):
                            while($row = mysqli_fetch_assoc($result)):
                                $validUrl = getValidFileUrl($row['foto']);
                                $fullUrl = getFileUrl($row['foto']);
                                
                                echo '<tr>';
                                echo '<td>' . $row['id_laporan'] . '</td>';
                                echo '<td>' . htmlspecialchars($row['komoditas']) . '</td>';
                                echo '<td><code>' . htmlspecialchars($row['foto']) . '</code></td>';
                                echo '<td>' . ($validUrl ? '<span class="badge bg-success">Valid</span>' : '<span class="badge bg-danger">Invalid</span>') . '</td>';
                                echo '<td><code>' . htmlspecialchars($fullUrl) . '</code></td>';
                                echo '<td>';
                                if ($validUrl) {
                                    echo '<a href="' . $validUrl . '" target="_blank" class="btn btn-sm btn-primary">Test Link</a>';
                                } else {
                                    echo '<span class="text-muted">No Link</span>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            endwhile;
                        else:
                            echo '<tr><td colspan="6" class="text-center">No foto records found</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Manual Upload Test</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group-modern">
                    <label for="test_file">Upload Test Image:</label>
                    <input type="file" name="test_file" id="test_file" class="form-control-modern" accept="image/*">
                </div>
                <button type="submit" name="upload_test" class="btn-modern btn-primary-modern">Upload & Test</button>
            </form>
            
            <?php
            if(isset($_POST['upload_test']) && !empty($_FILES['test_file']['name'])) {
                $uploadDir = '../uploads/';
                $fileName = 'test_' . time() . '_' . $_FILES['test_file']['name'];
                $uploadPath = $uploadDir . $fileName;
                
                if(move_uploaded_file($_FILES['test_file']['tmp_name'], $uploadPath)) {
                    echo '<div class="alert-modern alert-success-modern">';
                    echo '<h5>Upload Success!</h5>';
                    echo '<p>File: ' . htmlspecialchars($fileName) . '</p>';
                    echo '<p>Path: ' . htmlspecialchars($uploadPath) . '</p>';
                    echo '<p>Valid URL: ' . htmlspecialchars(getValidFileUrl($fileName)) . '</p>';
                    echo '<p>Full URL: ' . htmlspecialchars(getFileUrl($fileName)) . '</p>';
                    echo '<a href="' . htmlspecialchars(getValidFileUrl($fileName)) . '" target="_blank" class="btn btn-sm btn-primary">Test Link</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert-modern alert-danger-modern">Upload failed!</div>';
                }
            }
            ?>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>
