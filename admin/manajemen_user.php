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

$page_title = "Manajemen User";
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
                    <h1 class="page-title">Manajemen User</h1>
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

        <!-- Filter Section -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-filter me-2"></i>
                Filter User
            </h3>
            <form method="GET" class="filter-form-modern">
                <div class="filter-grid">
                    <div class="form-group-modern">
                        <label for="filter_role" class="form-label-modern">Filter Role</label>
                        <select class="form-control-modern" id="filter_role" name="role">
                            <option value="">Semua Role</option>
                            <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="operator" <?php echo (isset($_GET['role']) && $_GET['role'] == 'operator') ? 'selected' : ''; ?>>Operator</option>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label for="filter_balai" class="form-label-modern">Filter Balai</label>
                        <select class="form-control-modern" id="filter_balai" name="balai_id">
                            <option value="">Semua Balai</option>
                            <?php
                            $query_balai = "SELECT * FROM balai ORDER BY nama_balai";
                            $result_balai = mysqli_query($conn, $query_balai);
                            while($balai = mysqli_fetch_assoc($result_balai)):
                            ?>
                                <option value="<?php echo $balai['id_balai']; ?>" <?php echo (isset($_GET['balai_id']) && $_GET['balai_id'] == $balai['id_balai']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($balai['nama_balai']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label for="search" class="form-label-modern">Cari User</label>
                        <input type="text" class="form-control-modern" id="search" name="search" placeholder="Cari berdasarkan nama atau username" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-search me-2"></i>
                        Cari
                    </button>
                    <a href="manajemen_user.php" class="btn-modern btn-secondary-modern">
                        <i class="fas fa-times me-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel User -->
        <div class="card-modern">
            <h3 class="card-title">
                <i class="fas fa-users me-2"></i>
                Data User
            </h3>
            <?php
            // Tampilkan pesan sukses/error
            if(isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>' . $_SESSION['success'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
                unset($_SESSION['success']);
            }
            
            if(isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>' . $_SESSION['error'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
                unset($_SESSION['error']);
            }
            ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Balai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query dengan filter
                        $where_conditions = [];

                        if(isset($_GET['role']) && !empty($_GET['role'])) {
                            $where_conditions[] = "u.role = '" . mysqli_real_escape_string($conn, $_GET['role']) . "'";
                        }

                        if(isset($_GET['balai_id']) && !empty($_GET['balai_id'])) {
                            $where_conditions[] = "u.balai_id = " . (int)$_GET['balai_id'];
                        }

                        if(isset($_GET['search']) && !empty($_GET['search'])) {
                            $where_conditions[] = "(u.username LIKE '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%' OR u.nama LIKE '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%')";
                        }

                        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

                        $query = "SELECT u.*, b.nama_balai 
                                 FROM users u 
                                 LEFT JOIN balai b ON u.balai_id = b.id_balai 
                                 $where_clause 
                                 ORDER BY u.nama ASC";

                        $result = mysqli_query($conn, $query);

                        $no = 1;
                        if(mysqli_num_rows($result) > 0):
                            while($user = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['nama_balai'] ? htmlspecialchars($user['nama_balai']) : '-'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_user.php?id=<?php echo $user['id_user']; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus_user.php?id=<?php echo $user['id_user']; ?>" class="btn btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data user</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>
