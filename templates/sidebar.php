<?php if(isset($_SESSION['user_id'])): ?>

<style>
    /* --- SIKBS MODERN SIDEBAR MENU --- */
    .sidebar-menu {
        padding: 0 15px !important; /* Jarak aman dari tepi layar */
        list-style: none;
        margin-top: 15px;
    }

    .sidebar-menu li {
        margin-bottom: 8px; /* Jarak antar menu agar tidak dempet */
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 12px 18px;
        color: rgba(255, 255, 255, 0.7) !important; /* Abu-abu transparan elegan */
        text-decoration: none;
        border-radius: 12px !important; /* Kunci modern: Sudut melengkung halus */
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
    }

    .sidebar-menu a i {
        margin-right: 12px;
        font-size: 1.15rem;
        transition: transform 0.3s ease;
        width: 25px;
        text-align: center;
    }

    /* Efek Saat Kursor Diarahkan (Hover) */
    .sidebar-menu a:hover {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1); /* Efek kaca tipis */
        transform: translateX(5px); /* Animasi geser dikit ke kanan */
    }

    .sidebar-menu a:hover i {
        transform: scale(1.15); /* Ikon membesar dikit */
    }

    /* Efek Saat Menu Sedang Dibuka (Active) */
    .sidebar-menu a.active {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        color: #10b981 !important; /* Hijau Emerald */
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar-menu a.active i {
        color: #10b981 !important;
    }
    
    /* Judul Kategori (MENU UTAMA) */
    .sidebar-heading-modern {
        padding: 0 18px;
        font-size: 0.75rem;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
        margin-top: 25px;
    }
    
    /* Perbaikan untuk Dropdown Admin agar serasi */
    #collapseInputAdmin {
        background-color: rgba(0,0,0,0.15);
        border-radius: 12px;
        margin-top: 5px;
        padding: 5px 0;
    }
    #collapseInputAdmin a {
        padding: 8px 18px;
        font-size: 0.85rem;
        border-radius: 8px !important;
        margin: 0 10px 4px 10px;
    }
</style>

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
                <i class="fas fa-chart-pie"></i> Ringkasan
            </a>
        </li>
        
        <li class="sidebar-heading-modern">MENU UTAMA</li>
        
        <?php if($_SESSION['role'] == 'operator'): ?>
            <li>
                <a href="../laporan/tambah_laporan.php" class="<?php echo ($current_page == 'tambah_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Input Laporan
                </a>
            </li>
            <li>
                <a href="../laporan/riwayat_laporan.php" class="<?php echo ($current_page == 'riwayat_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Riwayat Data
                </a>
            </li>
        <?php else: ?>
            
            <?php
                // Ambil daftar balai untuk menu dropdown admin
                $query_nav_balai = mysqli_query($conn, "SELECT id_balai, nama_balai FROM balai ORDER BY nama_balai ASC");
            ?>
            <li class="nav-item">
                <a href="#" class="nav-link d-flex align-items-center <?php echo (strpos($current_page, 'tambah_laporan') !== false) ? 'active' : ''; ?>" data-bs-toggle="collapse" data-bs-target="#collapseInputAdmin" aria-expanded="false">
                    <i class="fas fa-keyboard"></i> 
                    <span class="ms-1">Input Data Balai</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem; margin-right:0;"></i>
                </a>
                <div id="collapseInputAdmin" class="collapse mt-1">
                    <ul class="list-unstyled fw-normal pb-1 small ms-4 ps-3 mt-2 mb-2" style="border-left: 1px solid rgba(255,255,255,0.2);">
                        <?php while($nav_balai = mysqli_fetch_assoc($query_nav_balai)): ?>
                            <li class="mb-2 mt-2">
                                <a href="../laporan/tambah_laporan.php?balai_id=<?= $nav_balai['id_balai']; ?>" class="d-block py-1">
                                    <i class="fas fa-angle-right me-2"></i> <?= htmlspecialchars($nav_balai['nama_balai']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </li>

            <li>
                <a href="../admin/semua_laporan.php" class="<?php echo ($current_page == 'semua_laporan') ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open"></i> Data Laporan
                </a>
            </li>
       
            <li>
                <a href="../admin/manajemen_user.php" class="<?php echo ($current_page == 'manajemen_user') ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Kelola Akun
                </a>
            </li>
            <li>
                <a href="../admin/export.php" class="<?php echo ($current_page == 'export') ? 'active' : ''; ?>">
                    <i class="fas fa-file-excel"></i> Export Laporan
                </a>
            </li>
        <?php endif; ?>

        <li style="margin-top: 30px;">
            <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i> Keluar Sistem
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

        if(toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                body.classList.toggle('sidebar-open');
                
                if(window.innerWidth > 991) {
                    if(body.classList.contains('sidebar-open')) {
                        localStorage.setItem('sidebarState', 'open');
                    } else {
                        localStorage.setItem('sidebarState', 'closed');
                    }
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (body.classList.contains('sidebar-open')) {
                if (sidebar && !sidebar.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
                    body.classList.remove('sidebar-open');
                    if (window.innerWidth > 991) {
                        localStorage.setItem('sidebarState', 'closed');
                    }
                }
            }
        });

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