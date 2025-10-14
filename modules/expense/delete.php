<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລຶບລາຍຈ່າຍ
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

// ດຶງຂໍ້ມູນເພື່ອບັນທຶກ audit log
$stmt = $db->prepare("SELECT * FROM expense WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$record = $stmt->fetch();

if ($record) {
    try {
        // ລຶບຂໍ້ມູນ
        $stmt = $db->prepare("DELETE FROM expense WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // ບັນທຶກ audit log
        logAudit($_SESSION['user_id'], 'DELETE', 'expense', $id, $record, null);
        
        setFlashMessage('ລຶບລາຍຈ່າຍສຳເລັດແລ້ວ ✓', 'success');
    } catch (PDOException $e) {
        setFlashMessage('ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ: ' . $e->getMessage(), 'error');
    }
} else {
    setFlashMessage('ບໍ່ພົບຂໍ້ມູນທີ່ຕ້ອງການລຶບ', 'error');
}

header('Location: ' . BASE_URL . '/modules/expense/list.php');
exit();
