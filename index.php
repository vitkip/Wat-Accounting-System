<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໜ້າຫຼັກ (Dashboard)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/temple_functions.php';

requireLogin();

$db = getDB();

// ດຶງ temple_id ຂອງຜູ້ໃຊ້ປະຈຸບັນ (ຖ້າລະບົບ multi-temple ເປີດໃຊ້)
$currentTempleId = null;
$isMultiTemple = function_exists('isMultiTempleEnabled') && isMultiTempleEnabled();
if ($isMultiTemple && function_exists('getCurrentTempleId')) {
    $currentTempleId = getCurrentTempleId();
}

// ດຶງຂໍ້ມູນສະຫຼຸບ
$currentMonth = date('Y-m');
$currentYear = date('Y');

// ລວມລາຍຮັບທັງໝົດ
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE temple_id = ?");
    $stmt->execute([$currentTempleId]);
} else {
    $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM income");
}
$totalIncome = $stmt->fetch()['total'];

// ລວມລາຍຈ່າຍທັງໝົດ
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE temple_id = ?");
    $stmt->execute([$currentTempleId]);
} else {
    $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expense");
}
$totalExpense = $stmt->fetch()['total'];

// ຍອດຄົງເຫຼືອ
$balance = $totalIncome - $totalExpense;

// ລາຍຮັບເດືອນນີ້
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentTempleId, $currentMonth]);
} else {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
}
$monthlyIncome = $stmt->fetch()['total'];

// ລາຍຈ່າຍເດືອນນີ້
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentTempleId, $currentMonth]);
} else {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
}
$monthlyExpense = $stmt->fetch()['total'];

// ດຶງຂໍ້ມູນ 6 ເດືອນຫຼ້າສຸດສຳລັບກາຟ
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    // ລາຍຮັບ
    if ($currentTempleId) {
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$currentTempleId, $month]);
    } else {
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$month]);
    }
    $income = $stmt->fetch()['total'];
    
    // ລາຍຈ່າຍ
    if ($currentTempleId) {
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE temple_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$currentTempleId, $month]);
    } else {
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expense WHERE DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt->execute([$month]);
    }
    $expense = $stmt->fetch()['total'];
    
    $monthlyData[] = [
        'month' => $month,
        'label' => date('m/Y', strtotime($month . '-01')),
        'income' => $income,
        'expense' => $expense
    ];
}

// ດຶງລາຍການລ່າສຸດ 5 ລາຍການ
if ($currentTempleId) {
    $stmt = $db->prepare("
        SELECT 'income' as type, date, description, amount, created_at 
        FROM income 
        WHERE temple_id = ?
        UNION ALL 
        SELECT 'expense' as type, date, description, amount, created_at 
        FROM expense 
        WHERE temple_id = ?
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$currentTempleId, $currentTempleId]);
} else {
    $stmt = $db->query("
        SELECT 'income' as type, date, description, amount, created_at 
        FROM income 
        UNION ALL 
        SELECT 'expense' as type, date, description, amount, created_at 
        FROM expense 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
}
$recentTransactions = $stmt->fetchAll();

// ກຳນົດຊື່ໜ້າ
$pageTitle = "ໜ້າຫຼັກ";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">ໜ້າຫຼັກ</h1>
    <p class="text-gray-600">ພາບລວມລາຍຮັບ ແລະ ລາຍຈ່າຍຂອງວັດ</p>
</div>

<?php displayFlashMessage(); ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- ລວມລາຍຮັບ -->
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ລວມລາຍຮັບ</p>
                <p class="text-2xl font-bold text-green-600"><?php echo formatMoney($totalIncome); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- ລວມລາຍຈ່າຍ -->
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ລວມລາຍຈ່າຍ</p>
                <p class="text-2xl font-bold text-red-600"><?php echo formatMoney($totalExpense); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- ຍອດຄົງເຫຼືອ -->
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ຍອດຄົງເຫຼືອ</p>
                <p class="text-2xl font-bold <?php echo $balance >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                    <?php echo formatMoney($balance); ?>
                </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- ຍອດເດືອນນີ້ -->
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ຍອດເດືອນນີ້</p>
                <p class="text-2xl font-bold <?php echo ($monthlyIncome - $monthlyExpense) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo formatMoney($monthlyIncome - $monthlyExpense); ?>
                </p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>
    
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="<?php echo BASE_URL; ?>/modules/income/add.php" 
       class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl shadow-md p-6 transition duration-200 transform hover:scale-[1.02]">
        <div class="flex items-center">
            <div class="bg-white bg-opacity-30 rounded-full p-4 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-1">ເພີ່ມລາຍຮັບ</h3>
                <p class="text-green-100">ບັນທຶກລາຍຮັບເຂົ້າລະບົບ</p>
            </div>
        </div>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/modules/expense/add.php" 
       class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-2xl shadow-md p-6 transition duration-200 transform hover:scale-[1.02]">
        <div class="flex items-center">
            <div class="bg-white bg-opacity-30 rounded-full p-4 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-1">ເພີ່ມລາຍຈ່າຍ</h3>
                <p class="text-red-100">ບັນທຶກລາຍຈ່າຍເຂົ້າລະບົບ</p>
            </div>
        </div>
    </a>
</div>

<!-- Chart & Recent Transactions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">ລາຍຮັບ-ລາຍຈ່າຍ 6 ເດືອນຫຼ້າສຸດ</h2>
        <canvas id="monthlyChart"></canvas>
    </div>
    
    <!-- Recent Transactions -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">ລາຍການລ່າສຸດ</h2>
        <div class="space-y-3">
            <?php if (empty($recentTransactions)): ?>
                <p class="text-gray-500 text-center py-4">ຍັງບໍ່ມີຂໍ້ມູນ</p>
            <?php else: ?>
                <?php foreach ($recentTransactions as $trans): ?>
                <div class="border-l-4 <?php echo $trans['type'] === 'income' ? 'border-green-500' : 'border-red-500'; ?> pl-3 py-2">
                    <p class="font-medium text-gray-800 text-sm">
                        <?php echo e(mb_substr($trans['description'], 0, 30, 'UTF-8')); ?>
                        <?php echo mb_strlen($trans['description'], 'UTF-8') > 30 ? '...' : ''; ?>
                    </p>
                    <p class="text-xs text-gray-600"><?php echo formatDate($trans['date']); ?></p>
                    <p class="font-bold <?php echo $trans['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $trans['type'] === 'income' ? '+' : '-'; ?> <?php echo formatMoney($trans['amount']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<script>
// ສ້າງກາຟ
const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($monthlyData, 'label')); ?>,
        datasets: [
            {
                label: 'ລາຍຮັບ',
                data: <?php echo json_encode(array_column($monthlyData, 'income')); ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            },
            {
                label: 'ລາຍຈ່າຍ',
                data: <?php echo json_encode(array_column($monthlyData, 'expense')); ?>,
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        family: "'Noto Sans Lao', sans-serif",
                        size: 12
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        family: "'Noto Sans Lao', sans-serif"
                    },
                    callback: function(value) {
                        return value.toLocaleString('en-US') + ' ກີບ';
                    }
                }
            },
            x: {
                ticks: {
                    font: {
                        family: "'Noto Sans Lao', sans-serif"
                    }
                }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
