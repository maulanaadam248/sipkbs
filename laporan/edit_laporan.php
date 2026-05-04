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

// Tentukan arah kembali (redirect)
$redirect = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";

if(!isset($_GET['id'])) {
    header("Location: $redirect");
    exit();
}

$laporan_id = $_GET['id'];

// 4. LOGIKA KEAMANAN DATA
if($_SESSION['role'] == 'admin') {
    // Jika Admin: Bisa edit SEMUA laporan
    $query = "SELECT l.*, b.nama_balai FROM laporan l JOIN balai b ON l.balai_id = b.id_balai WHERE l.id_laporan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $laporan_id);
} else {
    // Jika Operator: HANYA bisa edit laporan milik BALAINYA SENDIRI
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
// Tentukan URL kembali berdasarkan role
$url_kembali = ($_SESSION['role'] == 'admin') ? "../admin/semua_laporan.php" : "riwayat_laporan.php";
?>

<style>
    /* --- CSS TOMBOL MODERN --- */
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
    
    .btn-modern-action { padding: 12px 28px !important; font-size: 0.95rem !important; border-radius: 10px !important; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; font-weight: 700 !important; text-decoration: none !important; }
    .btn-back-modern { background-color: #ffffff !important; color: #64748b !important; border: 2px solid #e2e8f0 !important; }
    .btn-back-modern:hover { background-color: #f8fafc !important; color: #1e293b !important; border-color: #94a3b8 !important; transform: translateY(-3px) !important; }
  

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
    .btn-modern-save i {
        transition: transform 0.3s ease;
    }
    .btn-modern-save:hover i {
        transform: scale(1.15); 
    }
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
                      <a href="<?= $url_kembali; ?>" class="btn-modern-action btn-back-modern shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
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
                                    <label for="komoditas" class="form-label fw-semibold text-dark mb-2">Komoditas *</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="komoditas" name="komoditas" value="<?php echo htmlspecialchars($laporan['komoditas']); ?>" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelompok_komoditas" class="form-label fw-semibold text-dark mb-2">Kelompok Komoditas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="kelompok_komoditas" name="kelompok_komoditas" value="<?php echo htmlspecialchars($laporan['kelompok_komoditas']); ?>" placeholder="Contoh: Dalam, Menengah, Luar">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="varietas" class="form-label fw-semibold text-dark mb-2">Varietas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="varietas" name="varietas" value="<?php echo htmlspecialchars($laporan['varietas']); ?>" placeholder="Contoh: Mapanget, Sidikalang">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelas_benih" class="form-label fw-semibold text-dark mb-2">Kelas Benih</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="kelas_benih_select" name="kelas_benih_select">
                                        <option value="">Pilih Kelas Benih</option>
                                        <option value="Benih Dasar">Benih Dasar</option>
                                        <option value="Benih Pokok">Benih Pokok</option>
                                        <option value="Benih Sumber">Benih Sumber</option>
                                        <option value="Benih Sebar">Benih Sebar</option>
                                        <option value="Benih Tanam">Benih Tanam</option>
                                        <option value="Benih Klonal">Benih Klonal</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="kelas_benih_custom" name="kelas_benih_custom" placeholder="Tulis kelas benih lainnya..." style="display: none;">
                                    <input type="hidden" id="kelas_benih" name="kelas_benih" value="<?php echo htmlspecialchars($laporan['kelas_benih']); ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="jumlah_benih" class="form-label fw-semibold text-dark mb-2">Jumlah Stok</label>
                                    <input type="number" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="jumlah_benih" name="jumlah_benih" value="<?php echo htmlspecialchars($laporan['jumlah_benih']); ?>" placeholder="0">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="satuan" class="form-label fw-semibold text-dark mb-2">Satuan</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="satuan_select" name="satuan_select">
                                        <option value="">Pilih Satuan</option>
                                        <option value="Benih">Benih</option>
                                        <option value="Bibit">Bibit</option>
                                        <option value="Polybag">Polybag</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Ton">Ton</option>
                                        <option value="Unit">Unit</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="satuan_custom" name="satuan_custom" placeholder="Tulis satuan lainnya..." style="display: none;">
                                    <input type="hidden" id="satuan" name="satuan" value="<?php echo htmlspecialchars($laporan['satuan']); ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="harga_satuan" class="form-label fw-semibold text-dark mb-2">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-muted px-3">Rp</span>
                                        <input type="number" class="form-control bg-light border-0 py-2 rounded-end-3" id="harga_satuan" name="harga_satuan" value="<?php echo htmlspecialchars($laporan['harga_satuan']); ?>" placeholder="0" min="0">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="status_ketersediaan" class="form-label fw-semibold text-dark mb-2">Keterangan *</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="status_ketersediaan_select" name="status_ketersediaan_select" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Tersedia">Tersedia</option>
                                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                                        <option value="Terbatas">Terbatas</option>
                                        <option value="PO">PO</option>
                                        <option value="custom">Lainnya (Tulis manual)</option>
                                    </select>
                                    <input type="text" class="form-control bg-light border-0 mt-2 rounded-3" id="status_ketersediaan_custom" name="status_ketersediaan_custom" placeholder="Tulis status lainnya..." style="display: none;">
                                    <input type="hidden" id="status_ketersediaan" name="status_ketersediaan" value="<?php echo htmlspecialchars($laporan['status_ketersediaan']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-dark mb-2">Periode *</label>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <select class="form-select bg-light border-0 py-2 px-3 w-100 rounded-3" id="bulan" name="bulan" required>
                                            <option value="">Pilih Bulan</option>
                                            <?php
                                            $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulans as $b) {
                                                $selected = ($laporan['bulan'] == $b) ? 'selected' : '';
                                                echo "<option value=\"$b\" $selected>$b</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="number" class="form-control bg-light border-0 py-2 px-3 w-100 rounded-3" id="tahun" name="tahun" value="<?php echo htmlspecialchars($laporan['tahun']); ?>" min="1900" max="2100" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold text-dark mb-2">Catatan / Deskripsi Tambahan</label>
                                    <textarea class="form-control bg-light border-0 p-3 rounded-3" id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan keterangan tambahan jika ada..."><?php echo htmlspecialchars($laporan['deskripsi']); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="<?= $url_kembali; ?>" class="btn btn-modern-cancel fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    Batal
                                </a>
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
document.addEventListener('DOMContentLoaded', function() {
    function setupCustomInput(selectElement, customInput, hiddenField) {
        // Cek nilai awal saat halaman dimuat (Pre-populate dari DB)
        const currentHiddenValue = hiddenField.value;
        
        if (currentHiddenValue) {
            const dropdownOptions = Array.from(selectElement.options).map(option => option.value);
            if (!dropdownOptions.includes(currentHiddenValue) && currentHiddenValue !== 'custom') {
                // Jika nilai dari DB tidak ada di pilihan standar, anggap sebagai "custom"
                selectElement.value = 'custom';
                customInput.value = currentHiddenValue;
                customInput.style.display = 'block';
            } else {
                // Jika ada di pilihan standar
                selectElement.value = currentHiddenValue;
            }
        }

        // Handle saat dropdown diubah
        selectElement.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue === 'custom') {
                customInput.style.display = 'block';
                customInput.focus();
                hiddenField.value = customInput.value; 
            } else {
                customInput.style.display = 'none';
                customInput.value = '';
                hiddenField.value = selectedValue;
            }
        });
        
        // Handle saat text input custom diketik
        customInput.addEventListener('input', function() {
            hiddenField.value = this.value;
        });
    }
    
    // Inisialisasi Kelas Benih
    const kelasBenihSelect = document.getElementById('kelas_benih_select');
    const kelasBenihCustom = document.getElementById('kelas_benih_custom');
    const kelasBenihHidden = document.getElementById('kelas_benih');
    if (kelasBenihSelect && kelasBenihCustom && kelasBenihHidden) {
        setupCustomInput(kelasBenihSelect, kelasBenihCustom, kelasBenihHidden);
    }
    
    // Inisialisasi Satuan
    const satuanSelect = document.getElementById('satuan_select');
    const satuanCustom = document.getElementById('satuan_custom');
    const satuanHidden = document.getElementById('satuan');
    if (satuanSelect && satuanCustom && satuanHidden) {
        setupCustomInput(satuanSelect, satuanCustom, satuanHidden);
    }
    
    // Inisialisasi Status Ketersediaan
    const statusSelect = document.getElementById('status_ketersediaan_select');
    const statusCustom = document.getElementById('status_ketersediaan_custom');
    const statusHidden = document.getElementById('status_ketersediaan');
    if (statusSelect && statusCustom && statusHidden) {
        setupCustomInput(statusSelect, statusCustom, statusHidden);
    }
});
</script>

<?php require_once '../templates/footer.php'; ?>