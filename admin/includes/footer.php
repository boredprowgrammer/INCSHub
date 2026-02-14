        </main>
    </div>
    
    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');
        const closeSidebar = document.getElementById('close-sidebar');
        
        function openMobileMenu() { sidebar.classList.remove('-translate-x-full'); mobileOverlay.classList.remove('hidden'); }
        function closeMobileMenu() { sidebar.classList.add('-translate-x-full'); mobileOverlay.classList.add('hidden'); }
        
        mobileMenuButton?.addEventListener('click', openMobileMenu);
        closeSidebar?.addEventListener('click', closeMobileMenu);
        mobileOverlay?.addEventListener('click', closeMobileMenu);
        
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-auto-hide').forEach(a => {
                a.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                a.style.opacity = '0'; a.style.transform = 'translateY(-8px)';
                setTimeout(() => a.remove(), 400);
            });
        }, 4000);
        
        // Delete confirmations
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function(e) { if (!confirm(this.getAttribute('data-confirm'))) e.preventDefault(); });
        });
        
        // Image preview
        document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0]; const pid = this.getAttribute('data-preview');
                if (file && pid) { const p = document.getElementById(pid); if (p) { const r = new FileReader(); r.onload = e => { p.src = e.target.result; p.classList.remove('hidden'); }; r.readAsDataURL(file); } }
            });
        });
        
        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(t => t.addEventListener('input', function() { this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px'; }));
    </script>
</body>
</html>