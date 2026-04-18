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

$page_title = "Test URL Generation";
$current_page = 'test';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container-modern">
        <div class="header-section">
            <div>
                <h1 class="page-title">Test URL Generation</h1>
                <p class="page-subtitle">Testing URL generation for foto links</p>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">URL Generation Test</h3>
            
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Test Case</th>
                            <th>Input Path</th>
                            <th>getFileUrl() Result</th>
                            <th>getValidFileUrl() Result</th>
                            <th>Test Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $testCases = [
                            'assets/uploads/test.jpg',
                            'uploads/test.jpg',
                            'test.jpg',
                            'assets/images/test.jpg'
                        ];
                        
                        foreach ($testCases as $testCase) {
                            $fileUrl = getFileUrl($testCase);
                            $validUrl = getValidFileUrl($testCase);
                            
                            echo '<tr>';
                            echo '<td>Test ' . ($testCase) . '</td>';
                            echo '<td><code>' . htmlspecialchars($testCase) . '</code></td>';
                            echo '<td><code>' . htmlspecialchars($fileUrl) . '</code></td>';
                            echo '<td><code>' . htmlspecialchars($validUrl) . '</code></td>';
                            echo '<td>';
                            if ($validUrl) {
                                echo '<a href="' . $validUrl . '" target="_blank" class="btn btn-sm btn-primary">Test</a>';
                            } else {
                                echo '<span class="text-muted">No Link</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Create Test File</h3>
            <form method="POST">
                <div class="form-group-modern">
                    <label for="test_content">Test Content:</label>
                    <textarea name="test_content" id="test_content" class="form-control-modern" rows="3">This is a test image</textarea>
                </div>
                <div class="form-group-modern">
                    <label for="test_filename">Filename:</label>
                    <input type="text" name="test_filename" id="test_filename" class="form-control-modern" value="test_image.jpg">
                </div>
                <div class="form-group-modern">
                    <label for="test_folder">Folder:</label>
                    <select name="test_folder" id="test_folder" class="form-control-modern">
                        <option value="assets/uploads">assets/uploads</option>
                        <option value="uploads">uploads</option>
                        <option value="assets/images">assets/images</option>
                    </select>
                </div>
                <button type="submit" name="create_test" class="btn-modern btn-primary-modern">Create Test File</button>
            </form>
            
            <?php
            if(isset($_POST['create_test'])) {
                $content = $_POST['test_content'];
                $filename = $_POST['test_filename'];
                $folder = $_POST['test_folder'];
                
                $fullPath = '../' . $folder . '/' . $filename;
                $relativePath = $folder . '/' . $filename;
                
                if (file_put_contents($fullPath, $content)) {
                    echo '<div class="alert-modern alert-success-modern">';
                    echo '<h5>Test File Created!</h5>';
                    echo '<p>Path: ' . htmlspecialchars($fullPath) . '</p>';
                    echo '<p>Relative: ' . htmlspecialchars($relativePath) . '</p>';
                    echo '<p>getFileUrl(): ' . htmlspecialchars(getFileUrl($relativePath)) . '</p>';
                    echo '<p>getValidFileUrl(): ' . htmlspecialchars(getValidFileUrl($relativePath)) . '</p>';
                    echo '<a href="' . htmlspecialchars(getValidFileUrl($relativePath)) . '" target="_blank" class="btn btn-sm btn-primary">Open File</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert-modern alert-danger-modern">Failed to create test file!</div>';
                }
            }
            ?>
        </div>

        <div class="card-modern">
            <h3 class="card-title">Database Test</h3>
            <?php
            $query = "SELECT id_laporan, komoditas, foto FROM laporan WHERE foto IS NOT NULL AND foto != '' LIMIT 3";
            $result = mysqli_query($conn, $query);
            
            if(mysqli_num_rows($result) > 0):
            ?>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Komoditas</th>
                            <th>Database Path</th>
                            <th>Full URL</th>
                            <th>Valid URL</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($result)):
                            $fullUrl = getFileUrl($row['foto']);
                            $validUrl = getValidFileUrl($row['foto']);
                        ?>
                        <tr>
                            <td><?php echo $row['id_laporan']; ?></td>
                            <td><?php echo htmlspecialchars($row['komoditas']); ?></td>
                            <td><code><?php echo htmlspecialchars($row['foto']); ?></code></td>
                            <td><code><?php echo htmlspecialchars($fullUrl); ?></code></td>
                            <td><code><?php echo htmlspecialchars($validUrl); ?></code></td>
                            <td>
                                <?php if ($validUrl): ?>
                                    <a href="<?php echo $validUrl; ?>" target="_blank" class="btn btn-sm btn-primary">Open</a>
                                <?php else: ?>
                                    <span class="text-muted">No Link</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert-modern alert-info-modern">No foto records found in database</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>
