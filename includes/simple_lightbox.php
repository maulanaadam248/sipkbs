<?php
/**
 * Simple Lightbox System - Error-Free Version
 */

/**
 * Generate simple lightbox HTML and JavaScript
 */
function generateSimpleLightbox() {
    ob_start();
    ?>
    <!-- Simple Lightbox Modal -->
    <div id="simpleLightbox" class="simple-lightbox" style="display: none;">
        <div class="simple-lightbox-content">
            <button class="simple-lightbox-close" onclick="closeSimpleLightbox()">&times;</button>
            <img class="simple-lightbox-image" id="simpleLightboxImage" src="" alt="Foto">
            <div class="simple-lightbox-info">
                <h4 id="simpleLightboxTitle">Foto Benih</h4>
                <p id="simpleLightboxDescription"></p>
            </div>
        </div>
    </div>

    <style>
    /* Simple Lightbox Styles */
    .simple-lightbox {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0,0,0,0.9) !important;
        z-index: 999999 !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .simple-lightbox-content {
        position: relative !important;
        max-width: 90% !important;
        max-height: 90% !important;
        background: white !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5) !important;
    }

    .simple-lightbox-close {
        position: absolute !important;
        top: 15px !important;
        right: 20px !important;
        background: rgba(0,0,0,0.7) !important;
        color: white !important;
        border: none !important;
        width: 50px !important;
        height: 50px !important;
        border-radius: 50% !important;
        font-size: 30px !important;
        cursor: pointer !important;
        z-index: 1000000 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.3s !important;
    }

    .simple-lightbox-close:hover {
        background: rgba(255,0,0,0.8) !important;
        transform: scale(1.1) !important;
    }

    .simple-lightbox-image {
        width: 100% !important;
        max-height: 70vh !important;
        object-fit: contain !important;
        display: block !important;
    }

    .simple-lightbox-info {
        padding: 20px !important;
        background: white !important;
        text-align: center !important;
    }

    .simple-lightbox-info h4 {
        margin: 0 0 10px 0 !important;
        color: #333 !important;
        font-size: 18px !important;
    }

    .simple-lightbox-info p {
        margin: 0 !important;
        color: #666 !important;
        font-size: 14px !important;
    }

    @media (max-width: 768px) {
        .simple-lightbox-content {
            max-width: 95% !important;
            max-height: 95% !important;
        }
        
        .simple-lightbox-close {
            width: 40px !important;
            height: 40px !important;
            font-size: 24px !important;
        }
    }
    </style>

    <script>
    // Simple Lightbox Functions
    (function() {
        window.openSimpleLightbox = function(imageSrc, title, description) {
            try {
                var modal = document.getElementById('simpleLightbox');
                var image = document.getElementById('simpleLightboxImage');
                var titleEl = document.getElementById('simpleLightboxTitle');
                var descEl = document.getElementById('simpleLightboxDescription');
                
                if (modal && image) {
                    image.src = imageSrc;
                    if (titleEl) titleEl.textContent = title || 'Foto Benih';
                    if (descEl) descEl.textContent = description || '';
                    
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } catch(e) {
                console.log('Lightbox error:', e);
            }
        };
        
        window.closeSimpleLightbox = function() {
            try {
                var modal = document.getElementById('simpleLightbox');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            } catch(e) {
                console.log('Close lightbox error:', e);
            }
        };
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeSimpleLightbox();
            }
        });
        
        // Close on background click
        document.addEventListener('click', function(e) {
            var modal = document.getElementById('simpleLightbox');
            if (modal && e.target === modal) {
                window.closeSimpleLightbox();
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Generate simple photo link
 */
function generateSimplePhotoLink($filePath, $title = 'Foto Benih', $description = '') {
    if (empty($filePath)) {
        return '-';
    }
    
    $validUrl = getValidFileUrl($filePath);
    if (empty($validUrl)) {
        return '-';
    }
    
    $fileName = basename($filePath);
    $fullUrl = getFileUrl($filePath);
    
    return '<a href="javascript:void(0)" onclick="openSimpleLightbox(\'' . $fullUrl . '\', \'' . addslashes($title) . '\', \'' . addslashes($description) . '\')" style="color: #2E75B6; text-decoration: underline; cursor: pointer;">' . htmlspecialchars($fileName) . '</a>';
}
?>
