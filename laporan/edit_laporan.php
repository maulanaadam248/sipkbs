<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

// 1. Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. BUKA GEMBOK: Izinkan Admin DAN Operator masuk
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$redirect = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

if(!isset($_GET['id'])) {
    header("Location: $redirect");
    exit();
}

$laporan_id = $_GET['id'];

if($_SESSION['role'] == 'admin') {
    $query = "SELECT l.*, b.nama_balai FROM laporan l JOIN balai b ON l.balai_id = b.id_balai WHERE l.id_laporan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $laporan_id);
} else {
    $balai_id_operator = $_SESSION['balai_id'];
    $query = "SELECT l.*, b.nama_balai FROM laporan l JOIN balai b ON l.balai_id = b.id_balai WHERE l.id_laporan = ? AND l.balai_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $laporan_id, $balai_id_operator);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Data tidak ditemukan atau Anda tidak memiliki akses!";
    header("Location: $redirect");
    exit();
}

$laporan = mysqli_fetch_assoc($result);

$page_title = "Edit Laporan";
$current_page = 'riwayat_laporan';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
$url_kembali = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";
?>

<style>
    .btn-modern-cancel { background-color: #ffffff; color: #64748b; border: 2px solid #e2e8f0; transition: all 0.3s ease; }
    .btn-modern-cancel:hover { background-color: #f8fafc; color: #ef4444; border-color: #fca5a5; transform: translateY(-3px); }
    .btn-modern-action { padding: 12px 28px !important; font-size: 0.95rem !important; border-radius: 10px !important; transition: all 0.3s ease !important; display: inline-flex !important; align-items: center !important; font-weight: 700 !important; text-decoration: none !important; }
    .btn-back-modern { background-color: #ffffff !important; color: #64748b !important; border: 2px solid #e2e8f0 !important; }
    .btn-back-modern:hover { background-color: #f8fafc !important; color: #1e293b !important; border-color: #94a3b8 !important; transform: translateY(-3px) !important; }
    .btn-modern-save { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; transition: all 0.3s ease; }
    .btn-modern-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); color: white; }
</style>

<main class="bg-dashboard min-vh-100 w-100" style="background-color: #f8f9fc; padding-top: 2rem; padding-bottom: 4rem;">
    <div class="container-fluid px-4 px-md-5 pt-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10 col-xxl-9">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-3 border-bottom">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 fw-bolder text-dark mb-2">Edit Laporan</h1>
                        <p class="text-secondary mb-0">Memperbarui data: <strong class="text-success"><?php echo htmlspecialchars($laporan['komoditas']); ?> <?php echo !empty($laporan['varietas']) ? ' - ' . htmlspecialchars($laporan['varietas']) : ''; ?></strong></p>
                    </div>
                    <div>
                      <a href="<?= $url_kembali; ?>" class="btn-modern-action btn-back-modern shadow-sm"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
                    </div>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4 px-4 py-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-5"></i><span class="fw-medium"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4 bg-white mb-5" style="border-top: 5px solid #16a34a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 px-lg-5 pb-0 mt-2">
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center justify-content-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-edit fs-5"></i>
                            </div>
                            Formulir Perubahan Data
                        </h5>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5 pt-4">
                        <form method="POST" action="proses_edit_laporan.php">
                            <input type="hidden" name="id_laporan" value="<?php echo $laporan['id_laporan']; ?>">
                            
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label for="komoditas" class="form-label fw-semibold text-dark mb-2">Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="komoditas" name="komoditas" value="<?php echo htmlspecialchars($laporan['komoditas']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelompok_komoditas" class="form-label fw-semibold text-dark mb-2">Kelompok Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="kelompok_komoditas" name="kelompok_komoditas" value="<?php echo htmlspecialchars($laporan['kelompok_komoditas']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="varietas" class="form-label fw-semibold text-dark mb-2">Varietas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="varietas" name="varietas" value="<?php echo htmlspecialchars($laporan['varietas']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelas_benih" class="form-label fw-semibold text-dark mb-2">Kelas Benih</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="kelas_benih_select" name="kelas_benih_select">
                                        <option value="">Pilih Kelas Benih</option>
                                        <option value="Benih Dasar" <?= ($laporan['kelas_benih'] == 'Benih Dasar') ? 'selected' : ''; ?>>Benih Dasar</option>
                                        <option value="Benih Pokok" <?= ($laporan['kelas_benih'] == 'Benih Pokok') ? 'selected' : ''; ?>>Benih Pokok</option>
                                        <option value="Benih Sumber" <?= ($laporan['kelas_benih'] == 'Benih Sumber') ? 'selected' : ''; ?>>Benih Sumber</option>
                                        <option value="Benih Sebar" <?= ($laporan['kelas_benih'] == 'Benih Sebar') ? 'selected' : ''; ?>>Benih Sebar</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="hidden" id="kelas_benih" name="kelas_benih" value="<?php echo htmlspecialchars($laporan['kelas_benih']); ?>">
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="kelas_benih_custom" name="kelas_benih_custom" style="display: none;">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="jumlah_benih" class="form-label fw-semibold text-dark mb-2">Jumlah Stok</label>
                                    <input type="number" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="jumlah_benih" name="jumlah_benih" value="<?php echo htmlspecialchars($laporan['jumlah_benih']); ?>" placeholder="0">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="satuan" class="form-label fw-semibold text-dark mb-2">Satuan</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="satuan_select" name="satuan_select">
                                        <option value="">Pilih Satuan</option>
                                        <option value="Benih" <?= ($laporan['satuan'] == 'Benih') ? 'selected' : ''; ?>>Benih</option>
                                        <option value="Bibit" <?= ($laporan['satuan'] == 'Bibit') ? 'selected' : ''; ?>>Bibit</option>
                                        <option value="Polybag" <?= ($laporan['satuan'] == 'Polybag') ? 'selected' : ''; ?>>Polybag</option>
                                        <option value="Kg" <?= ($laporan['satuan'] == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                        <option value="Ton" <?= ($laporan['satuan'] == 'Ton') ? 'selected' : ''; ?>>Ton</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="hidden" id="satuan" name="satuan" value="<?php echo htmlspecialchars($laporan['satuan']); ?>">
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="satuan_custom" name="satuan_custom" style="display: none;">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="harga_satuan" class="form-label fw-semibold text-dark mb-2">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-muted px-3">Rp</span>
                                        <input type="number" class="form-control bg-light border-0 py-2 rounded-end-3" id="harga_satuan" name="harga_satuan" value="<?php echo htmlspecialchars($laporan['harga_satuan']); ?>" placeholder="0" min="0">
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="card border-0 shadow-sm" style="background-color: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="fw-bold text-dark mb-0"></i>Rincian Distribusi</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="tambahBarisDistribusi()">
                                                    <i class="fas fa-plus me-1"></i> Tambah Rincian
                                                </button>
                                            </div>
                                            
                                            <div id="container-distribusi">
                                                <?php 
                                        
                                                $raw_json = $laporan['penerima_manfaat'] ?? '';
                                                $data_distribusi = !empty($raw_json) ? json_decode($raw_json, true) : null;
                                                if (!$data_distribusi || !is_array($data_distribusi)) {
                                                    // Jika format lama, paksa jadi array untuk dilooping
                                                    $data_distribusi = [['qty' => $laporan['volume_penyaluran'], 'target' => $laporan['penerima_manfaat']]];
                                                }
                                                
                                                foreach ($data_distribusi as $item): 
                                                ?>
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
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <small class="text-muted mt-2 d-block"><em>* Biarkan kosong jika tidak ada data penjualan pada komoditas ini.</em></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="status_ketersediaan" class="form-label fw-semibold text-dark mb-2">Keterangan</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="status_ketersediaan_select" name="status_ketersediaan_select">
                                        <option value="">Pilih Status</option>
                                        <option value="Tersedia" <?= ($laporan['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Tidak Tersedia" <?= ($laporan['status_ketersediaan'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                        <option value="Terbatas" <?= ($laporan['status_ketersediaan'] == 'Terbatas') ? 'selected' : ''; ?>>Terbatas</option>
                                        <option value="PO" <?= ($laporan['status_ketersediaan'] == 'PO') ? 'selected' : ''; ?>>PO</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="hidden" id="status_ketersediaan" name="status_ketersediaan" value="<?php echo htmlspecialchars($laporan['status_ketersediaan']); ?>">
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="status_ketersediaan_custom" name="status_ketersediaan_custom" style="display: none;">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-dark mb-2">Periode</label>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <select class="form-select bg-light border-0 py-2 px-3 w-100 rounded-3" id="bulan" name="bulan">
                                            <option value="">Pilih Bulan</option>
                                            <?php
                                            $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulans as $b) {
                                                $selected = ($laporan['bulan'] == $b) ? 'selected' : '';
                                                echo "<option value=\"$b\" $selected>$b</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="number" class="form-control bg-light border-0 py-2 px-3 w-100 rounded-3" id="tahun" name="tahun" value="<?php echo htmlspecialchars($laporan['tahun']); ?>" min="1900" max="2100">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold text-dark mb-2">Catatan / Deskripsi Tambahan</label>
                                    <textarea class="form-control bg-light border-0 p-3 rounded-3" id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($laporan['deskripsi']); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="<?= $url_kembali; ?>" class="btn btn-modern-cancel fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">Batal</a>
                                <button type="submit" name="edit_laporan" class="btn btn-modern-save fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    <i class="fas fa-save me-2"></i> Update Laporan
                                </button>
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
    const baris = btn.closest('.baris-distribusi');
    if (document.querySelectorAll('.baris-distribusi').length > 1) {
        baris.remove();
    } else {
        baris.querySelectorAll('input').forEach(i => i.value = '');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    function setupCustomInput(selectElement, customInput, hiddenField) {
        const currentHiddenValue = hiddenField.value;
        if (currentHiddenValue) {
            const dropdownOptions = Array.from(selectElement.options).map(option => option.value);
            if (!dropdownOptions.includes(currentHiddenValue) && currentHiddenValue !== '') {
                selectElement.value = 'custom';
                customInput.value = currentHiddenValue;
                customInput.style.display = 'block';
            } else if (currentHiddenValue !== '') {
                selectElement.value = currentHiddenValue;
            }
        }

        selectElement.addEventListener('change', function() {
            if (this.value === 'custom') {
                customInput.style.display = 'block';
                customInput.focus();
                hiddenField.value = customInput.value; 
            } else {
                customInput.style.display = 'none';
                customInput.value = '';
                hiddenField.value = this.value;
            }
        });
        customInput.addEventListener('input', function() { hiddenField.value = this.value; });
    }
    
    setupCustomInput(document.getElementById('kelas_benih_select'), document.getElementById('kelas_benih_custom'), document.getElementById('kelas_benih'));
    setupCustomInput(document.getElementById('satuan_select'), document.getElementById('satuan_custom'), document.getElementById('satuan'));
    setupCustomInput(document.getElementById('status_ketersediaan_select'), document.getElementById('status_ketersediaan_custom'), document.getElementById('status_ketersediaan'));
});
</script>

<?php require_once '../templates/footer.php'; ?>