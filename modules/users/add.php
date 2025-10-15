<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ເພີ່ມຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireAdmin();

$db = getDB();

// ກວດສອບສິດຂອງຜູ້ໃຊ້ປັດຈຸບັນ
$currentUser = $_SESSION;
$isSuperAdmin = ($currentUser['is_super_admin'] ?? 0) == 1;
$currentTempleId = getCurrentTempleId();
$isMultiTemple = isMultiTempleEnabled();

// ດຶງລາຍການວັດ (ສຳລັບ super admin)
$temples = [];
if ($isSuperAdmin && $isMultiTemple) {
    $stmt = $db->query("SELECT id, temple_name, temple_name_lao FROM temples ORDER BY temple_name");
    $temples = $stmt->fetchAll();
}

// ປະມວນຜົນ POST ກ່ອນໂຫຼດ HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $templeId = $_POST['temple_id'] ?? null;
    $isSuperAdminUser = isset($_POST['is_super_admin']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້';
    } elseif (strlen($username) < 3) {
        $errors[] = 'ຊື່ຜູ້ໃຊ້ຕ້ອງມີຢ່າງໜ້ອຍ 3 ຕົວອັກສອນ';
    } else {
        // ກວດສອບວ່າຊື່ຜູ້ໃຊ້ຊ້ຳຫຼືບໍ່
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = 'ຊື່ຜູ້ໃຊ້ນີ້ມີໃນລະບົບແລ້ວ';
        }
    }
    
    if (empty($fullName)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ເຕັມ';
    }
    
    if (empty($password)) {
        $errors[] = 'ກະລຸນາປ້ອນລະຫັດຜ່ານ';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ ' . PASSWORD_MIN_LENGTH . ' ຕົວອັກສອນ';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'ລະຫັດຜ່ານບໍ່ຕົງກັນ';
    }
    
    if (!in_array($role, ['admin', 'user'])) {
        $errors[] = 'ສິດຜູ້ໃຊ້ບໍ່ຖືກຕ້ອງ';
    }
    
    // ກວດສອບວັດ
    if ($isMultiTemple) {
        if ($isSuperAdminUser) {
            // Super admin user ບໍ່ຕ້ອງມີ temple_id (ເຂົ້າເຖິງທຸກວັດໄດ້)
            $templeId = null;
        } elseif ($isSuperAdmin) {
            // Super Admin ສ້າງ admin/user ທົ່ວໄປຕ້ອງເລືອກວັດ
            if (empty($templeId)) {
                $errors[] = 'ກະລຸນາເລືອກວັດ';
            }
        } else {
            // Admin ທົ່ວໄປສ້າງ user ໃນວັດຂອງຕົນເອງເທົ່ານັ້ນ
            $templeId = $currentTempleId;
        }
    }
    
    // Super admin ສ້າງໄດ້ສະເພາະ super admin ເທົ່ານັ້ນ
    if ($isSuperAdminUser && !$isSuperAdmin) {
        $errors[] = 'ທ່ານບໍ່ມີສິດສ້າງ Super Admin';
    }
    
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (temple_id, username, password, full_name, role, is_super_admin) 
                    VALUES (:temple_id, :username, :password, :full_name, :role, :is_super_admin)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':temple_id' => $templeId,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':full_name' => $fullName,
                ':role' => $role,
                ':is_super_admin' => $isSuperAdminUser
            ]);
            
            $userId = $db->lastInsertId();
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'INSERT', 'users', $userId, null, [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role,
                'temple_id' => $templeId,
                'is_super_admin' => $isSuperAdminUser
            ]);
            
            setFlashMessage('ເພີ່ມຜູ້ໃຊ້ສຳເລັດແລ້ວ ✓', 'success');
            header('Location: ' . BASE_URL . '/modules/users/list.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage();
        }
    }
}

// ໂຫຼດ header ຫຼັງຈາກປະມວນຜົນ POST ແລ້ວ
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ເພີ່ມຜູ້ໃຊ້</h1>
                <p class="text-gray-600">ສ້າງບັນຊີຜູ້ໃຊ້ໃໝ່</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
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
            
            <div class="mb-6">
                <label for="username" class="block text-gray-700 font-medium mb-2">
                    ຊື່ຜູ້ໃຊ້ <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required
                       value="<?php echo e($_POST['username'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ປ້ອນຊື່ຜູ້ໃຊ້">
                <p class="text-sm text-gray-500 mt-1">ຊື່ຜູ້ໃຊ້ສຳລັບເຂົ້າສູ່ລະບົບ (ຕ້ອງມີຢ່າງໜ້ອຍ 3 ຕົວອັກສອນ)</p>
            </div>
            
            <div class="mb-6">
                <label for="full_name" class="block text-gray-700 font-medium mb-2">
                    ຊື່ເຕັມ <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       required
                       value="<?php echo e($_POST['full_name'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ປ້ອນຊື່ເຕັມ">
            </div>
            
            <?php if ($isSuperAdmin && $isMultiTemple && !empty($temples)): ?>
            <div class="mb-6">
                <label for="temple_id" class="block text-gray-700 font-medium mb-2">
                    ວັດ <span class="text-red-500" id="temple_required">*</span>
                </label>
                <select id="temple_id" 
                        name="temple_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- ເລືອກວັດ --</option>
                    <?php foreach ($temples as $temple): ?>
                        <option value="<?php echo $temple['id']; ?>" 
                                <?php echo (isset($_POST['temple_id']) && $_POST['temple_id'] == $temple['id']) ? 'selected' : ''; ?>>
                            <?php echo e($temple['temple_name_lao'] ?: $temple['temple_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-1">ເລືອກວັດທີ່ຜູ້ໃຊ້ຈະສັງກັດ (ຍົກເວັ້ນ Super Admin)</p>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">
                        ລະຫັດຜ່ານ <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="ປ້ອນລະຫັດຜ່ານ">
                    <p class="text-sm text-gray-500 mt-1">ຢ່າງໜ້ອຍ <?php echo PASSWORD_MIN_LENGTH; ?> ຕົວອັກສອນ</p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-gray-700 font-medium mb-2">
                        ຢືນຢັນລະຫັດຜ່ານ <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="ຢືນຢັນລະຫັດຜ່ານ">
                </div>
                
            </div>
            
            <div class="mb-6">
                <label for="role" class="block text-gray-700 font-medium mb-2">
                    ສິດຜູ້ໃຊ້ <span class="text-red-500">*</span>
                </label>
                <select id="role" 
                        name="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>
                        ຜູ້ໃຊ້ທົ່ວໄປ
                    </option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>
                        ແອດມິນ
                    </option>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    <strong>ຜູ້ໃຊ້ທົ່ວໄປ:</strong> ສາມາດບັນທຶກລາຍຮັບ-ລາຍຈ່າຍ ແລະ ເບິ່ງລາຍງານ<br>
                    <strong>ແອດມິນ:</strong> ມີສິດເຂົ້າເຖິງທຸກໜ້າທີ່ລວມທັງການຈັດການຜູ້ໃຊ້
                </p>
            </div>
            
            <?php if ($isSuperAdmin): ?>
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_super_admin" 
                           name="is_super_admin" 
                           value="1"
                           <?php echo (isset($_POST['is_super_admin'])) ? 'checked' : ''; ?>
                           class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                    <label for="is_super_admin" class="ml-2 text-sm font-medium text-gray-900">
                        Super Admin (ເຂົ້າເຖິງທຸກວັດ)
                    </label>
                </div>
                <p class="text-sm text-gray-500 mt-2 ml-6">
                    <strong>Super Admin:</strong> ສາມາດເຂົ້າເຖິງຂໍ້ມູນທຸກວັດ ແລະ ຈັດການລະບົບທັງໝົດ. 
                    ຖ້າເລືອກນີ້ຈະບໍ່ຕ້ອງເລືອກວັດ.
                </p>
            </div>
            <?php endif; ?>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
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

<?php if ($isSuperAdmin): ?>
<script>
// ຈັດການການສະແດງຟິວວັດຕາມການເລືອກ Super Admin
document.addEventListener('DOMContentLoaded', function() {
    const superAdminCheckbox = document.getElementById('is_super_admin');
    const templeField = document.getElementById('temple_id');
    const templeRequired = document.getElementById('temple_required');
    
    function updateTempleField() {
        if (superAdminCheckbox && superAdminCheckbox.checked) {
            // Super Admin ບໍ່ຕ້ອງການວັດ
            if (templeField) {
                templeField.value = '';
                templeField.disabled = true;
                templeField.required = false;
                templeField.parentElement.classList.add('opacity-50');
            }
            if (templeRequired) {
                templeRequired.style.display = 'none';
            }
        } else {
            // ຕ້ອງເລືອກວັດ (ສຳລັບ admin/user ທົ່ວໄປ)
            if (templeField) {
                templeField.disabled = false;
                templeField.required = true;
                templeField.parentElement.classList.remove('opacity-50');
            }
            if (templeRequired) {
                templeRequired.style.display = 'inline';
            }
        }
    }
    
    // ເອີ້ນໃນເວລາໂຫຼດ
    updateTempleField();
    
    // ເອີ້ນເມື່ອມີການປ່ຽນແປງ
    if (superAdminCheckbox) {
        superAdminCheckbox.addEventListener('change', updateTempleField);
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
