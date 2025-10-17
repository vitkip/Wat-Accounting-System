<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລາຍການລາຍຈ່າຍ
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

// Build Query (ເພີ່ມ filter temple_id ຖ້າມີ - ໃຊ້ e.temple_id ເພື່ອປ້ອງກັນ ambiguous column)
$where = [];
$params = [];

if ($currentTempleId) {
    $where[] = 'e.temple_id = :temple_id';
    $params[':temple_id'] = $currentTempleId;
}

if (empty($where)) {
    $where[] = '1=1'; // ຖ້າບໍ່ມີ filter ໃດໆ
}

if (!empty($search)) {
    $where[] = "e.description LIKE :search";
    $params[':search'] = "%{$search}%";
}

if (!empty($category)) {
    $where[] = "e.category = :category";
    $params[':category'] = $category;
}

if (!empty($dateFrom)) {
    $where[] = "e.date >= :date_from";
    $params[':date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "e.date <= :date_to";
    $params[':date_to'] = $dateTo;
}

$whereClause = implode(' AND ', $where);

// Get Total
$stmt = $db->prepare("SELECT COUNT(*) as total FROM expense e WHERE {$whereClause}");
$stmt->execute($params);
$totalRecords = $stmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get Records - ລະບຸຄັອລຳຢ່າງຊັດເຈນເພື່ອປ້ອງກັນ ambiguous
$sql = "SELECT e.id AS id, e.date, e.description, e.category, e.amount, 
               e.created_by, e.temple_id, e.created_at, e.updated_at,
               u.full_name 
        FROM expense e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE {$whereClause}
        ORDER BY e.date DESC, e.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Total Amount
$stmt = $db->prepare("SELECT COALESCE(SUM(e.amount), 0) as total FROM expense e WHERE {$whereClause}");
$stmt->execute($params);
$totalAmount = $stmt->fetch()['total'];

// Get Categories (ຂອງວັດນີ້ເທົ່ານັ້ນ ຖ້າລະບົບ multi-temple ເປີດໃຊ້)
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT * FROM expense_categories WHERE temple_id = ? ORDER BY name");
    $stmt->execute([$currentTempleId]);
} else {
    $stmt = $db->query("SELECT * FROM expense_categories ORDER BY name");
}
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ລາຍການລາຍຈ່າຍ</h1>
            <p class="text-gray-600">ຈັດການລາຍຈ່າຍຂອງວັດ</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/modules/expense/add.php" 
           class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            ເພີ່ມລາຍຈ່າຍ
        </a>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Summary Card -->
<div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-2xl shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-red-100 mb-1">ລວມລາຍຈ່າຍທັງໝົດ</p>
            <p class="text-3xl font-bold"><?php echo formatMoney($totalAmount); ?></p>
            <p class="text-red-100 text-sm mt-1">ທັງໝົດ <?php echo $totalRecords; ?> ລາຍການ</p>
        </div>
        <div class="bg-white bg-opacity-20 rounded-full p-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
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
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ໝວດໝູ່</label>
            <select name="category" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
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
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">ວັນທີ່ສິ້ນສຸດ</label>
            <input type="date" 
                   name="date_to" 
                   value="<?php echo e($dateTo); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                ຄົ້ນຫາ
            </button>
            <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
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
                        ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍ
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): 
                        // ກວດສອບວ່າ ID ມີຄ່າບໍ່
                        $recordId = intval($record['id'] ?? 0);
                        if ($recordId <= 0) {
                            continue; // ຂ້າມແຖວທີ່ມີ ID ບໍ່ຖືກຕ້ອງ
                        }
                    ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo formatDate($record['date']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo e($record['description']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                <?php echo e($record['category']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600 text-right">
                            <?php echo formatMoney($record['amount']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($record['full_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/modules/expense/edit.php?id=<?php echo $recordId; ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">ແກ້ໄຂ</a>
                            <a href="#" 
                               onclick="confirmDeleteExpense(<?php echo $recordId; ?>); return false;"
                               class="text-red-600 hover:text-red-900">ລຶບ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
function confirmDeleteExpense(id) {
    // ກວດສອບວ່າ ID ເປັນຕົວເລກທີ່ຖືກຕ້ອງ
    console.log('Delete Expense ID:', id, 'Type:', typeof id);
    
    if (!id || isNaN(id) || id <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'ຂໍ້ຜິດພາດ!',
            text: 'ID ບໍ່ຖືກຕ້ອງ: ' + id,
            confirmButtonText: 'ຕົກລົງ'
        });
        return;
    }
    
    confirmDelete('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບລາຍຈ່າຍນີ້?').then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/expense/delete.php?id=' + parseInt(id);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
