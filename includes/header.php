<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/csrf.php';
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(SITE_NAME); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phetsarath Lao Font -->
    <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Phetsarath', sans-serif;
        }
        .swal2-popup {
            font-family: 'Phetsarath', sans-serif;
        }
    </style>
    
    <!-- SweetAlert Helper - Inline for immediate availability -->
    <script>
    // ລໍຖ້າ SweetAlert2 ໂຫຼດສຳເລັດກ່ອນ
    (function() {
        // ຟັງຊັນກວດສອບວ່າ Swal ພ້ອມແລ້ວຫຼືຍັງ
        function initSweetAlertHelpers() {
            if (typeof Swal === 'undefined') {
                setTimeout(initSweetAlertHelpers, 50);
                return;
            }
            
            // ຟັງຊັນສະແດງຄວາມສຳເລັດ
            window.showSuccess = function(message, title = 'ສຳເລັດ!') {
                return Swal.fire({
                    icon: 'success',
                    title: title,
                    text: message,
                    confirmButtonText: 'ຕົກລົງ',
                    confirmButtonColor: '#10b981'
                });
            };

            // ຟັງຊັນສະແດງຂໍ້ຜິດພາດ
            window.showError = function(message, title = 'ເກີດຂໍ້ຜິດພາດ!') {
                return Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message,
                    confirmButtonText: 'ຕົກລົງ',
                    confirmButtonColor: '#ef4444'
                });
            };

            // ຟັງຊັນສະແດງຄຳເຕືອນ
            window.showWarning = function(message, title = 'ແຈ້ງເຕືອນ!') {
                return Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: message,
                    confirmButtonText: 'ຕົກລົງ',
                    confirmButtonColor: '#f59e0b'
                });
            };

            // ຟັງຊັນສະແດງຂໍ້ມູນ
            window.showInfo = function(message, title = 'ຂໍ້ມູນ') {
                return Swal.fire({
                    icon: 'info',
                    title: title,
                    text: message,
                    confirmButtonText: 'ຕົກລົງ',
                    confirmButtonColor: '#3b82f6'
                });
            };

            // ຟັງຊັນຢືນຢັນການລຶບ
            window.confirmDelete = function(message = 'ທ່ານຕ້ອງການລຶບຂໍ້ມູນນີ້ແທ້ບໍ?') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ຢືນຢັນການລຶບ',
                    text: message,
                    showCancelButton: true,
                    confirmButtonText: 'ລຶບ',
                    cancelButtonText: 'ຍົກເລີກ',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                });
            };

            // ຟັງຊັນຢືນຢັນທົ່ວໄປ
            window.confirmAction = function(message, title = 'ຢືນຢັນ') {
                return Swal.fire({
                    icon: 'question',
                    title: title,
                    text: message,
                    showCancelButton: true,
                    confirmButtonText: 'ຕົກລົງ',
                    cancelButtonText: 'ຍົກເລີກ',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                });
            };

            // ຟັງຊັນສະແດງ loading
            window.showLoading = function(message = 'ກຳລັງປະມວນຜົນ...') {
                return Swal.fire({
                    title: message,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            };

            // ຟັງຊັນປິດ loading
            window.closeLoading = function() {
                Swal.close();
            };

            // ຟັງຊັນສະແດງຜົນສຳເລັດພ້ອມ redirect
            window.showSuccessAndRedirect = function(message, redirectUrl, delay = 1500) {
                Swal.fire({
                    icon: 'success',
                    title: 'ສຳເລັດ!',
                    text: message,
                    timer: delay,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = redirectUrl;
                });
            };

            // ຟັງຊັນສະແດງ Toast notification
            window.showToast = function(message, icon = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                return Toast.fire({
                    icon: icon,
                    title: message
                });
            };
            
            console.log('✅ SweetAlert2 helpers loaded successfully');
        }
        
        // ເລີ່ມຕົ້ນ
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSweetAlertHelpers);
        } else {
            initSweetAlertHelpers();
        }
    })();
    </script>
</head>
<body class="bg-gray-50">
    
    <?php if (isLoggedIn()): ?>
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-green-600 to-green-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="text-xl sm:text-2xl font-bold text-white flex items-center hover:text-green-100 transition duration-200">
                        <span class="text-2xl">🏛️</span>
                        <span class="ml-2 hidden sm:inline"><?php echo e(SITE_NAME); ?></span>
                        <span class="ml-2 sm:hidden">ວັດ</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    <a href="<?php echo BASE_URL; ?>/index.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">📊</span> ໜ້າຫຼັກ
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">💰</span> ລາຍຮັບ
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">💸</span> ລາຍຈ່າຍ
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/report/index.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">📈</span> ລາຍງານ
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/categories/income_list.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">🏷️</span> ໝວດໝູ່
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">👥</span> ຜູ້ໃຊ້
                    </a>
                    <?php endif; ?>
                    
                    <!-- User Info Desktop -->
                    <div class="flex items-center space-x-2 ml-3 pl-3 border-l border-green-500">
                        <div class="hidden lg:flex items-center px-3 py-1.5 bg-green-800 bg-opacity-50 rounded-lg">
                            <span class="text-xs text-white">
                                <span class="mr-1">👤</span><?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                                <?php if (isAdmin()): ?>
                                    <span class="ml-2 px-1.5 py-0.5 text-xs bg-yellow-400 text-yellow-900 rounded font-medium">Admin</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/logout.php" 
                           class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center shadow-md">
                            <span class="mr-1">🚪</span> ອອກ
                        </a>
                    </div>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button type="button" 
                            onclick="toggleMobileMenu()"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">ເປີດເມນູ</span>
                        <!-- Hamburger Icon -->
                        <svg id="menu-icon-open" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Close Icon -->
                        <svg id="menu-icon-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-green-700 border-t border-green-600">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <!-- User Info Mobile -->
                <div class="px-3 py-3 bg-green-800 bg-opacity-50 rounded-lg mb-2">
                    <div class="text-sm font-medium text-white">
                        👤 <?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                    </div>
                    <?php if (isAdmin()): ?>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs bg-yellow-400 text-yellow-900 rounded font-medium">Admin</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Menu Items -->
                <a href="<?php echo BASE_URL; ?>/index.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    📊 ໜ້າຫຼັກ
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    💰 ລາຍຮັບ
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    💸 ລາຍຈ່າຍ
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/report/index.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    📈 ລາຍງານ
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?php echo BASE_URL; ?>/modules/categories/income_list.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    🏷️ ໝວດໝູ່
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
                   class="block px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800">
                    👥 ຜູ້ໃຊ້
                </a>
                <?php endif; ?>
                
                <!-- Logout Button Mobile -->
                <a href="<?php echo BASE_URL; ?>/logout.php" 
                   class="block px-3 py-3 mt-2 rounded-lg text-base font-medium text-white bg-red-500 hover:bg-red-600 text-center shadow-md">
                    🚪 ອອກຈາກລະບົບ
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu Script -->
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    }
    
    // ປິດເມນູເວລາຄລິກນອກເມນູ
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('mobile-menu');
        const button = event.target.closest('button[onclick="toggleMobileMenu()"]');
        const menuContent = document.getElementById('mobile-menu');
        
        if (!button && !menuContent.contains(event.target) && !menu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    });
    </script>
    <?php endif; ?>
    
    <!-- Flash Messages with SweetAlert2 -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        <?php 
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($type === 'success'): ?>
                showToast('<?php echo addslashes($message); ?>', 'success');
            <?php elseif ($type === 'error'): ?>
                showError('<?php echo addslashes($message); ?>');
            <?php elseif ($type === 'warning'): ?>
                showWarning('<?php echo addslashes($message); ?>');
            <?php else: ?>
                showInfo('<?php echo addslashes($message); ?>');
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
