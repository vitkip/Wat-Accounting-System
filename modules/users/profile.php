<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໂປຣໄຟລ໌ຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];

// ດຶງຂໍ້ມູນຜູ້ໃຊ້ປັດຈຸບັນ
$stmt = $db->prepare("
    SELECT u.*, t.temple_name, t.temple_name_lao 
    FROM users u 
    LEFT JOIN temples t ON u.temple_id = t.id 
    WHERE u.id = :id 
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້', 'error');
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// ປະມວນຜົນອັບເດດໂປຣໄຟລ໌
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $errors = [];
        
        if (empty($fullName)) {
            $errors[] = 'ກະລຸນາປ້ອນຊື່ເຕັມ';
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'ອີເມວບໍ່ຖືກຕ້ອງ';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    UPDATE users 
                    SET full_name = :full_name, 
                        email = :email,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    ':full_name' => $fullName,
                    ':email' => $email,
                    ':id' => $userId
                ]);
                
                $_SESSION['full_name'] = $fullName;
                
                setFlashMessage('ອັບເດດໂປຣໄຟລ໌ສຳເລັດ', 'success');
                header('Location: ' . BASE_URL . '/modules/users/profile.php');
                exit();
            } catch (PDOException $e) {
                $errors[] = 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage(implode('<br>', $errors), 'error');
        }
    }
    elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'ກະລຸນາປ້ອນລະຫັດຜ່ານປັດຈຸບັນ';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'ກະລຸນາປ້ອນລະຫັດຜ່ານໃໝ່';
        } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ ' . PASSWORD_MIN_LENGTH . ' ຕົວອັກສອນ';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'ລະຫັດຜ່ານໃໝ່ບໍ່ຕົງກັນ';
        }
        
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("
                    UPDATE users 
                    SET password = :password,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $userId
                ]);
                
                setFlashMessage('ປ່ຽນລະຫັດຜ່ານສຳເລັດ', 'success');
                header('Location: ' . BASE_URL . '/modules/users/profile.php');
                exit();
            } catch (PDOException $e) {
                $errors[] = 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage(implode('<br>', $errors), 'error');
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ໂປຣໄຟລ໌ຜູ້ໃຊ້</h1>
                <p class="text-gray-600">ແກ້ໄຂຂໍ້ມູນສ່ວນຕົວ ແລະ ປ່ຽນລະຫັດຜ່ານ</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">ກັບຄືນ</a>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6">ແກ້ໄຂຂໍ້ມູນສ່ວນຕົວ</h3>
        
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    ຊື່ຜູ້ໃຊ້ <span class="text-xs text-gray-500">(ບໍ່ສາມາດແກ້ໄຂ)</span>
                </label>
                <input type="text" value="<?php echo e($user['username']); ?>" disabled class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg cursor-not-allowed text-gray-600">
                <p class="text-xs text-gray-500 mt-1">
                    <?php if (($user['is_super_admin'] ?? 0) == 1): ?>
                        ສິດການນຳໃຊ້: <span class="font-semibold text-purple-700">Super Admin</span>
                    <?php elseif ($user['role'] === 'admin'): ?>
                        ສິດການນຳໃຊ້: <span class="font-semibold text-blue-700">Admin</span>
                    <?php else: ?>
                        ສິດການນຳໃຊ້: <span class="font-semibold text-gray-700">User</span>
                    <?php endif; ?>
                    <?php if ($user['temple_id'] && ($user['temple_name_lao'] || $user['temple_name'])): ?>
                        • ວັດ: <span class="font-semibold"><?php echo e($user['temple_name_lao'] ?: $user['temple_name']); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="mb-6">
                <label for="full_name" class="block text-gray-700 font-medium mb-2">ຊື່ເຕັມ <span class="text-red-500">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo e($user['full_name']); ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="ປ້ອນຊື່ເຕັມ">
            </div>
            
            <div class="mb-6">
                <label for="email" class="block text-gray-700 font-medium mb-2">ອີເມວ</label>
                <input type="email" id="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="ປ້ອນອີເມວ (ຖ້າມີ)">
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200">ບັນທຶກການປ່ຽນແປງ</button>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6">ປ່ຽນລະຫັດຜ່ານ</h3>
        
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="passwordForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="change_password">
            
            <div class="mb-6">
                <label for="current_password" class="block text-gray-700 font-medium mb-2">ລະຫັດຜ່ານປັດຈຸບັນ <span class="text-red-500">*</span></label>
                <input type="password" id="current_password" name="current_password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="ປ້ອນລະຫັດຜ່ານປັດຈຸບັນ">
            </div>
            
            <div class="mb-6">
                <label for="new_password" class="block text-gray-700 font-medium mb-2">ລະຫັດຜ່ານໃໝ່ <span class="text-red-500">*</span></label>
                <input type="password" id="new_password" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="ປ້ອນລະຫັດຜ່ານໃໝ່">
                <p class="text-xs text-gray-500 mt-1">ຢ່າງໜ້ອຍ <?php echo PASSWORD_MIN_LENGTH; ?> ຕົວອັກສອນ</p>
            </div>
            
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-medium mb-2">ຢືນຢັນລະຫັດຜ່ານໃໝ່ <span class="text-red-500">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="ຢືນຢັນລະຫັດຜ່ານໃໝ່">
            </div>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-6">
                <p class="text-sm text-yellow-800"><strong>ຄຳເຕືອນ:</strong> ເມື່ອປ່ຽນລະຫັດຜ່ານສຳເລັດ, ທ່ານອາດຈະຕ້ອງເຂົ້າສູ່ລະບົບໃໝ່.</p>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200">ບັນທຶກລະຫັດຜ່ານ</button>
            </div>
        </form>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('ລະຫັດຜ່ານໃໝ່ບໍ່ຕົງກັນ');
                return false;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
