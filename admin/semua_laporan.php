<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
require_once '../includes/simple_file_helper.php';

// Fungsi sinkronisasi warna Balai
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

$page_title = "Semua Laporan";
$current_page = 'semua_laporan';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    /* Bikin tombol export jadi lega dan cantik */
    .btn-export-modern {
        background-color: #16a34a !important;
        color: white !important;
        padding: 10px 24px !important;
        border-radius: 8px !important;
        border: none !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }
    
    .btn-export-modern:hover {
        background-color: #15803d !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

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
    /* Pastikan pembungkus tabel memiliki tinggi atau batas */
.table-responsive {
    max-height: 80vh; /* Tabel akan mengisi 80% tinggi layar sebelum muncul scrollbar internal */
    overflow-y: auto;
    border-radius: 12px;
}

.table-green-theme thead th {
    /* Ini adalah kuncinya */
    position: sticky !important;
    top: 0; /* Menempel tepat di paling atas container */
    z-index: 10; /* Angka 10 memastikan dia di atas baris data (z-index 1) */
    
    /* Warna latar harus solid agar data di bawahnya tidak tembus pandang saat di-scroll */
    background-color: #d1fae5 !important; 
    color: #065f46 !important;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1); /* Efek bayangan tipis agar terlihat terpisah */
}
</style>

<main class="main-content">
    <div class="container-fluid px-4 py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Semua Laporan</h2>
                <p class="text-muted mb-0">Kelola semua laporan ketersediaan benih dari seluruh balai</p>
            </div>
            <div>
                <a href="export.php" class="btn btn-export-modern shadow-sm d-inline-flex align-items-center">
                    <i class="fas fa-download me-2"></i> Export Data
                </a>
                <button type="button" class="btn btn-outline-danger fw-bold rounded-3 shadow-sm px-3 py-2 ms-2" data-bs-toggle="modal" data-bs-target="#hapusMassalModal">
                    <i class="fas fa-trash-alt me-2"></i> Kosongkan Bulan Tertentu
                </button>
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
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter_balai" class="form-label text-muted small fw-bold">Balai</label>
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
                    
                    <div class="col-md-3">
                        <label for="filter_komoditas" class="form-label text-muted small fw-bold">Komoditas</label>
                        <input type="text" class="form-control border-0 bg-light" id="filter_komoditas" name="komoditas" value="<?= isset($_GET['komoditas']) ? htmlspecialchars($_GET['komoditas']) : ''; ?>" placeholder="Nama komoditas...">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filter_bulan" class="form-label text-muted small fw-bold">Bulan</label>
                        <select class="form-select border-0 bg-light" id="filter_bulan" name="bulan">
                            <option value="">Semua</option>
                            <?php
                            $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            foreach($bulan_list as $bulan):
                            ?>
                                <option value="<?= $bulan; ?>" <?= (isset($_GET['bulan']) && $_GET['bulan'] == $bulan) ? 'selected' : ''; ?>><?= $bulan; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filter_tahun" class="form-label text-muted small fw-bold">Tahun</label>
                        <select class="form-select border-0 bg-light" id="filter_tahun" name="tahun">
                            <option value="">Semua</option>
                            <?php for($tahun = 2020; $tahun <= 2030; $tahun++): ?>
                                <option value="<?= $tahun; ?>" <?= (isset($_GET['tahun']) && $_GET['tahun'] == $tahun) ? 'selected' : ''; ?>><?= $tahun; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-success flex-grow-1 rounded-3"><i class="fas fa-search"></i> Cari</button>
                            <a href="semua_laporan.php" class="btn btn-light border" title="Reset Filter"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-list-alt me-2"></i>Data Ketersediaan Benih</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive px-2 pb-2 pt-2">
                    <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Balai</th>
                                <th>Komoditas (Varietas)</th>
                                <th>Kelas Benih</th>
                                <th>Stok & Harga</th>
                                <th>Satuan</th>
                                <th>Status</th>
                                <th>Periode</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $where_conditions = [];
                            if(isset($_GET['balai_id']) && !empty($_GET['balai_id'])) $where_conditions[] = "l.balai_id = " . (int)$_GET['balai_id'];
                            if(isset($_GET['komoditas']) && !empty($_GET['komoditas'])) $where_conditions[] = "l.komoditas LIKE '%" . mysqli_real_escape_string($conn, $_GET['komoditas']) . "%'";
                            if(isset($_GET['bulan']) && !empty($_GET['bulan'])) $where_conditions[] = "l.bulan = '" . mysqli_real_escape_string($conn, $_GET['bulan']) . "'";
                            if(isset($_GET['tahun']) && !empty($_GET['tahun'])) $where_conditions[] = "l.tahun = " . (int)$_GET['tahun'];

                            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
                            $query = "SELECT l.*, b.nama_balai FROM laporan l JOIN balai b ON l.balai_id = b.id_balai $where_clause ORDER BY l.id_laporan DESC";
                            $result = mysqli_query($conn, $query);
                            $no = 1;

                            if(mysqli_num_rows($result) > 0):
                                while($laporan = mysqli_fetch_assoc($result)):
                                    $warna_balai_tabel = getBalaiColor($laporan['nama_balai']);
                                    
                                    // BACA METADATA SATUAN (gr & /gram) DARI DESKRIPSI
                                    $stok_unit = ''; $harga_unit = '';
                                    if(strpos($laporan['deskripsi'], 'MetaUnit=[') !== false) {
                                        preg_match('/MetaUnit=\[([^|]+)\|([^\]]+)\]/', $laporan['deskripsi'], $m);
                                        if(isset($m[1]) && $m[1] != '-') $stok_unit = ' ' . trim($m[1]);
                                        if(isset($m[2]) && $m[2] != '-') {
                                            $harga_unit = trim($m[2]);
                                            if(strpos($harga_unit, '/') === false) $harga_unit = '/' . $harga_unit;
                                        }
                                    }
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td><span class="badge rounded-pill px-3 shadow-sm" style="background-color: <?= $warna_balai_tabel; ?>; color: white;"><?= htmlspecialchars($laporan['nama_balai']); ?></span></td>
                                    <td>
                                        <div class="fw-bold text-success"><?= htmlspecialchars($laporan['komoditas']); ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($laporan['kelompok_komoditas'] ?: '-'); ?> | <em><?= htmlspecialchars($laporan['varietas'] ?: '-'); ?></em></div>
                                    </td>
                                    <td><?= htmlspecialchars($laporan['kelas_benih'] ?: '-'); ?></td>
                                    
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= number_format($laporan['jumlah_benih']) . $stok_unit; ?></div>
                                        <div class="small text-success fw-medium">
                                            <?= !empty($laporan['harga_satuan']) ? 'Rp ' . number_format($laporan['harga_satuan'], 0, ',', '.') . ' <span class="fw-normal text-muted">' . htmlspecialchars($harga_unit) . '</span>' : '-'; ?>
                                        </div>
                                    </td>
                                    
                                    <td><span class="badge bg-light text-secondary border px-2 py-1 fw-medium"><?= htmlspecialchars($laporan['satuan'] ?: '-'); ?></span></td>
                                    
                                    <td>
                                        <?php
                                        // PENDETEKSI WARNA STATUS CERDAS
                                        $status = $laporan['status_ketersediaan'];
                                        $st_lower = strtolower($status);
                                        $badge_class = 'border-secondary text-secondary'; 
                                        
                                        if (strpos($st_lower, 'tidak') !== false) {
                                            $badge_class = 'border-danger text-danger'; 
                                        } elseif (strpos($st_lower, 'tersedia') !== false) {
                                            $badge_class = 'border-success text-success'; 
                                        } elseif (strpos($st_lower, 'pesan') !== false) {
                                            $badge_class = 'border-info text-info-emphasis'; // Biru Muda untuk Dipesan
                                        } elseif (strpos($st_lower, 'potensi') !== false) {
                                            $badge_class = 'border-primary text-primary'; // Biru Tua untuk Potensi
                                        } elseif (strpos($st_lower, 'batas') !== false) {
                                            $badge_class = 'border-warning text-warning-emphasis'; 
                                        }
                                        ?>
                                        <span class="badge bg-transparent border <?= $badge_class; ?> px-2 py-1 rounded-2 fw-bold" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($laporan['bulan']); ?></div>
                                        <div class="small text-muted fw-bold"><?= htmlspecialchars($laporan['tahun']); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm rounded-3 overflow-hidden shadow-sm" role="group">
                                            <a href="../laporan/detail_laporan.php?id=<?= $laporan['id_laporan']; ?>" class="btn btn-white border"><i class="fas fa-eye text-info"></i></a>
                                            <a href="../laporan/edit_laporan.php?id=<?= $laporan['id_laporan']; ?>" class="btn btn-white border"><i class="fas fa-edit text-primary"></i></a>
                                            <a href="../laporan/hapus_laporan.php?id=<?= $laporan['id_laporan']; ?>" class="btn btn-white border" onclick="konfirmasiHapus(event, this.href)"><i class="fas fa-trash text-danger"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted bg-white"><p class="fw-bold mb-0">Oops! Data tidak ditemukan.</p></td></tr>
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
        title: 'Hapus Data Laporan?',
        text: "Data ini akan dimusnahkan selamanya!",
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

<div class="modal fade" id="hapusMassalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Hapus Data Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="../laporan/proses_hapus_massal.php" method="POST">
                <div class="modal-body py-4">
                    <div class="alert alert-danger rounded-3 small mb-4 bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger">
                        <strong>PERINGATAN:</strong> Tindakan ini akan menghapus <strong>seluruh data</strong> pada bulan dan tahun yang Anda pilih secara permanen. Data yang dihapus tidak dapat dikembalikan!
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small mb-1">Pilih Bulan</label>
                            <select class="form-select bg-light border-0" name="bulan" required>
                                <option value="">Pilih Bulan...</option>
                                <?php
                                $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                foreach($bulans as $b) {
                                    echo "<option value=\"$b\">$b</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small mb-1">Tahun</label>
                            <input type="number" class="form-control bg-light border-0" name="tahun" value="<?= date('Y') ?>" required>
                        </div>
                        
                        <?php 
                        // JIKA ROLE ADALAH ADMIN, TAMPILKAN PILIHAN BALAI
                        if($_SESSION['role'] == 'admin'): 
                            $query_balai_modal = mysqli_query($conn, "SELECT id_balai, nama_balai FROM balai ORDER BY nama_balai");
                        ?>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-semibold small mb-1 text-danger">Pilih Balai (Khusus Admin)</label>
                            <select class="form-select bg-light border-0 border-start border-danger border-4" name="balai_id">
                                <option value="0">⚠️ BERBAHAYA: Hapus dari SELURUH Balai</option>
                                <?php while($b_modal = mysqli_fetch_assoc($query_balai_modal)): ?>
                                    <option value="<?= $b_modal['id_balai'] ?>"><?= htmlspecialchars($b_modal['nama_balai']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted" style="font-size: 0.7rem;">Pilih instansi mana yang datanya ingin dihapus.</small>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold rounded-3 shadow-sm">
                        <i class="fas fa-trash-alt me-2"></i> Ya, Hapus Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>