<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໜ້າອອກຈາກລະບົບ
 */

require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    // ບັນທຶກ audit log
    logAudit($_SESSION['user_id'], 'INSERT', 'logout', $_SESSION['user_id'], null, ['logout' => 'success']);
    
    // ລ້າງ session
    session_unset();
    session_destroy();
}

header('Location: ' . BASE_URL . '/login.php');
exit();
