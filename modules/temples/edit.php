<?php
/**
 * ລະບົບບັນຊີວັດ - ແກ້ໄຂຂໍ້ມູນວັດ (Super Admin Only)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// ກວດສອບສິດ Super Admin
if (!isSuperAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$db = getDB();
$errors = [];
$templeId = (int)($_GET['id'] ?? 0);

// ກວດສອບວ່າມີ ID ຫຼືບໍ່
if ($templeId <= 0) {
    setFlashMessage('ກະລຸນາລະບຸ ID ວັດ', 'error');
    redirect('/modules/temples/index.php');
}

// ດຶງຂໍ້ມູນວັດ - ໃຊ້ $templeData ເພື່ອບໍ່ໃຫ້ຊ້ຳກັບ $temple ໃນ header.php
$templeData = getTempleById($templeId);

if (!$templeData) {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນວັດ', 'error');
    redirect('/modules/temples/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ກວດສອບ CSRF Token
    if (!checkCSRF()) {
        setFlashMessage('ຂໍ້ຜິດພາດຄວາມປອດໄພ: CSRF Token ບໍ່ຖືກຕ້ອງ. ກະລຸນາລອງໃໝ່.', 'error');
        header('Location: ' . BASE_URL . '/modules/temples/edit.php?id=' . $templeId);
        exit();
    }

    // ຮັບຂໍ້ມູນຈາກ Form
    $templeName = trim($_POST['temple_name'] ?? '');
    $templeNameLao = trim($_POST['temple_name_lao'] ?? '');
    $abbotName = trim($_POST['abbot_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($templeNameLao)) {
        $errors[] = 'ກະລຸນາໃສ່ຊື່ວັດພາສາລາວ';
    }
    
    // ຖ້າບໍ່ມີຂໍ້ຜິດພາດ ໃຫ້ອັບເດດ
    if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    UPDATE temples SET 
                        temple_name = ?,
                        temple_name_lao = ?,
                        abbot_name = ?,
                        address = ?,
                        district = ?,
                        province = ?,
                        phone = ?,
                        email = ?,
                        status = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $templeName,
                    $templeNameLao,
                    $abbotName,
                    $address,
                    $district,
                    $province,
                    $phone,
                    $email,
                    $status,
                    $templeId
                ]);
                
                // Log audit
                logAudit('temples', $templeId, 'update', "ອັບເດດຂໍ້ມູນວັດ: {$templeNameLao}");
                
                setFlashMessage("ອັບເດດຂໍ້ມູນວັດ '{$templeNameLao}' ສຳເລັດ", 'success');
                redirect('/modules/temples/index.php');
                
            } catch (Exception $e) {
                $errors[] = 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
            }
        }
}

$pageTitle = "ແກ້ໄຂຂໍ້ມູນວັດ";
require_once __DIR__ . '/../../includes/header.php';

// Debug Information (can be enabled with ?debug=1)
if ($debugMode) {
    echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 px-4 py-3 rounded mb-4">';
    echo '<strong>🔍 Debug Mode Enabled</strong><br>';
    echo 'Temple ID: ' . $templeId . '<br>';
    echo 'Temple Found: ✅ Yes<br>';
    echo 'Temple Code: ' . ($templeData['temple_code'] ?? 'N/A') . '<br>';
    echo 'Temple Name Lao: ' . ($templeData['temple_name_lao'] ?? 'N/A') . '<br>';
    echo '<details><summary>Full Data (click to expand)</summary><pre style="max-height: 300px; overflow: auto;">' . print_r($templeData, true) . '</pre></details>';
    echo '</div>';
}
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ແກ້ໄຂຂໍ້ມູນວັດ</h1>
            <p class="text-gray-600">ລະຫັດວັດ: <strong><?php echo e($templeData['temple_code']); ?></strong></p>
        </div>
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            ກັບຄືນ
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">ພົບຂໍ້ຜິດພາດ:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Form -->
<form method="POST" class="space-y-6">
    <?php echo csrfField(); ?>

    <!-- ຂໍ້ມູນວັດ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">
            <span class="mr-2">🏛️</span> ຂໍ້ມູນວັດ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- ລະຫັດວັດ (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ລະຫັດວັດ
                </label>
                <input type="text" value="<?php echo e($templeData['temple_code']); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                       readonly>
                <p class="text-xs text-gray-500 mt-1">ບໍ່ສາມາດແກ້ໄຂລະຫັດວັດໄດ້</p>
            </div>
            
            <!-- ສະຖານະ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ສະຖານະ
                </label>
                <select name="status" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="active" <?php echo $templeData['status'] === 'active' ? 'selected' : ''; ?>>ເປີດໃຊ້ງານ</option>
                    <option value="inactive" <?php echo $templeData['status'] === 'inactive' ? 'selected' : ''; ?>>ປິດໃຊ້ງານ</option>
                </select>
            </div>
            
            <!-- ຊື່ວັດ (English) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ວັດ (ອັງກິດ)
                </label>
                <input type="text" name="temple_name" 
                       value="<?php echo e(isset($_POST['temple_name']) ? $_POST['temple_name'] : ($templeData['temple_name'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Wat Pa Nongboua Tongtai">
            </div>
            
            <!-- ຊື່ວັດ (ລາວ) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ວັດ (ລາວ) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="temple_name_lao" 
                       value="<?php echo e(isset($_POST['temple_name_lao']) ? $_POST['temple_name_lao'] : ($templeData['temple_name_lao'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ວັດປ່າໜອງບົວທອງໃຕ້" required>
            </div>
            
            <!-- ຊື່ເຈົ້າອະທິການ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ເຈົ້າອະທິການ
                </label>
                <input type="text" name="abbot_name" 
                       value="<?php echo e(isset($_POST['abbot_name']) ? $_POST['abbot_name'] : ($templeData['abbot_name'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ພະອາຈານ...">
            </div>
            
            <!-- ເບີໂທ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ເບີໂທ
                </label>
                <input type="text" name="phone" 
                       value="<?php echo e(isset($_POST['phone']) ? $_POST['phone'] : ($templeData['phone'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="020 1234 5678">
            </div>
            
            <!-- ອີເມລ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ອີເມລ
                </label>
                <input type="email" name="email" 
                       value="<?php echo e(isset($_POST['email']) ? $_POST['email'] : ($templeData['email'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="temple@example.com">
            </div>
            
            <!-- ເມືອງ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ເມືອງ
                </label>
                <input type="text" name="district" 
                       value="<?php echo e(isset($_POST['district']) ? $_POST['district'] : ($templeData['district'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ໄຊເສດຖາ">
            </div>
            
            <!-- ແຂວງ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ແຂວງ
                </label>
                <input type="text" name="province" 
                       value="<?php echo e(isset($_POST['province']) ? $_POST['province'] : ($templeData['province'] ?? '')); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ວຽງຈັນ">
            </div>
            
            <!-- ທີ່ຢູ່ -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ທີ່ຢູ່ເຕັມ
                </label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="ບ້ານ..., ເມືອງ..., ແຂວງ..."><?php echo e(isset($_POST['address']) ? $_POST['address'] : ($templeData['address'] ?? '')); ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- ປຸ່ມ Submit -->
    <div class="flex justify-between">
        <a href="view.php?id=<?php echo $templeId; ?>" 
           class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition duration-200">
            👁️ ເບິ່ງລາຍລະອຽດ
        </a>
        <div class="space-x-4">
            <a href="index.php" 
               class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition duration-200">
                ຍົກເລີກ
            </a>
            <button type="submit" 
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition duration-200 flex items-center inline-flex">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                ບັນທຶກການແກ້ໄຂ
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
