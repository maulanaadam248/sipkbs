<?php
session_start();
require_once 'config/database.php';

// Cek jika user sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard/dashboard.php");
    exit();
}

// Ambil data balai
$query_balai = "SELECT * FROM balai ORDER BY nama_balai";
$result_balai = mysqli_query($conn, $query_balai);

$page_title = "Pilih Balai - SIPKBS";
$no_layout = true;
$current_page = 'index';
require_once 'templates/header.php';
?>

<style>
    /* Latar belakang bersih dan terang */
    body {
        background-color: #f8f9fc;
    }

    /* Gaya kartu yang bersih dan modern */
    .clean-card {
        background-color: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04) !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
    }

    .clean-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.06);
        border-color: rgba(34, 197, 94, 0.3) !important;
    }

    /* --- ANIMASI NAIK-TURUN (BOB) PADA IKON --- */
    /* Kotak pastel tempat ikon berada - TETAP DIAM */
    .icon-box {
        width: 60px;
        height: 60px;
        background-color: #eefdf4;
        color: #16a34a;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    /* Animasi khusus untuk ikon di dalam kotak */
    .icon-box i {
        /* Panggil animasi 'floatingIcon', durasi 3 detik, berulang terus, dengan gerakan halus */
        animation: floatingIcon 3s ease-in-out infinite; 
    }

    /* Definisi gerakan naik-turun */
    @keyframes floatingIcon {
        0% {
            transform: translateY(0); /* Posisi awal */
        }
        50% {
            transform: translateY(-8px); /* Naik 8 pixel ke atas (ubah angka ini untuk mengatur tinggi lompatan) */
        }
        100% {
            transform: translateY(0); /* Kembali ke posisi awal */
        }
    }
    /* ------------------------------------------- */

</style>

<a href="index.php" class="btn text-secondary position-absolute top-0 start-0 m-4 z-3 fw-medium text-decoration-none">
    <i class="fas fa-arrow-left me-2"></i> Kembali
</a>

<div class="vh-100 d-flex flex-column justify-content-center align-items-center position-relative">
    
    <div class="container px-3 text-center">
        
        <div class="mb-5">
            <div class="icon-box mb-3" style="width: 80px; height: 80px; border-radius: 20px;">
                <i class="fas fa-seedling fa-2x"></i> </div>
            <h1 class="display-5 fw-bolder text-dark mb-2" style="letter-spacing: -1px;">SIPKBS</h1>
            <p class="text-secondary fw-normal mx-auto" style="max-width: 500px; font-size: 1.1rem;">
                Sistem Informasi Pelaporan Ketersediaan Benih Sumber
            </p>
        </div>

        <h2 class="h5 fw-semibold text-dark mb-4">Pilih balai untuk masuk</h2>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 justify-content-center mx-auto" style="max-width: 1000px;">
            <?php while($balai = mysqli_fetch_assoc($result_balai)): ?>
                <div class="col">
                    <a href="auth/login.php?balai=<?php echo $balai['id_balai']; ?>" class="text-decoration-none">
                        <div class="card h-100 rounded-4 clean-card">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box">
                                    <i class="fas fa-building fs-4"></i> </div>
                                <h3 class="h6 fw-bold text-dark mb-0">
                                    <?php echo htmlspecialchars($balai['nama_balai']); ?>
                                </h3>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>