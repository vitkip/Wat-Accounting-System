<?php
require_once '../../config.php';

// Temporary fix: Ensure generateCSRF function exists
if (!function_exists('generateCSRF')) {
    function generateCSRF() {
        return generateCSRFToken();
    }
}

// ກວດສອບການເຂົ້າສູ່ລະບົບ
if (!isLoggedIn()) {
    redirect('/login.php');
}

// ຕ້ອງເປັນແອດມິນເທົ່ານັ້ນ
if (!isAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$db = getDB();

// ປະມວນຜົນຟອມ (ກ່ອນ header)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ກວດສອບ CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        setFlashMessage('ຂໍ້ຜິດພາດຄວາມປອດໄພ: CSRF Token ບໍ່ຖືກຕ້ອງ', 'error');
        redirect('/modules/categories/expense_add.php');
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ໝວດໝູ່';
    }

    if (empty($errors)) {
        try {
            // ກວດສອບວ່າມີຊື່ຊໍ້າບໍ່
            $stmt = $db->prepare("SELECT id FROM expense_categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                setFlashMessage('ມີໝວດໝູ່ນີ້ຢູ່ແລ້ວ', 'error');
                redirect('/modules/categories/expense_add.php');
            }

            $stmt = $db->prepare("INSERT INTO expense_categories (name, description) VALUES (?, ?)");
            
            if ($stmt->execute([$name, $description])) {
                $new_id = $db->lastInsertId();
                logActivity($_SESSION['user_id'], 'INSERT', 'expense_categories', $new_id, "ເພີ່ມໝວດໝູ່ລາຍຈ່າຍ: {$name}");
                setFlashMessage('ເພີ່ມໝວດໝູ່ສຳເລັດ', 'success');
                redirect('/modules/categories/expense_list.php');
            }
        } catch (PDOException $e) {
            setFlashMessage('ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage(), 'error');
        }
    } else {
        foreach ($errors as $error) {
            setFlashMessage($error, 'error');
        }
    }
}

$pageTitle = 'ເພີ່ມໝວດໝູ່ລາຍຈ່າຍໃໝ່';
require_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" class="text-gray-600 hover:text-gray-800 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            ເພີ່ມໝວດໝູ່ລາຍຈ່າຍໃໝ່
                        </h1>
                        <p class="text-gray-600 mt-1">ສ້າງໝວດໝູ່ໃໝ່ສຳລັບການຈັດກຸ່ມລາຍຈ່າຍ</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ຟອມ -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                <!-- ຊື່ໝວດໝູ່ -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        ຊື່ໝວດໝູ່ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           placeholder="ເຊັ່ນ: ຄ່າໄຟຟ້າ, ຄ່ານ້ຳ, ຄ່າອາຫານ"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition duration-200"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ຊື່ໝວດໝູ່ຄວນສັ້ນ ແລະ ຊັດເຈນ
                    </p>
                </div>

                <!-- ລາຍລະອຽດ -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        ລາຍລະອຽດ
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              placeholder="ລາຍລະອຽດເພີ່ມເຕີມກ່ຽວກັບໝວດໝູ່ນີ້ (ຖ້າມີ)"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition duration-200"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ອະທິບາຍວ່າໝວດໝູ່ນີ້ໃຊ້ສຳລັບແນວໃດ
                    </p>
                </div>

                <!-- ຕົວຢ່າງໝວດໝູ່ທີ່ແນະນຳ -->
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <div>
                            <h3 class="text-orange-800 font-medium">ຕົວຢ່າງໝວດໝູ່ລາຍຈ່າຍທີ່ແນະນຳ:</h3>
                            <ul class="mt-2 text-sm text-orange-700 space-y-1">
                                <li>• <strong>ຄ່າສາທາລະນູປະໂພກ:</strong> ຄ່າໄຟ, ຄ່ານ້ຳ, ຄ່າອິນເຕີເນັດ</li>
                                <li>• <strong>ຄ່າອາຫານ:</strong> ອາຫານພະໃນວັດ</li>
                                <li>• <strong>ຄ່າສ້ອມແປງ:</strong> ຄ່າສ້ອມແປງອາຄານ, ສິ່ງຂອງຕ່າງໆ</li>
                                <li>• <strong>ວັດສະດຸສຳນັກງານ:</strong> ເຄື່ອງຂຽນ, ເຄື່ອງໃຊ້ຕ່າງໆ</li>
                                <li>• <strong>ລາຍຈ່າຍອື່ນໆ:</strong> ລາຍຈ່າຍນອກເໜືອທີ່ລະບຸໄວ້</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ປຸ່ມ -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t">
                    <a href="<?php echo BASE_URL; ?>/modules/categories/expense_list.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        ຍົກເລີກ
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        ບັນທຶກ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
