<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'SIKBS'; ?> - Sistem Informasi Ketersediaan Benih Sumber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="<?php echo isset($css_path) ? $css_path : '../assets/css/modern-ui.css'; ?>" rel="stylesheet">
    
    <style>
        /* BASE RESET & TYPOGRAPHY */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* --- 1. SIDEBAR MODERN --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
            z-index: 1040;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-100%);
            display: flex;
            flex-direction: column;
        }

        body.sidebar-open .sidebar {
            transform: translateX(0);
        }

        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-user-profile {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(0,0,0,0.15);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .sidebar-user-info { color: white; line-height: 1.2; }
        .sidebar-user-name { font-weight: 600; font-size: 0.95rem; }
        .sidebar-user-role { font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;}

        .sidebar-menu {
            flex-grow: 1;
            padding: 1rem 0;
            overflow-y: auto;
            list-style: none;
            margin: 0;
            padding-left: 0;
        }

        .sidebar-menu li { padding: 0 1rem; margin-bottom: 0.25rem; }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-menu a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .sidebar-menu a.active {
            color: #16a34a;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            font-weight: 600;
        }

        /* --- 2. HEADER & KONTEN UTAMA --- */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1030;
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Jarak Aman Default (Kiri dan Kanan) */
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .btn-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-toggle:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #16a34a;
        }

        .main-wrapper {
            padding-top: 90px;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            padding-bottom: 2rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        /* --- 3. OVERLAY (LAPISAN GELAP) --- */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(2px);
            z-index: 1035;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            display: none; /* Default Mati */
        }

        /* --- 4. RESPONSIVE MEDIA QUERIES --- */
        
        /* UNTUK HP / TABLET */
        @media (max-width: 991.98px) {
            .sidebar-overlay {
                display: block; /* Nyalakan fungsi overlay di HP */
            }
            body.sidebar-open .sidebar-overlay {
                opacity: 1;
                visibility: visible; /* Gelapkan layar saat sidebar terbuka */
            }
        }

        /* UNTUK LAPTOP / PC */
        @media (min-width: 992px) {
            .top-header {
                padding-left: 3rem; /* Jarak aman kiri di laptop */
                padding-right: 3rem; /* Jarak aman kanan di laptop */
            }
            
            body.sidebar-open .top-header {
                /* 280px (lebar sidebar) + 3rem (jarak aman kiri) */
                padding-left: calc(280px + 3rem) !important; 
            }
            body.sidebar-open .main-wrapper {
                margin-left: 280px; 
            }
        }
    </style>
</head>
<body>

<?php if(isset($_SESSION['user_id'])): ?>
<header class="top-header fixed-top bg-white border-bottom shadow-sm d-flex align-items-center justify-content-between">
    
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light text-black py-2 px-3" id="toggleBtn">
            <i class="fas fa-bars"></i>
            <span class="d-none d-sm-inline ms-1 fw-medium" style="font-size: 0.95rem;">Menu</span>
        </button>
        
        <a class="text-decoration-none fw-bolder fs-4 text-success ms-2 border-start ps-3 border-2" href="#home">
            <i class="fas fa-seedling me-1"></i> SIKBS
        </a>
    </div>

    <div class="d-flex align-items-center">
        <span class="badge text-secondary px-3 py-2 fs-6 fw-medium ">
            <i class="far fa-calendar-alt me-2 text-success"></i> <?php echo date('d M Y'); ?>
        </span>
    </div>

</header>
<?php endif; ?>