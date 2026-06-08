<?php
session_start();
require_once '../config/database.php';

// FUNGSI HYBRID SEMPURNA: MENGAMBIL GAMBAR ATAU IKON DEFAULT
if (!function_exists('getKomoditasMedia')) {
    function getKomoditasMedia($nama_komoditas) {
        $k = strtolower(trim($nama_komoditas));

        // DEFAULT: Jika komoditas tidak dikenali (seperti ABAKA), gunakan IKON DAUN
        $type = 'icon'; 
        $media = 'fa-seedling'; 
        $color = '#16a34a';
        $bg = 'rgba(22, 163, 74, 0.1)';

        // Jika dikenali, ganti TIPE menjadi GAMBAR
        if (strpos($k, 'kakao') !== false) {
            $type = 'image'; $media = 'kakao.png'; $color = '#78350f'; $bg = 'rgba(120, 53, 15, 0.1)';
        } elseif (strpos($k, 'kopi') !== false) {
            $type = 'image'; $media = 'kopi.png'; $color = '#451a03'; $bg = 'rgba(69, 26, 3, 0.1)'; 
        } elseif (strpos($k, 'kelapa') !== false) {
            $type = 'image'; $media = 'kelapa.png'; $color = '#047857'; $bg = 'rgba(4, 120, 87, 0.1)'; 
        } elseif (strpos($k, 'tembakau') !== false) {
            $type = 'image'; $media = 'tembakau.png'; $color = '#c3aa1a'; $bg = 'rgba(101, 163, 13, 0.1)'; 
        } elseif (strpos($k, 'kapas') !== false) {
            $type = 'image'; $media = 'kapas.png'; $color = '#0ea5e9'; $bg = 'rgba(14, 165, 233, 0.1)'; 
        } elseif (strpos($k, 'lada') !== false) {
            $type = 'image'; $media = 'lada.png'; $color = '#064e3b'; $bg = 'rgba(6, 78, 59, 0.1)'; 
        } elseif (strpos($k, 'vanili') !== false) {
            $type = 'image'; $media = 'vanili.png'; $color = '#0d9488'; $bg = 'rgba(13, 148, 136, 0.1)'; 
        } elseif (strpos($k, 'nilam') !== false) {
            $type = 'image'; $media = 'nilam.png'; $color = '#15803d'; $bg = 'rgba(21, 128, 61, 0.1)';
        } elseif (strpos($k, 'wijen') !== false) {
            $type = 'image'; $media = 'wijen.png'; $color = '#06d95e'; $bg = 'rgba(217, 119, 6, 0.1)'; 
        } elseif (strpos($k, 'rosella') !== false) {
            $type = 'image'; $media = 'rosella.png'; $color = '#be123c'; $bg = 'rgba(190, 18, 60, 0.1)';
        } elseif (strpos($k, 'jarak') !== false) {
            $type = 'image'; $media = 'jarakkepyar.png'; $color = '#4d7c0f'; $bg = 'rgba(77, 124, 15, 0.1)'; 
        } elseif (strpos($k, 'kenaf') !== false) {
            $type = 'image'; $media = 'kenaf.png'; $color = '#15803d'; $bg = 'rgba(21, 128, 61, 0.1)';
        } elseif (strpos($k, 'rami') !== false) {
            $type = 'image'; $media = 'rami.png'; $color = '#15803d'; $bg = 'rgba(21, 128, 61, 0.1)';
        } elseif (strpos($k, 'tebu') !== false) {
            $type = 'image'; $media = 'tebu.png'; $color = '#9dcf12'; $bg = 'rgba(21, 128, 61, 0.1)';
        } elseif (strpos($k, 'abaka') !== false) {
            $type = 'image'; $media = 'abaka.png'; $color = '#15803d'; $bg = 'rgba(21, 128, 61, 0.1)';
        }

        return ['type' => $type, 'media' => $media, 'color' => $color, 'bg' => $bg];
    }
}

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
                                            <th>Stok & Harga</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // UPDATE QUERY: Ambil semua kolom (*)
                                        $query_terbaru = "SELECT * FROM laporan 
                                                          WHERE balai_id = $balai_id 
                                                          ORDER BY id_laporan DESC 
                                                          LIMIT 5";
                                        $result_terbaru = mysqli_query($conn, $query_terbaru);
                                        
                                        if (mysqli_num_rows($result_terbaru) > 0):
                                            while ($row_baru = mysqli_fetch_assoc($result_terbaru)):
                                                
                                                // BACA METADATA SATUAN DARI DESKRIPSI
                                                $stok_unit = ''; $harga_unit = '';
                                                if(strpos($row_baru['deskripsi'], 'MetaUnit=[') !== false) {
                                                    preg_match('/MetaUnit=\[([^|]+)\|([^\]]+)\]/', $row_baru['deskripsi'], $m);
                                                    if(isset($m[1]) && $m[1] != '-') $stok_unit = ' ' . trim($m[1]);
                                                    if(isset($m[2]) && $m[2] != '-') {
                                                        $harga_unit = trim($m[2]);
                                                        if(strpos($harga_unit, '/') === false) $harga_unit = '/' . $harga_unit;
                                                    }
                                                }
                                                
                                                // LOGIKA WARNA STATUS CERDAS
                                                $status = $row_baru['status_ketersediaan'];
                                                $st_lower = strtolower(trim($status));
                                                $outline_class = 'border-secondary text-secondary'; 
                                                
                                                if (strpos($st_lower, 'tidak') !== false) {
                                                    $outline_class = 'border-danger text-danger'; 
                                                } elseif (strpos($st_lower, 'tersedia') !== false) {
                                                    $outline_class = 'border-success text-success'; 
                                                } elseif (strpos($st_lower, 'pesan') !== false) {
                                                    $outline_class = 'border-info text-info-emphasis'; // Biru Muda
                                                } elseif (strpos($st_lower, 'potensi') !== false) {
                                                    $outline_class = 'border-primary text-primary'; // Biru Tua
                                                } elseif (strpos($st_lower, 'batas') !== false) {
                                                    $outline_class = 'border-warning text-warning-emphasis'; // Oranye
                                                }

                                                // PANGGIL MEDIA KOMODITAS UNTUK BARIS INI
                                                $styleTanaman = getKomoditasMedia($row_baru['komoditas']);
                                        ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm overflow-hidden border border-2" 
                                                                 style="width: 42px; height: 42px; min-width: 42px; background-color: <?= $styleTanaman['bg']; ?>; border-color: <?= $styleTanaman['color']; ?> !important; color: <?= $styleTanaman['color']; ?>;">
                                                                
                                                                <?php if($styleTanaman['type'] == 'image'): ?>
                                                                    <img src="../assets/img/komoditas/<?= $styleTanaman['media']; ?>" 
                                                                         alt="<?= htmlspecialchars($row_baru['komoditas']); ?>" 
                                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                                                         onerror="this.onerror=null; this.outerHTML='<i class=\'fas fa-seedling\'></i>';">
                                                                <?php else: ?>
                                                                    <i class="fas <?= $styleTanaman['media']; ?>"></i>
                                                                <?php endif; ?>
                                                                
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
                                                        <div class="fw-bold text-dark fs-6">
                                                            <?= number_format($row_baru['jumlah_benih']) . $stok_unit; ?> 
                                                            <span class="badge bg-light text-secondary border px-2 ms-1 fw-medium" style="font-size:0.7rem;"><?= htmlspecialchars($row_baru['satuan']); ?></span>
                                                        </div>
                                                        <div class="small text-success fw-medium mt-1">
                                                            <?= !empty($row_baru['harga_satuan']) ? 'Rp ' . number_format($row_baru['harga_satuan'], 0, ',', '.') . ' <span class="fw-normal text-muted">' . htmlspecialchars($harga_unit) . '</span>' : '-'; ?>
                                                        </div>
                                                    </td>
                                                    
                                                    <td>
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