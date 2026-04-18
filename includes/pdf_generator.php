<?php
/**
 * Simple PDF Generator for SIPKBS
 * Menggunakan DOMPDF atau TCPDF yang tersedia, dengan fallback ke HTML
 */

class SimplePDFGenerator {
    private $html;
    private $filename;
    
    public function __construct($filename = 'document.pdf') {
        $this->filename = $filename;
        $this->html = '';
    }
    
    public function addHTML($html) {
        $this->html .= $html;
    }
    
    public function output($download = true) {
        // Cek apakah TCPDF tersedia di XAMPP
        $tcpdf_path = 'C:/xampp/php/PEAR/TCPDF/tcpdf.php';
        
        if (file_exists($tcpdf_path)) {
            return $this->outputWithTCPDF($download, $tcpdf_path);
        } else {
            // Fallback: gunakan HTML ke PDF conversion dengan JavaScript
            return $this->outputWithHTML($download);
        }
    }
    
    private function outputWithTCPDF($download, $tcpdf_path) {
        if (!class_exists('TCPDF')) {
            require_once($tcpdf_path);
        }
        
        // Define constants if not defined
        if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
        if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
        if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('SIPKBS System');
        $pdf->SetAuthor('SIPKBS Admin');
        $pdf->SetTitle('Laporan SIPKBS');
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Write HTML content
        $pdf->writeHTML($this->html, true, false, true, false, '');
        
        // Output PDF
        if ($download) {
            $pdf->Output($this->filename, 'D');
        } else {
            $pdf->Output($this->filename, 'I');
        }
        
        return true;
    }
    
    private function outputWithHTML($download) {
        // Clean HTML untuk PDF conversion
        $clean_html = $this->prepareHTMLForPDF();
        
        // Set headers untuk PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $this->filename . '"');
        header('Cache-Control: private, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Buat PDF menggunakan HTML2PDF sederhana
        echo $this->convertHTMLToPDF($clean_html);
        return true;
    }
    
    private function prepareHTMLForPDF() {
        // Clean HTML untuk PDF conversion
        $html = $this->html;
        
        // Remove JavaScript
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        
        // Remove no-print elements
        $html = preg_replace('/<div class="no-print"[^>]*>.*?<\/div>/mis', '', $html);
        
        // Add PDF-specific styles
        $pdf_styles = '
        <style>
            @page { 
                size: A4; 
                margin: 1cm; 
            }
            body { 
                font-family: Arial, sans-serif; 
                font-size: 10px; 
                margin: 0; 
            }
            table { 
                border-collapse: collapse; 
                width: 100%; 
                page-break-inside: auto;
            }
            th, td { 
                border: 1px solid #000; 
                padding: 5px; 
                font-size: 9px;
            }
            th { 
                background: #f0f0f0; 
                font-weight: bold; 
            }
            tr { 
                page-break-inside: avoid; 
                page-break-after: auto;
            }
            thead { 
                display: table-header-group; 
            }
            tfoot { 
                display: table-footer-group; 
            }
        </style>';
        
        // Insert styles after <head> tag
        if (preg_match('/<head>/', $html)) {
            $html = preg_replace('/<head>/', '<head>' . $pdf_styles, $html);
        } else {
            $html = '<html><head>' . $pdf_styles . '</head><body>' . $html . '</body></html>';
        }
        
        return $html;
    }
    
    private function convertHTMLToPDF($html) {
        // Simple HTML to PDF conversion menggunakan wkhtmltopdf jika tersedia
        $wkhtmltopdf = 'C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe';
        
        if (file_exists($wkhtmltopdf)) {
            // Buat temporary file
            $temp_html = tempnam(sys_get_temp_dir(), 'html_');
            $temp_pdf = tempnam(sys_get_temp_dir(), 'pdf_');
            
            file_put_contents($temp_html, $html);
            
            // Convert menggunakan wkhtmltopdf
            $command = "\"$wkhtmltopdf\" --page-size A4 --margin-top 10mm --margin-right 10mm --margin-bottom 10mm --margin-left 10mm \"$temp_html\" \"$temp_pdf\"";
            exec($command);
            
            if (file_exists($temp_pdf)) {
                $pdf_content = file_get_contents($temp_pdf);
                unlink($temp_html);
                unlink($temp_pdf);
                return $pdf_content;
            }
            
            unlink($temp_html);
        }
        
        // Fallback: Return HTML dengan PDF headers (browser akan handle)
        return $html;
    }
}

/**
 * Fungsi helper untuk generate PDF dari HTML
 */
function generatePDF($html, $filename = 'document.pdf', $download = true) {
    $pdf = new SimplePDFGenerator($filename);
    $pdf->addHTML($html);
    return $pdf->output($download);
}
?>
