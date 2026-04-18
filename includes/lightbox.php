<?php
/**
 * Lightbox System for Photo Viewing
 */

/**
 * Generate lightbox HTML and JavaScript
 */
function generateLightbox() {
    ob_start();
    ?>
    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal" style="display: none;">
        <div class="lightbox-content">
            <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
            <img class="lightbox-image" id="lightboxImage" src="" alt="Foto">
            <div class="lightbox-info">
                <h4 id="lightboxTitle">Foto Benih</h4>
                <p id="lightboxDescription"></p>
            </div>
        </div>
    </div>

    <style>
    /* Lightbox Styles */
    .lightbox-modal {
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease-in-out;
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        animation: slideUp 0.3s ease-out;
    }

    .lightbox-close {
        position: absolute;
        top: 15px;
        right: 20px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 100000;
        background: rgba(0, 0, 0, 0.7);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        line-height: 1;
    }

    .lightbox-close:hover {
        background: rgba(255, 0, 0, 0.8);
        transform: scale(1.1);
    }

    .lightbox-image {
        width: 100%;
        max-height: 70vh;
        object-fit: contain;
        display: block;
    }

    .lightbox-info {
        padding: 20px;
        background: white;
        text-align: center;
    }

    .lightbox-info h4 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 18px;
    }

    .lightbox-info p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { 
            transform: translateY(50px);
            opacity: 0;
        }
        to { 
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .lightbox-content {
            max-width: 95%;
            max-height: 95%;
        }
        
        .lightbox-close {
            top: 10px;
            right: 15px;
            font-size: 30px;
            width: 40px;
            height: 40px;
        }
        
        .lightbox-image {
            max-height: 60vh;
        }
    }
    </style>

    <script>
    // Prevent conflicts with existing scripts
    if (typeof window.openLightbox === 'undefined') {
        function openLightbox(imageSrc, title, description) {
            try {
                var modal = document.getElementById('lightboxModal');
                var image = document.getElementById('lightboxImage');
                var titleElement = document.getElementById('lightboxTitle');
                var descElement = document.getElementById('lightboxDescription');
                
                if (!modal || !image) {
                    console.error('Lightbox elements not found');
                    return;
                }
                
                image.src = imageSrc;
                titleElement.textContent = title || 'Foto Benih';
                descElement.textContent = description || '';
                
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Add escape key listener
                document.addEventListener('keydown', handleEscape);
            } catch (error) {
                console.error('Error opening lightbox:', error);
            }
        }
        
        function closeLightbox() {
            try {
                var modal = document.getElementById('lightboxModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                
                // Remove escape key listener
                document.removeEventListener('keydown', handleEscape);
            } catch (error) {
                console.error('Error closing lightbox:', error);
            }
        }
        
        function handleEscape(event) {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        }
        
        // Close lightbox when clicking outside the image
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('lightboxModal');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closeLightbox();
                    }
                });
                
                // Prevent image click from closing lightbox
                var image = document.getElementById('lightboxImage');
                if (image) {
                    image.addEventListener('click', function(event) {
                        event.stopPropagation();
                    });
                }
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Generate photo link with lightbox
 */
function generatePhotoLink($filePath, $title = 'Foto Benih', $description = '') {
    if (empty($filePath)) {
        return '-';
    }
    
    $validUrl = getValidFileUrl($filePath);
    if (empty($validUrl)) {
        return '-';
    }
    
    $fileName = basename($filePath);
    $fullUrl = getFileUrl($filePath);
    
    return '<a href="javascript:void(0)" onclick="openLightbox(\'' . $fullUrl . '\', \'' . addslashes($title) . '\', \'' . addslashes($description) . '\')" style="color: #2E75B6; text-decoration: underline; cursor: pointer;">' . htmlspecialchars($fileName) . '</a>';
}
?>
