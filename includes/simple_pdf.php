<?php
/**
 * Simple PDF Generator using Browser Print
 * Solusi tanpa library eksternal untuk generate PDF
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
        // Clean HTML untuk PDF
        $clean_html = $this->prepareHTMLForPDF();
        
        if ($download) {
            // Set headers untuk download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->filename . '"');
            header('Cache-Control: private, must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            
            // Generate PDF content
            $pdf_content = $this->generatePDFContent($clean_html);
            echo $pdf_content;
        } else {
            // Preview di browser
            header('Content-Type: text/html');
            header('Content-Disposition: inline; filename="' . str_replace('.pdf', '.html', $this->filename) . '"');
            echo $clean_html;
        }
        
        return true;
    }
    
    private function prepareHTMLForPDF() {
        // Clean HTML untuk PDF
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
                line-height: 1.2;
            }
            .header { 
                text-align: center; 
                margin-bottom: 15px; 
                border-bottom: 2px solid #2E75B6; 
                padding-bottom: 10px;
            }
            .header h2 { 
                color: #2E75B6; 
                margin: 0 0 8px 0; 
                font-size: 16px; 
                font-weight: bold;
            }
            .header p { 
                margin: 3px 0; 
                font-size: 10px; 
                color: #333;
            }
            table { 
                border-collapse: collapse; 
                width: 100%; 
                page-break-inside: auto;
                margin: 8px 0;
            }
            th, td { 
                border: 1px solid #000; 
                padding: 4px; 
                font-size: 8px;
                vertical-align: top;
            }
            th { 
                background: #2E75B6; 
                color: white; 
                font-weight: bold; 
                text-align: center;
            }
            tr { 
                page-break-inside: avoid; 
                page-break-after: auto;
            }
            tr:nth-child(even) { background: #f9f9f9; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .footer {
                text-align: center;
                margin-top: 15px;
                padding: 8px;
                border-top: 2px solid #2E75B6;
                font-size: 8px;
                color: #666;
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
            $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>' . $html . '</body></html>';
        }
        
        return $html;
    }
    
    private function generatePDFContent($html) {
        // Cek apakah wkhtmltopdf tersedia
        $wkhtmltopdf_paths = [
            'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe',
            'C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe',
            'C:/wkhtmltopdf/bin/wkhtmltopdf.exe'
        ];
        
        $wkhtmltopdf = null;
        foreach ($wkhtmltopdf_paths as $path) {
            if (file_exists($path)) {
                $wkhtmltopdf = $path;
                break;
            }
        }
        
        if ($wkhtmltopdf) {
            // Buat temporary files
            $temp_html = tempnam(sys_get_temp_dir(), 'html_');
            $temp_pdf = tempnam(sys_get_temp_dir(), 'pdf_');
            
            // Write HTML to temp file
            file_put_contents($temp_html, $html);
            
            // Convert to PDF
            $command = "\"$wkhtmltopdf\" --page-size A4 --margin-top 10mm --margin-right 10mm --margin-bottom 10mm --margin-left 10mm --encoding UTF-8 \"$temp_html\" \"$temp_pdf\" 2>&1";
            exec($command, $output, $return_code);
            
            // Read PDF if successful
            if ($return_code === 0 && file_exists($temp_pdf)) {
                $pdf_content = file_get_contents($temp_pdf);
                unlink($temp_html);
                unlink($temp_pdf);
                return $pdf_content;
            }
            
            // Cleanup on error
            unlink($temp_html);
            if (file_exists($temp_pdf)) {
                unlink($temp_pdf);
            }
        }
        
        // Fallback: Generate simple PDF using basic PDF format
        return $this->generateSimplePDF($html);
    }
    
    private function generateSimplePDF($html) {
        // Extract text content from HTML
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Simple PDF header
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n";
        
        // Pages object
        $pdf .= "2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n";
        
        // Page object
        $pdf .= "3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 595 842]\n/Contents 4 0 R\n/Resources <<\n/Font <<\n/F1 5 0 R\n>>\n>>\n>>\nendobj\n";
        
        // Content stream (simplified)
        $content = "BT\n/F1 12 Tf\n50 800 Td\n(" . $this->escapePDFString(substr($text, 0, 500)) . ") Tj\nET";
        $content_stream = $this->createStreamObject($content);
        
        $pdf .= "4 0 obj\n<<\n/Length " . strlen($content) . "\n>>\nstream\n" . $content . "\nendstream\nendobj\n";
        
        // Font object (simplified)
        $pdf .= "5 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n";
        
        // Cross-reference table
        $xref_offset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        
        $objects = [1, 2, 3, 4, 5];
        foreach ($objects as $i => $obj_num) {
            $offset = 0; // Simplified - would need actual offsets
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        
        // Trailer
        $pdf .= "trailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n$xref_offset\n%%EOF\n";
        
        return $pdf;
    }
    
    private function escapePDFString($str) {
        // Escape special characters for PDF strings
        return str_replace(['\\', '(', ')', "\n", "\r"], ['\\\\', '\\(', '\\)', '\\n', '\\r'], $str);
    }
    
    private function createStreamObject($content) {
        // Create PDF stream object (simplified)
        return $content;
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
