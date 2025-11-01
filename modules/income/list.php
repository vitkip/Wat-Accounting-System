<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລາຍການລາຍຮັບ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();

// ດຶງ temple_id ຂອງຜູ້ໃຊ້ປະຈຸບັນ (ຖ້າລະບົບ multi-temple ເປີດໃຊ້)
$currentTempleId = null;
$isMultiTemple = function_exists('isMultiTempleEnabled') && isMultiTempleEnabled();
if ($isMultiTemple && function_exists('getCurrentTempleId')) {
    $currentTempleId = getCurrentTempleId();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search & Filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build Query (ເພີ່ມ filter temple_id ຖ້າມີ - ໃຊ້ i.temple_id ເພື່ອປ້ອງກັນ ambiguous column)
$where = [];
$params = [];

if ($currentTempleId) {
    $where[] = 'i.temple_id = :temple_id';
    $params[':temple_id'] = $currentTempleId;
}

if (empty($where)) {
    $where[] = '1=1'; // ຖ້າບໍ່ມີ filter ໃດໆ
}

if (!empty($search)) {
    $where[] = "i.description LIKE :search";
    $params[':search'] = "%{$search}%";
}

if (!empty($category)) {
    $where[] = "i.category = :category";
    $params[':category'] = $category;
}

if (!empty($dateFrom)) {
    $where[] = "i.date >= :date_from";
    $params[':date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "i.date <= :date_to";
    $params[':date_to'] = $dateTo;
}

$whereClause = implode(' AND ', $where);

// Get Total
$stmt = $db->prepare("SELECT COUNT(*) as total FROM income i WHERE {$whereClause}");
$stmt->execute($params);
$totalRecords = $stmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get Records - ລະບຸຄັອລຳຢ່າງຊັດເຈນເພື່ອປ້ອງກັນ ambiguous
$sql = "SELECT i.id, i.date, i.description, i.category, i.amount,
               i.created_by, i.temple_id, i.created_at, i.updated_at,
               u.full_name
        FROM income i
        LEFT JOIN users u ON i.created_by = u.id
        WHERE {$whereClause}
        ORDER BY i.date DESC, i.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔍 DEBUG: ກວດສອບຂໍ້ມູນທີ່ດຶງມາ
error_log("📊 Income List - Total records fetched: " . count($records));
if (!empty($records)) {
    error_log("📊 First record ID: " . ($records[0]['id'] ?? 'NULL') . " (type: " . gettype($records[0]['id'] ?? null) . ")");
    error_log("📊 First record data: " . json_encode($records[0]));
}

// Get Total Amount
$stmt = $db->prepare("SELECT COALESCE(SUM(i.amount), 0) as total FROM income i WHERE {$whereClause}");
$stmt->execute($params);
$totalAmount = $stmt->fetch()['total'];

// Get Categories (ຂອງວັດນີ້ເທົ່ານັ້ນ ຖ້າລະບົບ multi-temple ເປີດໃຊ້)
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT * FROM income_categories WHERE temple_id = ? ORDER BY name");
    $stmt->execute([$currentTempleId]);
} else {
    $stmt = $db->query("SELECT * FROM income_categories ORDER BY name");
}
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ລາຍການລາຍຮັບ</h1>
            <p class="text-gray-600">ຈັດການລາຍຮັບຂອງວັດ</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/modules/income/add.php" 
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            ເພີ່ມລາຍຮັບ
        </a>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Summary Card -->
<div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-green-100 mb-1">ລວມລາຍຮັບທັງໝົດ</p>
            <p class="text-3xl font-bold"><?php echo formatMoney($totalAmount); ?></p>
            <p class="text-green-100 text-sm mt-1">ທັງໝົດ <?php echo $totalRecords; ?> ລາຍການ</p>
        </div>
        <div class="bg-white bg-opacity-20 rounded-full p-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-md p-6 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ຄົ້ນຫາ</label>
            <input type="text" 
                   name="search" 
                   value="<?php echo e($search); ?>"
                   placeholder="ຄົ້ນຫາລາຍລະອຽດ..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ໝວດໝູ່</label>
            <select name="category" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">ທັງໝົດ</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ວັນທີ່ເລີ່ມຕົ້ນ</label>
            <input type="date" 
                   name="date_from" 
                   value="<?php echo e($dateFrom); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ວັນທີ່ສິ້ນສຸດ</label>
            <input type="date" 
                   name="date_to" 
                   value="<?php echo e($dateTo); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                ຄົ້ນຫາ
            </button>
            <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                ລ້າງ
            </a>
        </div>
        
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ວັນທີ່</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ລາຍລະອຽດ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ໝວດໝູ່</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ຈຳນວນເງິນ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຜູ້ບັນທຶກ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ການຈັດການ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        ບໍ່ມີຂໍ້ມູນລາຍຮັບ
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                    $displayedCount = 0;
                    foreach ($records as $record):
                        // ດຶງ ID ຈາກ record (ຖ້າບໍ່ມີໃຫ້ໃຊ້ 0)
                        $recordId = intval($record['id'] ?? 0);

                        // DEBUG: ລົງ log ທຸກ record
                        error_log("🔍 Income record - ID: {$recordId}, Date: {$record['date']}, Amount: {$record['amount']}");

                        $displayedCount++;
                    ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo formatDate($record['date']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo e($record['description']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <?php echo e($record['category']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">
                            <?php echo formatMoney($record['amount']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($record['full_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/modules/income/edit.php?id=<?php echo $recordId; ?>"
                               class="text-blue-600 hover:text-blue-900 mr-3">ແກ້ໄຂ</a>
                            <form method="POST" action="<?php echo BASE_URL; ?>/modules/income/delete.php" style="display:inline;"
                                  onsubmit="return confirmDeleteIncome('<?php echo e($record['description']); ?>');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <input type="hidden" name="id" value="<?php echo $recordId; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-transparent border-0 cursor-pointer">ລຶບ</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php 
                    // DEBUG: ລາຍງານຈຳນວນທີ່ສະແດງ
                    error_log("✅ Income List - Displayed $displayedCount out of " . count($records) . " records");
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                ໜ້າ <?php echo $page; ?> ຈາກ <?php echo $totalPages; ?>
            </div>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        ກ່ອນໜ້າ
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        ຕໍ່ໄປ
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmDeleteIncome(description) {
    return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບລາຍຮັບນີ້?\n\n' + description);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
