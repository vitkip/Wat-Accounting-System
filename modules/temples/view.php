<?php
/**
 * ລະບົບບັນຊີວັດ - ເບິ່ງລາຍລະອຽດວັດ (Super Admin Only)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// ກວດສອບສິດ Super Admin
if (!isSuperAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$db = getDB();
$templeId = $_GET['id'] ?? 0;

// ດຶງຂໍ້ມູນວັດ
$temple = getTempleById($templeId);

if (!$temple) {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນວັດ', 'error');
    redirect('/modules/temples/index.php');
}

// ດຶງສະຖິຕິ
$stats = getTempleStatistics($templeId);

// ດຶງຜູ້ໃຊ້ທັງໝົດຂອງວັດ
$users = getTempleUsers($templeId);

// ດຶງລາຍການລ່າສຸດ 10 ລາຍການ
$stmt = $db->prepare("
    SELECT 'income' as type, date, description, amount, created_at 
    FROM income 
    WHERE temple_id = ?
    UNION ALL 
    SELECT 'expense' as type, date, description, amount, created_at 
    FROM expense 
    WHERE temple_id = ?
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$templeId, $templeId]);
$recentTransactions = $stmt->fetchAll();

$pageTitle = "ລາຍລະອຽດວັດ";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo e($temple['temple_name_lao']); ?></h1>
            <p class="text-gray-600">ລະຫັດວັດ: <strong><?php echo e($temple['temple_code']); ?></strong></p>
        </div>
        <div class="flex space-x-3">
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                ກັບຄືນ
            </a>
            <a href="edit.php?id=<?php echo $templeId; ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                ແກ້ໄຂ
            </a>
        </div>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- ສະຖານະວັດ -->
<div class="bg-white rounded-2xl shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">ສະຖານະວັດ</h2>
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full <?php echo $temple['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $temple['status'] === 'active' ? '✅ ເປີດໃຊ້ງານ' : '❌ ປິດໃຊ້ງານ'; ?>
            </span>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">ສ້າງເມື່ອ: <?php echo formatDate($temple['created_at']); ?></p>
            <p class="text-sm text-gray-600">ອັບເດດ: <?php echo formatDate($temple['updated_at']); ?></p>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ລາຍຮັບລວມ</p>
                <p class="text-2xl font-bold text-green-600"><?php echo formatMoney($stats['total_income'] ?? 0); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ລາຍຈ່າຍລວມ</p>
                <p class="text-2xl font-bold text-red-600"><?php echo formatMoney($stats['total_expense'] ?? 0); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ຍອດຄົງເຫຼືອ</p>
                <p class="text-2xl font-bold <?php echo ($stats['balance'] ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                    <?php echo formatMoney($stats['balance'] ?? 0); ?>
                </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-1">ຈຳນວນຜູ້ໃຊ້</p>
                <p class="text-3xl font-bold text-purple-600"><?php echo count($users); ?></p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- 2 Columns Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- ຂໍ້ມູນວັດ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
            <span class="mr-2">🏛️</span> ຂໍ້ມູນວັດ
        </h2>
        
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ລະຫັດວັດ:</span>
                <span class="text-gray-900 font-bold"><?php echo e($temple['temple_code']); ?></span>
            </div>
            
            <?php if ($temple['temple_name']): ?>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ຊື່ວັດ (EN):</span>
                <span class="text-gray-900"><?php echo e($temple['temple_name']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ຊື່ວັດ (ລາວ):</span>
                <span class="text-gray-900 font-bold"><?php echo e($temple['temple_name_lao']); ?></span>
            </div>
            
            <?php if ($temple['abbot_name']): ?>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ເຈົ້າອະທິການ:</span>
                <span class="text-gray-900"><?php echo e($temple['abbot_name']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($temple['phone']): ?>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ເບີໂທ:</span>
                <span class="text-gray-900"><?php echo e($temple['phone']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($temple['email']): ?>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ອີເມລ:</span>
                <span class="text-gray-900"><?php echo e($temple['email']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($temple['district'] || $temple['province']): ?>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600 font-medium">ພື້นທີ່:</span>
                <span class="text-gray-900">
                    <?php echo e($temple['district']); ?><?php echo $temple['district'] && $temple['province'] ? ', ' : ''; ?><?php echo e($temple['province']); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($temple['address']): ?>
            <div class="py-2">
                <span class="text-gray-600 font-medium block mb-1">ທີ່ຢູ່ເຕັມ:</span>
                <span class="text-gray-900"><?php echo nl2br(e($temple['address'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ຜູ້ໃຊ້ງານ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
            <span class="mr-2">👥</span> ຜູ້ໃຊ້ງານ (<?php echo count($users); ?> ຄົນ)
        </h2>
        
        <?php if (empty($users)): ?>
            <p class="text-center text-gray-500 py-8">ຍັງບໍ່ມີຜູ້ໃຊ້ໃນວັດນີ້</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($users as $user): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-purple-600 font-bold"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?php echo e($user['full_name'] ?? $user['username']); ?></p>
                            <p class="text-xs text-gray-600">@<?php echo e($user['username']); ?></p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $user['role'] === 'admin' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<!-- ລາຍການລ່າສຸດ -->
<div class="bg-white rounded-2xl shadow-md p-6 mt-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
        <span class="mr-2">📋</span> ລາຍການລ່າສຸດ (10 ລາຍການ)
    </h2>
    
    <?php if (empty($recentTransactions)): ?>
        <p class="text-center text-gray-500 py-8">ຍັງບໍ່ມີລາຍການໃນວັດນີ້</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($recentTransactions as $trans): ?>
            <div class="border-l-4 <?php echo $trans['type'] === 'income' ? 'border-green-500' : 'border-red-500'; ?> pl-4 py-2 bg-gray-50 rounded-r-lg hover:bg-gray-100 transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">
                            <?php echo e(mb_substr($trans['description'], 0, 50, 'UTF-8')); ?>
                            <?php echo mb_strlen($trans['description'], 'UTF-8') > 50 ? '...' : ''; ?>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            <span class="mr-3">📅 <?php echo formatDate($trans['date']); ?></span>
                            <span><?php echo $trans['type'] === 'income' ? '💰 ລາຍຮັບ' : '💸 ລາຍຈ່າຍ'; ?></span>
                        </p>
                    </div>
                    <div class="ml-4">
                        <p class="font-bold text-lg <?php echo $trans['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $trans['type'] === 'income' ? '+' : '-'; ?> <?php echo formatMoney($trans['amount']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Actions -->
<div class="mt-8 flex justify-center space-x-4">
    <button onclick="switchToTemple(<?php echo $templeId; ?>, '<?php echo e($temple['temple_name_lao']); ?>')" 
            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition duration-200 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
        </svg>
        ສະຫຼັບໄປວັດນີ້
    </button>
</div>

<script>
function switchToTemple(templeId, templeName) {
    Swal.fire({
        title: 'ສະຫຼັບໄປວັດ',
        text: `ທ່ານຕ້ອງການສະຫຼັບໄປເບີ່ງຂໍ້ມູນຂອງ "${templeName}" ບໍ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ແມ່ນແລ້ວ, ສະຫຼັບເລີຍ',
        cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'switch.php?temple_id=' + templeId;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
