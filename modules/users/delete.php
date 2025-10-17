<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລຶບຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireAdmin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ⚠️ ກວດສອບ ID ວ່າເປັນຕົວເລກທີ່ຖືກຕ້ອງ
if (!$id || !is_numeric($id) || $id <= 0) {
    setFlashMessage('ID ບໍ່ຖືກຕ້ອງ', 'error');
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit();
}

// ກວດສອບສິດຂອງຜູ້ໃຊ້ປັດຈຸບັນ
$currentUser = $_SESSION;
$isSuperAdmin = ($currentUser['is_super_admin'] ?? 0) == 1;
$currentTempleId = getCurrentTempleId();
$isMultiTemple = isMultiTempleEnabled();

// ບໍ່ສາມາດລຶບຕົວເອງໄດ້
if ($id == $_SESSION['user_id']) {
    setFlashMessage('ບໍ່ສາມາດລຶບບັນຊີຕົວເອງໄດ້', 'error');
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit();
}

// ດຶງຂໍ້ມູນເພື່ອບັນທຶກ audit log ແລະ ກວດສອບສິດ
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if ($user) {
    // ກວດສອບສິດໃນການລຶບ
    if (!$isSuperAdmin) {
        // Admin ທົ່ວໄປລຶບໄດ້ສະເພາະຜູ້ໃຊ້ໃນວັດຂອງຕົນ
        if ($isMultiTemple && $user['temple_id'] != $currentTempleId) {
            setFlashMessage('ທ່ານບໍ່ມີສິດລຶບຜູ້ໃຊ້ນີ້', 'error');
            header('Location: ' . BASE_URL . '/modules/users/list.php');
            exit();
        }
        // ບໍ່ສາມາດລຶບ Super Admin
        if ($user['is_super_admin'] == 1) {
            setFlashMessage('ທ່ານບໍ່ມີສິດລຶບ Super Admin', 'error');
            header('Location: ' . BASE_URL . '/modules/users/list.php');
            exit();
        }
    }
    
    try {
        // ກວດສອບວ່າຜູ້ໃຊ້ນີ້ມີການບັນທຶກຂໍ້ມູນຫຼືບໍ່
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM income WHERE created_by = :user_id");
        $stmt->execute([':user_id' => $id]);
        $incomeCount = $stmt->fetch()['count'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM expense WHERE created_by = :user_id");
        $stmt->execute([':user_id' => $id]);
        $expenseCount = $stmt->fetch()['count'];
        
        if ($incomeCount > 0 || $expenseCount > 0) {
            setFlashMessage('ບໍ່ສາມາດລຶບຜູ້ໃຊ້ນີ້ໄດ້ເນື່ອງຈາກມີການບັນທຶກຂໍ້ມູນໃນລະບົບ', 'error');
        } else {
            // ⚠️ ລຶບພ້ອມກວດສອບ temple_id ອີກຄັ້ງເພື່ອຄວາມປອດໄພ
            if (!$isSuperAdmin && $isMultiTemple && $currentTempleId) {
                // Admin ທົ່ວໄປຕ້ອງກວດສອບ temple_id
                $stmt = $db->prepare("DELETE FROM users WHERE id = :id AND temple_id = :temple_id");
                $stmt->execute([':id' => $id, ':temple_id' => $currentTempleId]);
            } else {
                // Super Admin ລຶບໄດ້ທຸກຄົນ
                $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
            
            if ($stmt->rowCount() > 0) {
                // ບັນທຶກ audit log
                logAudit($_SESSION['user_id'], 'DELETE', 'users', $id, $user, null);
                setFlashMessage('ລຶບຜູ້ໃຊ້ສຳເລັດແລ້ວ ✓', 'success');
            } else {
                setFlashMessage('ບໍ່ສາມາດລຶບຜູ້ໃຊ້ໄດ້', 'error');
            }
        }
    } catch (PDOException $e) {
        setFlashMessage('ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ: ' . $e->getMessage(), 'error');
    }
} else {
    setFlashMessage('ບໍ່ພົບຜູ້ໃຊ້ທີ່ຕ້ອງການລຶບ', 'error');
}

header('Location: ' . BASE_URL . '/modules/users/list.php');
exit();
