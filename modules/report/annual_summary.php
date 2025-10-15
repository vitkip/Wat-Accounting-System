<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລາຍງານສະຫຼຸບປະຈຳປີ 12 ເດືອນ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();

$selectedYear = $_GET['year'] ?? date('Y');

// ກວດສອບສິດຂອງຜູ້ໃຊ້
$isSuperAdmin = ($_SESSION['is_super_admin'] ?? 0) == 1;
$currentTempleId = getCurrentTempleId();
$isMultiTemple = isMultiTempleEnabled();

// ຊື່ເດືອນພາສາລາວ
$monthNames = [
    '01' => 'ມັງກອນ', '02' => 'ກຸມພາ', '03' => 'ມີນາ',
    '04' => 'ເມສາ', '05' => 'ພຶດສະພາ', '06' => 'ມິຖຸນາ',
    '07' => 'ກໍລະກົດ', '08' => 'ສິງຫາ', '09' => 'ກັນຍາ',
    '10' => 'ຕຸລາ', '11' => 'ພະຈິກ', '12' => 'ທັນວາ'
];

// ດຶງຂໍ້ມູນລາຍຮັບ/ລາຍຈ່າຍ ແຕ່ລະເດືອນຕາມສິດ
$monthlyData = [];
$yearTotalIncome = 0;
$yearTotalExpense = 0;

for ($m = 1; $m <= 12; $m++) {
    $month = str_pad($m, 2, '0', STR_PAD_LEFT);
    $yearMonth = "{$selectedYear}-{$month}";
    
    // ລາຍຮັບຕາມສິດ
    if ($isSuperAdmin) {
        // Super Admin ເບິ່ງທຸກວັດ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM income 
            WHERE DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$yearMonth]);
    } elseif ($isMultiTemple && $currentTempleId) {
        // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM income 
            WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$currentTempleId, $yearMonth]);
    } else {
        // ລະບົບເກົ່າ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM income 
            WHERE DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$yearMonth]);
    }
    $incomeData = $stmt->fetch();
    
    // ລາຍຈ່າຍຕາມສິດ
    if ($isSuperAdmin) {
        // Super Admin ເບິ່ງທຸກວັດ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM expense 
            WHERE DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$yearMonth]);
    } elseif ($isMultiTemple && $currentTempleId) {
        // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM expense 
            WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$currentTempleId, $yearMonth]);
    } else {
        // ລະບົບເກົ່າ
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
            FROM expense 
            WHERE DATE_FORMAT(date, '%Y-%m') = ?
        ");
        $stmt->execute([$yearMonth]);
    }
    $expenseData = $stmt->fetch();
    
    $income = floatval($incomeData['total']);
    $expense = floatval($expenseData['total']);
    $balance = $income - $expense;
    
    $monthlyData[$month] = [
        'month' => $month,
        'month_name' => $monthNames[$month],
        'income' => $income,
        'income_count' => $incomeData['count'],
        'expense' => $expense,
        'expense_count' => $expenseData['count'],
        'balance' => $balance
    ];
    
    $yearTotalIncome += $income;
    $yearTotalExpense += $expense;
}

$yearBalance = $yearTotalIncome - $yearTotalExpense;

// ດຶງຂໍ້ມູນຕາມໝວດໝູ່ສຳລັບປີນີ້ຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM income
        WHERE YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$selectedYear]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM income
        WHERE temple_id = ? AND YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$currentTempleId, $selectedYear]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM income
        WHERE YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$selectedYear]);
}
$incomeByCategory = $stmt->fetchAll();

if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM expense
        WHERE YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$selectedYear]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM expense
        WHERE temple_id = ? AND YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$currentTempleId, $selectedYear]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT category, COALESCE(SUM(amount), 0) as total, COUNT(*) as count
        FROM expense
        WHERE YEAR(date) = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$selectedYear]);
}
$expenseByCategory = $stmt->fetchAll();

// ດຶງຂໍ້ມູນວັດສຳລັບສະແດງໃນລາຍງານ
$templeInfo = null;
if ($isSuperAdmin) {
    $templeName = "ທຸກວັດ";
} elseif ($isMultiTemple && $currentTempleId) {
    $stmt = $db->prepare("SELECT temple_name, temple_name_lao FROM temples WHERE id = ?");
    $stmt->execute([$currentTempleId]);
    $templeInfo = $stmt->fetch();
    $templeName = $templeInfo ? $templeInfo['temple_name_lao'] : "ວັດ";
} else {
    $templeName = "ວັດປ່າໜອງບົວທອງໃຕ້"; // ຄ່າເລີ່ມຕົ້ນສຳລັບລະບົບເກົ່າ
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລາຍງານສະຫຼຸບປະຈຳປີ <?php echo $selectedYear; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Phetsarath', sans-serif;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-white">
    
    <div class="max-w-full mx-auto p-8">
        
        <!-- Print Button -->
        <div class="no-print mb-6 text-right">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                ພິມລາຍງານ
            </button>
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg ml-2">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                ປິດ
            </button>
        </div>
        
        <!-- Header -->
        <div class="text-center mb-8 border-b-2 border-gray-800 pb-6">
            <div class="mb-4">
                <h1 class="text-lg text-gray-600">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</h1>
                <h1 class="text-lg text-gray-600">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</h1>
                <p class="text-3xl mt-2"> ⭐ ⭐ ⭐ </p>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">ລາຍງານສະຫຼຸບບັນຊີປະຈຳປີ</h2>
            <h2 class="text-2xl font-bold text-blue-700 mb-2">ວັດ<?php echo $templeName; ?> ເມືອງສີໂຄດຕະບອງ ນະຄອນຫຼວງວຽງຈັນ</h2>
            <h3 class="text-xl font-medium text-gray-700">ປະຈຳປີ <?php echo $selectedYear; ?></h3>
            <p class="text-gray-600 mt-2">ວັນທີ່ພິມ: <?php echo formatDate(date('Y-m-d')); ?> | ເວລາ: <?php echo date('H:i'); ?> ໂມງ</p>
        </div>
        
        <!-- Summary Cards -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">ລວມລາຍຮັບທັງປີ</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatMoney($yearTotalIncome); ?></p>
                        <p class="text-green-100 text-xs mt-1">
                            <?php echo array_sum(array_column($monthlyData, 'income_count')); ?> ລາຍການ
                        </p>
                    </div>
                    <div class="text-5xl opacity-50">💰</div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">ລວມລາຍຈ່າຍທັງປີ</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatMoney($yearTotalExpense); ?></p>
                        <p class="text-red-100 text-xs mt-1">
                            <?php echo array_sum(array_column($monthlyData, 'expense_count')); ?> ລາຍການ
                        </p>
                    </div>
                    <div class="text-5xl opacity-50">💸</div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">ຍອດຄົງເຫຼືອ</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatMoney($yearBalance); ?></p>
                        <p class="text-blue-100 text-xs mt-1">
                            <?php echo $yearBalance >= 0 ? '✓ ກຳໄລ' : '✗ ຂາດທຶນ'; ?>
                        </p>
                    </div>
                    <div class="text-5xl opacity-50"><?php echo $yearBalance >= 0 ? '📈' : '📉'; ?></div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">ຄ່າສະເລ່ຍຕໍ່ເດືອນ</p>
                        <p class="text-2xl font-bold mt-2"><?php echo formatMoney($yearTotalIncome / 12); ?></p>
                        <p class="text-purple-100 text-xs mt-1">ລາຍຮັບສະເລ່ຍ</p>
                    </div>
                    <div class="text-5xl opacity-50">📊</div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Summary Table -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-gray-800 pb-2">
                📅 ສະຫຼຸບລາຍເດືອນ ປີ <?php echo $selectedYear; ?>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                            <th class="border border-gray-300 px-3 py-3 text-center w-16">ລ/ດ</th>
                            <th class="border border-gray-300 px-4 py-3 text-left">ເດືອນ</th>
                            <th class="border border-gray-300 px-4 py-3 text-right">ລາຍຮັບ (ກີບ)</th>
                            <th class="border border-gray-300 px-4 py-3 text-center">ລາຍການ</th>
                            <th class="border border-gray-300 px-4 py-3 text-right">ລາຍຈ່າຍ (ກີບ)</th>
                            <th class="border border-gray-300 px-4 py-3 text-center">ລາຍການ</th>
                            <th class="border border-gray-300 px-4 py-3 text-right">ຍອດຄົງເຫຼືອ (ກີບ)</th>
                            <th class="border border-gray-300 px-4 py-3 text-center">ສະຖານະ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $runningBalance = 0;
                        foreach ($monthlyData as $index => $data): 
                            $runningBalance += $data['balance'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-3 text-center font-medium"><?php echo $index; ?></td>
                            <td class="border border-gray-300 px-4 py-3 font-medium"><?php echo $data['month_name']; ?></td>
                            <td class="border border-gray-300 px-4 py-3 text-right text-green-700 font-medium">
                                <?php echo formatMoney($data['income']); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-gray-600">
                                <?php echo $data['income_count']; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-3 text-right text-red-700 font-medium">
                                <?php echo formatMoney($data['expense']); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-gray-600">
                                <?php echo $data['expense_count']; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-3 text-right font-bold <?php echo $data['balance'] >= 0 ? 'text-blue-700' : 'text-red-700'; ?>">
                                <?php echo formatMoney($data['balance']); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-3 text-center">
                                <?php if ($data['balance'] > 0): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">ກຳໄລ</span>
                                <?php elseif ($data['balance'] < 0): ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">ຂາດທຶນ</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">ເທົ່າກັນ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Total Row -->
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 font-bold text-lg">
                            <td colspan="2" class="border border-gray-300 px-4 py-4 text-right">ລວມທັງປີ:</td>
                            <td class="border border-gray-300 px-4 py-4 text-right text-green-700">
                                <?php echo formatMoney($yearTotalIncome); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-4 text-center text-gray-600">
                                <?php echo array_sum(array_column($monthlyData, 'income_count')); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-4 text-right text-red-700">
                                <?php echo formatMoney($yearTotalExpense); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-4 text-center text-gray-600">
                                <?php echo array_sum(array_column($monthlyData, 'expense_count')); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-4 text-right <?php echo $yearBalance >= 0 ? 'text-blue-700' : 'text-red-700'; ?>">
                                <?php echo formatMoney($yearBalance); ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-4 text-center">
                                <?php if ($yearBalance > 0): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">ກຳໄລ</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">ຂາດທຶນ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="no-print mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6 page-break">
            <!-- Income vs Expense Chart -->
            <div class="bg-white p-6 rounded-lg shadow-lg border">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📊 ກຣາຟລາຍຮັບ-ລາຍຈ່າຍ ແຕ່ລະເດືອນ</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
            
            <!-- Balance Trend Chart -->
            <div class="bg-white p-6 rounded-lg shadow-lg border">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📈 ກຣາຟຄວາມເຄື່ອນໄຫວຍອດຄົງເຫຼືອ</h3>
                <canvas id="balanceChart"></canvas>
            </div>
        </div>
        
        <!-- Category Analysis -->
        <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Income by Category -->
            <div class="bg-white p-6 rounded-lg shadow-lg border">
                <h3 class="text-lg font-bold text-green-700 mb-4 border-b-2 border-green-700 pb-2">
                    💰 ລາຍຮັບຕາມໝວດໝູ່
                </h3>
                <?php if (empty($incomeByCategory)): ?>
                    <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຮັບ</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($incomeByCategory as $cat): 
                            $percentage = ($cat['total'] / $yearTotalIncome) * 100;
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-700"><?php echo e($cat['category']); ?></span>
                                <span class="text-sm text-gray-600"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-grow bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-sm font-bold text-green-700 whitespace-nowrap"><?php echo formatMoney($cat['total']); ?></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $cat['count']; ?> ລາຍການ</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Expense by Category -->
            <div class="bg-white p-6 rounded-lg shadow-lg border">
                <h3 class="text-lg font-bold text-red-700 mb-4 border-b-2 border-red-700 pb-2">
                    💸 ລາຍຈ່າຍຕາມໝວດໝູ່
                </h3>
                <?php if (empty($expenseByCategory)): ?>
                    <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍ</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($expenseByCategory as $cat): 
                            $percentage = ($cat['total'] / $yearTotalExpense) * 100;
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-700"><?php echo e($cat['category']); ?></span>
                                <span class="text-sm text-gray-600"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-grow bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-sm font-bold text-red-700 whitespace-nowrap"><?php echo formatMoney($cat['total']); ?></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $cat['count']; ?> ລາຍການ</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <div class="grid grid-cols-3 gap-8 text-center">
                <div>
                    <p class="mb-12 font-medium">ເຈົ້າອະທິການວັດ</p>
                    <div class="border-t-2 border-gray-800 pt-2 mx-8">
                        <br>
                        <p class="font-medium">(...........................)</p>
                    </div>
                </div>
                <div>
                    <p class="mb-12 font-medium">ຜູ້ກວດສອບ</p>
                    <div class="border-t-2 border-gray-800 pt-2 mx-8">
                        <br>
                        <p class="font-medium">(...........................)</p>
                    </div>
                </div>
                <div>
                    <p class="mb-12 font-medium">ຜູ້ລາຍງານ</p>
                    <div class="border-t-2 border-gray-800 pt-2 mx-8">
                        <br>
                        <p class="font-medium">(...........................)</p>
                    </div>
                </div>
            </div>
            <div class="text-center text-gray-500 text-sm mt-8">
                <p>ລາຍງານນີ້ສ້າງໂດຍອັດຕະໂນມັດຈາກລະບົບບັນຊີວັດ ວັນທີ່ <?php echo formatDate(date('Y-m-d')); ?></p>
            </div>
        </div>
        
    </div>
    
    <!-- Chart.js Scripts -->
    <script>
        // ຂໍ້ມູນສຳລັບກຣາຟ
        const months = <?php echo json_encode(array_values(array_column($monthlyData, 'month_name'))); ?>;
        const incomeData = <?php echo json_encode(array_values(array_column($monthlyData, 'income'))); ?>;
        const expenseData = <?php echo json_encode(array_values(array_column($monthlyData, 'expense'))); ?>;
        const balanceData = <?php echo json_encode(array_values(array_column($monthlyData, 'balance'))); ?>;
        
        // Monthly Income vs Expense Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'ລາຍຮັບ',
                        data: incomeData,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 2
                    },
                    {
                        label: 'ລາຍຈ່າຍ',
                        data: expenseData,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                       new Intl.NumberFormat('lo-LA').format(context.parsed.y) + ' ກີບ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('lo-LA', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
        
        // Balance Trend Chart
        const balanceCtx = document.getElementById('balanceChart').getContext('2d');
        new Chart(balanceCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'ຍອດຄົງເຫຼືອ',
                    data: balanceData,
                    fill: true,
                    backgroundColor: function(context) {
                        const value = context.parsed.y;
                        return value >= 0 ? 'rgba(59, 130, 246, 0.2)' : 'rgba(239, 68, 68, 0.2)';
                    },
                    borderColor: function(context) {
                        const value = context.parsed.y;
                        return value >= 0 ? 'rgb(59, 130, 246)' : 'rgb(239, 68, 68)';
                    },
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: function(context) {
                        const value = context.parsed.y;
                        return value >= 0 ? 'rgb(59, 130, 246)' : 'rgb(239, 68, 68)';
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                const status = value >= 0 ? 'กຳໄລ' : 'ຂາດທຶນ';
                                return status + ': ' + 
                                       new Intl.NumberFormat('lo-LA').format(Math.abs(value)) + ' ກີບ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('lo-LA', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
    
</body>
</html>
