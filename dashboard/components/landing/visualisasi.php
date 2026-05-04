<?php 

// Fungsi sinkronisasi warna Balai
if (!function_exists('getBalaiColor')) {
    function getBalaiColor($nama_balai) {
        $balai = strtoupper($nama_balai);
        $colors = ['PALMA' => '#28a745', 'TRI' => '#007bff', 'TAS' => '#fd7e14', 'TROA' => '#6f42c1'];
        return isset($colors[$balai]) ? $colors[$balai] : '#6c757d';
    }
}

// 1. Ambil Data Balai
$query_balai = "SELECT b.nama_balai, COUNT(l.id_laporan) as jumlah FROM balai b LEFT JOIN laporan l ON b.id_balai = l.balai_id GROUP BY b.id_balai, b.nama_balai ORDER BY jumlah DESC";
$result_balai = mysqli_query($conn, $query_balai);
$label_balai = []; $data_balai = []; $warna_balai = [];

if($result_balai) {
    while($row = mysqli_fetch_assoc($result_balai)) {
        $label_balai[] = $row['nama_balai'];
        $data_balai[] = $row['jumlah'];
        $warna_balai[] = getBalaiColor($row['nama_balai']);
    }
}

// 2. Ambil Data Status Ketersediaan & LOGIKA WARNA DINAMIS
$query_status = "SELECT status_ketersediaan, COUNT(*) as jumlah FROM laporan GROUP BY status_ketersediaan";
$result_status = mysqli_query($conn, $query_status);
$label_status = []; $data_status = []; $warna_status = [];

if($result_status) {
    while($row = mysqli_fetch_assoc($result_status)) {
        $status = $row['status_ketersediaan'];
        $label_status[] = $status;
        $data_status[] = $row['jumlah'];
        
        // Memisahkan warna dengan tegas menggunakan pendeteksi kata
        $st_lower = strtolower(trim($status));
        if (strpos($st_lower, 'tidak') !== false) {
            $warna_status[] = '#ef4444'; // Merah
        } elseif (strpos($st_lower, 'tersedia') !== false) {
            $warna_status[] = '#10b981'; // Hijau Zamrud
        } elseif (strpos($st_lower, 'pesan') !== false) {
            $warna_status[] = '#0dcaf0'; // Biru Muda (Cyan)
        } elseif (strpos($st_lower, 'potensi') !== false) {
            $warna_status[] = '#0d6efd'; // Biru Tua (Primary)
        } elseif (strpos($st_lower, 'batas') !== false) {
            $warna_status[] = '#f59e0b'; // Oranye
        } else {
            $warna_status[] = '#8b5cf6'; // Ungu (Default)
        }
    }
}

// 3. Ambil Data Komoditas
$query_komoditas = "SELECT b.nama_balai, l.komoditas, COUNT(l.id_laporan) as jumlah FROM laporan l JOIN balai b ON l.balai_id = b.id_balai GROUP BY b.id_balai, b.nama_balai, l.komoditas ORDER BY jumlah DESC LIMIT 10";
$result_komoditas = mysqli_query($conn, $query_komoditas);
?>

<style>
    .table-green-theme {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        border: 1px solid #d1fae5 !important;
        width: 100% !important;
        background-color: #ffffff !important;
    }
    
    .table-green-theme thead th {
        background-color: #d1fae5 !important; /* Hijau mint kalem */
        color: #065f46 !important; /* Teks hijau zamrud gelap */
        border-bottom: 2px solid #a7f3d0 !important;
        border-top: none !important;
        padding: 14px 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
        vertical-align: middle !important;
    }

    .table-green-theme tbody tr {
        background-color: #ffffff !important;
        transition: all 0.2s ease !important;
    }

    /* Efek Zebra: Putih dan Hijau Pudar */
    .table-green-theme tbody tr:nth-of-type(even) {
        background-color: #f6fdf9 !important;
    }

    /* Efek Hover */
    .table-green-theme tbody tr:hover {
        background-color: #ecfdf5 !important;
    }

    .table-green-theme tbody td {
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        padding: 12px 15px !important;
        color: #334155 !important;
        vertical-align: middle !important;
    }
</style>

<section id="visualization" class="visualization-section">
    <div class="container">
        <h2 class="section-title fade-in">Visualisasi Data</h2>
        <div class="row">
            
            <div class="col-lg-6 mb-4">
                <div class="chart-container fade-in shadow-sm bg-white rounded p-4" style="height: 380px;">
                    <h5 class="mb-4 font-weight-bold text-success"><i class="fas fa-building me-2"></i>Laporan per Balai</h5>
                    <div style="height: 250px;">
                        <canvas id="balaiChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="chart-container fade-in shadow-sm bg-white rounded p-4" style="height: 380px;">
                    <h5 class="mb-4 font-weight-bold text-success"><i class="fas fa-check-circle me-2"></i>Status Ketersediaan</h5>
                    <div style="height: 250px;">
                        <canvas id="grafikStatusBenih"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12 mb-4">
                <div class="chart-container fade-in shadow-sm bg-white rounded p-4" style="height: auto;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="font-weight-bold text-success mb-0">
                            <i class="fas fa-list-alt me-2"></i>Data Terbaru Ketersediaan Benih
                        </h5>
                        <span class="badge bg-light text-muted border px-3 py-2">Menampilkan 5 Data Terbaru</span>
                    </div>
                    
                    <div class="table-responsive px-2">
                        <table class="table-green-theme align-middle mb-0" style="font-size: 0.95rem;">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="12%">Balai</th>
                                    <th width="20%">Komoditas (Varietas)</th>
                                    <th width="12%">Kelas</th>
                                    <th width="15%">Stok & Harga</th>
                                    <th width="12%">Status</th>
                                    <th width="14%">Periode</th>
                                    <th class="text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // 1. LOGIKA RESET TANGGAL 20
                                $hari_ini = (int)date('d');
                                if ($hari_ini >= 20) {
                                    $tgl_mulai = date('Y-m-20 00:00:00'); // Tanggal 20 bulan ini
                                } else {
                                    $tgl_mulai = date('Y-m-20 00:00:00', strtotime('-1 month')); // Tanggal 20 bulan lalu
                                }

                                // 2. Terapkan filter ke Query (hanya tampilkan >= tanggal mulai)
                                // DIBATASI HANYA 5 DATA TERBARU
                                $query_semua = "SELECT l.*, b.nama_balai 
                                                FROM laporan l 
                                                JOIN balai b ON l.balai_id = b.id_balai 
                                                WHERE l.created_at >= '$tgl_mulai'
                                                ORDER BY l.id_laporan DESC 
                                                LIMIT 5";
                                                
                                $result_semua = mysqli_query($conn, $query_semua);
                                $no = 1;

                                if($result_semua && mysqli_num_rows($result_semua) > 0) {
                                    while($row = mysqli_fetch_assoc($result_semua)) {
                                        
                                        // AMBIL WARNA BALAI SECARA DINAMIS
                                        $warna_balai_tabel = getBalaiColor($row['nama_balai']);
                                        
                                        // LOGIKA BADGE TABEL DINAMIS (SAMA SEPERTI QUERY ATAS)
                                        $status = $row['status_ketersediaan'];
                                        $st_lower = strtolower(trim($status));
                                        $badge_class = 'border-secondary text-secondary'; 
                                        
                                        if (strpos($st_lower, 'tidak') !== false) {
                                            $badge_class = 'border-danger text-danger'; 
                                        } elseif (strpos($st_lower, 'tersedia') !== false) {
                                            $badge_class = 'border-success text-success'; 
                                        } elseif (strpos($st_lower, 'pesan') !== false) {
                                            $badge_class = 'border-info text-info-emphasis'; // Biru Muda
                                        } elseif (strpos($st_lower, 'potensi') !== false) {
                                            $badge_class = 'border-primary text-primary'; // Biru Tua
                                        } elseif (strpos($st_lower, 'batas') !== false) {
                                            $badge_class = 'border-warning text-warning-emphasis'; // Oranye
                                        }
                                ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                        
                                        <td>
                                            <span class="badge rounded-pill px-3 shadow-sm" style="background-color: <?= $warna_balai_tabel; ?>; color: white;">
                                                <?= htmlspecialchars($row['nama_balai']); ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <div class="fw-bold text-success"><?= htmlspecialchars($row['komoditas']); ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($row['kelompok_komoditas'] ?: '-'); ?> | <em><?= htmlspecialchars($row['varietas'] ?: '-'); ?></em></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['kelas_benih'] ?: '-'); ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= number_format($row['jumlah_benih']); ?> <span class="small fw-normal"><?= htmlspecialchars($row['satuan']); ?></span></div>
                                            <div class="small text-success fw-medium"><?= !empty($row['harga_satuan']) ? 'Rp ' . number_format($row['harga_satuan'], 0, ',', '.') : '-'; ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-transparent border <?= $badge_class; ?> px-2 py-1 rounded-2 fw-bold" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark"><?= htmlspecialchars($row['bulan']); ?></div>
                                            <div class="small text-muted fw-bold"><?= htmlspecialchars($row['tahun']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <a href="dashboard/components/landing/detail_publik.php?id=<?= $row['id_laporan']; ?>" class="btn btn-outline-info btn-sm rounded-3 px-3 shadow-sm" title="Lihat Detail">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                    } // end while
                                } else {
                                    echo "<tr><td colspan='8' class='text-center py-5 text-muted bg-light rounded-3'><i class='fas fa-box-open fa-3x mb-3 opacity-25'></i><p class='mb-0 fw-bold'>Belum ada data ketersediaan benih.</p></td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="dashboard/components/landing/semua_laporan_publik.php" class="btn btn-success px-5 py-2 rounded-pill fw-bold shadow-sm" style="transition: all 0.3s ease;">
                            Lihat Seluruh Laporan <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                    
                </div>
            </div>
            </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // MENGGUNAKAN ID BARU: grafikStatusBenih
    const ctxStatus = document.getElementById('grafikStatusBenih');
    
    if(ctxStatus) {
        const chartLabels = <?= json_encode($label_status); ?>;
        const chartData = <?= json_encode($data_status); ?>;

        // LOGIKA WARNA CHART JS DINAMIS
        const chartColors = chartLabels.map(label => {
            let namaStatus = label.trim().toLowerCase();
            
            if (namaStatus.includes('tidak')) return '#ef4444';       // Merah (Tidak Tersedia)
            if (namaStatus.includes('tersedia')) return '#10b981';    // Hijau (Tersedia)
            if (namaStatus.includes('pesan')) return '#0dcaf0';       // Cyan/Biru Muda (Sudah Dipesan)
            if (namaStatus.includes('potensi')) return '#0d6efd';     // Primary/Biru Tua (Potensi Panen)
            if (namaStatus.includes('batas')) return '#f59e0b';       // Oranye (Terbatas)
            
            return '#8b5cf6'; // Ungu/Lainnya (Default)
        });

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                }
            }
        });
    }
});
</script>