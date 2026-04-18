<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$query_balai = "SELECT * FROM balai ORDER BY nama_balai";
$result_balai = mysqli_query($conn, $query_balai);

$page_title = "Export Laporan";
$current_page = 'export';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';
?>

<style>
    /* Bikin tombol export jadi lega dan profesional */
    .btn-export-classic {
        background-color: #16a34a !important;
        color: white !important;
        padding: 12px 35px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        border: none !important;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-export-classic:hover {
        background-color: #15803d !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .btn-back-classic {
        background-color: #ffffff !important;
        color: #475569 !important; /* Warna abu-abu kebiruan yang elegan */
        border: 1px solid #cbd5e1 !important;
        padding: 8px 24px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    
    .btn-back-classic:hover {
        background-color: #f8fafc !important;
        color: #1e293b !important; /* Teks jadi lebih gelap saat di-hover */
        border-color: #94a3b8 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
    }
</style>

<main class="main-content">
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h2 class="fw-bold mb-0 text-dark">Export Data Laporan</h2>
            <a href="semua_laporan.php" class="btn-back-classic">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="export_processor.php">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">PILIH FORMAT FILE</label>
                            <select class="form-select py-2 border-2" name="export_type" required>
                                <option value="excel">Microsoft Excel (.xls)</option>
                                <option value="pdf">PDF Document (.pdf)</option>
                                <option value="word">Microsoft Word (.doc)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">FILTER BALAI</label>
                            <select class="form-select py-2" name="balai_id">
                                <option value="">Semua Balai</option>
                                <?php while($b = mysqli_fetch_assoc($result_balai)) echo "<option value='{$b['id_balai']}'>{$b['nama_balai']}</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">BULAN</label>
                            <select class="form-select py-2" name="bulan">
                                <option value="">Semua Bulan</option>
                                <?php 
                                $bulans = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                foreach($bulans as $bln) echo "<option value='$bln'>$bln</option>"; 
                                ?>
                            </select>
                        </div>
                       <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">TAHUN</label>
                            <select class="form-select py-2 border-2" name="tahun">
                                <option value="">Semua Tahun</option>
                                <?php 
                                 $tahun_sekarang = date('Y');
                                for($i = 2020; $i <= ($tahun_sekarang + 5); $i++) {
                                    echo "<option value='$i'>$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">STATUS</label>
                            <select class="form-select py-2" name="status">
                                <option value="">Semua Status</option>
                                <option value="Tersedia">Tersedia</option>
                                <option value="PO">PO</option>
                                <option value="Terbatas">Terbatas</option>
                                <option value="Tidak Tersedia">Tidak Tersedia</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end mt-5 pt-3 border-top">
                        <button type="submit" name="btn_export" class="btn-export-classic">
                            <i class="fas fa-file-download me-2"></i>Download Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../templates/footer.php'; ?>