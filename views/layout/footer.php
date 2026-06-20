            </main>
            <footer class="mt-auto border-top py-3 text-center text-muted small bg-white">
                &copy; 2026 SILK-Swarakarna — UAS Pemrograman Web Pagi 01
            </footer>
        </div> <!-- End Content Wrapper -->
    </div> <!-- End d-flex -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
        });
    </script>
</body>
</html>
