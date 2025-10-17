<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລຶບລາຍຮັບ
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ⚠️ ກວດສອບ ID ວ່າເປັນຕົວເລກທີ່ຖືກຕ້ອງ
if (!$id || !is_numeric($id) || $id <= 0) {
    setFlashMessage('ID ບໍ່ຖືກຕ້ອງ', 'error');
    header('Location: ' . BASE_URL . '/modules/income/list.php');
    exit();
}

// ກວດສອບວ່າລະບົບ multi-temple ເປີດໃຊ້ຫຼືບໍ່
$isMultiTemple = function_exists('isMultiTempleEnabled') && isMultiTempleEnabled();
$currentTempleId = null;
if ($isMultiTemple && function_exists('getCurrentTempleId')) {
    $currentTempleId = getCurrentTempleId();
}

// ດຶງຂໍ້ມູນເພື່ອກວດສອບສິດທິ ແລະ ບັນທຶກ audit log
$stmt = $db->prepare("SELECT * FROM income WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$record = $stmt->fetch();

if ($record) {
    // ກວດສອບສິດທິການລຶບ
    $canDelete = false;
    
    // ຖ້າລະບົບ multi-temple ເປີດໃຊ້
    if ($isMultiTemple && $currentTempleId) {
        // ກວດສອບວ່າລາຍການນີ້ເປັນຂອງວັດນີ້ບໍ່
        if (isset($record['temple_id']) && $record['temple_id'] == $currentTempleId) {
            $canDelete = true;
        }
    } else {
        // ລະບົບແບບເດີມ - Admin ສາມາດລຶບໄດ້ທຸກຢ່າງ, User ລຶບໄດ້ແຕ່ຂອງຕົນເອງ
        if (isAdmin()) {
            $canDelete = true;
        } elseif ($record['created_by'] == $_SESSION['user_id']) {
            $canDelete = true;
        }
    }
    
    if ($canDelete) {
        try {
            // ⚠️ ລຶບພ້ອມກວດສອບ temple_id ອີກຄັ້ງເພື່ອຄວາມປອດໄພ
            if ($isMultiTemple && $currentTempleId) {
                $stmt = $db->prepare("DELETE FROM income WHERE id = :id AND temple_id = :temple_id");
                $stmt->execute([':id' => $id, ':temple_id' => $currentTempleId]);
            } else {
                $stmt = $db->prepare("DELETE FROM income WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
            
            if ($stmt->rowCount() > 0) {
                // ບັນທຶກ audit log
                logAudit($_SESSION['user_id'], 'DELETE', 'income', $id, $record, null);
                setFlashMessage('ລຶບລາຍຮັບສຳເລັດແລ້ວ ✓', 'success');
            } else {
                setFlashMessage('ບໍ່ສາມາດລຶບຂໍ້ມູນໄດ້', 'error');
            }
        } catch (PDOException $e) {
            setFlashMessage('ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ: ' . $e->getMessage(), 'error');
        }
    } else {
        setFlashMessage('ທ່ານບໍ່ມີສິດລຶບຂໍ້ມູນນີ້', 'error');
    }
} else {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນທີ່ຕ້ອງການລຶບ', 'error');
}

header('Location: ' . BASE_URL . '/modules/income/list.php');
exit();
