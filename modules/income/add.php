<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ເພີ່ມລາຍຮັບ
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

$db = getDB();

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
            $sql = "INSERT INTO income (date, description, amount, category, created_by) 
                    VALUES (:date, :description, :amount, :category, :created_by)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':date' => $date,
                ':description' => $description,
                ':amount' => $amount,
                ':category' => $category,
                ':created_by' => $_SESSION['user_id']
            ]);
            
            $incomeId = $db->lastInsertId();
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'INSERT', 'income', $incomeId, null, [
                'date' => $date,
                'description' => $description,
                'amount' => $amount,
                'category' => $category
            ]);
            
            setFlashMessage('ເພີ່ມລາຍຮັບສຳເລັດແລ້ວ ✓', 'success');
            header('Location: ' . BASE_URL . '/modules/income/list.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage();
        }
    }
}

// ໂຫຼດ header ຫຼັງຈາກປະມວນຜົນ POST ແລ້ວ
require_once __DIR__ . '/../../includes/header.php';

// ດຶງໝວດໝູ່ລາຍຮັບ
$stmt = $db->query("SELECT * FROM income_categories ORDER BY name");
$categories = $stmt->fetchAll();

?>

<div class="max-w-3xl mx-auto">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ເພີ່ມລາຍຮັບ</h1>
                <p class="text-gray-600">ບັນທຶກລາຍຮັບເຂົ້າລະບົບ</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
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
                           value="<?php echo e($_POST['date'] ?? date('Y-m-d')); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <!-- ຈຳນວນເງິນ -->
                <div>
                    <label for="amount" class="block text-gray-700 font-medium mb-2">
                        ຈຳນວນເງິນ (ກີບ) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="amount_display" 
                           required
                           value="<?php echo e($_POST['amount'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="0"
                           onkeyup="formatNumber(this)">
                    <input type="hidden" id="amount" name="amount">
                </div>
                
            </div>
            
            <!-- ໝວດໝູ່ -->
            <div class="mb-6">
                <label for="category" class="block text-gray-700 font-medium mb-2">
                    ໝວດໝູ່
                </label>
                <select id="category" 
                        name="category"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="ທົ່ວໄປ">ທົ່ວໄປ</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo e($cat['name']); ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
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
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="ລາຍລະອຽດລາຍຮັບ..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/modules/income/list.php" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    ຍົກເລີກ
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200">
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
