<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ແກ້ໄຂຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';

requireAdmin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ດຶງຂໍ້ມູນຜູ້ໃຊ້
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('ບໍ່ພົບຜູ້ໃຊ້ທີ່ຕ້ອງການ', 'error');
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit();
}

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
        // ກວດສອບວ່າຊື່ຜູ້ໃຊ້ຊ້ຳຫຼືບໍ່ (ຍົກເວັ້ນຕົວເອງ)
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $stmt->execute([':username' => $username, ':id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'ຊື່ຜູ້ໃຊ້ນີ້ມີໃນລະບົບແລ້ວ';
        }
    }
    
    if (empty($fullName)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ເຕັມ';
    }
    
    // ຖ້າມີການປ່ຽນລະຫັດຜ່ານ
    if (!empty($password)) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ ' . PASSWORD_MIN_LENGTH . ' ຕົວອັກສອນ';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'ລະຫັດຜ່ານບໍ່ຕົງກັນ';
        }
    }
    
    if (!in_array($role, ['admin', 'user'])) {
        $errors[] = 'ສິດຜູ້ໃຊ້ບໍ່ຖືກຕ້ອງ';
    }
    
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // ປ່ຽນລະຫັດຜ່ານດ້ວຍ
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = :username, password = :password, full_name = :full_name, role = :role WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':full_name' => $fullName,
                    ':role' => $role,
                    ':id' => $id
                ]);
            } else {
                // ບໍ່ປ່ຽນລະຫັດຜ່ານ
                $sql = "UPDATE users SET username = :username, full_name = :full_name, role = :role WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':full_name' => $fullName,
                    ':role' => $role,
                    ':id' => $id
                ]);
            }
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'UPDATE', 'users', $id, $user, [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role
            ]);
            
            // ຖ້າແກ້ໄຂຂໍ້ມູນຕົວເອງ ໃຫ້ອັບເດດ session
            if ($id == $_SESSION['user_id']) {
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $fullName;
                $_SESSION['role'] = $role;
            }
            
            setFlashMessage('ແກ້ໄຂຂໍ້ມູນຜູ້ໃຊ້ສຳເລັດແລ້ວ ✓', 'success');
            header('Location: ' . BASE_URL . '/modules/users/list.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການແກ້ໄຂຂໍ້ມູນ: ' . $e->getMessage();
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ແກ້ໄຂຂໍ້ມູນຜູ້ໃຊ້</h1>
                <p class="text-gray-600">ແກ້ໄຂຂໍ້ມູນບັນຊີຜູ້ໃຊ້</p>
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
                       value="<?php echo e($_POST['username'] ?? $user['username']); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ປ້ອນຊື່ຜູ້ໃຊ້">
            </div>
            
            <div class="mb-6">
                <label for="full_name" class="block text-gray-700 font-medium mb-2">
                    ຊື່ເຕັມ <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       required
                       value="<?php echo e($_POST['full_name'] ?? $user['full_name']); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="ປ້ອນຊື່ເຕັມ">
            </div>
            
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <p class="text-sm text-yellow-700">
                    <strong>ໝາຍເຫດ:</strong> ປ່ອຍວ່າງຖ້າບໍ່ຕ້ອງການປ່ຽນລະຫັດຜ່ານ
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">
                        ລະຫັດຜ່ານໃໝ່
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="ປ້ອນລະຫັດຜ່ານໃໝ່">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-gray-700 font-medium mb-2">
                        ຢືນຢັນລະຫັດຜ່ານໃໝ່
                    </label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="ຢືນຢັນລະຫັດຜ່ານໃໝ່">
                </div>
                
            </div>
            
            <div class="mb-6">
                <label for="role" class="block text-gray-700 font-medium mb-2">
                    ສິດຜູ້ໃຊ້ <span class="text-red-500">*</span>
                </label>
                <select id="role" 
                        name="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="user" <?php echo (isset($_POST['role']) ? $_POST['role'] === 'user' : $user['role'] === 'user') ? 'selected' : ''; ?>>
                        ຜູ້ໃຊ້ທົ່ວໄປ
                    </option>
                    <option value="admin" <?php echo (isset($_POST['role']) ? $_POST['role'] === 'admin' : $user['role'] === 'admin') ? 'selected' : ''; ?>>
                        ແອດມິນ
                    </option>
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/modules/users/list.php" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    ຍົກເລີກ
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200">
                    ບັນທຶກການແກ້ໄຂ
                </button>
            </div>
            
        </form>
    </div>
    
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
