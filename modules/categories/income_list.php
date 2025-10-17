<?php
// ບັງຄັບໃຫ້ບໍ່ cache
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

require_once '../../config.php';
require_once '../../includes/temple_functions.php';

// Temporary fix: Ensure generateCSRF function exists
if (!function_exists('generateCSRF')) {
    function generateCSRF() {
        return generateCSRFToken();
    }
}

// ກວດສອບການເຂົ້າສູ່ລະບົບ
if (!isLoggedIn()) {
    redirect('/login.php');
}

// ຕ້ອງເປັນແອດມິນເທົ່ານັ້ນ
if (!isAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$pageTitle = 'ຈັດການໝວດໝູ່ລາຍຮັບ';
$db = getDB();

// ດຶງ temple_id ຂອງຜູ້ໃຊ້ປະຈຸບັນ
$currentTempleId = null;
$isMultiTemple = function_exists('isMultiTempleEnabled') && isMultiTempleEnabled();
if ($isMultiTemple && function_exists('getCurrentTempleId')) {
    $currentTempleId = getCurrentTempleId();
}

// ລຶບໝວດໝູ່
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // ກວດສອບ CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        setFlashMessage('ຂໍ້ຜິດພາດຄວາມປອດໄພ: CSRF Token ບໍ່ຖືກຕ້ອງ', 'error');
        redirect('/modules/categories/income_list.php');
    }
    
    $delete_id = $_POST['delete_id'];
    
    // ⚠️ ກວດສອບວ່າ delete_id ເປັນຕົວເລກທີ່ຖືກຕ້ອງ
    if (!$delete_id || !is_numeric($delete_id) || $delete_id <= 0) {
        setFlashMessage('ID ບໍ່ຖືກຕ້ອງ', 'error');
        redirect('/modules/categories/income_list.php');
    }
    
    try {
        // ⚠️ ກວດສອບວ່າໝວດໝູ່ນີ້ເປັນຂອງວັດນີ້ບໍ່ (ຄວາມປອດໄພ)
        if ($currentTempleId) {
            $stmt = $db->prepare("SELECT id FROM income_categories WHERE id = ? AND temple_id = ?");
            $stmt->execute([$delete_id, $currentTempleId]);
            if (!$stmt->fetch()) {
                setFlashMessage('ບໍ່ພົບໝວດໝູ່ນີ້ ຫຼື ທ່ານບໍ່ມີສິດລຶບ', 'error');
                redirect('/modules/categories/income_list.php');
            }
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM income WHERE category = (SELECT name FROM income_categories WHERE id = ?)");
        $stmt->execute([$delete_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            setFlashMessage("ບໍ່ສາມາດລຶບໄດ້! ໝວດໝູ່ນີ້ຖືກໃຊ້ງານຢູ່ໃນ {$count} ລາຍການ", 'error');
        } else {
            // ລຶບພ້ອມກວດສອບ temple_id
            if ($currentTempleId) {
                $stmt = $db->prepare("DELETE FROM income_categories WHERE id = ? AND temple_id = ?");
                $stmt->execute([$delete_id, $currentTempleId]);
            } else {
                $stmt = $db->prepare("DELETE FROM income_categories WHERE id = ?");
                $stmt->execute([$delete_id]);
            }
            
            if ($stmt->rowCount() > 0) {
                logActivity($_SESSION['user_id'], 'DELETE', 'income_categories', $delete_id, 'ລຶບໝວດໝູ່ລາຍຮັບ');
                setFlashMessage('ລຶບໝວດໝູ່ສຳເລັດ', 'success');
            } else {
                setFlashMessage('ບໍ່ສາມາດລຶບໄດ້', 'error');
            }
        }
    } catch (PDOException $e) {
        setFlashMessage('ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage(), 'error');
    }
    redirect('/modules/categories/income_list.php');
}

// ດຶງຂໍ້ມູນ (ຕາມ temple_id)
try {
    // Debug: ກວດສອບວ່າ query ດຶງຂໍ້ມູນໄດ້ບໍ່
    error_log("🔍 Fetching income categories at: " . date('Y-m-d H:i:s') . " for temple_id: " . ($currentTempleId ?? 'NULL'));
    
    if ($currentTempleId) {
        // ດຶງໝວດໝູ່ຂອງວັດນີ້ເທົ່ານັ້ນ
        $stmt = $db->prepare("
            SELECT ic.id, ic.name, ic.description, ic.created_at,
                   COUNT(i.id) as usage_count,
                   COALESCE(SUM(i.amount), 0) as total_amount
            FROM income_categories ic
            LEFT JOIN income i ON ic.name = i.category AND i.temple_id = ic.temple_id
            WHERE ic.temple_id = ?
            GROUP BY ic.id, ic.name, ic.description, ic.created_at
            ORDER BY ic.name ASC
        ");
        $stmt->execute([$currentTempleId]);
    } else {
        // ຖ້າບໍ່ມີ multi-temple ໃຫ້ດຶງທັງໝົດ
        $stmt = $db->query("
            SELECT ic.id, ic.name, ic.description, ic.created_at,
                   COUNT(i.id) as usage_count,
                   COALESCE(SUM(i.amount), 0) as total_amount
            FROM income_categories ic
            LEFT JOIN income i ON ic.name = i.category
            GROUP BY ic.id, ic.name, ic.description, ic.created_at
            ORDER BY ic.name ASC
        ");
    }
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: ລົງບັນທຶກຈຳນວນທີ່ດຶງໄດ້
    error_log("✅ Found " . count($categories) . " income categories");
    
} catch (PDOException $e) {
    error_log("❌ Error fetching income categories: " . $e->getMessage());
    $categories = [];
}

require_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                ຈັດການໝວດໝູ່ລາຍຮັບ
            </h1>
            <p class="text-gray-600 mt-2">ຈັດການໝວດໝູ່ລາຍຮັບຂອງວັດ</p>
            <?php if (count($categories) > 0): ?>
                <p class="text-xs text-gray-400 mt-1">🔄 ອັບເດດ: <?= date('d/m/Y H:i:s') ?> | ພົບ <?= count($categories) ?> ໝວດໝູ່</p>
            <?php endif; ?>
        </div>

        <!-- ສະຖິຕິ -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">ຈຳນວນໝວດໝູ່</p>
                        <p class="text-3xl font-bold mt-1"><?= count($categories) ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">ລາຍການທັງໝົດ</p>
                        <p class="text-3xl font-bold mt-1"><?= array_sum(array_column($categories, 'usage_count')) ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">ຍອດລວມ</p>
                        <p class="text-3xl font-bold mt-1"><?= number_format(array_sum(array_column($categories, 'total_amount'))) ?> ກີບ</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mb-4 flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" 
               class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                ໝວດໝູ່ລາຍຈ່າຍ
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/categories/income_add.php" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                ເພີ່ມໝວດໝູ່ລາຍຮັບ
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-green-500 to-green-600">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">ລ/ດ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">ຊື່ໝວດໝູ່</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">ຄຳອະທິບາຍ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">ຈຳນວນການໃຊ້</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">ຍອດລວມ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">ວັນທີ່ສ້າງ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-lg font-medium">ຍັງບໍ່ມີຂໍ້ມູນໝວດໝູ່ລາຍຮັບ</p>
                                    <p class="text-sm mt-1">ກົດປຸ່ມ "ເພີ່ມໝວດໝູ່ລາຍຮັບ" ເພື່ອເລີ່ມຕົ້ນ</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($categories as $cat): ?>
                                <tr class="hover:bg-green-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-semibold"><?= $no++ ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($cat['name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= $cat['description'] ? htmlspecialchars($cat['description']) : '<span class="text-gray-400 italic">ບໍ່ມີຄຳອະທິບາຍ</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= number_format($cat['usage_count']) ?> ລາຍການ
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <span class="font-bold text-green-600"><?= number_format($cat['total_amount']) ?> ກີບ</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <?= date('d/m/Y', strtotime($cat['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="<?php echo BASE_URL; ?>/modules/categories/income_edit.php?id=<?= $cat['id'] ?>" 
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-lg mr-2 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            ແກ້ໄຂ
                                        </a>
                                        <?php if ($cat['usage_count'] == 0): ?>
                                            <form method="POST" class="inline" onsubmit="return confirmDelete('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບໝວດໝູ່ນີ້?');">
                                                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                                                <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg transition duration-200">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    ລຶບ
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button disabled class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed" title="ບໍ່ສາມາດລຶບໄດ້ເນື່ອງຈາກມີການໃຊ້ງານ">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                                ລຶບ
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
