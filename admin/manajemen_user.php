<?php
session_start();
require_once '../config/database.php';

// Fungsi sinkronisasi warna Balai (Ditambahkan di sini agar sinkron)
if (!function_exists('getBalaiColor')) {
    function getBalaiColor($nama_balai) {
        $balai = strtoupper($nama_balai);
        $colors = ['PALMA' => '#28a745', 'TRI' => '#007bff', 'TAS' => '#fd7e14', 'TROA' => '#6f42c1'];
        return isset($colors[$balai]) ? $colors[$balai] : '#6c757d';
    }
}

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

<style>
    /* TEMA TABEL HIJAU MUDA ADEM */
    .table-green-theme {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        border: 1px solid #d1fae5 !important;
        width: 100% !important;
        background-color: #ffffff !important;
    }
    
    .table-green-theme thead th {
        background-color: #d1fae5 !important; /* Hijau mint kalem */
        color: #065f46 !important; /* Teks hijau zamrud gelap */
        border-bottom: 2px solid #a7f3d0 !important;
        border-top: none !important;
        padding: 14px 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
        vertical-align: middle !important;
    }

    .table-green-theme tbody tr {
        background-color: #ffffff !important;
        transition: all 0.2s ease !important;
    }

    .table-green-theme tbody tr:nth-of-type(even) {
        background-color: #f6fdf9 !important;
    }

    .table-green-theme tbody tr:hover {
        background-color: #ecfdf5 !important;
    }

    .table-green-theme tbody td {
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        padding: 12px 15px !important;
        color: #334155 !important;
        vertical-align: middle !important;
    }
</style>

<main class="main-content">
    <div class="container-fluid px-4 py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Manajemen User</h2>
                <p class="text-muted mb-0">Kelola data pengguna sistem dan hak akses</p>
            </div>
            <div>
                <a href="tambah_user.php" class="btn btn-success px-4 py-2 rounded-3 shadow-sm fw-bold d-inline-flex align-items-center" style="transition: all 0.3s ease;">
                    <i class="fas fa-plus me-2"></i> Tambah User
                </a>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-filter me-2"></i>Filter Pencarian</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter_role" class="form-label text-muted small fw-bold">Hak Akses (Role)</label>
                        <select class="form-select border-0 bg-light" id="filter_role" name="role">
                            <option value="">Semua Role</option>
                            <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="operator" <?= (isset($_GET['role']) && $_GET['role'] == 'operator') ? 'selected' : ''; ?>>Operator</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filter_balai" class="form-label text-muted small fw-bold">Balai / Instansi</label>
                        <select class="form-select border-0 bg-light" id="filter_balai" name="balai_id">
                            <option value="">Semua Balai</option>
                            <?php
                            $query_balai = "SELECT * FROM balai ORDER BY nama_balai";
                            $result_balai = mysqli_query($conn, $query_balai);
                            while($balai = mysqli_fetch_assoc($result_balai)):
                            ?>
                                <option value="<?= $balai['id_balai']; ?>" <?= (isset($_GET['balai_id']) && $_GET['balai_id'] == $balai['id_balai']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($balai['nama_balai']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="search" class="form-label text-muted small fw-bold">Cari Nama / Username</label>
                        <input type="text" class="form-control border-0 bg-light" id="search" name="search" placeholder="Ketik kata kunci..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>

                    <div class="col-md-2">
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-success flex-grow-1 rounded-3"><i class="fas fa-search"></i> Cari</button>
                            <a href="manajemen_user.php" class="btn btn-light border" title="Reset Filter"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-5">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-users me-2"></i>Daftar Pengguna</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive px-2 pb-2 pt-2">
                    <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th width="20%">Nama Lengkap</th>
                                <th width="15%">Username</th>
                                <th width="15%">Role</th>
                                <th width="30%">Instansi / Balai</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $where_conditions = [];

                            if(isset($_GET['role']) && !empty($_GET['role'])) {
                                $where_conditions[] = "u.role = '" . mysqli_real_escape_string($conn, $_GET['role']) . "'";
                            }
                            if(isset($_GET['balai_id']) && !empty($_GET['balai_id'])) {
                                $where_conditions[] = "u.balai_id = " . (int)$_GET['balai_id'];
                            }
                            if(isset($_GET['search']) && !empty($_GET['search'])) {
                                $search_term = mysqli_real_escape_string($conn, $_GET['search']);
                                $where_conditions[] = "(u.username LIKE '%$search_term%' OR u.nama LIKE '%$search_term%')";
                            }

                            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

                            $query = "SELECT u.*, b.nama_balai 
                                     FROM users u 
                                     LEFT JOIN balai b ON u.balai_id = b.id_balai 
                                     $where_clause 
                                     ORDER BY u.role ASC, u.nama ASC";

                            $result = mysqli_query($conn, $query);
                            $no = 1;

                            if(mysqli_num_rows($result) > 0):
                                while($user = mysqli_fetch_assoc($result)):
                                    // Tarik warna dinamis balai
                                    $warna_balai_user = $user['nama_balai'] ? getBalaiColor($user['nama_balai']) : '#6c757d';
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-bold text-success"><?= htmlspecialchars($user['nama']); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted fw-medium">@<?= htmlspecialchars($user['username']); ?></div>
                                    </td>
                                    <td>
                                        <?php if($user['role'] == 'admin'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-2">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-2">Operator</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($user['nama_balai']): ?>
                                            <span class="badge px-3 py-2 rounded-2 shadow-sm" style="background-color: <?= $warna_balai_user; ?>; color: white;">
                                                <?= htmlspecialchars($user['nama_balai']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-dark bg-opacity-10 text-dark border border-dark px-3 py-2 rounded-2 fst-italic">
                                                - Semua Akses -
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm rounded-3 overflow-hidden shadow-sm" role="group">
                                            <a href="edit_user.php?id=<?= $user['id_user']; ?>" class="btn btn-white border" title="Edit Data"><i class="fas fa-edit text-primary"></i></a>
                                            <a href="hapus_user.php?id=<?= $user['id_user']; ?>" class="btn btn-white border" title="Hapus User" onclick="konfirmasiHapus(event, this.href)"><i class="fas fa-trash text-danger"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted bg-white">
                                        <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0 fw-bold">Oops! Data pengguna tidak ditemukan.</p>
                                        <small>Coba gunakan filter pencarian yang berbeda.</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function konfirmasiHapus(event, url_hapus) {
    event.preventDefault();
    Swal.fire({
        title: 'Hapus Pengguna?',
        text: "Akun ini akan dihapus permanen dari sistem!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url_hapus;
        }
    });
}
</script>

<?php require_once '../templates/footer.php'; ?>