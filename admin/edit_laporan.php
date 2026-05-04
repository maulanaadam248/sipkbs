<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;
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

if($stmt === false) {
    $_SESSION['error'] = "Query preparation failed: " . mysqli_error($conn);
    header("Location: semua_laporan.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $laporan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Laporan tidak ditemukan!";
    header("Location: semua_laporan.php");
    exit();
}

$laporan = mysqli_fetch_assoc($result);

// Ambil data balai untuk dropdown
$query_balai = "SELECT * FROM balai ORDER BY nama_balai";
$result_balai = mysqli_query($conn, $query_balai);

$page_title = "Edit Laporan";
$current_page = 'semua_laporan';
$css_path = '../assets/css/modern-ui.css';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
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
                        <h1 class="h3 fw-bolder text-dark mb-2">Admin: Edit Laporan</h1>
                        <p class="text-secondary mb-0">Memperbarui data milik: <strong class="text-success"><?php echo htmlspecialchars($laporan['nama_balai']); ?></strong></p>
                    </div>
                    <div>
                        <a href="semua_laporan.php" class="btn btn-light text-secondary fw-semibold px-4 py-2 d-inline-flex align-items-center transition-all">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
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

                <div class="card border-0 shadow-sm rounded-4 bg-white mb-5" style="border-top: 5px solid #16a34a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 px-lg-5 pb-0 mt-2">
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center justify-content-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-user-shield fs-5"></i>
                            </div>
                            Formulir Koreksi Admin
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
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="kelompok_komoditas" name="kelompok_komoditas" value="<?php echo htmlspecialchars($laporan['kelompok_komoditas']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="varietas" class="form-label fw-semibold text-dark mb-2">Varietas</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="varietas" name="varietas" value="<?php echo htmlspecialchars($laporan['varietas']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="balai_id" class="form-label fw-semibold text-dark mb-2">Pindahkan ke Balai *</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="balai_id" name="balai_id" required>
                                        <?php 
                                        mysqli_data_seek($result_balai, 0);
                                        while($balai = mysqli_fetch_assoc($result_balai)): ?>
                                            <option value="<?php echo $balai['id_balai']; ?>" <?php echo ($laporan['balai_id'] == $balai['id_balai']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($balai['nama_balai']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kelas_benih" class="form-label fw-semibold text-dark mb-2">Kelas Benih</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="kelas_benih" name="kelas_benih">
                                        <option value="">Pilih Kelas Benih</option>
                                        <option value="Benih Dasar" <?php echo ($laporan['kelas_benih'] == 'Benih Dasar') ? 'selected' : ''; ?>>Benih Dasar</option>
                                        <option value="Benih Pokok" <?php echo ($laporan['kelas_benih'] == 'Benih Pokok') ? 'selected' : ''; ?>>Benih Pokok</option>
                                        <option value="Benih Sumber" <?php echo ($laporan['kelas_benih'] == 'Benih Sumber') ? 'selected' : ''; ?>>Benih Sumber</option>
                                        <option value="Benih Sebar" <?php echo ($laporan['kelas_benih'] == 'Benih Sebar') ? 'selected' : ''; ?>>Benih Sebar</option>
                                        <option value="Benih Tanam" <?php echo ($laporan['kelas_benih'] == 'Benih Tanam') ? 'selected' : ''; ?>>Benih Tanam</option>
                                        <option value="Benih Klonal" <?php echo ($laporan['kelas_benih'] == 'Benih Klonal') ? 'selected' : ''; ?>>Benih Klonal</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="satuan" class="form-label fw-semibold text-dark mb-2">Satuan</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="satuan" name="satuan">
                                        <option value="">Pilih Satuan</option>
                                        <option value="Benih" <?php echo ($laporan['satuan'] == 'Benih') ? 'selected' : ''; ?>>Benih</option>
                                        <option value="Bibit" <?php echo ($laporan['satuan'] == 'Bibit') ? 'selected' : ''; ?>>Bibit</option>
                                        <option value="Polybag" <?php echo ($laporan['satuan'] == 'Polybag') ? 'selected' : ''; ?>>Polybag</option>
                                        <option value="Kg" <?php echo ($laporan['satuan'] == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                        <option value="Ton" <?php echo ($laporan['satuan'] == 'Ton') ? 'selected' : ''; ?>>Ton</option>
                                        <option value="Unit" <?php echo ($laporan['satuan'] == 'Unit') ? 'selected' : ''; ?>>Unit</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="jumlah_benih" class="form-label fw-semibold text-dark mb-2">Jumlah Stok</label>
                                    <input type="number" class="form-control bg-light border-0 py-2 px-3 rounded-3" id="jumlah_benih" name="jumlah_benih" value="<?php echo htmlspecialchars($laporan['jumlah_benih']); ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="harga_satuan" class="form-label fw-semibold text-dark mb-2">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-muted px-3">Rp</span>
                                        <input type="number" class="form-control bg-light border-0 py-2 rounded-end-3" id="harga_satuan" name="harga_satuan" value="<?php echo htmlspecialchars($laporan['harga_satuan']); ?>">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="status_ketersediaan" class="form-label fw-semibold text-dark mb-2">Status *</label>
                                    <select class="form-select bg-light border-0 py-2 px-3 rounded-3" id="status_ketersediaan" name="status_ketersediaan" required>
                                        <option value="Tersedia" <?php echo ($laporan['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Tidak Tersedia" <?php echo ($laporan['status_ketersediaan'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                        <option value="Terbatas" <?php echo ($laporan['status_ketersediaan'] == 'Terbatas') ? 'selected' : ''; ?>>Terbatas</option>
                                        <option value="PO" <?php echo ($laporan['status_ketersediaan'] == 'PO') ? 'selected' : ''; ?>>PO</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-12">
                                    <label class="form-label fw-semibold text-dark mb-2">Periode Laporan *</label>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <select class="form-select bg-light border-0 py-2 px-3 w-100 rounded-3" id="bulan" name="bulan" required>
                                            <?php
                                            $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulans as $b) {
                                                $selected = ($laporan['bulan'] == $b) ? 'selected' : '';
                                                echo "<option value=\"$b\" $selected>$b</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="number" class="form-control bg-light border-0 py-2 px-3 w-100 rounded-3" id="tahun" name="tahun" value="<?php echo htmlspecialchars($laporan['tahun']); ?>" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold text-dark mb-2">Deskripsi</label>
                                    <textarea class="form-control bg-light border-0 p-3 rounded-3" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($laporan['deskripsi']); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="semua_laporan.php" class="btn btn-modern-cancel fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    Batal
                                </a>
                                <button type="submit" name="edit_laporan" class="btn btn-modern-save fw-bold rounded-3" style="padding: 12px 32px; font-size: 1rem;">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>