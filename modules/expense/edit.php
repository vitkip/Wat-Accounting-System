<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ແກ້ໄຂລາຍຈ່າຍ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ກວດສອບວ່າລະບົບ multi-temple ເປີດໃຊ້ຫຼືບໍ່
$isMultiTemple = function_exists('isMultiTempleEnabled') && isMultiTempleEnabled();
$currentTempleId = null;
if ($isMultiTemple && function_exists('getCurrentTempleId')) {
    $currentTempleId = getCurrentTempleId();
}

// ດຶງຂໍ້ມູນ
$stmt = $db->prepare("SELECT * FROM expense WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$record = $stmt->fetch();

if (!$record) {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນທີ່ຕ້ອງການ', 'error');
    header('Location: ' . BASE_URL . '/modules/expense/list.php');
    exit();
}

// ກວດສອບສິດທິການແກ້ໄຂ
$canEdit = false;

// ຕ້ອງກວດສອບວ່າລະບົບ multi-temple ເປີດໃຊ້ຫຼືບໍ່
if ($isMultiTemple && $currentTempleId) {
    // ກວດສອບວ່າລາຍການນີ້ເປັນຂອງວັດນີ້ບໍ່
    if (isset($record['temple_id']) && $record['temple_id'] == $currentTempleId) {
        $canEdit = true;
    }
} else {
    // ລະບົບແບບເດີມ - Admin ສາມາດແກ້ໄຂໄດ້ທຸກຢ່າງ, User ແກ້ໄຂໄດ້ແຕ່ຂອງຕົນເອງ
    if (isAdmin()) {
        $canEdit = true;
    } elseif ($record['created_by'] == $_SESSION['user_id']) {
        $canEdit = true;
    }
}

if (!$canEdit) {
    setFlashMessage('ທ່ານບໍ່ມີສິດແກ້ໄຂຂໍ້ມູນນີ້', 'error');
    header('Location: ' . BASE_URL . '/modules/expense/list.php');
    exit();
}

// ປະມວນຜົນ POST ກ່ອນໂຫຼດ HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $date = $_POST['date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    // ຮັບຄ່າຈາກ hidden input ທີ່ເປັນຕົວເລກທຳມະດາ
    $amount = $_POST['amount'] ?? '0';
    $category = trim($_POST['category'] ?? 'ທົ່ວໄປ');
    
    $errors = [];
    
    if (empty($date)) {
        $errors[] = 'ກະລຸນາເລືອກວັນທີ່';
    }
    
    if (empty($description)) {
        $errors[] = 'ກະລຸນາປ້ອນລາຍລະອຽດ';
    }
    
    if (!is_numeric($amount) || $amount <= 0) {
        $errors[] = 'ຈຳນວນເງິນບໍ່ຖືກຕ້ອງ';
    }
    
    if (empty($errors)) {
        try {
            $sql = "UPDATE expense 
                    SET date = :date, description = :description, amount = :amount, category = :category
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':date' => $date,
                ':description' => $description,
                ':amount' => $amount,
                ':category' => $category,
                ':id' => $id
            ]);
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'UPDATE', 'expense', $id, $record, [
                'date' => $date,
                'description' => $description,
                'amount' => $amount,
                'category' => $category
            ]);
            
            setFlashMessage('ແກ້ໄຂລາຍຈ່າຍສຳເລັດແລ້ວ ✓', 'success');
            header('Location: ' . BASE_URL . '/modules/expense/list.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການແກ້ໄຂຂໍ້ມູນ: ' . $e->getMessage();
        }
    }
}

// ໂຫຼດ header ຫຼັງຈາກປະມວນຜົນ POST ແລ້ວ
require_once __DIR__ . '/../../includes/header.php';

// ດຶງໝວດໝູ່
$stmt = $db->query("SELECT * FROM expense_categories ORDER BY name");
$categories = $stmt->fetchAll();

?>

<div class="max-w-3xl mx-auto">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ແກ້ໄຂລາຍຈ່າຍ</h1>
                <p class="text-gray-600">ແກ້ໄຂຂໍ້ມູນລາຍຈ່າຍ</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                ກັບຄືນ
            </a>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
        <ul class="list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <!-- ວັນທີ່ -->
                <div>
                    <label for="date" class="block text-gray-700 font-medium mb-2">
                        ວັນທີ່ <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           required
                           value="<?php echo e($_POST['date'] ?? $record['date']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
                
                <!-- ຈຳນວນເງິນ -->
                <div>
                    <label for="amount" class="block text-gray-700 font-medium mb-2">
                        ຈຳນວນເງິນ (ກີບ) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           required
                           min="0"
                           step="1"
                           value="<?php echo e($_POST['amount'] ?? $record['amount']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="0">
                </div>
                
            </div>
            
            <!-- ໝວດໝູ່ -->
            <div class="mb-6">
                <label for="category" class="block text-gray-700 font-medium mb-2">
                    ໝວດໝູ່
                </label>
                <select id="category" 
                        name="category"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="ທົ່ວໄປ" <?php echo ($record['category'] === 'ທົ່ວໄປ') ? 'selected' : ''; ?>>ທົ່ວໄປ</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo e($cat['name']); ?>" 
                                <?php echo (isset($_POST['category']) ? $_POST['category'] === $cat['name'] : $record['category'] === $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- ລາຍລະອຽດ -->
            <div class="mb-6">
                <label for="description" class="block text-gray-700 font-medium mb-2">
                    ລາຍລະອຽດ <span class="text-red-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          required
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="ລາຍລະອຽດລາຍຈ່າຍ..."><?php echo e($_POST['description'] ?? $record['description']); ?></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    ຍົກເລີກ
                </a>
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200">
                    ບັນທຶກການແກ້ໄຂ
                </button>
            </div>
            
        </form>
    </div>
    
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
