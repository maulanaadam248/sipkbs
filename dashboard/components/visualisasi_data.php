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

<div class="row g-4">
    
    <div class="col-12 col-lg-6 d-flex flex-column">
        <?php if(!empty($result_per_balai)): ?>
        <div class="card border-0 shadow-sm rounded-4 bg-white flex-grow-1">
            <div class="card-body p-4 p-lg-5">
                
                <div class="d-flex align-items-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3 me-3" style="width: 45px; height: 45px;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Visualisasi Data per Balai</h5>
                </div>
                
                <div class="row g-4 mt-1">
                    <?php 
                    foreach($result_per_balai as $row): 
                        // Hitung persentase
                        $persentase = ($total_laporan > 0) ? round(($row['jumlah'] / $total_laporan) * 100, 1) : 0;
                        
                        // Ambil warna khusus untuk balai ini
                        $warna_dinamis = getBalaiColor($row['nama_balai']);
                    ?>
                    <div class="col-12">
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
    </div>

    <div class="col-12 col-lg-6 d-flex flex-column">
        <div class="card border-0 shadow-sm rounded-4 bg-white flex-grow-1">
            <div class="card-body p-4 p-lg-5 d-flex flex-column">
                
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Laporan Terbaru</h5>
                    </div>
                    <a href="../admin/semua_laporan.php" class="btn btn-sm btn-light border text-success fw-medium rounded-3 px-3">Lihat Semua</a>
                </div>
                
                <div class="table-responsive flex-grow-1">
                    <table class="table align-middle table-borderless mb-0">
                        <tbody>
                            <?php
                            // Ambil 6 laporan terakhir dari semua balai
                            $query_recent = "SELECT l.komoditas, l.varietas, l.jumlah_benih, l.satuan, l.status_ketersediaan, b.nama_balai 
                                             FROM laporan l 
                                             JOIN balai b ON l.balai_id = b.id_balai 
                                             ORDER BY l.id_laporan DESC LIMIT 5";
                            $result_recent = mysqli_query($conn, $query_recent);

                            if(mysqli_num_rows($result_recent) > 0):
                                while($row_recent = mysqli_fetch_assoc($result_recent)):
                                    
                                    $warna_balai_recent = getBalaiColor($row_recent['nama_balai']);
                                    
                                    // Set warna text status
                                    $status = $row_recent['status_ketersediaan'];
                                    $status_color = 'text-secondary'; 
                                    if($status == 'Tersedia') $status_color = 'text-success'; 
                                    elseif($status == 'Tidak Tersedia') $status_color = 'text-danger'; 
                                    elseif($status == 'Terbatas') $status_color = 'text-warning'; 
                                    elseif($status == 'PO') $status_color = 'text-primary';
                            ?>
                            <tr class="border-bottom" style="border-color: #f1f5f9 !important;">
                                <td class="ps-0 py-3">
                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><?= htmlspecialchars($row_recent['komoditas']); ?></div>
                                    <div class="text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($row_recent['varietas'] ?: 'Tanpa Varietas'); ?></div>
                                </td>
                                <td class="py-3">
                                    <span class="badge px-2 py-1 rounded-2 shadow-sm" style="background-color: <?= $warna_balai_recent; ?>; color: white; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.3px;">
                                        <?= htmlspecialchars($row_recent['nama_balai']); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-end pe-0">
                                    <div class="fw-bold text-dark fs-6"><?= number_format($row_recent['jumlah_benih']); ?> <span class="fw-normal text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($row_recent['satuan']); ?></span></div>
                                    <div class="fw-bold small <?= $status_color; ?>"><?= htmlspecialchars($status); ?></div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 small fw-medium">Belum ada data laporan terbaru.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
    
</div>