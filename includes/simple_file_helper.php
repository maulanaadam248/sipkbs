<?php
/**
 * Simple File Helper - No Validation, Direct URL Generation
 */

/**
 * Generate direct URL for file
 */
function getDirectFileUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // If already full URL, return as is
    if (strpos($filePath, 'http') === 0) {
        return $filePath;
    }
    
    // Remove leading slashes
    $filePath = ltrim($filePath, '/');
    
    // Generate direct URL
    return 'http://localhost/sipkbs2/' . $filePath;
}

/**
 * Generate URL for assets/uploads path
 */
function getAssetsUploadsUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // Remove leading slashes
    $filePath = ltrim($filePath, '/');
    
    // For assets/uploads, generate direct URL
    return 'http://localhost/sipkbs2/assets/uploads/' . basename($filePath);
}

/**
 * Generate URL for uploads path
 */
function getUploadsUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }
    
    // Remove leading slashes
    $filePath = ltrim($filePath, '/');
    
    // For uploads, generate direct URL
    return 'http://localhost/sipkbs2/uploads/' . basename($filePath);
}
?>
