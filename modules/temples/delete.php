<?php
/**
 * ລະບົບບັນຊີວັດ - ລຶບວັດ (Super Admin Only)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// ກວດສອບສິດ Super Admin
if (!isSuperAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$db = getDB();
$templeId = $_GET['id'] ?? 0;

// ⚠️ ກວດສອບ ID ວ່າເປັນຕົວເລກທີ່ຖືກຕ້ອງ
if (!$templeId || !is_numeric($templeId) || $templeId <= 0) {
    setFlashMessage('ID ບໍ່ຖືກຕ້ອງ', 'error');
    redirect('/modules/temples/index.php');
}

// ກວດສອບວ່າວັດມີຢູ່ຈິງ - ໃຊ້ $templeData ເພື່ອບໍ່ໃຫ້ຊ້ຳກັບ $temple ໃນ header.php
$templeData = getTempleById($templeId);

if (!$templeData) {
    setFlashMessage('ບໍ່ພົບວັດທີ່ທ່ານຕ້ອງການລຶບ', 'error');
    redirect('/modules/temples/index.php');
}

// ກວດສອບວ່າມີຂໍ້ມູນໃນວັດບໍ່
$stmt = $db->prepare("SELECT COUNT(*) as total FROM income WHERE temple_id = ?");
$stmt->execute([$templeId]);
$incomeCount = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM expense WHERE temple_id = ?");
$stmt->execute([$templeId]);
$expenseCount = $stmt->fetch()['total'];

if ($incomeCount > 0 || $expenseCount > 0) {
    setFlashMessage("ບໍ່ສາມາດລຶບວັດນີ້ໄດ້ເນື່ອງຈາກມີລາຍການ {$incomeCount} ລາຍຮັບ ແລະ {$expenseCount} ລາຍຈ່າຍ. ກະລຸນາລຶບລາຍການເຫຼົ່ານັ້ນກ່ອນ ຫຼື ປ່ຽນສະຖານະເປັນ 'ປິດໃຊ້ງານ'", 'error');
    redirect('/modules/temples/index.php');
}

// ຢືນຢັນການລຶບຜ່ານ CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token', 'error');
        redirect('/modules/temples/index.php');
    }
    
    try {
        $db->beginTransaction();
        
        // ລຶບ Settings
        $stmt = $db->prepare("DELETE FROM temple_settings WHERE temple_id = ?");
        $stmt->execute([$templeId]);
        
        // ລຶບ Users
        $stmt = $db->prepare("DELETE FROM users WHERE temple_id = ?");
        $stmt->execute([$templeId]);
        
        // ລຶບ Categories
        $stmt = $db->prepare("DELETE FROM income_categories WHERE temple_id = ?");
        $stmt->execute([$templeId]);
        
        $stmt = $db->prepare("DELETE FROM expense_categories WHERE temple_id = ?");
        $stmt->execute([$templeId]);
        
        // ລຶບວັດ
        $stmt = $db->prepare("DELETE FROM temples WHERE id = ?");
        $stmt->execute([$templeId]);
        
        $db->commit();
        
        logAudit('temples', $templeId, 'delete', "ລຶບວັດ: {$templeData['temple_name_lao']} ({$templeData['temple_code']})");
        
        setFlashMessage("ລຶບວັດ '{$templeData['temple_name_lao']}' ສຳເລັດ", 'success');
        redirect('/modules/temples/index.php');
        
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage(), 'error');
        redirect('/modules/temples/index.php');
    }
}

$pageTitle = "ລຶບວັດ";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">ຢືນຢັນການລຶບວັດ</h1>
    <p class="text-gray-600">ການລຶບນີ້ບໍ່ສາມາດຍົກເລີກໄດ້</p>
</div>

<!-- Warning -->
<div class="bg-white rounded-2xl shadow-md p-8 max-w-2xl mx-auto">
    <div class="text-center mb-6">
        <div class="bg-red-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-red-600 mb-2">ເຕືອນ! ການກະທຳນີ້ບໍ່ສາມາດຍົກເລີກໄດ້</h2>
        <p class="text-gray-600">ທ່ານແນ່ໃຈບໍວ່າຕ້ອງການລຶບວັດນີ້?</p>
    </div>
    
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h3 class="font-bold text-gray-800 mb-3">ຂໍ້ມູນວັດທີ່ຈະຖືກລຶບ:</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">ລະຫັດວັດ:</span>
                <span class="font-bold"><?php echo e($templeData['temple_code']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">ຊື່ວັດ:</span>
                <span class="font-bold"><?php echo e($templeData['temple_name_lao']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
        <p class="text-sm text-yellow-800">
            <strong>ໝາຍເຫດ:</strong> ການລຶບວັດຈະລຶບຂໍ້ມູນທັງໝົດລວມເຖິງ:
        </p>
        <ul class="list-disc list-inside text-sm text-yellow-800 mt-2 ml-4">
            <li>ຜູ້ໃຊ້ງານທັງໝົດຂອງວັດ</li>
            <li>ໝວດໝູ່ລາຍຮັບ ແລະ ລາຍຈ່າຍ</li>
            <li>ການຕັ້ງຄ່າວັດ</li>
        </ul>
    </div>
    
    <form method="POST" class="space-y-4">
        <?php echo csrfField(); ?>
        
        <div class="flex justify-center space-x-4">
            <a href="index.php" 
               class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition duration-200">
                ຍົກເລີກ
            </a>
            <button type="submit" 
                    onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບວັດນີ້? ການກະທຳນີ້ບໍ່ສາມາດຍົກເລີກໄດ້!')"
                    class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                ແມ່ນແລ້ວ, ລຶບວັດນີ້
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
