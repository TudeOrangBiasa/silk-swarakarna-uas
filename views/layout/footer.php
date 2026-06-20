            </main>
            <footer class="mt-auto border-top py-3 text-center text-muted small bg-white">
                &copy; 2026 SILK-Swarakarna — UAS Pemrograman Web Pagi 01
            </footer>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/_command_palette.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Topbar toggle: drawer on mobile (<992px), collapse on desktop
        document.getElementById('topbarToggle')?.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('sidebarDrawer')).show();
            } else {
                const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
            }
        });
    </script>
</body>
</html>
