<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ພິມລາຍງານ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();

$selectedYear = $_GET['year'] ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');
$yearMonth = "{$selectedYear}-{$selectedMonth}";

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

// ດຶງຂໍ້ມູນລາຍຮັບຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT i.*, u.full_name 
        FROM income i
        LEFT JOIN users u ON i.created_by = u.id
        WHERE DATE_FORMAT(i.date, '%Y-%m') = ?
        ORDER BY i.date ASC
    ");
    $stmt->execute([$yearMonth]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT i.*, u.full_name 
        FROM income i
        LEFT JOIN users u ON i.created_by = u.id
        WHERE i.temple_id = ? AND DATE_FORMAT(i.date, '%Y-%m') = ?
        ORDER BY i.date ASC
    ");
    $stmt->execute([$currentTempleId, $yearMonth]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT i.*, u.full_name 
        FROM income i
        LEFT JOIN users u ON i.created_by = u.id
        WHERE DATE_FORMAT(i.date, '%Y-%m') = ?
        ORDER BY i.date ASC
    ");
    $stmt->execute([$yearMonth]);
}
$incomeRecords = $stmt->fetchAll();
$totalIncome = array_sum(array_column($incomeRecords, 'amount'));

// ດຶງຂໍ້ມູນລາຍຈ່າຍຕາມສິດ
if ($isSuperAdmin) {
    // Super Admin ເບິ່ງທຸກວັດ
    $stmt = $db->prepare("
        SELECT e.*, u.full_name 
        FROM expense e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE DATE_FORMAT(e.date, '%Y-%m') = ?
        ORDER BY e.date ASC
    ");
    $stmt->execute([$yearMonth]);
} elseif ($isMultiTemple && $currentTempleId) {
    // Admin/User ເບິ່ງສະເພາະວັດຂອງຕົນ
    $stmt = $db->prepare("
        SELECT e.*, u.full_name 
        FROM expense e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE e.temple_id = ? AND DATE_FORMAT(e.date, '%Y-%m') = ?
        ORDER BY e.date ASC
    ");
    $stmt->execute([$currentTempleId, $yearMonth]);
} else {
    // ລະບົບເກົ່າ
    $stmt = $db->prepare("
        SELECT e.*, u.full_name 
        FROM expense e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE DATE_FORMAT(e.date, '%Y-%m') = ?
        ORDER BY e.date ASC
    ");
    $stmt->execute([$yearMonth]);
}
$expenseRecords = $stmt->fetchAll();
$totalExpense = array_sum(array_column($expenseRecords, 'amount'));

$balance = $totalIncome - $totalExpense;

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
    <title>ລາຍງານປະຈຳເດືອນ <?php echo $monthNames[$selectedMonth]; ?> <?php echo $selectedYear; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Phetsarath', sans-serif;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
            @page {
                size: A4;
                margin: 1cm;
            }
        }
    </style>
</head>
<body class="bg-white">
    
    <div class="max-w-full mx-auto p-8">
        
        <!-- Print Button -->
        <div class="no-print mb-6 text-right">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                ພິມລາຍງານ
            </button>
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg ml-2">
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">ລາຍງານບັນຊີວັດ <?php echo $templeName; ?> ເມືອງສີໂຄດຕະບອງ ນະຄອນຫຼວງວຽງຈັນ</h2>
            <h2 class="text-xl font-medium text-gray-700">ປະຈຳເດືອນ <?php echo $monthNames[$selectedMonth]; ?> <?php echo $selectedYear; ?></h2>
            <p class="text-gray-600 mt-2">ວັນທີ່ພິມ: <?php echo formatDate(date('Y-m-d')); ?></p>
        </div>
        
        <!-- Summary -->
        <div class="mb-8 bg-gray-100 p-6 rounded-lg print:rounded-none">
            <h3 class="text-lg font-bold mb-4">ສະຫຼຸບລວມ</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="bg-green-100 p-4 rounded">
                    <p class="text-sm text-gray-600 mb-1">ລວມລາຍຮັບ</p>
                    <p class="text-2xl font-bold text-green-700"><?php echo formatMoney($totalIncome); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo count($incomeRecords); ?> ລາຍການ</p>
                </div>
                <div class="bg-red-100 p-4 rounded">
                    <p class="text-sm text-gray-600 mb-1">ລວມລາຍຈ່າຍ</p>
                    <p class="text-2xl font-bold text-red-700"><?php echo formatMoney($totalExpense); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo count($expenseRecords); ?> ລາຍການ</p>
                </div>
                <div class="bg-blue-100 p-4 rounded">
                    <p class="text-sm text-gray-600 mb-1">ຍອດຄົງເຫຼືອ</p>
                    <p class="text-2xl font-bold <?php echo $balance >= 0 ? 'text-blue-700' : 'text-red-700'; ?>">
                        <?php echo formatMoney($balance); ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $balance >= 0 ? 'ກຳໄລ' : 'ຂາດທຶນ'; ?></p>
                </div>
            </div>
        </div>
        
        <!-- ລາຍຮັບ -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-green-700 mb-4 border-b-2 border-green-700 pb-2">ລາຍການລາຍຮັບ</h3>
            <?php if (empty($incomeRecords)): ?>
                <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຮັບໃນເດືອນນີ້</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-green-100">
                            <th class="border border-gray-300 px-2 py-2 text-left w-16">ລຳດັບ</th>
                            <th class="border border-gray-300 px-2 py-2 text-left w-32">ວັນທີ່</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">ລາຍລະອຽດ</th>
                            <th class="border border-gray-300 px-2 py-2 text-left w-40">ໝວດໝູ່</th>
                            <th class="border border-gray-300 px-3 py-2 text-right w-40">ຈຳນວນເງິນ (ກີບ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incomeRecords as $index => $record): ?>
                        <tr>
                            <td class="border border-gray-300 px-2 py-2 text-center"><?php echo $index + 1; ?></td>
                            <td class="border border-gray-300 px-2 py-2"><?php echo formatDate($record['date']); ?></td>
                            <td class="border border-gray-300 px-3 py-2"><?php echo e($record['description']); ?></td>
                            <td class="border border-gray-300 px-2 py-2"><?php echo e($record['category']); ?></td>
                            <td class="border border-gray-300 px-3 py-2 text-right font-medium"><?php echo formatMoney($record['amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-green-200 font-bold">
                            <td colspan="4" class="border border-gray-300 px-3 py-3 text-right">ລວມລາຍຮັບທັງໝົດ:</td>
                            <td class="border border-gray-300 px-3 py-3 text-right text-lg"><?php echo formatMoney($totalIncome); ?></td>
                        </tr>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ລາຍຈ່າຍ -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-red-700 mb-4 border-b-2 border-red-700 pb-2">ລາຍການລາຍຈ່າຍ</h3>
            <?php if (empty($expenseRecords)): ?>
                <p class="text-gray-500 text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍໃນເດືອນນີ້</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-red-100">
                            <th class="border border-gray-300 px-2 py-2 text-left w-16">ລຳດັບ</th>
                            <th class="border border-gray-300 px-2 py-2 text-left w-32">ວັນທີ່</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">ລາຍລະອຽດ</th>
                            <th class="border border-gray-300 px-2 py-2 text-left w-40">ໝວດໝູ່</th>
                            <th class="border border-gray-300 px-3 py-2 text-right w-40">ຈຳນວນເງິນ (ກີບ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenseRecords as $index => $record): ?>
                        <tr>
                            <td class="border border-gray-300 px-2 py-2 text-center"><?php echo $index + 1; ?></td>
                            <td class="border border-gray-300 px-2 py-2"><?php echo formatDate($record['date']); ?></td>
                            <td class="border border-gray-300 px-3 py-2"><?php echo e($record['description']); ?></td>
                            <td class="border border-gray-300 px-2 py-2"><?php echo e($record['category']); ?></td>
                            <td class="border border-gray-300 px-3 py-2 text-right font-medium"><?php echo formatMoney($record['amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-red-200 font-bold">
                            <td colspan="4" class="border border-gray-300 px-3 py-3 text-right">ລວມລາຍຈ່າຍທັງໝົດ:</td>
                            <td class="border border-gray-300 px-3 py-3 text-right text-lg"><?php echo formatMoney($totalExpense); ?></td>
                        </tr>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <div class="grid grid-cols-3 gap-8 text-center">
                <div>
                    <p class="mb-12">ເຈົ້າອະທິການວັດ</p>
                    <div class="border-t border-gray-800 pt-2">
                        <br>
                        <p>(...........................)</p>
                    </div>
                </div>
                <div>
                    <p class="mb-12">ຜູ້ກວດສອບ</p>
                    <div class="border-t border-gray-800 pt-2">
                        <br>
                        <p>(...........................)</p>
                    </div>
                </div>
                <div>
                    <p class="mb-12">ຜູ້ລາຍງານ</p>
                    <div class="border-t border-gray-800 pt-2">
                        <br>
                        <p>(...........................)</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
</body>
</html>
