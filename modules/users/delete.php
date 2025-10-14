<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລຶບຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';

requireAdmin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ບໍ່ສາມາດລຶບຕົວເອງໄດ້
if ($id == $_SESSION['user_id']) {
    setFlashMessage('ບໍ່ສາມາດລຶບບັນຊີຕົວເອງໄດ້', 'error');
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit();
}

// ດຶງຂໍ້ມູນເພື່ອບັນທຶກ audit log
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if ($user) {
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
            // ລຶບຂໍ້ມູນ
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // ບັນທຶກ audit log
            logAudit($_SESSION['user_id'], 'DELETE', 'users', $id, $user, null);
            
            setFlashMessage('ລຶບຜູ້ໃຊ້ສຳເລັດແລ້ວ ✓', 'success');
        }
    } catch (PDOException $e) {
        setFlashMessage('ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ: ' . $e->getMessage(), 'error');
    }
} else {
    setFlashMessage('ບໍ່ພົບຜູ້ໃຊ້ທີ່ຕ້ອງການລຶບ', 'error');
}

header('Location: ' . BASE_URL . '/modules/users/list.php');
exit();
