<?php
session_start();

// 1. Panggil koneksi database
require __DIR__ . '/../config/database.php';
global $conn;

// 2. Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    die("Anda tidak memiliki akses.");
}

// 3. TANGKAP DATA FILTER DARI FORM
$export_type = isset($_POST['export_type']) ? $_POST['export_type'] : 'excel';
$balai_id = isset($_POST['balai_id']) ? $_POST['balai_id'] : '';
$bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
$tahun = isset($_POST['tahun']) ? $_POST['tahun'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

// 4. BANGUN QUERY BERDASARKAN FILTER
$where_conditions = [];

if (!empty($balai_id)) {
    $where_conditions[] = "l.balai_id = " . (int)$balai_id;
}
if (!empty($bulan)) {
    $where_conditions[] = "l.bulan = '" . mysqli_real_escape_string($conn, $bulan) . "'";
}
if (!empty($tahun)) {
    $where_conditions[] = "l.tahun = " . (int)$tahun;
}
if (!empty($status)) {
    $where_conditions[] = "l.status_ketersediaan = '" . mysqli_real_escape_string($conn, $status) . "'";
}

// Gabungkan filter menjadi klausa WHERE
$where_clause = "";
if (count($where_conditions) > 0) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
}

// Eksekusi Query
$query = "SELECT l.*, b.nama_balai 
          FROM laporan l 
          JOIN balai b ON l.balai_id = b.id_balai 
          $where_clause 
          ORDER BY b.nama_balai ASC, l.id_laporan DESC";
          
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

// 5. ATUR HEADER BERDASARKAN FORMAT YANG DIPILIH
$date_str = date('Ymd_His');

if ($export_type == 'excel') {
    $filename = "Laporan_SIPKBS_" . $date_str . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
} elseif ($export_type == 'word') {
    $filename = "Laporan_SIPKBS_" . $date_str . ".doc";
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
} 
// Jika PDF, tidak perlu header attachment, biarkan browser membuka HTML dan memicu fungsi Print
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Laporan SIPKBS</title>
    <style>
        /* PENGATURAN KERTAS WORD/PDF (LANDSCAPE) */
        @page WordSection1 {
            size: 841.9pt 595.3pt;
            mso-page-orientation: landscape;
            margin: 0.5in 0.5in 0.5in 0.5in;
        }
        @page { size: landscape; margin: 1cm; }
        
        div.WordSection1 {
            page: WordSection1;
        }

        body { 
            font-family: Calibri, sans-serif; 
            font-size: 11px; 
            color: #000000; 
        }
        
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

        .info-box {
            margin-bottom: 10px;
            font-size: 11px;
            text-align: left;
        }
        
        table { 
            border-collapse: collapse; 
            width: 100%;
            mso-table-lspace: 0pt; 
            mso-table-rspace: 0pt;
        }
        
        th { 
            background-color: #A9D08E !important; 
            color: #000000 !important; 
            border: 1px solid #000000; 
            padding: 5px 10px; 
            text-align: center; 
            font-weight: bold;
            -webkit-print-color-adjust: exact;
        }
        
        td { 
            border: 1px solid #000000; 
            padding: 5px 10px; 
            vertical-align: middle; 
        }
        
        .balai-group { 
            color: #2E75B6 !important; 
            font-weight: bold !important; 
            text-align: center !important;
        }
        
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
    </style>
</head>
<body <?php if($export_type == 'pdf') echo 'onload="window.print()"'; ?>>

    <div class="WordSection1">
        <div class="header-container">
            <h2 class="header-title">LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>
            <div class="header-sub"><strong>Tanggal Export:</strong> <?= date('d/m/Y') ?></div>
            <div class="header-sub"><strong>Total Data:</strong> <?= mysqli_num_rows($result) ?> laporan</div>
            <div class="header-sub"><strong>Export oleh:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
        </div>

        <div class="info-box">
            <strong>FILTER DITERAPKAN:</strong><br>
            ▪ Balai: <?= !empty($balai_id) ? "ID " . htmlspecialchars($balai_id) : "Semua Balai" ?><br>
            ▪ Periode: <?= !empty($bulan) ? htmlspecialchars($bulan) : "Semua Bulan" ?> <?= !empty($tahun) ? htmlspecialchars($tahun) : "Semua Tahun" ?><br>
            ▪ Status: <?= !empty($status) ? htmlspecialchars($status) : "Semua Status" ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Balai</th>
                    <th>No</th>
                    <th>Komoditas</th>
                    <th>Varietas</th>
                    <th>Kelas Benih</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Terdistribusi</th>
                    <th>Lokasi Distribusi</th>
                    <th>HPP (Rp)</th>
                    <th>Keterangan</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $data_rows = [];
                mysqli_data_seek($result, 0); 
                while($row = mysqli_fetch_assoc($result)) {
                    $data_rows[] = $row;
                }
                
                $grouped = [];
                foreach($data_rows as $row) {
                    $grouped[$row['nama_balai']][] = $row;
                }
                
                foreach($grouped as $balai => $items):
                    $rowspan = count($items);
                    $first = true;
                    $no = 1;
                    
                    foreach($items as $item):
                ?>
                    <tr>
                        <?php if($first): ?>
                            <td rowspan="<?= $rowspan ?>" class="balai-group"><?= htmlspecialchars($balai) ?></td>
                        <?php $first = false; endif; ?>
                        
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($item['komoditas']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['varietas'] ?: '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['kelas_benih'] ?: '-') ?></td>
                        <td class="text-right"><?= number_format($item['jumlah_benih'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['satuan']) ?></td>
                        
                        <td class="text-right" style="color: #D97706; font-weight: bold;">
                            <?= number_format((int)($item['volume_penyaluran'] ?? 0), 0, ',', '.') ?>
                        </td>
                        <td><?= htmlspecialchars($item['lokasi_distribusi'] ?: '-') ?></td>

                        <td class="text-right"><?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['status_ketersediaan']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['bulan']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['tahun']) ?></td>
                    </tr>
                <?php 
                    endforeach;
                endforeach; 
                
                if(empty($data_rows)):
                ?>
                    <tr>
                        <td colspan="13" class="text-center">Tidak ada data laporan ditemukan dengan filter tersebut.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>