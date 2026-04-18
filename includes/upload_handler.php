<?php
/**
 * Upload Handler for SIPKBS
 * Menangani upload file ke folder uploads
 */

/**
 * Handle file upload dengan validasi
 */
function handleFileUpload($fileInput, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    // Cek apakah ada file yang diupload
    if (!isset($fileInput) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Tidak ada file yang diupload atau terjadi error',
            'file_path' => ''
        ];
    }
    
    $fileName = $fileInput['name'];
    $fileSize = $fileInput['size'];
    $fileTmpName = $fileInput['tmp_name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validasi file type
    if (!in_array($fileType, $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $allowedTypes),
            'file_path' => ''
        ];
    }
    
    // Validasi file size
    if ($fileSize > $maxSize) {
        return [
            'success' => false,
            'message' => 'Ukuran file terlalu besar. Maksimal: ' . ($maxSize / 1024 / 1024) . 'MB',
            'file_path' => ''
        ];
    }
    
    // Buat nama file unik
    $newFileName = time() . '_' . sanitizeFileName($fileName);
    $uploadPath = '../uploads/' . $newFileName;
    
    // Pindahkan file ke folder uploads
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return [
            'success' => true,
            'message' => 'File berhasil diupload',
            'file_path' => 'uploads/' . $newFileName,
            'file_name' => $newFileName
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal memindahkan file ke folder uploads',
            'file_path' => ''
        ];
    }
}

/**
 * Sanitasi nama file
 */
function sanitizeFileName($fileName) {
    // Hapus karakter berbahaya
    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
    
    // Ganti spasi dengan underscore
    $fileName = str_replace(' ', '_', $fileName);
    
    // Pastikan tidak terlalu panjang
    if (strlen($fileName) > 100) {
        $fileName = substr($fileName, 0, 100);
    }
    
    return $fileName;
}

/**
 * Hapus file dari folder uploads
 */
function deleteUploadedFile($filePath) {
    if (empty($filePath)) {
        return false;
    }
    
    $fullPath = '../uploads/' . basename($filePath);
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Cek apakah file ada di folder uploads
 */
function isUploadedFileExists($filePath) {
    if (empty($filePath)) {
        return false;
    }
    
    $fullPath = '../uploads/' . basename($filePath);
    return file_exists($fullPath);
}

/**
 * Get info file di folder uploads
 */
function getUploadedFileInfo($filePath) {
    if (empty($filePath)) {
        return null;
    }
    
    $fullPath = '../uploads/' . basename($filePath);
    
    if (!file_exists($fullPath)) {
        return null;
    }
    
    return [
        'name' => basename($filePath),
        'size' => filesize($fullPath),
        'type' => mime_content_type($fullPath),
        'modified' => filemtime($fullPath),
        'path' => $fullPath
    ];
}
?>
