<section id="statistics" class="stats-section py-5 bg-white">
    <div class="container py-5">
        
        <div class="text-center mb-5 fade-in">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3 fw-semibold" style="letter-spacing: 1px;">
                DATA TERKINI
            </span>
            <h2 class="fw-bold text-dark mb-3" style="font-size: 2.5rem;">Statistik Ketersediaan Benih</h2>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 1.1rem;">
                Ringkasan data aktual dari seluruh balai pengujian dan sertifikasi benih secara Real Time.
            </p>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm stat-card-premium fade-in">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <i class="fas fa-database position-absolute text-success opacity-10" style="font-size: 5rem; top: -10px; right: -15px;"></i>
                        
                        <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-database fs-5 text-success stat-icon-inner"></i>
                        </div>
                        <h2 class="fw-bolder text-dark mb-1 display-6"><?php echo number_format($total_laporan); ?></h2>
                        <p class="text-muted fw-medium mb-0 small text-uppercase" style="letter-spacing: 0.5px;">Total Laporan</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm stat-card-premium fade-in" style="animation-delay: 0.1s;">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <i class="fas fa-building position-absolute text-success opacity-10" style="font-size: 5rem; top: -10px; right: -15px;"></i>
                        
                        <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-building fs-5 text-success stat-icon-inner"></i>
                        </div>
                        <h2 class="fw-bolder text-dark mb-1 display-6">4</h2>
                        <p class="text-muted fw-medium mb-0 small text-uppercase" style="letter-spacing: 0.5px;">Balai Aktif</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm stat-card-premium fade-in" style="animation-delay: 0.2s;">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <i class="fas fa-seedling position-absolute text-success opacity-10" style="font-size: 5rem; top: -10px; right: -15px;"></i>
                        
                        <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-seedling fs-5 text-success stat-icon-inner"></i>
                        </div>
                        <h2 class="fw-bolder text-dark mb-1 display-6"><?php echo mysqli_num_rows($result_komoditas); ?></h2>
                        <p class="text-muted fw-medium mb-0 small text-uppercase" style="letter-spacing: 0.5px;">Jenis Komoditas</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm stat-card-premium fade-in" style="animation-delay: 0.3s;">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <i class="fas fa-check-circle position-absolute text-success opacity-10" style="font-size: 5rem; top: -10px; right: -15px;"></i>
                        
                        <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-check-circle fs-5 text-success stat-icon-inner"></i>
                        </div>
                        <h2 class="fw-bolder text-dark mb-1 display-6">
                            <?php 
                            $tersedia = 0;
                            mysqli_data_seek($result_status, 0); 
                            while($row = mysqli_fetch_assoc($result_status)) {
                                if($row['status_ketersediaan'] == 'Tersedia') {
                                    $tersedia = $row['jumlah'];
                                    break;
                                }
                            }
                            echo number_format($tersedia);
                            mysqli_data_seek($result_status, 0); 
                            ?>
                        </h2>
                        <p class="text-muted fw-medium mb-0 small text-uppercase" style="letter-spacing: 0.5px;">Stok Tersedia</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .stat-card-premium {
        border-radius: 20px !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(34, 197, 94, 0.05) !important;
    }
    
    .stat-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(34, 197, 94, 0.12) !important;
        border-color: rgba(34, 197, 94, 0.2) !important;
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        background-color: rgba(34, 197, 94, 0.1);
        transition: all 0.4s ease;
    }

    .stat-icon-inner {
        transition: all 0.4s ease;
    }

    .stat-card-premium:hover .stat-icon-wrapper {
        background-color: #22c55e;
        transform: scale(1.1) rotate(5deg);
    }

    .stat-card-premium:hover .stat-icon-inner {
        color: #ffffff !important;
    }
</style>