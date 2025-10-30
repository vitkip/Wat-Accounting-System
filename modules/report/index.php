<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລາຍງານ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';
require_once __DIR__ . '/../../includes/header.php';

requireLogin();

$db = getDB();

// ກວດສອບສິດຂອງຜູ້ໃຊ້ປັດຈຸບັນ
$currentUser = $_SESSION;
$isSuperAdmin = ($currentUser['is_super_admin'] ?? 0) == 1;
$currentTempleId = getCurrentTempleId();
$isMultiTemple = isMultiTempleEnabled();

// ກຳນົດປີ ແລະ ເດືອນ
$selectedYear = $_GET['year'] ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');

// ດຶງປີທີ່ມີຂໍ້ມູນຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->query("
        SELECT DISTINCT YEAR(date) as year 
        FROM (
            SELECT date FROM income 
            UNION 
            SELECT date FROM expense
        ) as dates 
        ORDER BY year DESC
    ");
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT DISTINCT YEAR(date) as year 
        FROM (
            SELECT date FROM income WHERE temple_id = ?
            UNION 
            SELECT date FROM expense WHERE temple_id = ?
        ) as dates 
        ORDER BY year DESC
    ");
    $stmt->execute([$currentTempleId, $currentTempleId]);
} else {
    // ລະບົບເກົ່າບໍ່ມີ multi-temple
    $stmt = $db->query("
        SELECT DISTINCT YEAR(date) as year 
        FROM (
            SELECT date FROM income 
            UNION 
            SELECT date FROM expense
        ) as dates 
        ORDER BY year DESC
    ");
}
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ຖ້າບໍ່ມີຂໍ້ມູນໃຫ້ໃຊ້ປີປັດຈຸບັນ
if (empty($years)) {
    $years = [date('Y')];
}

// ລາຍງານຕາມເດືອນ
$monthlyIncome = [];
$monthlyExpense = [];

for ($m = 1; $m <= 12; $m++) {
    $month = str_pad($m, 2, '0', STR_PAD_LEFT);
    $yearMonth = "{$selectedYear}-{$month}";
    
    // ລາຍຮັບຕາມສິດ
    if ($isSuperAdmin) {
        // Super Admin ເບິ່ງທຸກວັດ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
    } elseif ($isMultiTemple && $currentTempleId) {
        // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$currentTempleId, $yearMonth]);
    } else {
        // ລະບົບເກົ່າ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
    }
    $monthlyIncome[$m] = $stmt->fetch()['total'];
    
    // ລາຍຈ່າຍຕາມສິດ
    if ($isSuperAdmin) {
        // Super Admin ເບິ່ງທຸກວັດ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
    } elseif ($isMultiTemple && $currentTempleId) {
        // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$currentTempleId, $yearMonth]);
    } else {
        // ລະບົບເກົ່າ
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
    }
    $monthlyExpense[$m] = $stmt->fetch()['total'];
}

// ລາຍງານຕາມໝວດໝູ່ (ເດືອນທີ່ເລືອກ)
$yearMonth = "{$selectedYear}-{$selectedMonth}";

// ລາຍຮັບຕາມໝວດໝູ່ຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM income 
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$yearMonth]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM income 
        WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$currentTempleId, $yearMonth]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM income 
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$yearMonth]);
}
$incomeByCategory = $stmt->fetchAll();

// ລາຍຈ່າຍຕາມໝວດໝູ່ຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM expense 
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$yearMonth]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM expense 
        WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$currentTempleId, $yearMonth]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM expense 
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute([$yearMonth]);
}
$expenseByCategory = $stmt->fetchAll();

// ສະຫຼຸບລວມ
$totalIncomeMonth = array_sum(array_column($incomeByCategory, 'total'));
$totalExpenseMonth = array_sum(array_column($expenseByCategory, 'total'));
$balanceMonth = $totalIncomeMonth - $totalExpenseMonth;

$totalIncomeYear = array_sum($monthlyIncome);
$totalExpenseYear = array_sum($monthlyExpense);
$balanceYear = $totalIncomeYear - $totalExpenseYear;

// ຊື່ເດືອນພາສາລາວ
$monthNames = [
    '01' => 'ມັງກອນ', '02' => 'ກຸມພາ', '03' => 'ມີນາ',
    '04' => 'ເມສາ', '05' => 'ພຶດສະພາ', '06' => 'ມິຖຸນາ',
    '07' => 'ກໍລະກົດ', '08' => 'ສິງຫາ', '09' => 'ກັນຍາ',
    '10' => 'ຕຸລາ', '11' => 'ພະຈິກ', '12' => 'ທັນວາ'
];
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">ລາຍງານບັນຊີ</h1>
    <p class="text-gray-600">ລາຍງານລາຍຮັບ ແລະ ລາຍຈ່າຍຂອງວັດ</p>
</div>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-md p-6 mb-6">
    <form method="GET" action="" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-gray-700 text-sm font-medium mb-2">ເລືອກປີ</label>
            <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $selectedYear == $year ? 'selected' : ''; ?>>
                        ປີ <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-gray-700 text-sm font-medium mb-2">ເລືອກເດືອນ</label>
            <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ($monthNames as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo $selectedMonth == $num ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            ສະແດງລາຍງານ
        </button>
        
        <a href="<?php echo BASE_URL; ?>/modules/report/summary.php?year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>" 
           target="_blank"
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            ພິມລາຍງານລາຍເດືອນ
        </a>
        
        <a href="<?php echo BASE_URL; ?>/modules/report/annual_summary.php?year=<?php echo $selectedYear; ?>" 
           target="_blank"
           class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            ລາຍງານປະຈຳປີ (12 ເດືອນ)
        </a>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-md p-6">
        <h3 class="text-lg font-medium mb-4">ສະຫຼຸບປະຈຳເດືອນ <?php echo $monthNames[$selectedMonth]; ?> <?php echo $selectedYear; ?></h3>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>ລາຍຮັບ:</span>
                <span class="font-bold"><?php echo formatMoney($totalIncomeMonth); ?></span>
            </div>
            <div class="flex justify-between">
                <span>ລາຍຈ່າຍ:</span>
                <span class="font-bold"><?php echo formatMoney($totalExpenseMonth); ?></span>
            </div>
            <div class="border-t border-blue-400 pt-2 flex justify-between">
                <span class="font-bold">ຍອດຄົງເຫຼືອ:</span>
                <span class="font-bold"><?php echo formatMoney($balanceMonth); ?></span>
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-md p-6">
        <h3 class="text-lg font-medium mb-4">ສະຫຼຸບປະຈຳປີ <?php echo $selectedYear; ?></h3>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>ລາຍຮັບ:</span>
                <span class="font-bold"><?php echo formatMoney($totalIncomeYear); ?></span>
            </div>
            <div class="flex justify-between">
                <span>ລາຍຈ່າຍ:</span>
                <span class="font-bold"><?php echo formatMoney($totalExpenseYear); ?></span>
            </div>
            <div class="border-t border-purple-400 pt-2 flex justify-between">
                <span class="font-bold">ຍອດຄົງເຫຼືອ:</span>
                <span class="font-bold"><?php echo formatMoney($balanceYear); ?></span>
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-2xl shadow-md p-6">
        <h3 class="text-lg font-medium mb-4">ອັດຕາສ່ວນ</h3>
        <div class="space-y-2">
            <?php 
            $percentIncome = $totalIncomeMonth > 0 ? ($totalIncomeMonth / ($totalIncomeMonth + $totalExpenseMonth)) * 100 : 0;
            $percentExpense = $totalExpenseMonth > 0 ? ($totalExpenseMonth / ($totalIncomeMonth + $totalExpenseMonth)) * 100 : 0;
            ?>
            <div>
                <div class="flex justify-between mb-1">
                    <span>ລາຍຮັບ:</span>
                    <span class="font-bold"><?php echo number_format($percentIncome, 1); ?>%</span>
                </div>
                <div class="bg-white bg-opacity-30 rounded-full h-2">
                    <div class="bg-white rounded-full h-2" style="width: <?php echo $percentIncome; ?>%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span>ລາຍຈ່າຍ:</span>
                    <span class="font-bold"><?php echo number_format($percentExpense, 1); ?>%</span>
                </div>
                <div class="bg-white bg-opacity-30 rounded-full h-2">
                    <div class="bg-white rounded-full h-2" style="width: <?php echo $percentExpense; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- ກາຟລາຍຮັບ-ລາຍຈ່າຍປະຈຳປີ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">ລາຍຮັບ-ລາຍຈ່າຍປະຈຳປີ <?php echo $selectedYear; ?></h2>
        <canvas id="yearlyChart"></canvas>
    </div>
    
    <!-- ກາຟລາຍຈ່າຍຕາມໝວດໝູ່ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">ລາຍຈ່າຍຕາມໝວດໝູ່ (<?php echo $monthNames[$selectedMonth]; ?>)</h2>
        <canvas id="expenseCategoryChart"></canvas>
    </div>
</div>

<!-- Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- ລາຍຮັບຕາມໝວດໝູ່ -->
    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-bold">ລາຍຮັບຕາມໝວດໝູ່ (<?php echo $monthNames[$selectedMonth]; ?>)</h2>
        </div>
        <div class="p-6">
            <?php if (empty($incomeByCategory)): ?>
                <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຮັບໃນເດືອນນີ້</p>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600">ໝວດໝູ່</th>
                            <th class="text-center py-2 text-gray-600">ຈຳນວນ</th>
                            <th class="text-right py-2 text-gray-600">ຍອດລວມ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incomeByCategory as $item): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-3"><?php echo e($item['category']); ?></td>
                            <td class="text-center py-3"><?php echo $item['count']; ?></td>
                            <td class="text-right py-3 font-bold text-green-600"><?php echo formatMoney($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-50 font-bold">
                            <td class="py-3">ລວມທັງໝົດ</td>
                            <td class="text-center py-3"><?php echo array_sum(array_column($incomeByCategory, 'count')); ?></td>
                            <td class="text-right py-3 text-green-600"><?php echo formatMoney($totalIncomeMonth); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ລາຍຈ່າຍຕາມໝວດໝູ່ -->
    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-red-600 text-white px-6 py-4">
            <h2 class="text-lg font-bold">ລາຍຈ່າຍຕາມໝວດໝູ່ (<?php echo $monthNames[$selectedMonth]; ?>)</h2>
        </div>
        <div class="p-6">
            <?php if (empty($expenseByCategory)): ?>
                <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍໃນເດືອນນີ້</p>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 text-gray-600">ໝວດໝູ່</th>
                            <th class="text-center py-2 text-gray-600">ຈຳນວນ</th>
                            <th class="text-right py-2 text-gray-600">ຍອດລວມ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenseByCategory as $item): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-3"><?php echo e($item['category']); ?></td>
                            <td class="text-center py-3"><?php echo $item['count']; ?></td>
                            <td class="text-right py-3 font-bold text-red-600"><?php echo formatMoney($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-50 font-bold">
                            <td class="py-3">ລວມທັງໝົດ</td>
                            <td class="text-center py-3"><?php echo array_sum(array_column($expenseByCategory, 'count')); ?></td>
                            <td class="text-right py-3 text-red-600"><?php echo formatMoney($totalExpenseMonth); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ກາຟລາຍຮັບ-ລາຍຈ່າຍປະຈຳປີ
const ctx1 = document.getElementById('yearlyChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: ['ມ.ກ', 'ກ.ພ', 'ມ.ນ', 'ມ.ສ', 'ພ.ພ', 'ມິ.ຖ', 'ກ.ລ', 'ສ.ຫ', 'ກ.ຍ', 'ຕ.ລ', 'ພ.ຈ', 'ທ.ວ'],
        datasets: [
            {
                label: 'ລາຍຮັບ',
                data: <?php echo json_encode(array_values($monthlyIncome)); ?>,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'ລາຍຈ່າຍ',
                data: <?php echo json_encode(array_values($monthlyExpense)); ?>,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    font: { family: "'Noto Sans Lao', sans-serif" }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: { family: "'Noto Sans Lao', sans-serif" },
                    callback: function(value) {
                        return value.toLocaleString('en-US') + ' ກີບ';
                    }
                }
            },
            x: {
                ticks: {
                    font: { family: "'Noto Sans Lao', sans-serif" }
                }
            }
        }
    }
});

// ກາຟລາຍຈ່າຍຕາມໝວດໝູ່
<?php if (!empty($expenseByCategory)): ?>
const ctx2 = document.getElementById('expenseCategoryChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($expenseByCategory, 'category')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($expenseByCategory, 'total')); ?>,
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { family: "'Noto Sans Lao', sans-serif" }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
