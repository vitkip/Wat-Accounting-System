<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໜ້າເຂົ້າສູ່ລະບົບ
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf.php';

// ຖ້າເຂົ້າສູ່ລະບົບແລ້ວໃຫ້ໄປໜ້າຫຼັກ
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$error = '';
$timeout = isset($_GET['timeout']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ ແລະ ລະຫັດຜ່ານ';
    } else {
        try {
            $db = getDB();
            $sql = "SELECT id, username, password, full_name, role FROM users WHERE username = :username LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // ລ້າງ session ເກົ່າກ່ອນ
                session_regenerate_id(true);
                
                // ບັນທຶກຂໍ້ມູນເຂົ້າ session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // ບັນທຶກ audit log
                logAudit($user['id'], 'INSERT', 'login', $user['id'], null, ['login' => 'success']);
                
                header('Location: ' . BASE_URL . '/index.php');
                exit();
            } else {
                $error = 'ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ';
                
                // ບັນທຶກການພະຍາຍາມເຂົ້າສູ່ລະບົບທີ່ບໍ່ສຳເລັດ
                if ($user) {
                    logAudit($user['id'], 'INSERT', 'login', $user['id'], null, ['login' => 'failed']);
                }
            }
        } catch (PDOException $e) {
            $error = 'ເກີດຂໍ້ຜິດພາດໃນລະບົບ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ເຂົ້າສູ່ລະບົບ - <?php echo e(SITE_NAME); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phetsarath Font -->
    <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Phetsarath', 'Noto Sans Lao', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen">
    
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🏛️</div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo e(SITE_NAME); ?></h1>
                <p class="text-gray-600">ລະບົບຈັດການບັນຊີວັດອອນໄລນ໌</p>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">ເຂົ້າສູ່ລະບົບ</h2>
                
                <?php if ($timeout): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded" role="alert">
                    <p>ເວລາໃນການໃຊ້ງານໝົດແລ້ວ. ກະລຸນາເຂົ້າສູ່ລະບົບໃໝ່.</p>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                    <p><?php echo e($error); ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-medium mb-2">
                            ຊື່ຜູ້ໃຊ້
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required
                               autocomplete="username"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="ປ້ອນຊື່ຜູ້ໃຊ້"
                               value="<?php echo e($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-2">
                            ລະຫັດຜ່ານ
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               autocomplete="current-password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="ປ້ອນລະຫັດຜ່ານ">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 transform hover:scale-[1.02]">
                        ເຂົ້າສູ່ລະບົບ
                    </button>
                </form>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-600 text-center">
                        <p class="font-medium mb-2">ຂໍ້ມູນທົດລອງ:</p>
                        <p><strong>ແອດມິນ:</strong> admin / admin123</p>
                        <p><strong>ຜູ້ໃຊ້:</strong> user1 / admin123</p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-8 text-gray-600">
                <p class="text-sm">&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?></p>
            </div>
            
        </div>
    </div>
    
</body>
</html>
