    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600">
                <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. ສະຫງວນລິຂະສິດ.</p>
                <p class="text-sm mt-2">ພັດທະນາໂດຍ: ປອ.ອານັນທະສັກ ພັດທະສີລາ</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        // ປິດຂໍ້ຄວາມແຈ້ງເຕືອນອັດຕະໂນມັດ
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
