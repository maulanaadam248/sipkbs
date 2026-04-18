<?php
session_start();
// Mundur 3 folder ke root
require_once '../../../config/database.php';

// Fungsi sinkronisasi warna Balai (Ditambahkan di sini agar bisa dipanggil)
if (!function_exists('getBalaiColor')) {
    function getBalaiColor($nama_balai) {
        $balai = strtoupper($nama_balai);
        $colors = ['PALMA' => '#28a745', 'TRI' => '#007bff', 'TAS' => '#fd7e14', 'TROA' => '#6f42c1'];
        return isset($colors[$balai]) ? $colors[$balai] : '#6c757d';
    }
}

// 1. LOGIKA RESET TANGGAL 20
$hari_ini = (int)date('d');
if ($hari_ini >= 20) {
    $tgl_mulai = date('Y-m-20 00:00:00');
} else {
    $tgl_mulai = date('Y-m-20 00:00:00', strtotime('-1 month'));
}

// Fitur Pencarian & Filter Sederhana
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "WHERE l.created_at >= '$tgl_mulai'";

if(!empty($search)) {
    // Tambahkan pencarian menggunakan AND
    $where_clause .= " AND (l.komoditas LIKE '%$search%' 
                     OR l.varietas LIKE '%$search%' 
                     OR b.nama_balai LIKE '%$search%')";
}

$page_title = "Semua Ketersediaan Benih";
require_once '../../../templates/header.php';
?>

<style>
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

<main class="bg-dashboard min-vh-100 py-5 w-100" style="background-color: #f8f9fc;">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../../../index.php" class="text-decoration-none text-muted">Beranda</a></li>
                        <li class="breadcrumb-item active fw-bold text-success" aria-current="page">Semua Laporan</li>
                    </ol>
                </nav>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Data Ketersediaan Benih</h2>
            </div>
            <div>
                <a href="../../../index.php" class="btn btn-white border px-4 py-2 rounded-3 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <form method="GET" action="" class="row g-2 align-items-center justify-content-end">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Balai, Komoditas, Varietas..." value="<?= htmlspecialchars($search); ?>">
                            <button class="btn btn-success px-4" type="submit">Cari</button>
                        </div>
                    </div>
                    <?php if(!empty($search)): ?>
                    <div class="col-md-auto">
                        <a href="semua_laporan_publik.php" class="btn btn-light border text-danger"><i class="fas fa-times"></i> Reset</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive px-2 pb-2 pt-2">
                    <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th width="12%">Balai</th>
                                <th width="20%">Komoditas (Varietas)</th>
                                <th width="12%">Kelas</th>
                                <th width="15%">Stok & Harga</th>
                                <th width="12%">Status</th>
                                <th width="14%">Periode</th>
                                <th class="text-center" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $query = "SELECT l.*, b.nama_balai 
                                      FROM laporan l 
                                      JOIN balai b ON l.balai_id = b.id_balai 
                                      $where_clause
                                      ORDER BY l.id_laporan DESC";
                            $result = mysqli_query($conn, $query);
                            $no = 1;

                            if($result && mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    
                                    // AMBIL WARNA BALAI SECARA DINAMIS
                                    $warna_balai_tabel = getBalaiColor($row['nama_balai']);
                                    
                                    $status = $row['status_ketersediaan'];
                                    $badge = 'border-secondary text-secondary'; 
                                    if(strtolower(trim($status)) == 'tersedia') $badge = 'border-success text-success'; 
                                    elseif(strtolower(trim($status)) == 'tidak tersedia') $badge = 'border-danger text-danger'; 
                                    elseif(strtolower(trim($status)) == 'terbatas') $badge = 'border-warning text-warning-emphasis'; 
                                    elseif(strtolower(trim($status)) == 'po') $badge = 'border-primary text-primary'; 
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    
                                    <td>
                                        <span class="badge rounded-pill px-3 shadow-sm" style="background-color: <?= $warna_balai_tabel; ?>; color: white;">
                                            <?= htmlspecialchars($row['nama_balai']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold text-success"><?= htmlspecialchars($row['komoditas']); ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['kelompok_komoditas'] ?: '-'); ?> | <em><?= htmlspecialchars($row['varietas'] ?: '-'); ?></em></div>
                                    </td>
                                    <td><?= htmlspecialchars($row['kelas_benih'] ?: '-'); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= number_format($row['jumlah_benih']); ?> <span class="small fw-normal"><?= htmlspecialchars($row['satuan']); ?></span></div>
                                        <div class="small text-success fw-medium"><?= !empty($row['harga_satuan']) ? 'Rp ' . number_format($row['harga_satuan'], 0, ',', '.') : '-'; ?></div>
                                    </td>
                                    <td><span class="badge bg-transparent border <?= $badge; ?> px-2 py-1 rounded-2 fw-bold" style="font-size: 0.75rem;"><?= strtoupper(htmlspecialchars($status)); ?></span></td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($row['bulan']); ?></div>
                                        <div class="small text-muted fw-bold"><?= htmlspecialchars($row['tahun']); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <a href="detail_publik.php?id=<?= $row['id_laporan']; ?>" class="btn btn-outline-info btn-sm rounded-3 px-3 shadow-sm">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='8' class='text-center py-5 text-muted'><i class='fas fa-search fa-3x mb-3 opacity-25'></i><p class='mb-0 fw-bold'>Data tidak ditemukan.</p></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once '../../../templates/footer.php'; ?>