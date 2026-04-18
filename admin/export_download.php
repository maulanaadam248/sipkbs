<?php
session_start();
require_once '../config/database.php';
require_once '../includes/simple_pdf.php';
require_once '../includes/simple_file_helper.php';

// Set timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role - hanya admin yang bisa akses
if($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

// Cek apakah ada data export
if(!isset($_SESSION['export_data']) || empty($_SESSION['export_data'])) {
    $_SESSION['error'] = "Tidak ada data untuk di-download!";
    header("Location: export.php");
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : ''; // print atau download
$export_data = $_SESSION['export_data'];
$export_filter = $_SESSION['export_filter'];

// Bersihkan output buffer
if (ob_get_length()) ob_end_clean();

if($type == 'pdf') {
    // Cek action: download langsung atau preview di browser
    if($action == 'download') {
        // Generate PDF langsung dan download
        $filename = "laporan_sipkbs_" . date('Y-m-d_H-i-s') . ".pdf";
        
        // Buat HTML untuk PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SIPKBS</title>
    <style>
        @page { size: A4; margin: 1cm; }
        body { font-family: Arial, sans-serif; margin: 0; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #2E75B6; padding-bottom: 15px; }
        .header h2 { color: #2E75B6; margin: 0 0 10px 0; font-size: 18px; font-weight: bold; }
        .header p { margin: 5px 0; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; border: 1px solid #000; }
        th { background: #2E75B6; color: white; font-weight: bold; text-align: center; border: 1px solid #000; padding: 8px 6px; font-size: 11px; }
        td { border: 1px solid #000; padding: 6px 4px; vertical-align: top; font-size: 10px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 20px; padding: 10px; border-top: 2px solid #2E75B6; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>';
        
        $export_time = date('d/m/Y');
        
        if(!empty($export_filter['bulan']) || !empty($export_filter['tahun'])) {
            $html .= '<p><strong>Periode:</strong> ' . 
                     (!empty($export_filter['bulan']) ? $export_filter['bulan'] . ' ' : '') . 
                     (!empty($export_filter['tahun']) ? $export_filter['tahun'] : '') . '</p>';
        }
        
        $html .= '<p><strong>Tanggal Export:</strong> ' . $export_time . '</p>';
        $html .= '<p><strong>Total Data:</strong> ' . count($export_data) . ' laporan</p>';
        $html .= '<p><strong>Export oleh:</strong> ' . $_SESSION['username'] . '</p>';
        $html .= '</div>
    
    <table>
        <thead>
            <tr>
                <th>Balai</th>
                <th>No</th>
                <th>Komoditas</th>
                <th>Kelompok Komoditas</th>
                <th>Satuan</th>
                <th class="text-right">Harga Satuan</th>
                <th>Varietas</th>
                <th>Kelas Benih</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah Benih</th>
                <th>Deskripsi</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>';
        
        // Kelompokkan data berdasarkan balai
        $grouped_data = [];
        foreach($export_data as $row) {
            $balai_name = $row['nama_balai'];
            if(!isset($grouped_data[$balai_name])) {
                $grouped_data[$balai_name] = [];
            }
            $grouped_data[$balai_name][] = $row;
        }
        
        $no = 1;
        foreach($grouped_data as $balai_name => $balai_data) {
            $rowspan = count($balai_data);
            $first_row = true;
            $balai_no = 1;
            
            foreach($balai_data as $index => $row) {
                $html .= '<tr>';
                
                if($first_row) {
                    $html .= '<td rowspan="' . $rowspan . '" style="vertical-align: middle; text-align: center; font-weight: bold; background-color: #f0f8ff; border: 1px solid #000;">' . htmlspecialchars($balai_name) . '</td>';
                }
                
                $html .= '<td class="text-center">' . $balai_no . '</td>';
                $balai_no++;
                $html .= '<td>' . htmlspecialchars($row['komoditas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kelompok_komoditas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['satuan']) . '</td>';
                $html .= '<td class="text-right">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['varietas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kelas_benih']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['status_ketersediaan']) . '</td>';
                $html .= '<td class="text-right">' . htmlspecialchars($row['jumlah_benih']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['deskripsi'] ?? '') . '</td>';
                $fotoCell = '';
                if (!empty($row['foto'])) {
                    // Cek apakah path mengandung 'uploads' atau 'assets/uploads'
                    if (strpos($row['foto'], 'uploads/') !== false):
                        // Path sudah lengkap, gunakan langsung
                        $fotoUrl = getDirectFileUrl($row['foto']);
                    else:
                        // Path hanya nama file, asumsikan di assets/uploads
                        $fotoUrl = getAssetsUploadsUrl($row['foto']);
                    endif;
                    
                    $fotoCell = '<a href="' . $fotoUrl . '" target="_blank" style="color: #2E75B6; text-decoration: underline;">' . basename($row['foto']) . '</a>';
                } else {
                    $fotoCell = '-';
                }
                $html .= '<td>' . $fotoCell . '</td>';
                $html .= '</tr>';
                
                $first_row = false;
            }
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p><strong>LAPORAN DIHASILKAN OLEH SISTEM INFORMASI PENGELOLAAN KOMODITAS BENIH SIPKBS</strong></p>
        <p>Generated: ' . $export_time . ' | User: ' . $_SESSION['username'] . '</p>
        <p>© 2026 SIPKBS - Sistem Informasi Pelaporan Ketersediaan Benih Sumber</p>
    </div>
</body>
</html>';
        
        // Generate dan download PDF
        generatePDF($html, $filename, true);
        exit();
        
    } else {
        // Preview HTML di browser (print-friendly)
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="laporan_' . date('Y-m-d') . '.html"');
        header('Cache-Control: private, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Buat HTML yang langsung bisa di-print tanpa UI tambahan
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SIPKBS - Print Ready</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            font-size: 12px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px solid #2E75B6; 
            padding-bottom: 15px; 
        }
        .header h2 { 
            color: #2E75B6; 
            margin: 0 0 10px 0; 
            font-size: 18px; 
            font-weight: bold;
        }
        .header p { 
            margin: 5px 0; 
            font-size: 12px; 
            color: #333;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 11px;
            border: 1px solid #000;
        }
        th { 
            background: #2E75B6; 
            color: white; 
            font-weight: bold; 
            text-align: center; 
            border: 1px solid #000; 
            padding: 8px 6px;
            font-size: 11px;
        }
        td { 
            border: 1px solid #000; 
            padding: 6px 4px; 
            vertical-align: top;
            font-size: 10px;
        }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-top: 2px solid #2E75B6;
            font-size: 10px;
            color: #666;
        }
        /* Hide print button */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 15px; background: #f0f8ff; border: 1px solid #2E75B6; margin-bottom: 10px; border-radius: 5px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <p style="margin: 0; font-size: 14px; font-weight: bold; color: #2E75B6;">
                    📄 PREVIEW LAPORAN
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #333;">
                    💡 Gunakan tombol Print untuk mencetak laporan
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" style="background: linear-gradient(135deg, #2E75B6, #1F5A8A); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: bold; box-shadow: 0 2px 5px rgba(46, 117, 182, 0.3);">
                    <i class="fas fa-print" style="margin-right: 5px;"></i>
                    Print & Save As
                </button>
            </div>
        </div>
    </div>
        
    <div class="header">
        <h2>LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>';
        
        // Ambil waktu export yang tepat (waktu saat export dilakukan)
        $export_time = date('d/m/Y');
        
        if(!empty($export_filter['bulan']) || !empty($export_filter['tahun'])) {
            $html .= '<p><strong>Periode:</strong> ' . 
                     (!empty($export_filter['bulan']) ? $export_filter['bulan'] . ' ' : '') . 
                     (!empty($export_filter['tahun']) ? $export_filter['tahun'] : '') . '</p>';
        }
        
        $html .= '</div>
    
    <table>
        <thead>
            <tr>
                <th>Balai</th>
                <th>No</th>
                <th>Komoditas</th>
                <th>Kelompok Komoditas</th>
                <th>Satuan</th>
                <th class="text-right">Harga Satuan</th>
                <th>Varietas</th>
                <th>Kelas Benih</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah Benih</th>
                <th>Deskripsi</th>

            </tr>
        </thead>
        <tbody>';
        
        // Kelompokkan data berdasarkan balai
        $grouped_data = [];
        foreach($export_data as $row) {
            $balai_name = $row['nama_balai'];
            if(!isset($grouped_data[$balai_name])) {
                $grouped_data[$balai_name] = [];
            }
            $grouped_data[$balai_name][] = $row;
        }
        
        $no = 1;
        foreach($grouped_data as $balai_name => $balai_data) {
            $rowspan = count($balai_data);
            $first_row = true;
            $balai_no = 1; // Reset nomor untuk setiap balai
            
            foreach($balai_data as $index => $row) {
                $html .= '<tr>';
                
                // Nama balai hanya muncul sekali dengan rowspan
                if($first_row) {
                    $html .= '<td rowspan="' . $rowspan . '" style="vertical-align: middle; text-align: center; font-weight: bold; background-color: #f0f8ff; border: 1px solid #000;">' . htmlspecialchars($balai_name) . '</td>';
                }
                
                $html .= '<td class="text-center">' . $balai_no . '</td>';
                $balai_no++;
                $html .= '<td>' . htmlspecialchars($row['komoditas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kelompok_komoditas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['satuan']) . '</td>';
                $html .= '<td class="text-right">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['varietas']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kelas_benih']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['status_ketersediaan']) . '</td>';
                $html .= '<td class="text-right">' . htmlspecialchars($row['jumlah_benih']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['deskripsi'] ?? '') . '</td>';
                $fotoCell = '';
                if (!empty($row['foto'])) {
                    // Cek apakah path mengandung 'uploads' atau 'assets/uploads'
                    if (strpos($row['foto'], 'uploads/') !== false):
                        // Path sudah lengkap, gunakan langsung
                        $fotoUrl = getDirectFileUrl($row['foto']);
                    else:
                        // Path hanya nama file, asumsikan di assets/uploads
                        $fotoUrl = getAssetsUploadsUrl($row['foto']);
                    endif;
                    
                    $fotoCell = '<a href="' . $fotoUrl . '" target="_blank" style="color: #2E75B6; text-decoration: underline;">' . basename($row['foto']) . '</a>';
                } else {
                    $fotoCell = '-';
                }
                $html .= '<td>' . $fotoCell . '</td>';
                $html .= '</tr>';
                
                $first_row = false;
            }
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p><strong>LAPORAN DIHASILKAN OLEH SISTEM INFORMASI PENGELOLAAN KOMODITAS BENIH SIPKBS</strong></p>
        <p>Generated: ' . $export_time . ' | User: ' . $_SESSION['username'] . '</p>
    </div>
</body>
</html>';
    
        echo $html;
        exit();
    }
} elseif($type == 'excel') {
    // Export ke Excel dengan format yang rapi
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_SIPKBS_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: private, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    
    // Buat HTML Excel yang bagus
    $excel = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SIPKBS - Excel Export</title>
    <style>
        body { 
            font-family: Calibri, Arial, sans-serif; 
            margin: 20px; 
            font-size: 12px; 
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            background: #f8f9fa;
            border: 2px solid #2E75B6;
            border-radius: 8px;
        }
        
        .header h2 {
            color: #2E75B6;
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        
        .table-container {
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: auto;
        }
        
        th {
            background: #2E75B6;
            color: white;
            font-weight: bold;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #1F5A8A;
            font-size: 12px;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
            vertical-align: top;
            white-space: normal;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .number {
            text-align: right;
            mso-number-format: 0;
        }
        
        .foto-link {
            color: #2E75B6 !important;
            text-decoration: underline !important;
            font-weight: bold !important;
            text-align: center !important;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-top: 2px solid #2E75B6;
            font-size: 9px;
            color: #666;
            background: #f8f9fa;
        }
        
        .info-box {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>';
        
        // Ambil waktu export yang tepat (waktu saat export dilakukan)
        $export_time = date('d/m/Y');
        
        if(!empty($export_filter['bulan']) || !empty($export_filter['tahun'])) {
            $excel .= '<p><strong>Periode:</strong> ' . 
                     (!empty($export_filter['bulan']) ? $export_filter['bulan'] . ' ' : '') . 
                     (!empty($export_filter['tahun']) ? $export_filter['tahun'] : '') . '</p>';
        }
        
        $excel .= '</div>
    
    <div class="info-box">
        <strong>INFORMASI LAPORAN:</strong><br>
        • Laporan ini berisi data ketersediaan benih dari seluruh balai<br>
        • Data diambil dari sistem SIPKBS pada ' . date('d/m/Y') . '<br>
        • Format: Excel (.xls) - Kompatibel dengan Microsoft Excel
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Balai</th>
                <th>No</th>
                <th>Komoditas</th>
                <th>Kelompok Komoditas</th>
                <th>Satuan</th>
                <th class="text-right">Harga Satuan</th>
                <th>Varietas</th>
                <th>Kelas Benih</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah Benih</th>
                <th>Deskripsi</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>';
        
        // Kelompokkan data berdasarkan balai
        $grouped_data = [];
        foreach($export_data as $row) {
            $balai_name = $row['nama_balai'];
            if(!isset($grouped_data[$balai_name])) {
                $grouped_data[$balai_name] = [];
            }
            $grouped_data[$balai_name][] = $row;
        }
        
        $no = 1;
        foreach($grouped_data as $balai_name => $balai_data) {
            $rowspan = count($balai_data);
            $first_row = true;
            $balai_no = 1; // Reset nomor untuk setiap balai
            
            foreach($balai_data as $index => $row) {
                // Cek apakah nama balai termasuk yang special
                $balai_class = '';
                $balai_name_upper = strtoupper($balai_name);
                if(in_array($balai_name_upper, ['PALMA', 'TRI', 'TAS', 'TROA'])) {
                    $balai_class = 'class="balai-special"';
                }
                
                $excel .= '<tr>';
                
                // Nama balai hanya muncul sekali dengan rowspan
                if($first_row) {
                    $excel .= '<td rowspan="' . $rowspan . '" ' . $balai_class . ' style="vertical-align: middle; text-align: center; font-weight: bold; background-color: #f0f0f0; border: 1px solid #000;">' . htmlspecialchars($balai_name) . '</td>';
                }
                
                $excel .= '<td class="text-center">' . $balai_no++ . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['komoditas']) . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['kelompok_komoditas']) . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['satuan']) . '</td>';
                $excel .= '<td class="text-right">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['varietas']) . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['kelas_benih']) . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['status_ketersediaan']) . '</td>';
                $excel .= '<td class="text-right">' . htmlspecialchars($row['jumlah_benih']) . '</td>';
                $excel .= '<td>' . htmlspecialchars($row['deskripsi'] ?? '') . '</td>';
                $fotoCell = '';
                if (!empty($row['foto'])) {
                    // Cek apakah path mengandung 'uploads' atau 'assets/uploads'
                    if (strpos($row['foto'], 'uploads/') !== false):
                        // Path sudah lengkap, gunakan langsung
                        $fotoUrl = getDirectFileUrl($row['foto']);
                    else:
                        // Path hanya nama file, asumsikan di assets/uploads
                        $fotoUrl = getAssetsUploadsUrl($row['foto']);
                    endif;
                    
                    // Ambil hanya nama file untuk ditampilkan
                    $fileName = basename($row['foto']);
                    
                    // Buat hyperlink HTML biasa agar bisa langsung diklik
                    $fotoCell = '<a href="' . $fotoUrl . '" target="_blank">' . $fileName . '</a>';
                } else {
                    $fotoCell = 'Tidak ada foto';
                }
                $excel .= '<td class="foto-link">' . $fotoCell . '</td>';
                $excel .= '</tr>';
                
                $first_row = false;
            }
        }
        
        $excel .= '</tbody>
    </table>
    
    <div class="footer">
        <p><strong>LAPORAN DIHASILKAN OLEH SISTEM INFORMASI PENGELOLAAN KOMODITAS BENIH SIPKBS</strong></p>
        <p>Generated: ' . $export_time . ' | User: ' . $_SESSION['username'] . '</p>
        <p> 2026 SIPKBS - Sistem Informasi Pelaporan Ketersediaan Benih Sumber</p>
    </div>
</body>
</html>';
    
    echo $excel;
    exit();

} elseif($type == 'word') {
    // Cek action: print (buka di browser) atau download
    if($action == 'print') {
        // Buka HTML di browser
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="laporan_' . date('Y-m-d') . '.html"');
    } else {
        // Download file .doc (HTML table)
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename="laporan_sipkbs_' . date('Y-m-d_H-i-s') . '.doc"');
    }
    header('Cache-Control: private, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    
    // Buat HTML table yang proper untuk Word
    $word = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <meta name=ProgId content=Word.Document>
    <meta name=Generator content="Microsoft Word">
    <meta name=Originator content="Microsoft Word">
    <title>Laporan SIPKBS - Word Export</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>90</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        /* Page setup untuk Word - sama dengan PDF */
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            font-size: 12px; 
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            background: #f8f9fa;
            border: 2px solid #2E75B6;
            border-radius: 8px;
        }
        
        .header h1 {
            color: #2E75B6;
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #1F5A8A;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: normal;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        
        .table-container {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: auto;
        }
        
        th {
            background: #2E75B6;
            color: white;
            font-weight: bold;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #1F5A8A;
            font-size: 12px;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
            vertical-align: top;
            white-space: normal;
        }
        
        /* Column widths untuk Word landscape */
        th:nth-child(1) { width: 4%; }  /* No */
        th:nth-child(2) { width: 10%; } /* Balai */
        th:nth-child(3) { width: 8%; }  /* Komoditas */
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .number {
            text-align: right;
            mso-number-format: 0;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        
        .footer strong {
            color: #333;
        }
        
        /* Word specific */
        @page Section1 {
            size: 29.7cm 21cm; /* A4 landscape in cm */
            margin: 1cm;
        }
        div.Section1 { 
            page: Section1; 
            width: 100%;
        }
        
        /* Table layout untuk landscape */
        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        
        /* Prevent table breaking */
        tr {
            page-break-inside: avoid;
            mso-break-inside: avoid;
        }
        
        thead {
            display: table-header-group;
        }
        
        /* Print optimization */
        @media print {
            body { margin: 0.5in; }
            .header { 
                margin-bottom: 10px; 
                background: none;
                padding: 5px;
                border: none;
            }
            .header h2 { font-size: 14px; }
            .header p { font-size: 9px; }
            table { 
                margin: 5px 0;
                font-size: 8px;
                page-break-inside: avoid;
                mso-break-inside: avoid;
            }
            th, td { 
                padding: 2px 2px;
                font-size: 6px;
                white-space: normal;
                word-wrap: break-word;
                vertical-align: top;
                mso-ansi-language: ID;
                border: 1px solid #ddd;
            }
            
            /* Special styling for foto and tanggal columns */
            td:nth-child(12), td:nth-child(13) {
                white-space: normal;
                word-wrap: break-word;
                vertical-align: top;
                text-align: left;
                font-size: 6px;
                min-width: 60px;
                max-width: none;
            }
            
            td:nth-child(13) {
                text-align: center;
                min-width: 100px;
                max-width: none;
                font-size: 6px;
                white-space: nowrap;
                font-weight: normal;
                padding: 2px;
            }
            
            /* Special styling for jumlah benih column */
            td:nth-child(10) {
                white-space: normal;
                word-wrap: break-word;
                vertical-align: top;
                text-align: right;
                font-size: 6px;
                min-width: 30px;
                max-width: 30px;
                width: 30px;
                word-break: break-all;
            }

            /* Ensure foto column has same size as jumlah benih column */
            td:nth-child(12) {
                min-width: 30px;
                max-width: 30px;
                width: 30px;
                word-break: break-all;
                word-wrap: break-word;
                hyphens: auto;
                overflow-wrap: break-word;
                white-space: normal;
                font-size: 4px;
                line-height: 0.9;
                text-align: left;
                padding: 2px;
            }
            
            /* Special styling for deskripsi column */
            td:nth-child(11) {
                white-space: normal;
                word-wrap: break-word;
                vertical-align: top;
                text-align: left;
                font-size: 6px;
                max-width: 60px;
                word-break: break-all;
                hyphens: auto;
            }
            
            /* Header styling */
            th {
                background-color: #f2f2f2;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                line-height: 1.1;
                white-space: normal;
                padding: 3px 2px;
            }
            
            th.text-right {
                text-align: right;
            }
            
            /* Ensure 2-line headers are centered properly */
            th br {
                line-height: 0.8;
            }
            .footer {
                margin-top: 10px;
                font-size: 7px;
            }
        }
    </style>
</head>
<body>
    <div class="Section1">
        <div class="header">
            <h2>LAPORAN DATA KOMODITAS BENIH SIPKBS</h2>';
            
            // Ambil waktu export yang tepat (waktu saat export dilakukan)
            $export_time = date('d/m/Y');
            
            if(!empty($export_filter['bulan']) || !empty($export_filter['tahun'])) {
                $word .= '<p><strong>Periode:</strong> ' . 
                         (!empty($export_filter['bulan']) ? $export_filter['bulan'] . ' ' : '') . 
                         (!empty($export_filter['tahun']) ? $export_filter['tahun'] : '') . '</p>';
            }
            
            $word .= '</div>
        
        <div class="info-box">
            <strong>INFORMASI LAPORAN:</strong><br>
            • Laporan ini berisi data ketersediaan benih dari seluruh balai<br>
            • Data diambil dari sistem SIPKBS pada ' . $export_time . '<br>
            • Format: Word (.doc) - Landscape A4, Kompatibel dengan Microsoft Word
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 6%;">Balai</th>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">Komoditas</th>
                    <th style="width: 5%;">Kelompok<br>Komoditas</th>
                    <th style="width: 4%;">Satuan</th>
                    <th class="text-right" style="width: 5%;">Harga<br>Satuan</th>
                    <th style="width: 7%;">Varietas</th>
                    <th style="width: 4%;">Kelas<br>Benih</th>
                    <th style="width: 5%;">Status<br>Ketersediaan</th>
                    <th class="text-right" style="width: 4%;">Jumlah<br>Benih</th>
                    <th style="width: 8%;">Deskripsi</th>
                    <th style="width: 4%;">Foto</th>
                    <th class="text-center" style="width: 27%;">Tanggal<br>Dibuat</th>
                </tr>
            </thead>
            <tbody>';
            
            // Kelompokkan data berdasarkan balai
            $grouped_data = [];
            foreach($export_data as $row) {
                $balai_name = $row['nama_balai'];
                if(!isset($grouped_data[$balai_name])) {
                    $grouped_data[$balai_name] = [];
                }
                $grouped_data[$balai_name][] = $row;
            }
            
            $no = 1;
            foreach($grouped_data as $balai_name => $balai_data) {
                $rowspan = count($balai_data);
                $first_row = true;
                $balai_no = 1; // Reset nomor untuk setiap balai
                
                foreach($balai_data as $index => $row) {
                    $word .= '<tr>';
                    
                    // Nama balai hanya muncul sekali dengan rowspan
                    if($first_row) {
                        $word .= '<td rowspan="' . $rowspan . '" style="vertical-align: middle; text-align: center; font-weight: bold; background-color: #e8f5e8; border: 1px solid #000;">' . htmlspecialchars($balai_name) . '</td>';
                    }
                    
                    $word .= '<td class="text-center">' . $balai_no++ . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['komoditas']) . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['kelompok_komoditas']) . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['satuan']) . '</td>';
                    $word .= '<td class="number">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['varietas']) . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['kelas_benih']) . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['status_ketersediaan']) . '</td>';
                    $word .= '<td class="number">' . $row['jumlah_benih'] . '</td>';
                    $word .= '<td>' . htmlspecialchars($row['deskripsi'] ?? '') . '</td>';
                    $fotoCell = '';
                    if (!empty($row['foto'])) {
                        // Cek apakah path mengandung 'uploads' atau 'assets/uploads'
                        if (strpos($row['foto'], 'uploads/') !== false):
                            // Path sudah lengkap, gunakan langsung
                            $fotoUrl = getDirectFileUrl($row['foto']);
                        else:
                            // Path hanya nama file, asumsikan di assets/uploads
                            $fotoUrl = getAssetsUploadsUrl($row['foto']);
                        endif;
                        
                        $fotoCell = '<a href="' . $fotoUrl . '" target="_blank">' . basename($row['foto']) . '</a>';
                    } else {
                        $fotoCell = '-';
                    }
                    $word .= '<td>' . $fotoCell . '</td>';
                    $word .= '</tr>';
                    
                    $first_row = false;
                }
            }
            
            $word .= '</tbody>
        </table>
        
        <div class="footer">
            <p><strong>LAPORAN DIHASILKAN OLEH SISTEM INFORMASI PENGELOLAAN KOMODITAS BENIH SIPKBS</strong></p>
            <p>Generated: ' . $export_time . ' | User: ' . $_SESSION['username'] . '</p>
            <p> 2026 SIPKBS - Sistem Informasi Pelaporan Ketersediaan Benih Sumber</p>
        </div>
    </div>
</body>
</html>';
    
    echo $word;
    exit();
}

// Clear session data
unset($_SESSION['export_data']);
unset($_SESSION['export_type']);
unset($_SESSION['export_filter']);

exit();
?>
