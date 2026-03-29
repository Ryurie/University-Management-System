
<?php
// includes/footer.php
?>
    </div> <script>
        // SIDEBAR LOGIC
        function toggleSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if(sidebar && overlay) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
        }

        // THEME LOGIC
        function toggleAdminTheme() {
            const html = document.documentElement;
            const btn = document.getElementById('adminThemeBtn');
            let newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            if (btn) btn.innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
        }

        // Initialize Theme on Load
        document.addEventListener('DOMContentLoaded', () => {
            let savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const btn = document.getElementById('adminThemeBtn');
            if (btn) btn.innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
        });
    </script>
</body>
</html>