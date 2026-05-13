<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

// 1. Cek login & Role
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// 2. Logika Target Balai (Original Adam's Code)
$target_balai_id = 0;
$target_nama_balai = '';
$is_admin_override = false;

if($_SESSION['role'] == 'admin') {
    if(!isset($_GET['balai_id'])) {
        $_SESSION['error'] = "Silakan pilih balai terlebih dahulu dari menu sidebar.";
        header("Location: ../admin/semua_laporan.php");
        exit();
    }
    $target_balai_id = (int)$_GET['balai_id'];
    $is_admin_override = true;
    $query_b = mysqli_query($conn, "SELECT nama_balai FROM balai WHERE id_balai = $target_balai_id");
    if(mysqli_num_rows($query_b) > 0) {
        $b_data = mysqli_fetch_assoc($query_b);
        $target_nama_balai = $b_data['nama_balai'];
    } else {
        header("Location: ../admin/semua_laporan.php");
        exit();
    }
} else {
    $target_balai_id = $_SESSION['balai_id'];
    $target_nama_balai = $_SESSION['nama_balai'];
}

// Helper Adam
function getFieldValue($field, $default = '') {
    return isset($_SESSION['form_data'][$field]) ? htmlspecialchars($_SESSION['form_data'][$field]) : $default;
}

$page_title = "Tambah Laporan";
$current_page = 'tambah_laporan';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    .btn-modern-cancel { background-color: #ffffff; color: #64748b; border: 2px solid #e2e8f0; transition: all 0.3s ease; }
    .btn-modern-cancel:hover { background-color: #f8fafc; color: #ef4444; border-color: #fca5a5; transform: translateY(-3px); }
    .btn-modern-save { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; transition: all 0.3s ease; }
    .btn-modern-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); color: white; }
</style>

<main class="bg-dashboard min-vh-100 w-100" style="background-color: #f8f9fc; padding-top: 2rem; padding-bottom: 4rem;">
    <div class="container-fluid px-4 px-md-5 pt-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10 col-xxl-9">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-3 border-bottom">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 fw-bolder text-dark mb-2">Tambah Laporan Baru</h1>
                        <p class="text-secondary mb-0">Input data untuk <strong><?= htmlspecialchars($target_nama_balai); ?></strong>
                        <?php if($is_admin_override): ?><span class="badge bg-warning text-dark ms-2">Mode Admin</span><?php endif; ?></p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-success fw-bold rounded-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-2"></i> Import Excel (CSV)
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold text-success"><i class="fas fa-file-excel me-2"></i>Import Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="proses_import.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body py-4">
                                    <input type="hidden" name="target_balai_id" value="<?= $target_balai_id; ?>">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pilih File CSV</label>
                                        <input class="form-control bg-light" type="file" name="file_excel" accept=".csv" required>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Bulan</label>
                                            <select class="form-select bg-light" name="bulan">
                                                <option value="Auto">Auto-Deteksi</option>
                                                <?php $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                foreach($bulans as $b) echo "<option value=\"$b\">$b</option>"; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Tahun</label>
                                            <input type="number" class="form-control bg-light" name="tahun" value="<?= date('Y') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success px-4 fw-bold">Mulai Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white mb-5" style="border-top: 5px solid #16a34a !important;">
                    <div class="card-body p-4 p-lg-5">
                        <form method="POST" action="proses_tambah.php">
                            <input type="hidden" name="target_balai_id" value="<?= $target_balai_id; ?>">
                            
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2" name="komoditas" value="<?= getFieldValue('komoditas'); ?>" placeholder="Contoh: Kopi">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Kelompok Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2" name="kelompok_komoditas" value="<?= getFieldValue('kelompok_komoditas'); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Varietas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2" name="varietas" value="<?= getFieldValue('varietas'); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Kelas Benih</label>
                                    <select class="form-select bg-light border-0 py-2" id="kelas_benih_select" name="kelas_benih_select">
                                        <option value="">Pilih Kelas...</option>
                                        <option value="Benih Dasar">Benih Dasar</option>
                                        <option value="Benih Pokok">Benih Pokok</option>
                                        <option value="Benih Sumber">Benih Sumber</option>
                                        <option value="Benih Sebar">Benih Sebar</option>
                                        <option value="custom">Lainnya...</option>
                                    </select>
                                    <input type="hidden" name="kelas_benih" id="kelas_benih">
                                    <input type="text" class="form-control bg-light border-0 mt-2" id="kelas_benih_custom" style="display:none;" placeholder="Ketik Kelas Benih...">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Jumlah Stok</label>
                                    <input type="number" class="form-control bg-light border-0 py-2" name="jumlah_benih" placeholder="0">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Satuan</label>
                                    <select class="form-select bg-light border-0 py-2" id="satuan_select" name="satuan_select">
                                        <option value="">Pilih Satuan...</option>
                                        <option value="Bibit">Bibit</option><option value="Benih">Benih</option><option value="Kg">Kg</option>
                                        <option value="custom">Lainnya...</option>
                                    </select>
                                    <input type="hidden" name="satuan" id="satuan">
                                    <input type="text" class="form-control bg-light border-0 mt-2" id="satuan_custom" style="display:none;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Harga Satuan (Rp)</label>
                                    <input type="number" class="form-control bg-light border-0 py-2" name="harga_satuan" placeholder="0">
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="card border-0 shadow-sm" style="background-color: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-truck-loading me-2 text-warning"></i>Rincian Penyaluran / Distribusi</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="tambahBarisDistribusi()"><i class="fas fa-plus me-1"></i> Tambah Penerima</button>
                                            </div>
                                            <div id="container-distribusi">
                                            <div class="row g-2 mb-2 baris-distribusi">
                                                <div class="col-md-2">
                                                    <input type="number" name="dist_qty[]" class="form-control" placeholder="Qty" value="<?= $item['qty'] ?? '' ?>">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="dist_target[]" class="form-control" placeholder="Penerima" value="<?= $item['target'] ?? '' ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="dist_lokasi[]" class="form-control" placeholder="Lokasi" value="<?= $item['lokasi'] ?? '' ?>">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-light text-danger" onclick="hapusBaris(this)"><i class="fas fa-times"></i></button>
                                                </div>
                                            </div>                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Keterangan / Status</label>
                                    <select class="form-select bg-light border-0 py-2" name="status_ketersediaan">
                                        <option value="">Pilih Status...</option>
                                        <option value="Tersedia">Tersedia</option><option value="Tidak Tersedia">Tidak Tersedia</option><option value="Terbatas">Terbatas</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Periode Data</label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select bg-light border-0" name="bulan">
                                            <option value="">Bulan...</option>
                                            <?php foreach($bulans as $b) echo "<option value=\"$b\">$b</option>"; ?>
                                        </select>
                                        <input type="number" class="form-control bg-light border-0" name="tahun" value="<?= date('Y') ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Deskripsi Tambahan</label>
                                    <textarea class="form-control bg-light border-0" name="deskripsi" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="<?= $is_admin_override ? '../admin/semua_laporan.php' : 'riwayat_laporan.php'; ?>" class="btn btn-modern-cancel fw-bold rounded-3 px-5 py-2">Batal</a>
                                <button type="submit" class="btn btn-modern-save fw-bold rounded-3 px-5 py-2">Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function tambahBarisDistribusi() {
    const container = document.getElementById('container-distribusi');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 baris-distribusi';
    div.innerHTML = `
        <div class="col-md-2"><input type="number" name="dist_qty[]" class="form-control border-0 shadow-sm" placeholder="Qty"></div>
        <div class="col-md-5"><input type="text" name="dist_target[]" class="form-control border-0 shadow-sm" placeholder="Penerima"></div>
        <div class="col-md-4"><input type="text" name="dist_lokasi[]" class="form-control border-0 shadow-sm" placeholder="Lokasi (Desa/Kec)"></div>
        <div class="col-md-1"><button type="button" class="btn btn-light text-danger border-0 w-100 shadow-sm" onclick="hapusBaris(this)"><i class="fas fa-times"></i></button></div>
    `;
    container.appendChild(div);
}
function hapusBaris(btn) {
    if (document.querySelectorAll('.baris-distribusi').length > 1) btn.closest('.baris-distribusi').remove();
    else btn.closest('.baris-distribusi').querySelectorAll('input').forEach(i => i.value = '');
}
document.addEventListener('DOMContentLoaded', function() {
    function setupCustom(selectId, customId, hiddenId) {
        const s = document.getElementById(selectId), c = document.getElementById(customId), h = document.getElementById(hiddenId);
        s.addEventListener('change', function() {
            if(this.value==='custom') { c.style.display='block'; c.focus(); h.value=''; }
            else { c.style.display='none'; h.value=this.value; }
        });
        c.addEventListener('input', function() { h.value=this.value; });
    }
    setupCustom('kelas_benih_select', 'kelas_benih_custom', 'kelas_benih');
    setupCustom('satuan_select', 'satuan_custom', 'satuan');
});
</script>
<?php require_once '../templates/footer.php'; ?>