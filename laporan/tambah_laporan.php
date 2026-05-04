<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// LOGIKA BARU: Izinkan Admin dan Operator
if($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Menentukan ID Balai target berdasarkan Role
$target_balai_id = 0;
$target_nama_balai = '';
$is_admin_override = false;

if($_SESSION['role'] == 'admin') {
    // Jika Admin, wajib ada balai_id di URL
    if(!isset($_GET['balai_id'])) {
        $_SESSION['error'] = "Silakan pilih balai terlebih dahulu dari menu sidebar.";
        header("Location: ../admin/semua_laporan.php");
        exit();
    }
    
    $target_balai_id = (int)$_GET['balai_id'];
    $is_admin_override = true;
    
    // Cari nama balai target
    $query_b = mysqli_query($conn, "SELECT nama_balai FROM balai WHERE id_balai = $target_balai_id");
    if(mysqli_num_rows($query_b) > 0) {
        $b_data = mysqli_fetch_assoc($query_b);
        $target_nama_balai = $b_data['nama_balai'];
    } else {
        header("Location: ../admin/semua_laporan.php");
        exit();
    }
} else {
    // Jika Operator, ambil dari session aslinya
    $target_balai_id = $_SESSION['balai_id'];
    $target_nama_balai = $_SESSION['nama_balai'];
}

// Helper functions untuk form validation
function getFieldValue($field, $default = '') {
    return isset($_SESSION['form_data'][$field]) ? htmlspecialchars($_SESSION['form_data'][$field]) : $default;
}

function hasError($field) {
    return isset($_SESSION['errors'][$field]) && !empty($_SESSION['errors'][$field]);
}

function getError($field) {
    return isset($_SESSION['errors'][$field]) ? htmlspecialchars($_SESSION['errors'][$field]) : '';
}

$page_title = "Tambah Laporan";
$current_page = 'tambah_laporan';
$css_path = '../assets/css/modern-ui.css';
$js_path = '../assets/js/script.js';
$sidebar_dashboard_path = '../dashboard/dashboard.php';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    .btn-modern-cancel {
        background-color: #ffffff;
        color: #64748b;
        border: 2px solid #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-modern-cancel:hover {
        background-color: #f8fafc;
        color: #ef4444; 
        border-color: #fca5a5;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(239, 68, 68, 0.1);
    }
    .btn-modern-save {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
        color: white;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-modern-save:hover {
        transform: translateY(-3px); 
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); 
        color: white;
    }
    .btn-modern-save i { transition: transform 0.3s ease; }
    .btn-modern-save:hover i { transform: scale(1.15); }
</style>

<main class="bg-dashboard min-vh-100 w-100" style="background-color: #f8f9fc; padding-top: 2rem; padding-bottom: 4rem;">
    
    <div class="container-fluid px-4 px-md-5 pt-3">
        
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10 col-xxl-9">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-3 border-bottom">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 fw-bolder text-dark mb-2">Tambah Laporan Baru</h1>
                        <p class="text-secondary mb-0">
                            Input data ketersediaan benih untuk <strong><?php echo htmlspecialchars($target_nama_balai); ?></strong>
                            <?php if($is_admin_override): ?>
                                <span class="badge bg-warning text-dark ms-2"><i class="fas fa-crown me-1"></i> Mode Admin</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <button type="button" class="btn btn-outline-success fw-bold rounded-3 shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-2"></i> Import Excel (CSV)
                        </button>
                    </div>
                    
                </div>

                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold text-success" id="importModalLabel"><i class="fas fa-file-excel me-2"></i>Import Data Ketersediaan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="proses_import.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body py-4">
                                    <input type="hidden" name="target_balai_id" value="<?php echo $target_balai_id; ?>">
                                    
                                    <div class="alert alert-info rounded-3 small mb-4">
                                        <strong><i class="fas fa-info-circle me-1"></i> Aturan Import:</strong><br>
                                        1. Pastikan file Excel sudah di-Save As ke format <strong>.csv (Comma delimited)</strong>.<br>
                                        2. Urutan kolom harus: <i>Komoditas, Kelompok, Varietas, Kelas Benih, Jumlah, Satuan, Harga, Status(Keterangan), Deskripsi</i>.<br>
                                        3. Jangan hapus baris pertama (Judul Kolom).
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="file_excel" class="form-label fw-semibold text-dark">Pilih File CSV</label>
                                        <input class="form-control bg-light" type="file" id="file_excel" name="file_excel" accept=".csv" required>
                                    </div>
                                    
                                    <div class="row g-2 mt-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold small mb-1">Bulan Data</label>
                                            <select class="form-select bg-light" name="bulan" required>
                                                <option value="Auto" selected>Auto-Deteksi dari File Excel</option>
                                                <?php
                                                $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                foreach($bulans as $b) {
                                                    echo "<option value=\"$b\">$b</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold small mb-1">Tahun Data</label>
                                            <input type="number" class="form-control bg-light" name="tahun" value="<?= date('Y') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success px-4 fw-bold"><i class="fas fa-upload me-2"></i> Mulai Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4 px-4 py-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                        <span class="fw-medium"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4 px-4 py-3" role="alert">
                        <i class="fas fa-check-circle me-2 fs-5"></i>
                        <span class="fw-medium"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4 bg-white mb-5" style="border-top: 5px solid #16a34a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 px-lg-5 pb-0 mt-2">
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center justify-content-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-seedling fs-5"></i>
                            </div>
                            Formulir Ketersediaan Benih
                        </h5>
                    </div>
                    <div class="card-body p-4 p-lg-5 pt-4">
                        <form method="POST" action="proses_tambah.php">
                            
                            <input type="hidden" name="target_balai_id" value="<?php echo $target_balai_id; ?>">
                            
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label for="komoditas" class="form-label fw-semibold text-dark mb-2">Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('komoditas') ? 'is-invalid' : ''; ?>" id="komoditas" name="komoditas" value="<?php echo getFieldValue('komoditas'); ?>" placeholder="Contoh: Kelapa, Kopi, Kakao">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelompok_komoditas" class="form-label fw-semibold text-dark mb-2">Kelompok Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('kelompok_komoditas') ? 'is-invalid' : ''; ?>" id="kelompok_komoditas" name="kelompok_komoditas" value="<?php echo getFieldValue('kelompok_komoditas'); ?>" placeholder="Contoh: Dalam, Menengah, Luar">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="varietas" class="form-label fw-semibold text-dark mb-2">Varietas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('varietas') ? 'is-invalid' : ''; ?>" id="varietas" name="varietas" value="<?php echo getFieldValue('varietas'); ?>" placeholder="Contoh: Mapanget, Sidikalang">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelas_benih" class="form-label fw-semibold text-dark mb-2">Kelas Benih</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('kelas_benih') ? 'is-invalid' : ''; ?>" id="kelas_benih_select" name="kelas_benih_select">
                                        <option value="">Pilih Kelas Benih</option>
                                        <option value="Benih Dasar" <?php echo (getFieldValue('kelas_benih') == 'Benih Dasar') ? 'selected' : ''; ?>>Benih Dasar</option>
                                        <option value="Benih Pokok" <?php echo (getFieldValue('kelas_benih') == 'Benih Pokok') ? 'selected' : ''; ?>>Benih Pokok</option>
                                        <option value="Benih Sumber" <?php echo (getFieldValue('kelas_benih') == 'Benih Sumber') ? 'selected' : ''; ?>>Benih Sumber</option>
                                        <option value="Benih Sebar" <?php echo (getFieldValue('kelas_benih') == 'Benih Sebar') ? 'selected' : ''; ?>>Benih Sebar</option>
                                        <option value="Benih Tanam" <?php echo (getFieldValue('kelas_benih') == 'Benih Tanam') ? 'selected' : ''; ?>>Benih Tanam</option>
                                        <option value="Benih Klonal" <?php echo (getFieldValue('kelas_benih') == 'Benih Klonal') ? 'selected' : ''; ?>>Benih Klonal</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3 <?php echo hasError('kelas_benih') ? 'is-invalid' : ''; ?>" id="kelas_benih_custom" name="kelas_benih_custom" placeholder="Tulis kelas benih lainnya..." value="<?php echo getFieldValue('kelas_benih_custom'); ?>" style="display: none;">
                                    <input type="hidden" id="kelas_benih" name="kelas_benih" value="<?php echo getFieldValue('kelas_benih'); ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="jumlah_benih" class="form-label fw-semibold text-dark mb-2">Jumlah Stok</label>
                                    <input type="number" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('jumlah_benih') ? 'is-invalid' : ''; ?>" id="jumlah_benih" name="jumlah_benih" value="<?php echo getFieldValue('jumlah_benih'); ?>" placeholder="0">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="satuan" class="form-label fw-semibold text-dark mb-2">Satuan</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('satuan') ? 'is-invalid' : ''; ?>" id="satuan_select" name="satuan_select">
                                        <option value="">Pilih Satuan</option>
                                        <option value="Benih" <?php echo (getFieldValue('satuan') == 'Benih') ? 'selected' : ''; ?>>Benih</option>
                                        <option value="Bibit" <?php echo (getFieldValue('satuan') == 'Bibit') ? 'selected' : ''; ?>>Bibit</option>
                                        <option value="Polybag" <?php echo (getFieldValue('satuan') == 'Polybag') ? 'selected' : ''; ?>>Polybag</option>
                                        <option value="Kg" <?php echo (getFieldValue('satuan') == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                        <option value="Ton" <?php echo (getFieldValue('satuan') == 'Ton') ? 'selected' : ''; ?>>Ton</option>
                                        <option value="Unit" <?php echo (getFieldValue('satuan') == 'Unit') ? 'selected' : ''; ?>>Unit</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3 <?php echo hasError('satuan') ? 'is-invalid' : ''; ?>" id="satuan_custom" name="satuan_custom" placeholder="Tulis satuan lainnya..." value="<?php echo getFieldValue('satuan_custom'); ?>" style="display: none;">
                                    <input type="hidden" id="satuan" name="satuan" value="<?php echo getFieldValue('satuan'); ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="harga_satuan" class="form-label fw-semibold text-dark mb-2">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-muted px-3">Rp</span>
                                        <input type="number" class="form-control bg-light border-0 py-2 rounded-end-3 <?php echo hasError('harga_satuan') ? 'is-invalid' : ''; ?>" id="harga_satuan" name="harga_satuan" value="<?php echo getFieldValue('harga_satuan'); ?>" placeholder="0" min="0">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="status_ketersediaan" class="form-label fw-semibold text-dark mb-2">Keterangan <span class="text-danger">*</span></label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3 <?php echo hasError('status_ketersediaan') ? 'is-invalid' : ''; ?>" id="status_ketersediaan_select" name="status_ketersediaan_select" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Tersedia" <?php echo (getFieldValue('status_ketersediaan') == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Tidak Tersedia" <?php echo (getFieldValue('status_ketersediaan') == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                        <option value="Terbatas" <?php echo (getFieldValue('status_ketersediaan') == 'Terbatas') ? 'selected' : ''; ?>>Terbatas</option>
                                        <option value="PO" <?php echo (getFieldValue('status_ketersediaan') == 'PO') ? 'selected' : ''; ?>>PO</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3 <?php echo hasError('status_ketersediaan') ? 'is-invalid' : ''; ?>" id="status_ketersediaan_custom" name="status_ketersediaan_custom" placeholder="Tulis status lainnya..." value="<?php echo getFieldValue('status_ketersediaan_custom'); ?>" style="display: none;">
                                    <input type="hidden" id="status_ketersediaan" name="status_ketersediaan" value="<?php echo getFieldValue('status_ketersediaan'); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-dark mb-2">Periode <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <select class="form-select bg-light border-0 py-2 px-3 w-100 rounded-3 <?php echo hasError('bulan') ? 'is-invalid' : ''; ?>" id="bulan" name="bulan" required>
                                            <option value="">Pilih Bulan</option>
                                            <?php
                                            $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulans as $b) {
                                                $selected = (getFieldValue('bulan') == $b) ? 'selected' : '';
                                                echo "<option value=\"$b\" $selected>$b</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="number" class="form-control bg-light border-0 py-2 px-3 w-100 rounded-3 <?php echo hasError('tahun') ? 'is-invalid' : ''; ?>" id="tahun" name="tahun" placeholder="Tahun (e.g. 2025)" min="1900" max="2100" value="<?php echo getFieldValue('tahun', date('Y')); ?>" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold text-dark mb-2">Catatan / Deskripsi Tambahan</label>
                                    <textarea class="form-control bg-light border-0 p-3 rounded-3 <?php echo hasError('deskripsi') ? 'is-invalid' : ''; ?>" id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan keterangan tambahan jika ada..."><?php echo getFieldValue('deskripsi'); ?></textarea>
                                </div>
                            </div>

                           <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="<?php echo $is_admin_override ? '../admin/semua_laporan.php' : 'riwayat_laporan.php'; ?>" class="btn btn-modern-cancel fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-modern-save fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    <i class="fas fa-save me-2"></i> Simpan Data
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
document.addEventListener('DOMContentLoaded', function() {
    function setupCustomInput(selectElement, customInput, hiddenField, wrapperClass) {
        selectElement.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue === 'custom') {
                customInput.style.display = 'block';
                customInput.focus();
                hiddenField.value = '';
            } else {
                customInput.style.display = 'none';
                customInput.value = '';
                hiddenField.value = selectedValue;
            }
        });
        
        customInput.addEventListener('input', function() {
            hiddenField.value = this.value;
        });
        
        const currentHiddenValue = hiddenField.value;
        const currentCustomValue = customInput.value;
        
        if (currentCustomValue && !currentHiddenValue) {
            selectElement.value = 'custom';
            customInput.style.display = 'block';
            hiddenField.value = currentCustomValue;
        } else if (currentHiddenValue && !currentCustomValue) {
            const dropdownOptions = Array.from(selectElement.options).map(option => option.value);
            if (!dropdownOptions.includes(currentHiddenValue)) {
                selectElement.value = 'custom';
                customInput.value = currentHiddenValue;
                customInput.style.display = 'block';
            } else {
                selectElement.value = currentHiddenValue;
            }
        }
    }
    
    const kelasBenihSelect = document.getElementById('kelas_benih_select');
    const kelasBenihCustom = document.getElementById('kelas_benih_custom');
    const kelasBenihHidden = document.getElementById('kelas_benih');
    if (kelasBenihSelect && kelasBenihCustom && kelasBenihHidden) {
        setupCustomInput(kelasBenihSelect, kelasBenihCustom, kelasBenihHidden, 'kelas-benih');
    }
    
    const satuanSelect = document.getElementById('satuan_select');
    const satuanCustom = document.getElementById('satuan_custom');
    const satuanHidden = document.getElementById('satuan');
    if (satuanSelect && satuanCustom && satuanHidden) {
        setupCustomInput(satuanSelect, satuanCustom, satuanHidden, 'satuan');
    }
    
    const statusSelect = document.getElementById('status_ketersediaan_select');
    const statusCustom = document.getElementById('status_ketersediaan_custom');
    const statusHidden = document.getElementById('status_ketersediaan');
    if (statusSelect && statusCustom && statusHidden) {
        setupCustomInput(statusSelect, statusCustom, statusHidden, 'status');
    }
});
</script>

<?php 
unset($_SESSION['form_data']);
unset($_SESSION['errors']);
require_once '../templates/footer.php'; 
?>