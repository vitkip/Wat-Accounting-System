<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ເພີ່ມຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';

requireAdmin();

$db = getDB();

// ປະມວນຜົນ POST ກ່ອນໂຫຼດ HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    
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
    
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password, full_name, role) 
                    VALUES (:username, :password, :full_name, :role)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':full_name' => $fullName,
                ':role' => $role
            ]);
            
            $userId = $db->lastInsertId();
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'INSERT', 'users', $userId, null, [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
