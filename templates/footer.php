<?php if(!isset($no_layout) || !$no_layout): ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo isset($js_path) ? $js_path : 'assets/js/script.js'; ?>"></script>

<!-- Sidebar Toggle JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar-modern');
    const mainContent = document.querySelector('.main-content');
    
    // Buat overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    if (sidebarToggle && sidebar) {
        let isSidebarOpen = false;
        
        // Toggle sidebar function
        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            
            if (isSidebarOpen) {
                sidebar.classList.add('show');
                if (mainContent) mainContent.classList.add('sidebar-open');
                overlay.classList.add('show');
                sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                sidebar.classList.remove('show');
                if (mainContent) mainContent.classList.remove('sidebar-open');
                overlay.classList.remove('show');
                sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
        
        // Click event for toggle button
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isSidebarOpen) {
                toggleSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && isSidebarOpen) {
                if (mainContent) mainContent.classList.add('sidebar-open');
                overlay.classList.remove('show');
            } else if (window.innerWidth <= 768) {
                if (mainContent) mainContent.classList.remove('sidebar-open');
            }
        });
    }
});
</script>

</body>
</html>