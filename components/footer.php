            </div> <!-- Fim do content-wrapper -->
        </div> <!-- Fim do content -->
    </div> <!-- Fim do wrapper -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        $(document).ready(function () {
            // Toggle sidebar
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });

            // Inicializar Select2
            $('.select2').select2();
        });
    </script>
</body>
</html>
