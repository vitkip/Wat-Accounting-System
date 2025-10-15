<?php
/**
 * ລະບົບບັນຊີວັດ - ເພີ່ມວັດໃໝ່ (Super Admin Only)
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

// ສ້າງລະຫັດວັດແນະນຳ
$suggestedCode = generateTempleCode();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ຮັບຂໍ້ມູນຈາກ Form
    $templeCode = trim($_POST['temple_code'] ?? '');
    $templeName = trim($_POST['temple_name'] ?? '');
    $templeNameLao = trim($_POST['temple_name_lao'] ?? '');
    $abbotName = trim($_POST['abbot_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Admin User Info
    $adminUsername = trim($_POST['admin_username'] ?? '');
    $adminPassword = $_POST['admin_password'] ?? '';
    $adminFullName = trim($_POST['admin_full_name'] ?? '');
    
    // Validation
    if (empty($templeCode)) {
        $errors[] = 'ກະລຸນາໃສ່ລະຫັດວັດ';
    }
    if (empty($templeNameLao)) {
        $errors[] = 'ກະລຸນາໃສ່ຊື່ວັດພາສາລາວ';
    }
    if (empty($adminUsername)) {
        $errors[] = 'ກະລຸນາໃສ່ username ສຳລັບ Admin';
    }
    if (empty($adminPassword)) {
        $errors[] = 'ກະລຸນາໃສ່ລະຫັດຜ່ານສຳລັບ Admin';
    }
    if (empty($adminFullName)) {
        $errors[] = 'ກະລຸນາໃສ່ຊື່ເຕັມຂອງ Admin';
    }
    
    // ກວດສອບລະຫັດວັດຊ້ຳ
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM temples WHERE temple_code = ?");
        $stmt->execute([$templeCode]);
        if ($stmt->fetch()) {
            $errors[] = "ລະຫັດວັດ '{$templeCode}' ມີໃນລະບົບແລ້ວ";
        }
    }
    
    // ກວດສອບ username ຊ້ຳ
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$adminUsername]);
        if ($stmt->fetch()) {
            $errors[] = "Username '{$adminUsername}' ມີໃນລະບົບແລ້ວ";
        }
    }
    
    // ຖ້າບໍ່ມີຂໍ້ຜິດພາດ ໃຫ້ບັນທຶກ
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // ສ້າງວັດ
            $stmt = $db->prepare("
                INSERT INTO temples (temple_code, temple_name, temple_name_lao, abbot_name, 
                                    address, district, province, phone, email, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $templeCode, 
                $templeName, 
                $templeNameLao, 
                $abbotName, 
                $address, 
                $district, 
                $province, 
                $phone, 
                $email, 
                $status
            ]);
            
            $templeId = $db->lastInsertId();
            
            // ສ້າງ Admin User ສຳລັບວັດ
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (temple_id, username, password, full_name, role, is_super_admin) 
                VALUES (?, ?, ?, ?, 'admin', FALSE)
            ");
            $stmt->execute([$templeId, $adminUsername, $passwordHash, $adminFullName]);
            
            // Copy Global Categories ມາໃຫ້ວັດໃໝ່
            // Income Categories
            $stmt = $db->query("SELECT name, description FROM income_categories WHERE temple_id IS NULL");
            $globalIncomeCategories = $stmt->fetchAll();
            
            $insertIncome = $db->prepare("INSERT IGNORE INTO income_categories (temple_id, name, description) VALUES (?, ?, ?)");
            foreach ($globalIncomeCategories as $cat) {
                $insertIncome->execute([$templeId, $cat['name'], $cat['description']]);
            }
            
            // Expense Categories
            $stmt = $db->query("SELECT name, description FROM expense_categories WHERE temple_id IS NULL");
            $globalExpenseCategories = $stmt->fetchAll();
            
            $insertExpense = $db->prepare("INSERT IGNORE INTO expense_categories (temple_id, name, description) VALUES (?, ?, ?)");
            foreach ($globalExpenseCategories as $cat) {
                $insertExpense->execute([$templeId, $cat['name'], $cat['description']]);
            }
            
            // ສ້າງ Settings ເລີ່ມຕົ້ນ
            $defaultSettings = [
                ['setting_key' => 'fiscal_year_start', 'setting_value' => '01-01'],
                ['setting_key' => 'currency_symbol', 'setting_value' => 'ກີບ'],
                ['setting_key' => 'date_format', 'setting_value' => 'd/m/Y'],
                ['setting_key' => 'timezone', 'setting_value' => 'Asia/Vientiane']
            ];
            
            $stmt = $db->prepare("INSERT INTO temple_settings (temple_id, setting_key, setting_value) VALUES (?, ?, ?)");
            foreach ($defaultSettings as $setting) {
                $stmt->execute([$templeId, $setting['setting_key'], $setting['setting_value']]);
            }
            
            $db->commit();
            
            // Log audit
            logAudit('temples', $templeId, 'create', "ສ້າງວັດໃໝ່: {$templeNameLao} ({$templeCode})");
            
            setFlashMessage("ສ້າງວັດ '{$templeNameLao}' ສຳເລັດ! Admin: {$adminUsername}", 'success');
            redirect('/modules/temples/index.php');
            
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
        }
    }
}

$pageTitle = "ເພີ່ມວັດໃໝ່";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ເພີ່ມວັດໃໝ່</h1>
            <p class="text-gray-600">ສ້າງວັດໃໝ່ພ້ອມບັນຊີ Admin</p>
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
    
    <!-- ຂໍ້ມູນວັດ -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">
            <span class="mr-2">🏛️</span> ຂໍ້ມູນວັດ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- ລະຫັດວັດ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ລະຫັດວັດ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="temple_code" 
                       value="<?php echo e($_POST['temple_code'] ?? $suggestedCode); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="WAT001" required>
                <p class="text-xs text-gray-500 mt-1">ແນະນຳ: <?php echo $suggestedCode; ?></p>
            </div>
            
            <!-- ສະຖານະ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ສະຖານະ
                </label>
                <select name="status" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="active">ເປີດໃຊ້ງານ</option>
                    <option value="inactive">ປິດໃຊ້ງານ</option>
                </select>
            </div>
            
            <!-- ຊື່ວັດ (English) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ວັດ (ອັງກິດ)
                </label>
                <input type="text" name="temple_name" 
                       value="<?php echo e($_POST['temple_name'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Wat Pa Nongboua Tongtai">
            </div>
            
            <!-- ຊື່ວັດ (ລາວ) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ວັດ (ລາວ) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="temple_name_lao" 
                       value="<?php echo e($_POST['temple_name_lao'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ວັດປ່າໜອງບົວທອງໃຕ້" required>
            </div>
            
            <!-- ຊື່ເຈົ້າອະທິການ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ເຈົ້າອະທິການ
                </label>
                <input type="text" name="abbot_name" 
                       value="<?php echo e($_POST['abbot_name'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ພະອາຈານ...">
            </div>
            
            <!-- ເບີໂທ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ເບີໂທ
                </label>
                <input type="text" name="phone" 
                       value="<?php echo e($_POST['phone'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="020 1234 5678">
            </div>
            
            <!-- ອີເມລ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ອີເມລ
                </label>
                <input type="email" name="email" 
                       value="<?php echo e($_POST['email'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="temple@example.com">
            </div>
            
            <!-- ເມືອງ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ເມືອງ
                </label>
                <input type="text" name="district" 
                       value="<?php echo e($_POST['district'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ໄຊເສດຖາ">
            </div>
            
            <!-- ແຂວງ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ແຂວງ
                </label>
                <input type="text" name="province" 
                       value="<?php echo e($_POST['province'] ?? ''); ?>"
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
                          placeholder="ບ້ານ..., ເມືອງ..., ແຂວງ..."><?php echo e($_POST['address'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- ຂໍ້ມູນ Admin -->
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">
            <span class="mr-2">👤</span> ຂໍ້ມູນ Admin ຂອງວັດ
        </h2>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <p class="text-sm text-blue-800">
                <strong>ໝາຍເຫດ:</strong> ລະບົບຈະສ້າງບັນຊີ Admin ສຳລັບວັດນີ້ອັດຕະໂນມັດ
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" name="admin_username" 
                       value="<?php echo e($_POST['admin_username'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="admin_wat001" required>
            </div>
            
            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ລະຫັດຜ່ານ <span class="text-red-500">*</span>
                </label>
                <input type="password" name="admin_password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="••••••••" required>
            </div>
            
            <!-- ຊື່ເຕັມ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ເຕັມ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="admin_full_name" 
                       value="<?php echo e($_POST['admin_full_name'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ນາຍ..." required>
            </div>
        </div>
    </div>
    
    <!-- ປຸ່ມ Submit -->
    <div class="flex justify-end space-x-4">
        <a href="index.php" 
           class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition duration-200">
            ຍົກເລີກ
        </a>
        <button type="submit" 
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            ບັນທຶກວັດໃໝ່
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
