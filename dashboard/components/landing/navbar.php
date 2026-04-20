<nav class="navbar navbar-expand-lg navbar-dark fixed-top transition-nav" id="mainNav">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#home">
            <i class="fas fa-seedling me-2"></i> SIKBS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link px-3" href="#home">Beranda</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#statistics">Statistik</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#features">Layanan</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#visualization">Visualisasi</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#contact">Kontak</a></li>
                <li class="nav-item">
                    <a class="nav-link btn btn-success text-white px-4 ms-lg-3 rounded-pill shadow-sm" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* KONDISI AWAL (DI ATAS): Memaksa Transparan 100% */
    .transition-nav {
        background-color: transparent !important; 
        transition: all 0.4s ease-in-out;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    /* KONDISI KEDUA (SETELAH DI-SCROLL): Berubah jadi Putih */
    .navbar-scrolled {
        background-color: #ffffff !important; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding-top: 10px;
        padding-bottom: 10px;
    }

    /* Memastikan teks/link berubah warna jadi gelap saat background putih */
    .navbar-scrolled .nav-link {
        color: #333 !important;
        font-weight: 500;
    }

    /* Warna logo/brand saat di-scroll */
    .navbar-scrolled .navbar-brand {
        color: #198754 !important; 
    }

    /* Efek hover pada link */
    .nav-link:hover {
        color: #22c55e !important; 
    }
</style>

<script>
    (function() {
        const nav = document.getElementById('mainNav');
        
        // Fungsi untuk mengecek posisi scroll
        function checkScroll() {
            if (window.scrollY > 50) {
                // Jika halaman di-scroll ke bawah lebih dari 50px
                nav.classList.add('navbar-scrolled');
                nav.classList.remove('navbar-dark'); 
                nav.classList.add('navbar-light');   
            } else {
                // Jika halaman berada di paling atas
                nav.classList.remove('navbar-scrolled');
                nav.classList.add('navbar-dark');    
                nav.classList.remove('navbar-light');
            }
        }

        // Jalankan saat halaman di-scroll
        window.addEventListener('scroll', checkScroll);
        
        // Wajib: Jalankan sekali saat halaman pertama kali dimuat
        checkScroll();
    })();
</script>