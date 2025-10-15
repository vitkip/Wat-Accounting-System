<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ເພີ່ມລາຍຈ່າຍ
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

// ຖ້າເປັນ POST request, ປະມວນຜົນກ່ອນໂຫຼດ HTML
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
            // ຖ້າລະບົບ multi-temple ເປີດໃຊ້ ແລະ ມີ temple_id
            if ($currentTempleId) {
                $sql = "INSERT INTO expense (temple_id, date, description, amount, category, created_by) 
                        VALUES (:temple_id, :date, :description, :amount, :category, :created_by)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':temple_id' => $currentTempleId,
                    ':date' => $date,
                    ':description' => $description,
                    ':amount' => $amount,
                    ':category' => $category,
                    ':created_by' => $_SESSION['user_id']
                ]);
            } else {
                // ລະບົບແບບເດີມ (ບໍ່ມີ temple_id)
                $sql = "INSERT INTO expense (date, description, amount, category, created_by) 
                        VALUES (:date, :description, :amount, :category, :created_by)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':date' => $date,
                    ':description' => $description,
                    ':amount' => $amount,
                    ':category' => $category,
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            
            $expenseId = $db->lastInsertId();
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'INSERT', 'expense', $expenseId, null, [
                'date' => $date,
                'description' => $description,
                'amount' => $amount,
                'category' => $category
            ]);
            
            setFlashMessage('ເພີ່ມລາຍຈ່າຍສຳເລັດແລ້ວ ✓', 'success');
            header('Location: ' . BASE_URL . '/modules/expense/list.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage();
        }
    }
}

// ໂຫຼດ header ຫຼັງຈາກປະມວນຜົນ POST ແລ້ວ
require_once __DIR__ . '/../../includes/header.php';

// ດຶງໝວດໝູ່ລາຍຈ່າຍ (ຂອງວັດນີ້ເທົ່ານັ້ນ ຖ້າລະບົບ multi-temple ເປີດໃຊ້)
if ($currentTempleId) {
    $stmt = $db->prepare("SELECT * FROM expense_categories WHERE temple_id = ? ORDER BY name");
    $stmt->execute([$currentTempleId]);
} else {
    $stmt = $db->query("SELECT * FROM expense_categories ORDER BY name");
}
$categories = $stmt->fetchAll();

?>

<style>
@media (max-width: 768px) {
    .form-container {
        padding: 1rem !important;
    }
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    .page-header > div:first-child {
        width: 100%;
    }
    .page-header a {
        width: 100%;
        text-align: center;
    }
    .form-card {
        padding: 1.5rem !important;
        border-radius: 1rem !important;
    }
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    .button-group {
        flex-direction: column;
        gap: 0.75rem;
    }
    .button-group a,
    .button-group button {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
    h1 {
        font-size: 1.75rem !important;
    }
    .form-label {
        font-size: 0.95rem;
    }
    .form-input {
        font-size: 1rem;
        padding: 0.75rem !important;
    }
}
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8 page-header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="w-full sm:w-auto">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">ເພີ່ມລາຍຈ່າຍ</h1>
                <p class="text-sm sm:text-base text-gray-600">ບັນທຶກລາຍຈ່າຍເຂົ້າລະບົບ</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
               class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center">
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
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-md p-4 sm:p-8 form-card">
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6 form-grid">
                
                <!-- ວັນທີ່ -->
                <div>
                    <label for="date" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base form-label">
                        ວັນທີ່ <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           required
                           value="<?php echo e($_POST['date'] ?? date('Y-m-d')); ?>"
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent form-input text-base">
                </div>
                
                <!-- ຈຳນວນເງິນ -->
                <div>
                    <label for="amount" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base form-label">
                        ຈຳນວນເງິນ (ກີບ) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="amount_display" 
                           required
                           value="<?php echo e($_POST['amount'] ?? ''); ?>"
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent form-input text-base"
                           placeholder="0"
                           onkeyup="formatNumber(this)">
                    <input type="hidden" id="amount" name="amount">
                </div>
                
            </div>
            
            <!-- ໝວດໝູ່ -->
            <div class="mb-4 sm:mb-6">
                <label for="category" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base form-label">
                    ໝວດໝູ່
                </label>
                <select id="category" 
                        name="category"
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent form-input text-base">
                    <option value="ທົ່ວໄປ">ທົ່ວໄປ</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo e($cat['name']); ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- ລາຍລະອຽດ -->
            <div class="mb-4 sm:mb-6">
                <label for="description" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base form-label">
                    ລາຍລະອຽດ <span class="text-red-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          required
                          rows="4"
                          class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent form-input text-base"
                          placeholder="ລາຍລະອຽດລາຍຈ່າຍ..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 button-group">
                <a href="<?php echo BASE_URL; ?>/modules/expense/list.php" 
                   class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 text-center">
                    ຍົກເລີກ
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200">
                    ບັນທຶກ
                </button>
            </div>
            
        </form>
    </div>
    
</div>

<script>
function formatNumber(input) {
    // ລຶບທຸກຕົວທີ່ບໍ່ແມ່ນຕົວເລກ
    let value = input.value.replace(/[^\d]/g, '');
    
    // ຖ້າບໍ່ມີຄ່າ ໃຫ້ເປັນ 0
    if (value === '') {
        input.value = '';
        document.getElementById('amount').value = '';
        return;
    }
    
    // ແປງເປັນຕົວເລກແລ້ວຈັດຮູບແບບດ້ວຍຈຸດ
    let number = parseInt(value);
    // ໃຊ້ locale ເຢຍລະມັນ (de-DE) ເພື່ອໃຫ້ໄດ້ຈຸດແທນຈຳ
    input.value = number.toLocaleString('de-DE');
    
    // ເກັບຄ່າຕົວເລກທຳມະດາໄວ້ໃນ hidden input
    document.getElementById('amount').value = value;
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
