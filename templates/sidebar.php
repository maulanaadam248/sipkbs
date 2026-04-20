<?php if(isset($_SESSION['user_id'])): ?>
<aside class="sidebar" id="appSidebar">
    
    <div class="sidebar-brand">
        <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
            <i class="fas fa-seedling fs-6"></i>
        </div>
        SIKBS
    </div>

    <div class="sidebar-user-profile">
        <div class="sidebar-user-avatar">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name text-truncate" style="max-width: 170px;">
                <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?>
            </div>
            <div class="sidebar-user-role">
                <i class="fas fa-shield-alt me-1"></i>
                <?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Admin'; ?>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="../dashboard/dashboard.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie text-center" style="width: 25px;"></i> Ringkasan
            </a>
        </li>
        
        <li class="px-3 pt-3 pb-2 text-white opacity-50 small fw-bold" style="letter-spacing: 1px;">MENU UTAMA</li>
        
        <?php if($_SESSION['role'] == 'operator'): ?>
            <li>
                <a href="../laporan/tambah_laporan.php" class="<?php echo ($current_page == 'tambah_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle text-center" style="width: 25px;"></i> Input Laporan
                </a>
            </li>
            <li>
                <a href="../laporan/riwayat_laporan.php" class="<?php echo ($current_page == 'riwayat_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-history text-center" style="width: 25px;"></i> Riwayat Data
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="../admin/semua_laporan.php" class="<?php echo ($current_page == 'semua_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open text-center" style="width: 25px;"></i> Data Laporan
                </a>
            </li>
            <li>
                <a href="../admin/manajemen_user.php" class="<?php echo ($current_page == 'manajemen_user') ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog text-center" style="width: 25px;"></i> Kelola Akun
                </a>
            </li>
            <li>
                <a href="../admin/export.php" class="<?php echo ($current_page == 'export') ? 'active' : ''; ?>">
                    <i class="fas fa-file-excel text-center" style="width: 25px;"></i> Export Laporan
                </a>
            </li>
        <?php endif; ?>

        <li class="px-3 pt-4 pb-4">
            <a href="#" class="text-white mt-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#logoutModal" style="opacity: 0.8; transition: 0.3s;">
                <i class="fas fa-sign-out-alt text-center" style="width: 25px;"></i> Keluar Sistem
            </a>
        </li>
    </ul>
</aside>
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pb-5 px-5 mt-n2">              
                <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="fas fa-sign-out-alt fa-3x"></i>
                </div>                
                <h4 class="fw-bold text-dark mb-2" id="logoutModalLabel">Konfirmasi Keluar</h4>
                <p class="text-secondary mb-4">Apakah Anda yakin ingin keluar dari sistem SIKBS?</p>                
                <div class="d-flex w-100 gap-3 mt-3">
                    <button type="button" class="btn btn-light border w-50 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <a href="../auth/logout.php" class="btn btn-danger w-50 py-2 fw-semibold rounded-3 shadow-sm">
                        Ya, Keluar
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-wrapper" id="mainWrapper">
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const body = document.body;
        
        // 1. Seting Awal Saat Halaman Dibuka
        function initSidebarState() {
            if(window.innerWidth > 991) {
                if(localStorage.getItem('sidebarState') === 'closed') {
                    body.classList.remove('sidebar-open');
                } else {
                    body.classList.add('sidebar-open');
                }
            } else {
                body.classList.remove('sidebar-open');
            }
        }
        initSidebarState();

        // 2. Logika Saat Tombol Burger Diklik
        if(toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // SANGAT PENTING: Mencegah klik tembus ke document (halaman utama)
                
                body.classList.toggle('sidebar-open');
                
                // Simpan memori jika di PC
                if(window.innerWidth > 991) {
                    if(body.classList.contains('sidebar-open')) {
                        localStorage.setItem('sidebarState', 'open');
                    } else {
                        localStorage.setItem('sidebarState', 'closed');
                    }
                }
            });
        }

        // 3. FITUR BARU: Klik Sembarang Tempat di Halaman Utama untuk Menutup Sidebar
        document.addEventListener('click', function(e) {
            // Cek apakah sidebar sedang dalam keadaan terbuka
            if (body.classList.contains('sidebar-open')) {
                // Pastikan area yang diklik BUKAN sidebar itu sendiri, dan BUKAN tombol burger
                if (sidebar && !sidebar.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
                    
                    // Tutup sidebar
                    body.classList.remove('sidebar-open');
                    
                    // Update memori jika di PC
                    if (window.innerWidth > 991) {
                        localStorage.setItem('sidebarState', 'closed');
                    }
                }
            }
        });

        // 4. Logika Resize (Agar tidak error saat putar layar HP / ubah ukuran browser)
        window.addEventListener('resize', function() {
            if(window.innerWidth > 991) {
                if(localStorage.getItem('sidebarState') !== 'closed') {
                    body.classList.add('sidebar-open');
                }
            } else {
                body.classList.remove('sidebar-open');
            }
        });
    });
</script>