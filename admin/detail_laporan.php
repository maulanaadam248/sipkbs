<?php
session_start();
require_once '../config/database.php';

if (!function_exists('getKomoditasIcon')) {
    function getKomoditasIcon($nama_komoditas) {
        $k = strtolower(trim($nama_komoditas));

        $icon = 'fa-seedling';
        $color = '#16a34a';
        $bg = 'rgba(22, 163, 74, 0.1)';

        if (strpos($k, 'kakao') !== false) {
            $icon = 'fa-leaf'; $color = '#78350f'; $bg = 'rgba(120, 53, 15, 0.1)';
       } elseif (strpos($k, 'kopi') !== false) {
            $icon = 'fa-egg'; $color = '#451a03'; $bg = 'rgba(69, 26, 3, 0.1)'; 
        } elseif (strpos($k, 'kelapa') !== false) {
            $icon = 'fa-seedling'; $color = '#047857'; $bg = 'rgba(4, 120, 87, 0.1)'; 
        } elseif (strpos($k, 'tembakau') !== false) {
            $icon = 'fa-leaf'; $color = '#65a30d'; $bg = 'rgba(101, 163, 13, 0.1)'; 
        } elseif (strpos($k, 'kapas') !== false) {
            $icon = 'fa-spa'; $color = '#0ea5e9'; $bg = 'rgba(14, 165, 233, 0.1)'; 
        } elseif (strpos($k, 'lada') !== false) {
            $icon = 'fa-leaf'; $color = '#064e3b'; $bg = 'rgba(6, 78, 59, 0.1)'; 
        } elseif (strpos($k, 'vanili') !== false) {
            $icon = 'fa-leaf'; $color = '#0d9488'; $bg = 'rgba(13, 148, 136, 0.1)'; 
        } elseif (strpos($k, 'nilam') !== false) {
            $icon = 'fa-leaf'; $color = '#15803d'; $bg = 'rgba(21, 128, 61, 0.1)';
        } elseif (strpos($k, 'wijen') !== false) {
            $icon = 'fa-seedling'; $color = '#d97706'; $bg = 'rgba(217, 119, 6, 0.1)'; 
        } elseif (strpos($k, 'rosella') !== false) {
            $icon = 'fa-spa'; $color = '#be123c'; $bg = 'rgba(190, 18, 60, 0.1)';
        } elseif (strpos($k, 'jarak') !== false) {
            $icon = 'fa-seedling'; $color = '#4d7c0f'; $bg = 'rgba(77, 124, 15, 0.1)'; 
        }

        return ['icon' => $icon, 'color' => $color, 'bg' => $bg];
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

// Ambil data laporan berdasarkan ID
if(!isset($_GET['id'])) {
    header("Location: semua_laporan.php");
    exit();
}

$laporan_id = $_GET['id'];
$query = "SELECT l.*, b.nama_balai 
          FROM laporan l 
          JOIN balai b ON l.balai_id = b.id_balai 
          WHERE l.id_laporan = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $laporan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Laporan tidak ditemukan!";
    header("Location: semua_laporan.php");
    exit();
}

$laporan = mysqli_fetch_assoc($result);
$styleTanaman = getKomoditasIcon($laporan['komoditas']); // Panggil fungsi untuk mendapatkan styling

$page_title = "Detail Laporan";
$current_page = 'semua_laporan';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    /* 1. LAYOUT KARTU DETAIL (Sudah Profesional) */
    .detail-card {
        border-radius: 20px;
        overflow: hidden;
    }
    .detail-label {
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .detail-value {
        color: #1e293b;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .info-box {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .info-box:hover {
        background-color: #ffffff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #10b981;
    }

    /* 2. TOMBOL MODERN (Gaya Kotak & Konsisten) */
    .btn-modern-action {
        padding: 12px 28px !important;
        font-size: 0.95rem !important;
        border-radius: 10px !important; /* Ganti dari pill ke kotak rounded elegan */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }

    /* Gaya Tombol Kembali (Batal) */
    .btn-back-modern {
        background-color: #ffffff !important;
        color: #64748b !important;
        border: 2px solid #e2e8f0 !important;
    }
    .btn-back-modern:hover {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        border-color: #94a3b8 !important;
        transform: translateY(-3px) !important; /* Efek melayang */
    }

    /* Gaya Tombol Edit (Simpan) */
    .btn-edit-modern {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white !important;
        border: none !important;
    }
    .btn-edit-modern:hover {
        transform: translateY(-3px) !important; /* Efek melayang */
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3) !important;
        color: white !important;
    }

    /* 3. EFEK ANIMASI KLIK (BIAR GA BT) */
    .btn-modern-action:active {
        transform: translateY(2px) scale(0.95) !important; /* Amblas & Menciut dikit */
        box-shadow: inset 0 3px 5px rgba(0,0,0,0.1) !important;
        transition: all 0.1s !important;
    }

    /* 4. STATUS BADGE */
    .status-badge-lg {
        padding: 10px 20px;
        font-size: 0.9rem;
        border-radius: 8px; /* Disamakan kotaknya */
        font-weight: 700;
        letter-spacing: 0.5px;
    }
</style>

<main class="bg-dashboard min-vh-100 py-4 w-100" style="background-color: #f8f9fc;">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="semua_laporan.php" class="text-decoration-none text-muted">Daftar Laporan</a></li>
                        <li class="breadcrumb-item active fw-bold text-success" aria-current="page">Detail Data</li>
                    </ol>
                </nav>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Informasi Ketersediaan Benih</h2>
            </div>
          <div class="d-flex gap-3 mb-4">
                <a href="semua_laporan.php" class="btn-modern-action btn-back-modern shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
                <a href="edit_laporan.php?id=<?= $laporan['id_laporan']; ?>" class="btn-modern-action btn-edit-modern shadow-sm">
                    <i class="fas fa-edit me-2"></i> Edit Data
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
            <div class="p-1" style="background-color: <?= $styleTanaman['color']; ?>;"></div>
            
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center mb-5">
                    <div class="col-md-auto mb-3 mb-md-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 80px; height: 80px; background-color: <?= $styleTanaman['bg']; ?>; color: <?= $styleTanaman['color']; ?>;">
                            <i class="fas <?= $styleTanaman['icon']; ?> fa-3x"></i>
                        </div>
                    </div>
                    <div class="col-md">
                        <h3 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($laporan['komoditas']); ?></h3>
                        <p class="text-muted mb-0 fs-5">Varietas: <span class="text-dark fw-bold"><?= htmlspecialchars($laporan['varietas'] ?: '-'); ?></span></p>
                    </div>
                    <div class="col-md-auto text-md-end">
                        <?php
                            $status = $laporan['status_ketersediaan'];
                            $badge_class = 'bg-secondary text-white';
                            if($status == 'Tersedia') $badge_class = 'bg-success text-white';
                            elseif($status == 'PO') $badge_class = 'bg-primary text-white';
                            elseif($status == 'Terbatas') $badge_class = 'bg-warning text-dark';
                            elseif($status == 'Tidak Tersedia') $badge_class = 'bg-danger text-white';
                        ?>
                        <div class="detail-label mb-2">Status Saat Ini</div>
                        <span class="badge <?= $badge_class; ?> status-badge-lg shadow-sm">
                            <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100">
                            <div class="detail-label"><i class="fas fa-university me-2"></i>Balai Pengirim</div>
                            <div class="detail-value text-success"><?= htmlspecialchars($laporan['nama_balai']); ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100">
                            <div class="detail-label"><i class="fas fa-tags me-2"></i>Kelompok Komoditas</div>
                            <div class="detail-value"><?= htmlspecialchars($laporan['kelompok_komoditas'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100">
                            <div class="detail-label"><i class="fas fa-award me-2"></i>Kelas Benih</div>
                            <div class="detail-value"><?= htmlspecialchars($laporan['kelas_benih'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100 border-start border-primary border-4">
                            <div class="detail-label text-primary"><i class="fas fa-boxes me-2"></i>Jumlah Stok</div>
                            <div class="detail-value fs-3"><?= number_format($laporan['jumlah_benih']); ?> <span class="fs-6 fw-normal text-muted"><?= htmlspecialchars($laporan['satuan']); ?></span></div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100 border-start border-success border-4">
                            <div class="detail-label text-success"><i class="fas fa-tag me-2"></i>Harga Satuan</div>
                            <div class="detail-value fs-3"><?= !empty($laporan['harga_satuan']) ? 'Rp ' . number_format($laporan['harga_satuan'], 0, ',', '.') : '-'; ?></div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="info-box h-100">
                            <div class="detail-label"><i class="fas fa-calendar-alt me-2"></i>Periode Laporan</div>
                            <div class="detail-value"><?= htmlspecialchars($laporan['bulan']); ?> <?= htmlspecialchars($laporan['tahun']); ?></div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="info-box" style="background-color: #fff; border-style: dashed; border-width: 2px;">
                            <div class="detail-label mb-3"><i class="fas fa-align-left me-2"></i>Deskripsi Tambahan</div>
                            <div class="text-secondary lh-lg">
                                <?= nl2br(htmlspecialchars($laporan['deskripsi'] ?: 'Tidak ada deskripsi tambahan untuk data ini.')); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top text-center text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Data ini merupakan informasi resmi ketersediaan stok benih dari balai terkait.
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once '../templates/footer.php'; ?>