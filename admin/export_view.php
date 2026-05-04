<?php
session_start();

// 1. Panggil koneksi database dengan jalur mutlak
require __DIR__ . '/../config/database.php';
global $conn;

// 2. Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    die("Anda tidak memiliki akses.");
}

// 3. LAKUKAN QUERY UNTUK MENGISI VARIABEL $result
// Ambil semua data laporan digabung dengan nama balai
$query = "SELECT l.*, b.nama_balai 
          FROM laporan l 
          JOIN balai b ON l.balai_id = b.id_balai 
          ORDER BY b.nama_balai ASC, l.id_laporan DESC";
          
$result = mysqli_query($conn, $query);

// Cek jika query gagal
if (!$result) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* PENGATURAN KERTAS WORD (LANDSCAPE) */
        @page WordSection1 {
            size: 841.9pt 595.3pt;
            mso-page-orientation: landscape;
            margin: 0.5in 0.5in 0.5in 0.5in;
        }
        div.WordSection1 {
            page: WordSection1;
        }

        /* FONT CALIBRI SESUAI EXCEL */
        body { 
            font-family: Calibri, sans-serif; 
            font-size: 11px; 
            color: #000000; 
        }
        
        /* HEADER JUDUL */
        .header-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-title { 
            color: #2E75B6; 
            font-size: 18px; 
            font-weight: bold; 
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header-sub { 
            font-size: 11px; 
            margin: 2px 0;
            color: #000000;
        }

        /* KOTAK INFORMASI */
        .info-box {
            margin-bottom: 10px;
            font-size: 11px;
            text-align: left;
        }
        
        /* TABEL EXCEL */
        table { 
            border-collapse: collapse; 
            width: 100%;
            mso-table-lspace: 0pt; 
            mso-table-rspace: 0pt;
        }
        
        /* HEADER TABEL (Warna Hijau sesuai gambar) */
        th { 
            background-color: #A9D08E !important; /* Hijau Excel */
            color: #000000 !important; 
            border: 1px solid #000000; 
            padding: 5px 10px; 
            text-align: center; 
            font-weight: bold;
            white-space: nowrap !important; 
        }
        
        td { 
            border: 1px solid #000000; 
            padding: 5px 10px; 
            vertical-align: middle; 
            white-space: nowrap !important; 
        }
        
        /* NAMA BALAI (Biru & Bold) */
        .balai-group { 
            color: #2E75B6 !important; 
            font-weight: bold !important; 
            text-align: center !important;
            vertical-align: middle !important;
        }
        
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        @media print {
            body { margin: 1cm; }
        }
    </style>
</head>
<body <?php if(isset($_POST['export_type']) && $_POST['export_type'] == 'pdf') echo 'onload="window.print()"'; ?>>

    <div class="WordSection1">
        <div class="header-container">
            <h2 class="header-title">LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>
            <div class="header-sub"><strong>Tanggal Export:</strong> <?= date('d/m/Y') ?></div>
            <div class="header-sub"><strong>Total Data:</strong> <?= mysqli_num_rows($result) ?> laporan</div>
            <div class="header-sub"><strong>Export oleh:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
        </div>

        <div class="info-box">
            <strong>INFORMASI LAPORAN:</strong><br>
            ▪ Laporan ini berisi data ketersediaan benih dari seluruh balai<br>
            ▪ Data diambil dari sistem SIPKBS pada <?= date('d/m/Y') ?><br>
            ▪ Format: <?= strtoupper($_POST['export_type'] ?? 'EXCEL') ?> - Kompatibel dengan Microsoft Office
        </div>

        <table>
            <thead>
                <tr>
                    <th>Balai</th>
                    <th>No</th>
                    <th>Komoditas</th>
                    <th>Kelompok Komoditas</th>
                    <th>Varietas</th>
                    <th>Kelas Benih</th>
                    <th>Jumlah Stok</th>
                    <th>Satuan</th>
                    <th>HPP</th>
                    <th>Keterangan</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Kita kumpulkan datanya dulu agar bisa menghitung rowspan (penggabungan sel Balai)
                $data_rows = [];
                mysqli_data_seek($result, 0); 
                while($row = mysqli_fetch_assoc($result)) {
                    $data_rows[] = $row;
                }
                
                // Grouping berdasarkan nama balai
                $grouped = [];
                foreach($data_rows as $row) {
                    $grouped[$row['nama_balai']][] = $row;
                }
                
                // Render Tabel
                foreach($grouped as $balai => $items):
                    $rowspan = count($items);
                    $first = true;
                    $no = 1; // Nomor urut di-reset jadi 1 setiap ganti Balai
                    
                    foreach($items as $item):
                ?>
                    <tr>
                        <?php if($first): ?>
                            <td rowspan="<?= $rowspan ?>" class="balai-group"><?= htmlspecialchars($balai) ?></td>
                        <?php $first = false; endif; ?>
                        
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($item['komoditas']) ?></td>
                        <td><?= htmlspecialchars($item['kelompok_komoditas'] ?: '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['varietas'] ?: '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['kelas_benih'] ?: '-') ?></td>
                        <td class="text-right"><?= number_format($item['jumlah_benih'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['satuan']) ?></td>
                        <td class="text-right"><?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['status_ketersediaan']) ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($item['created_at'] ?? 'now')) ?></td>
                    </tr>
                <?php 
                    endforeach;
                endforeach; 
                
                // Jika tidak ada data
                if(empty($data_rows)):
                ?>
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data laporan ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>