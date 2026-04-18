<div class="stats-grid">
    <div class="stat-card-modern">
        <div class="stat-icon-modern">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content-modern">
            <div class="stat-number-modern"><?= number_format($total_laporan); ?></div>
            <div class="stat-label-modern">Total Laporan</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon-modern">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content-modern">
            <div class="stat-number-modern"><?= number_format($laporan_bulan_ini); ?></div>
            <div class="stat-label-modern">Laporan Bulan Ini</div>
        </div>
    </div>
</div>