<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role - hanya operator yang bisa lihat riwayat laporan balainya
if($_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$balai_id = $_SESSION['balai_id'];
$nama_balai = $_SESSION['nama_balai'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

// Filter
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk riwayat laporan operator
$query_laporan = "SELECT * FROM laporan WHERE balai_id = ?";
$params = [$balai_id];
$types = "i";

// Tambahkan filter
if(!empty($bulan_filter)) {
    $query_laporan .= " AND bulan = ?";
    $params[] = $bulan_filter;
    $types .= "s";
}
if(!empty($tahun_filter)) {
    $query_laporan .= " AND tahun = ?";
    $params[] = $tahun_filter;
    $types .= "i";
}
if(!empty($search)) {
    $query_laporan .= " AND (komoditas LIKE ? OR varietas LIKE ? OR kelompok_komoditas LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Urutkan berdasarkan komoditas lama dulu, komoditas kosong (varietas baru) di akhir, lalu id_laporan ASC
$query_laporan .= " ORDER BY CASE WHEN komoditas = '' OR komoditas IS NULL THEN 1 ELSE 0 END, komoditas ASC, id_laporan ASC";
$query_laporan .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $per_page;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query_laporan);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result_laporan = mysqli_stmt_get_result($stmt);

// Query untuk total data (pagination)
$query_total = "SELECT COUNT(*) as total FROM laporan WHERE balai_id = ?";
$total_params = [$balai_id];
$total_types = "i";

if(!empty($bulan_filter)) {
    $query_total .= " AND bulan = ?";
    $total_params[] = $bulan_filter;
    $total_types .= "s";
}
if(!empty($tahun_filter)) {
    $query_total .= " AND tahun = ?";
    $total_params[] = $tahun_filter;
    $total_types .= "i";
}
if(!empty($search)) {
    $query_total .= " AND (komoditas LIKE ? OR varietas LIKE ? OR kelompok_komoditas LIKE ?)";
    $search_param = "%$search%";
    $total_params[] = $search_param;
    $total_params[] = $search_param;
    $total_params[] = $search_param;
    $total_types .= "sss";
}

$stmt_total = mysqli_prepare($conn, $query_total);
mysqli_stmt_bind_param($stmt_total, $total_types, ...$total_params);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$total_data = mysqli_fetch_assoc($result_total)['total'];
$total_pages = ceil($total_data / $per_page);

$page_title = "Riwayat Laporan";
$current_page = 'riwayat_laporan';
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

<main class="main-content">
    <div class="container-fluid px-4 py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Riwayat Laporan</h2>
                <p class="text-muted mb-0">Daftar laporan ketersediaan benih dari <span class="fw-bold text-success">BRMP <?= htmlspecialchars($nama_balai); ?></span></p>
            </div>
            <div>
                <a href="tambah_laporan.php" class="btn btn-success px-4 py-2 rounded-3 shadow-sm fw-bold d-inline-flex align-items-center" style="transition: all 0.3s ease;">
                    <i class="fas fa-plus me-2"></i> Tambah Laporan
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label text-muted small fw-bold">Cari Data</label>
                        <input type="text" class="form-control border-0 bg-light" id="search" name="search" placeholder="Komoditas, Varietas..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="bulan" class="form-label text-muted small fw-bold">Bulan</label>
                        <select class="form-select border-0 bg-light" id="bulan" name="bulan">
                            <option value="">Semua Bulan</option>
                            <?php
                            $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            foreach($bulans as $b): ?>
                                <option value="<?= $b; ?>" <?= ($bulan_filter == $b) ? 'selected' : ''; ?>><?= $b; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="tahun" class="form-label text-muted small fw-bold">Tahun</label>
                        <input type="number" class="form-control border-0 bg-light" id="tahun" name="tahun" placeholder="Contoh: <?= date('Y') ?>" value="<?= htmlspecialchars($tahun_filter); ?>" min="2020" step="1">
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-success flex-grow-1 rounded-3"><i class="fas fa-search me-2"></i>Cari</button>
                            <a href="riwayat_laporan.php" class="btn btn-light border" title="Reset Filter"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-5">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-list-alt me-2"></i>Data Laporan Balai</h6>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive px-2 pb-2 pt-2">
                    <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th>Komoditas (Varietas)</th>
                                <th>Kelas Benih</th>
                                <th>Stok & Harga</th>
                                <th>Status Ketersediaan</th>
                                <th>Periode</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $last_komoditas = '';
                            $komoditas_counter = 0;
                            
                            if(mysqli_num_rows($result_laporan) > 0):
                                while($row = mysqli_fetch_assoc($result_laporan)): 
                                    
                                    $show_komoditas = ($row['komoditas'] !== $last_komoditas);
                                    if ($show_komoditas) {
                                        $last_komoditas = $row['komoditas'];
                                        $komoditas_counter++;
                                    }
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($show_komoditas): ?>
                                            <div class="fw-bold text-success fs-6">
                                                <span class="text-dark me-1"><?= $komoditas_counter; ?>.</span><?= htmlspecialchars($row['komoditas']); ?>
                                            </div>
                                            <?php if(!empty($row['kelompok_komoditas'])): ?>
                                                <div class="small text-muted mb-1"><?= htmlspecialchars($row['kelompok_komoditas']); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="ps-4 text-success opacity-50 small"><i class="fas fa-level-up-alt fa-rotate-90 me-2"></i></div>
                                        <?php endif; ?>
                                        
                                        <div class="<?= !$show_komoditas ? 'ps-5' : ''; ?>">
                                            <span class="fw-medium text-dark"><?= htmlspecialchars($row['varietas'] ?: '-'); ?></span>
                                        </div>
                                    </td>
                                    
                                    <td><?= htmlspecialchars($row['kelas_benih'] ?: '-'); ?></td>
                                    
                                    <td>
                                        <div class="fw-bold text-dark"><?= number_format($row['jumlah_benih']); ?> <span class="fw-normal fs-6 text-muted"><?= htmlspecialchars($row['satuan']); ?></span></div>
                                        <div class="small text-success fw-medium">
                                            <?= !empty($row['harga_satuan']) ? 'Rp ' . number_format($row['harga_satuan'], 0, ',', '.') : '-'; ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <?php
                                        $status = $row['status_ketersediaan'];
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
                                    
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($row['bulan']); ?></div>
                                        <div class="fw-bold text-muted small"><?= htmlspecialchars($row['tahun']); ?></div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex flex-column gap-2 align-items-center justify-content-center">
                                            <div class="btn-group btn-group-sm rounded-3 overflow-hidden shadow-sm" role="group">
                                                <a href="detail_laporan.php?id=<?= $row['id_laporan']; ?>" class="btn btn-white border" title="Detail"><i class="fas fa-eye text-info"></i></a>
                                                <a href="edit_laporan.php?id=<?= $row['id_laporan']; ?>" class="btn btn-white border" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                                                <a href="hapus_laporan.php?id=<?= $row['id_laporan']; ?>" class="btn btn-white border" title="Hapus" onclick="konfirmasiHapus(event, this.href)"><i class="fas fa-trash text-danger"></i></a>
                                            </div>
                                            
                                            <?php if ($show_komoditas && !empty($row['komoditas'])): ?>
                                                <button class="btn btn-sm btn-success text-white w-100 rounded-3 shadow-sm" style="font-size: 0.75rem; transition: all 0.2s;" onclick="addVarietyForKomoditas('<?= htmlspecialchars(addslashes($row['komoditas'])); ?>')">
                                                    <i class="fas fa-plus me-1"></i>Varietas
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted bg-white">
                                        <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0 fw-bold">Belum ada riwayat laporan.</p>
                                        <small>Data yang Anda inputkan akan muncul di sini.</small>
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
function addVarietyForKomoditas(komoditas) {
    const url = `tambah_laporan.php?komoditas=${encodeURIComponent(komoditas)}&add_variety=true`;
    window.location.href = url;
}

function konfirmasiHapus(event, url_hapus) {
    event.preventDefault();
    Swal.fire({
        title: 'Hapus Data Laporan?',
        text: "Data ini akan dihapus secara permanen!",
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

<?php 
// Notifikasi SweetAlert untuk Session Success/Error
if(isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_SESSION['success']; ?>',
            timer: 2500,
            showConfirmButton: false
        });
    </script>
<?php unset($_SESSION['success']); endif; 

if(isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= $_SESSION['error']; ?>',
            confirmButtonColor: '#dc3545'
        });
    </script>
<?php unset($_SESSION['error']); endif; ?>

<?php require_once '../templates/footer.php'; ?>