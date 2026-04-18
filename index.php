<?php
require_once 'config/database.php';

// 1. KUMPULAN QUERY DATABASE
$query_total = "SELECT COUNT(*) as total FROM laporan";
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, $query_total))['total'];

$query_balai = "SELECT b.nama_balai, COUNT(l.id_laporan) as jumlah 
                FROM balai b LEFT JOIN laporan l ON b.id_balai = l.balai_id 
                GROUP BY b.id_balai, b.nama_balai ORDER BY jumlah DESC";
$result_balai_stats = mysqli_query($conn, $query_balai);

// Query Komoditas sudah di-JOIN dengan Balai
$query_komoditas = "SELECT l.komoditas, b.nama_balai, COUNT(l.id_laporan) as jumlah 
                    FROM laporan l 
                    JOIN balai b ON l.balai_id = b.id_balai 
                    GROUP BY l.komoditas, b.nama_balai 
                    ORDER BY jumlah DESC LIMIT 10";
$result_komoditas = mysqli_query($conn, $query_komoditas);

$query_status = "SELECT status_ketersediaan, COUNT(*) as jumlah FROM laporan GROUP BY status_ketersediaan";
$result_status = mysqli_query($conn, $query_status);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPKBS - Sistem Informasi Ketersediaan Benih</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="assets/css/landing.css"> 
</head>
<body>

    <?php 
    // Memanggil komponen (menggunakan folder dashboard sesuai strukturmu)
    include 'dashboard/components/landing/navbar.php';
    include 'dashboard/components/landing/hero.php';
    include 'dashboard/components/landing/statistik.php';
    include 'dashboard/components/landing/layanan.php';
    include 'dashboard/components/landing/visualisasi.php';
    include 'dashboard/components/landing/kontak.php';
    include 'dashboard/components/landing/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efek Navbar & Animasi
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.navbar-custom');
            if(nav) window.scrollY > 50 ? nav.classList.add('scrolled') : nav.classList.remove('scrolled');
        });

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(el => obs.observe(el));
        
        // 1. Grafik Balai (Warna tersinkronisasi)
        const balaiLabels = <?php 
            $b_labels = []; $b_values = []; $b_colors = [];
            // Anti-Error: Cek apakah data ada sebelum diproses
            if($result_balai_stats && mysqli_num_rows($result_balai_stats) > 0) {
                mysqli_data_seek($result_balai_stats, 0);
                while($r = mysqli_fetch_assoc($result_balai_stats)) {
                    $b_labels[] = $r['nama_balai']; 
                    $b_values[] = $r['jumlah'];
                    
                    $balai_name = strtoupper($r['nama_balai']);
                    if($balai_name == 'PALMA') $b_colors[] = '#28a745';
                    else if($balai_name == 'TRI') $b_colors[] = '#007bff';
                    else if($balai_name == 'TAS') $b_colors[] = '#fd7e14';
                    else if($balai_name == 'TROA') $b_colors[] = '#6f42c1';
                    else $b_colors[] = '#6c757d';
                }
            }
            echo json_encode($b_labels); 
        ?>;
        
        const balaiCanvas = document.getElementById('balaiChart');
        if (balaiCanvas) {
            new Chart(balaiCanvas, {
                type: 'bar',
                data: {
                    labels: balaiLabels,
                    datasets: [{ 
                        label: 'Jumlah Laporan', 
                        data: <?= json_encode($b_values); ?>, 
                        backgroundColor: <?= json_encode($b_colors); ?>,
                        borderRadius: 5
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        // 2. Grafik Status
        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: <?php 
                        $s_labels = []; $s_values = [];
                        if($result_status && mysqli_num_rows($result_status) > 0) {
                            mysqli_data_seek($result_status, 0);
                            while($r = mysqli_fetch_assoc($result_status)) {
                                $s_labels[] = $r['status_ketersediaan']; $s_values[] = $r['jumlah'];
                            }
                        }
                        echo json_encode($s_labels); 
                    ?>,
                    datasets: [{ 
                        data: <?= json_encode($s_values); ?>, 
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107'], 
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%' }
            });
        }
    </script>
</body>
</html>