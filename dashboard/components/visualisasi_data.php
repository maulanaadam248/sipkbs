<?php 
// Memastikan fungsi getBalaiColor tersedia di komponen ini
if (!function_exists('getBalaiColor')) {
    function getBalaiColor($nama_balai) {
        $balai = strtoupper($nama_balai);
        // Pemetaan warna berdasarkan nama balai
        $colors = [
            'PALMA' => '#28a745', // Hijau
            'TRI'   => '#007bff', // Biru
            'TAS'   => '#fd7e14', // Oranye
            'TROA'  => '#6f42c1'  // Ungu
        ];
        // Jika nama balai tidak ada di daftar atas, gunakan warna hijau default
        return isset($colors[$balai]) ? $colors[$balai] : '#198754'; 
    }
}
?>

<?php if(!empty($result_per_balai)): ?>
<div class="card border-0 shadow-sm rounded-4 bg-white h-100">
    <div class="card-body p-4 p-lg-5">
        
        <h5 class="fw-bold text-dark mb-4 d-flex align-items-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-succes text-secondary rounded-3 me-3" style="width: 45px; height: 45px;">
                <i class="fas fa-chart-bar"></i>
            </div>
            Visualisasi Data per Balai
        </h5>
        
        <div class="row g-4 mt-1">
            <?php 
            foreach($result_per_balai as $row): 
                // Hitung persentase
                $persentase = ($total_laporan > 0) ? round(($row['jumlah'] / $total_laporan) * 100, 1) : 0;
                
                // Ambil warna khusus untuk balai ini
                $warna_dinamis = getBalaiColor($row['nama_balai']);
            ?>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <span class="fw-bold text-dark text-truncate pe-2" title="<?= htmlspecialchars($row['nama_balai']); ?>">
                        <?= htmlspecialchars($row['nama_balai']); ?>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-white border-0 px-2 py-1 fw-medium rounded-2" style="background-color: <?= $warna_dinamis; ?>;">
                            <?= number_format($row['jumlah']); ?> Data
                        </span>
                        <span class="fw-bolder" style="color: <?= $warna_dinamis; ?>; min-width: 45px; text-align: right;">
                            <?= $persentase; ?>%
                        </span>
                    </div>
                </div>
                
                <div class="progress shadow-sm" style="height: 12px; border-radius: 10px; background-color: #e9ecef;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill" 
                         role="progressbar" 
                         style="width: <?= $persentase; ?>%; background-color: <?= $warna_dinamis; ?> !important;" 
                         aria-valuenow="<?= $persentase; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</div>
<?php endif; ?>