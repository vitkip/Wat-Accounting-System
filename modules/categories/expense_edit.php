<?php
require_once '../../config.php';

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

$db = getDB();
$id = $_GET['id'] ?? 0;

// ດຶງຂໍ້ມູນໝວດໝູ່
try {
    $stmt = $db->prepare("SELECT * FROM expense_categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        setFlashMessage('ບໍ່ພົບໝວດໝູ່ທີ່ຕ້ອງການແກ້ໄຂ', 'error');
        redirect('/modules/categories/expense_list.php');
    }
} catch (PDOException $e) {
    setFlashMessage('ເກີດຂໍ້ຜິດພາດ', 'error');
    redirect('/modules/categories/expense_list.php');
}

// ປະມວນຜົນຟອມ (ກ່ອນ header)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ກວດສອບ CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        setFlashMessage('ຂໍ້ຜິດພາດຄວາມປອດໄພ: CSRF Token ບໍ່ຖືກຕ້ອງ', 'error');
        redirect('/modules/categories/expense_edit.php?id=' . $id);
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ໝວດໝູ່';
    }

    if (empty($errors)) {
        try {
            // ກວດສອບວ່າມີຊື່ຊໍ້າບໍ່ (ຍົກເວັ້ນໂຕມັນເອງ)
            $stmt = $db->prepare("SELECT id FROM expense_categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetch()) {
                setFlashMessage('ມີໝວດໝູ່ນີ້ຢູ່ແລ້ວ', 'error');
                redirect('/modules/categories/expense_edit.php?id=' . $id);
            }

            $stmt = $db->prepare("UPDATE expense_categories SET name = ?, description = ? WHERE id = ?");
            
            if ($stmt->execute([$name, $description, $id])) {
                logActivity($_SESSION['user_id'], 'UPDATE', 'expense_categories', $id, "ແກ້ໄຂໝວດໝູ່ລາຍຈ່າຍ: {$name}");
                setFlashMessage('ແກ້ໄຂໝວດໝູ່ສຳເລັດ', 'success');
                redirect('/modules/categories/expense_list.php');
            }
        } catch (PDOException $e) {
            setFlashMessage('ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage(), 'error');
        }
    } else {
        foreach ($errors as $error) {
            setFlashMessage($error, 'error');
        }
    }
}

$pageTitle = 'ແກ້ໄຂໝວດໝູ່ລາຍຈ່າຍ';
require_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" class="text-gray-600 hover:text-gray-800 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            ແກ້ໄຂໝວດໝູ່ລາຍຈ່າຍ
                        </h1>
                        <p class="text-gray-600 mt-1">ແກ້ໄຂຂໍ້ມູນໝວດໝູ່: <?= htmlspecialchars($category['name']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ຟອມ -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                <!-- ຊື່ໝວດໝູ່ -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        ຊື່ໝວດໝູ່ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           placeholder="ເຊັ່ນ: ຄ່າໄຟຟ້າ, ຄ່ານ້ຳ, ຄ່າອາຫານ"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           value="<?= htmlspecialchars($_POST['name'] ?? $category['name']) ?>">
                </div>

                <!-- ລາຍລະອຽດ -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        ລາຍລະອຽດ
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              placeholder="ລາຍລະອຽດເພີ່ມເຕີມກ່ຽວກັບໝວດໝູ່ນີ້ (ຖ້າມີ)"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"><?= htmlspecialchars($_POST['description'] ?? $category['description'] ?? '') ?></textarea>
                </div>

                <!-- ສະຖິຕິການໃຊ້ງານ -->
                <?php
                $stmt = $db->prepare("
                    SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                    FROM expense WHERE category_id = ?
                ");
                $stmt->execute([$id]);
                $usage = $stmt->fetch();
                ?>
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <h3 class="text-orange-800 font-medium">ສະຖິຕິການໃຊ້ງານ</h3>
                            <ul class="mt-2 text-sm text-orange-700 space-y-1">
                                <li>• ຈຳນວນການໃຊ້: <strong><?= $usage['count'] ?></strong> ລາຍການ</li>
                                <li>• ຍອດລວມ: <strong><?= number_format($usage['total']) ?></strong> ກີບ</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ປຸ່ມ -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t">
                    <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        ຍົກເລີກ
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        ບັນທຶກການແກ້ໄຂ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
