<?php
$page_title = "Halaman Tidak Ditemukan";
require_once 'templates/header.php';
?>

<div class="error-container" style="text-align: center; padding: 100px 20px;">
    <div style="font-size: 120px; color: var(--primary-green); margin-bottom: 20px;">404</div>
    <h1 style="color: var(--text-dark); margin-bottom: 20px;">Halaman Tidak Ditemukan</h1>
    <p style="color: var(--text-muted); margin-bottom: 30px;">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
</div>

<?php require_once 'templates/footer.php'; ?>
