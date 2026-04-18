<?php
/**
 * File Helper Functions
 */

/**
 * Generate proper URL for file access
 */
function getFileUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // If already full URL, return as is
    if (strpos($filePath, 'http') === 0) {
        return $filePath;
    }
    
    // Remove leading slashes if any
    $filePath = ltrim($filePath, '/');
    
    // Get base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . '://' . $host . '/sipkbs2/';
    
    return $baseUrl . $filePath;
}

/**
 * Generate relative path for file access from admin
 */
function getRelativeFileUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // If already full URL, return as is
    if (strpos($filePath, 'http') === 0) {
        return $filePath;
    }
    
    // Remove leading slashes if any
    $filePath = ltrim($filePath, '/');
    
    // For admin access, go up one level
    return '../' . $filePath;
}

/**
 * Check if file exists and return proper URL
 */
function getValidFileUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // Remove leading slashes
    $filePath = ltrim($filePath, '/');
    
    // Prioritaskan path sesuai dengan existing code
    $possiblePaths = [
        $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/assets/uploads/' . $filePath,  // Path yang digunakan di semua_laporan.php
        $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/uploads/' . $filePath,    // Path yang kita buat
        $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/assets/images/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/' . $filePath
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            // Return full URL for this found file
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'] . '/sipkbs2/', '', $path);
            return getFileUrl($relativePath);
        }
    }
    
    // If no file found, return empty
    return '';
}
?>
