<?php
session_start();
require_once '../config/database.php';

// Cek Autentikasi
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$balai_id = $_SESSION['balai_id'];
$nama_user = $_SESSION['nama'];

// Logika Query Database
if($role == 'admin') {
    $query_total = "SELECT COUNT(*) as total FROM laporan";
    $query_bulan = "SELECT COUNT(*) as total FROM laporan WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $query_balai = "SELECT b.nama_balai, COUNT(l.id_laporan) as jumlah FROM balai b LEFT JOIN laporan l ON b.id_balai = l.balai_id GROUP BY b.id_balai, b.nama_balai ORDER BY jumlah DESC";
    
    $total_laporan = mysqli_fetch_assoc(mysqli_query($conn, $query_total))['total'];
    $laporan_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, $query_bulan))['total'];
    $result_per_balai = mysqli_query($conn, $query_balai);
} else {
    $query_total = "SELECT COUNT(*) as total FROM laporan WHERE balai_id = $balai_id";
    $query_bulan = "SELECT COUNT(*) as total FROM laporan WHERE balai_id = $balai_id AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    
    $total_laporan = mysqli_fetch_assoc(mysqli_query($conn, $query_total))['total'];
    $laporan_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, $query_bulan))['total'];
    $result_per_balai = [];
}

$page_title = "Dashboard";
$current_page = 'dashboard';
$css_path = '../assets/css/modern-ui.css';
$js_path = '../assets/js/script.js';
$sidebar_dashboard_path = 'dashboard.php';

require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    .bg-dashboard { background-color: #f8f9fc; }
    
    .clean-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border: 1px solid rgba(0,0,0,0.03) !important;
    }
    .clean-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.05) !important;
    }
    
    .icon-box-lg {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* TEMA TABEL HIJAU MUDA ADEM UNTUK DASHBOARD */
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
        background-color: #d1fae5 !important;
        color: #065f46 !important;
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

<main class="bg-dashboard min-vh-100 py-4 w-100">
    <div class="container-fluid px-4">
        
        <div class="row mb-4 align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <h1 class="h4 fw-bold text-dark mb-2">Dashboard Sistem Stok Benih</h1>
                <p class="text-secondary mb-0">Selamat datang kembali, <strong><?php echo htmlspecialchars($nama_user); ?></strong>!</p>
            </div>
        </div>

        <?php if($role == 'admin'): ?>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 clean-card bg-white">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="icon-box-lg bg-success bg-opacity-10 text-success me-3">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Total Laporan</h6>
                                <h2 class="mb-0 fw-bolder text-dark"><?php echo number_format($total_laporan); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 clean-card bg-white">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="icon-box-lg bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Laporan Bulan Ini</h6>
                                <h2 class="mb-0 fw-bolder text-dark"><?php echo number_format($laporan_bulan_ini); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 clean-card bg-white">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="icon-box-lg bg-warning bg-opacity-10 text-warning me-3">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Total Balai Aktif</h6>
                                <h2 class="mb-0 fw-bolder text-dark"><?php echo mysqli_num_rows($result_per_balai); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <?php include 'components/visualisasi_data.php'; ?>
                </div>
            </div>

        <?php else: ?>

            <div class="row g-4 mb-4">
                
                <div class="col-12 col-lg-4 d-flex flex-column gap-4">
                    
                    <div class="card border-0 shadow-sm rounded-4 clean-card bg-white">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="icon-box-lg bg-success bg-opacity-10 text-success me-3">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Total Laporan</h6>
                                <h2 class="mb-0 fw-bolder text-dark"><?php echo number_format($total_laporan); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 clean-card bg-white">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="icon-box-lg bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Laporan Bulan Ini</h6>
                                <h2 class="mb-0 fw-bolder text-dark"><?php echo number_format($laporan_bulan_ini); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <?php include 'components/layanan_kami.php'; ?>
                    </div>

                </div>

                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="min-height: 450px;">
                        
                        <div class="p-4 p-lg-5 d-flex flex-column h-100">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <h5 class="fw-bold text-success mb-0">
                                    <i class="fas fa-clock me-2"></i>Laporan Terakhir
                                </h5>
                            </div>
                            
                            <div class="table-responsive px-2 flex-grow-1">
                                <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                                    <thead>
                                        <tr>
                                            <th>Komoditas & Varietas</th>
                                            <th>Kelas Benih</th>
                                            <th>Jumlah Stok</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // PENTING: Tambahkan 'kelas_benih' ke dalam query SQL
                                        $query_terbaru = "SELECT komoditas, varietas, kelompok_komoditas, kelas_benih, jumlah_benih, satuan, status_ketersediaan 
                                                          FROM laporan 
                                                          WHERE balai_id = $balai_id 
                                                          ORDER BY id_laporan DESC 
                                                          LIMIT 5";
                                        $result_terbaru = mysqli_query($conn, $query_terbaru);
                                        
                                        if (mysqli_num_rows($result_terbaru) > 0):
                                            while ($row_baru = mysqli_fetch_assoc($result_terbaru)):
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 text-success shadow-sm" style="width: 38px; height: 38px; min-width: 38px;">
                                                        <i class="fas fa-seedling"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-success" style="font-size: 0.95rem;"><?= htmlspecialchars($row_baru['komoditas']); ?></div>
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            <?= htmlspecialchars($row_baru['varietas'] ?: 'Tanpa Varietas'); ?> 
                                                            <?= !empty($row_baru['kelompok_komoditas']) ? '• ' . htmlspecialchars($row_baru['kelompok_komoditas']) : ''; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <?= htmlspecialchars($row_baru['kelas_benih'] ?: '-'); ?>
                                            </td>

                                            <td>
                                                <span class="fw-bold text-dark fs-6"><?= number_format($row_baru['jumlah_benih']); ?></span> 
                                                <span class="text-secondary fw-medium small"><?= htmlspecialchars($row_baru['satuan']); ?></span>
                                            </td>
                                            
                                            <td>
                                                <?php
                                                $status = $row_baru['status_ketersediaan'];
                                                $outline_class = 'border-secondary text-secondary'; 
                                                if($status == 'Tersedia') $outline_class = 'border-success text-success'; 
                                                elseif($status == 'Tidak Tersedia') $outline_class = 'border-danger text-danger'; 
                                                elseif($status == 'Terbatas') $outline_class = 'border-warning text-warning-emphasis'; 
                                                elseif($status == 'PO') $outline_class = 'border-primary text-primary'; 
                                                ?>
                                                <span class="badge bg-transparent border <?= $outline_class; ?> px-3 py-2 rounded-2 fw-bold" style="letter-spacing: 0.5px;">
                                                    <?= htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted border-0">
                                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                    <i class="fas fa-folder-open fa-2x text-secondary opacity-50"></i>
                                                </div>
                                                <h6 class="fw-bold text-dark mb-1">Belum Ada Data</h6>
                                                <p class="small mb-0">Silakan tambahkan laporan baru melalui menu Aksi Cepat.</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php require_once '../templates/footer.php'; ?>