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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
        
        /* Smooth Dropdown Animations */
        .dropdown-menu {
            transform-origin: top;
            animation: dropdownSlideIn 0.2s ease-out;
        }
        
        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Desktop Dropdown Hover Effect */
        .nav-dropdown {
            position: relative;
        }
        
        .nav-dropdown > .dropdown-menu {
            visibility: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        
        .nav-dropdown:hover > .dropdown-menu {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        
        /* Menu Item Hover Effect */
        .menu-item {
            transition: all 0.2s ease;
        }
        
        .menu-item:hover {
            transform: translateX(4px);
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
    <nav class="bg-gradient-to-r from-green-600 to-green-700 shadow-xl border border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="text-xl sm:text-2xl font-bold text-white flex items-center hover:text-green-100 transition duration-200">
                        <span class="text-2xl">🏛️</span>
                        <?php 
                        // ສະແດງຊື່ວັດຖ້າມີ, ຖ້າບໍ່ມີໃຫ້ສະແດງຊື່ລະບົບ
                        $displayName = SITE_NAME;
                        if (function_exists('getCurrentUserTemple')) {
                            $temple = getCurrentUserTemple();
                            if ($temple) {
                                $displayName = $temple['temple_name_lao'] ?: $temple['temple_name'];
                            }
                        } elseif (function_exists('getActiveTemple')) {
                            $temple = getActiveTemple();
                            if ($temple) {
                                $displayName = $temple['temple_name_lao'] ?: $temple['temple_name'];
                            }
                        }
                        ?>
                        <span class="ml-2 hidden sm:inline"><?php echo e($displayName); ?></span>
                        <span class="ml-2 sm:hidden">ວັດ</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    <!-- ໜ້າຫຼັກ -->
                    <a href="<?php echo BASE_URL; ?>/index.php" 
                       class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                        <span class="mr-1.5">🏠</span> ໜ້າຫຼັກ
                    </a>
                    
                    <!-- ລາຍຮັບ Dropdown -->
                    <div class="nav-dropdown">
                        <button class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                            <span class="mr-1.5">💰</span> ລາຍຮັບ
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-1 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu z-50">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ລາຍຮັບ</div>
                            <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition duration-150">
                                <span class="mr-2">�</span> ລາຍການລາຍຮັບ
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/income/add.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition duration-150">
                                <span class="mr-2">➕</span> ເພີ່ມລາຍຮັບໃໝ່
                            </a>
                        </div>
                    </div>
                    
                    <!-- ລາຍຈ່າຍ Dropdown -->
                    <div class="nav-dropdown">
                        <button class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                            <span class="mr-1.5">💸</span> ລາຍຈ່າຍ
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-1 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu z-50">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ລາຍຈ່າຍ</div>
                            <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition duration-150">
                                <span class="mr-2">�</span> ລາຍການລາຍຈ່າຍ
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/expense/add.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition duration-150">
                                <span class="mr-2">➕</span> ເພີ່ມລາຍຈ່າຍໃໝ່
                            </a>
                        </div>
                    </div>
                    
                    <!-- ລາຍງານ Dropdown -->
                    <div class="nav-dropdown">
                        <button class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                            <span class="mr-1.5">�</span> ລາຍງານ
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu z-50">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ລາຍງານ</div>
                            <a href="<?php echo BASE_URL; ?>/modules/report/index.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition duration-150">
                                <span class="mr-2">�</span> ລາຍງານພາບລວມ
                            </a>
                       
                
                        </div>
                    </div>
                    
                    <?php if (isAdmin()): ?>
                    <!-- ຈັດການລະບົບ Dropdown -->
                    <div class="nav-dropdown">
                        <button class="px-4 py-2 text-sm font-medium text-white hover:bg-green-800 rounded-lg transition duration-200 flex items-center">
                            <span class="mr-1.5">⚙️</span> ຈັດການ
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-1 w-60 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu z-50">
                            <!-- ໝວດໝູ່ -->
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ໝວດໝູ່</div>
                            <a href="<?php echo BASE_URL; ?>/modules/categories/income_list.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition duration-150">
                                <span class="mr-2">🏷️</span> ໝວດໝູ່ລາຍຮັບ
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition duration-150">
                                <span class="mr-2">🏷️</span> ໝວດໝູ່ລາຍຈ່າຍ
                            </a>
                            
                           
                            
                         
                            
                            <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                            <!-- Super Admin -->
                            <div class="border-t border-gray-200 my-2"></div>
                            <div class="px-3 py-2 text-xs font-semibold text-purple-500 uppercase tracking-wider">Super Admin</div>
                            <a href="<?php echo BASE_URL; ?>/modules/temples/index.php" 
                               class="menu-item block px-4 py-2 text-sm text-purple-700 font-semibold hover:bg-purple-50 transition duration-150">
                                <span class="mr-2">🏛️</span> ຈັດການວັດທັງໝົດ
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

    
                    
                    <!-- User Info Desktop Dropdown -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative ml-3 pl-3 border-l border-green-500">
                        <button @click="open = !open" class="flex items-center px-3 py-1.5 bg-green-800 bg-opacity-50 rounded-lg hover:bg-green-800 transition duration-200">
                            <span class="text-sm text-white font-medium">
                                <span class="mr-1">👤</span>
                                <span class="hidden lg:inline"><?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                            </span>
                            <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                                <span class="ml-2 px-1.5 py-0.5 text-xs bg-purple-400 text-purple-900 rounded font-medium hidden lg:inline">Super Admin</span>
                            <?php elseif (isAdmin()): ?>
                                <span class="ml-2 px-1.5 py-0.5 text-xs bg-yellow-400 text-yellow-900 rounded font-medium hidden lg:inline">Admin</span>
                            <?php endif; ?>
                            <svg class="w-4 h-4 ml-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             style="display: none;"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-200">
                            
                            <!-- User Info Header -->
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?></p>
                                <p class="text-xs text-gray-600 mt-1">@<?php echo e($_SESSION['username']); ?></p>
                                <div class="mt-2">
                                    <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded font-medium">🌟 Super Admin</span>
                                    <?php elseif (isAdmin()): ?>
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded font-medium">⚡ Admin</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded font-medium">👤 User</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Temple Switcher (Super Admin Only) -->
                            <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                            <div class="px-2 py-2 border-b border-gray-200">
                                <a href="<?php echo BASE_URL; ?>/modules/temples/index.php" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 rounded-lg transition duration-150">
                                    <span class="mr-3">🏛️</span>
                                    <span>ສະຫຼັບວັດ</span>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Menu Items -->
                            <div class="px-2 py-2">
                                <a href="<?php echo BASE_URL; ?>/modules/users/profile.php" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition duration-150">
                                    <span class="mr-3">👤</span>
                                    <span>ໂປຣໄຟລ໌</span>
                                </a>
                                 <!-- ຜູ້ໃຊ້ -->
                            <div class="border-t border-gray-200 my-2"></div>
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ຜູ້ໃຊ້ງານ</div>
                            <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
                               class="menu-item block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition duration-150">
                                <span class="mr-2">👥</span> ຈັດການຜູ້ໃຊ້
                            </a>
                                <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>/modules/temples/settings.php" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition duration-150">
                                    <span class="mr-3">⚙️</span>
                                    <span>ການຕັ້ງຄ່າ</span>
                                </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo BASE_URL; ?>/modules/report/index.php" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition duration-150">
                                    <span class="mr-3">📊</span>
                                    <span>ລາຍງານ</span>
                                </a>
                            </div>
                            
                            <!-- Logout -->
                            <div class="px-2 pt-2 border-t border-gray-200">
                                <a href="<?php echo BASE_URL; ?>/logout.php" 
                                   class="flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition duration-150 font-medium">
                                    <span class="mr-3">🚪</span>
                                    <span>ອອກຈາກລະບົບ</span>
                                </a>
                            </div>
                        </div>
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
                    <div class="text-sm font-medium text-white flex items-center justify-between">
                        <span>👤 <?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                        <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                            <span class="px-2 py-1 text-xs bg-purple-400 text-purple-900 rounded font-medium">Super Admin</span>
                        <?php elseif (isAdmin()): ?>
                            <span class="px-2 py-1 text-xs bg-yellow-400 text-yellow-900 rounded font-medium">Admin</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs bg-blue-400 text-blue-900 rounded font-medium">User</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-green-200 mt-1">@<?php echo e($_SESSION['username']); ?></div>
                </div>
                
                <!-- ໜ້າຫຼັກ -->
                <a href="<?php echo BASE_URL; ?>/index.php" 
                   class="flex items-center px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                    <span class="mr-2">🏠</span> ໜ້າຫຼັກ
                </a>
                
                <!-- ລາຍຮັບ Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                        <span><span class="mr-2">💰</span> ລາຍຮັບ</span>
                        <svg class="w-5 h-5 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="pl-4 space-y-1">
                        <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">📋</span> ລາຍການລາຍຮັບ
                        </a>
                        <a href="<?php echo BASE_URL; ?>/modules/income/add.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">➕</span> ເພີ່ມລາຍຮັບໃໝ່
                        </a>
                    </div>
                </div>
                
                <!-- ລາຍຈ່າຍ Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                        <span><span class="mr-2">�</span> ລາຍຈ່າຍ</span>
                        <svg class="w-5 h-5 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="pl-4 space-y-1">
                        <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">📋</span> ລາຍການລາຍຈ່າຍ
                        </a>
                        <a href="<?php echo BASE_URL; ?>/modules/expense/add.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">➕</span> ເພີ່ມລາຍຈ່າຍໃໝ່
                        </a>
                    </div>
                </div>
                
                <!-- ລາຍງານ Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                        <span><span class="mr-2">�</span> ລາຍງານ</span>
                        <svg class="w-5 h-5 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="pl-4 space-y-1">
                        <a href="<?php echo BASE_URL; ?>/modules/report/index.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">📈</span> ລາຍງານພາບລວມ
                        </a>
                    </div>
                </div>
                
                <?php if (isAdmin()): ?>
                <!-- ຈັດການລະບົບ Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                        <span><span class="mr-2">⚙️</span> ຈັດການລະບົບ</span>
                        <svg class="w-5 h-5 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="pl-4 space-y-1">
                        <div class="px-3 py-1 text-xs font-semibold text-green-300 uppercase">ໝວດໝູ່</div>
                        <a href="<?php echo BASE_URL; ?>/modules/categories/income_list.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">🏷️</span> ໝວດໝູ່ລາຍຮັບ
                        </a>
                        <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">🏷️</span> ໝວດໝູ່ລາຍຈ່າຍ
                        </a>
                        
                        <div class="px-3 py-1 text-xs font-semibold text-green-300 uppercase mt-2">ຜູ້ໃຊ້ງານ</div>
                        <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-green-100 hover:bg-green-800 transition duration-200">
                            <span class="mr-2">👥</span> ຈັດການຜູ້ໃຊ້
                        </a>
                        
                        <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                        <div class="border-t border-green-600 my-2"></div>
                        <div class="px-3 py-1 text-xs font-semibold text-purple-300 uppercase">Super Admin</div>
                        <a href="<?php echo BASE_URL; ?>/modules/temples/index.php" 
                           class="flex items-center px-3 py-2 rounded-lg text-sm text-purple-200 font-semibold hover:bg-green-800 transition duration-200">
                            <span class="mr-2">🏛️</span> ຈັດການວັດທັງໝົດ
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- ໂປຣໄຟລ໌ & ການຕັ້ງຄ່າ -->
                <div class="border-t border-green-600 my-2"></div>
                
                <a href="<?php echo BASE_URL; ?>/modules/users/profile.php" 
                   class="flex items-center px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                    <span class="mr-2">�</span> ໂປຣໄຟລ໌
                </a>
                
                <?php if (isAdmin()): ?>
                <a href="<?php echo BASE_URL; ?>/modules/temples/settings.php" 
                   class="flex items-center px-3 py-3 rounded-lg text-base font-medium text-white hover:bg-green-800 transition duration-200">
                    <span class="mr-2">⚙️</span> ການຕັ້ງຄ່າ
                </a>
                <?php endif; ?>
                
                <?php if (function_exists('isSuperAdmin') && isSuperAdmin()): ?>
                <a href="<?php echo BASE_URL; ?>/modules/temples/index.php" 
                   class="flex items-center px-3 py-3 rounded-lg text-base font-medium text-purple-200 hover:bg-green-800 transition duration-200">
                    <span class="mr-2">🏛️</span> ສະຫຼັບວັດ
                </a>
                <?php endif; ?>
                
                <!-- Logout Button Mobile -->
                <a href="<?php echo BASE_URL; ?>/logout.php" 
                   class="flex items-center justify-center px-3 py-3 mt-2 rounded-lg text-base font-medium text-white bg-red-500 hover:bg-red-600 shadow-xl border border-gray-100 transition duration-200">
                    <span class="mr-2">🚪</span> ອອກຈາກລະບົບ
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
