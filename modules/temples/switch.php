<?php
/**
 * ລະບົບບັນຊີວັດ - ສະຫຼັບໄປວັດອື່ນ (Super Admin Only)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// ກວດສອບສິດ Super Admin
if (!isSuperAdmin()) {
    setFlashMessage('ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້', 'error');
    redirect('/index.php');
}

$templeId = $_GET['temple_id'] ?? 0;

// ກວດສອບວ່າວັດມີຢູ່ຈິງ
$temple = getTempleById($templeId);

if (!$temple) {
    setFlashMessage('ບໍ່ພົບວັດທີ່ທ່ານຕ້ອງການ', 'error');
    redirect('/modules/temples/index.php');
}

// ສະຫຼັບວັດ
if (switchTemple($templeId)) {
    logAudit('temples', $templeId, 'switch', "ສະຫຼັບໄປວັດ: {$temple['temple_name_lao']} ({$temple['temple_code']})");
    setFlashMessage("ສະຫຼັບໄປວັດ '{$temple['temple_name_lao']}' ສຳເລັດ", 'success');
    redirect('/index.php');
} else {
    setFlashMessage('ບໍ່ສາມາດສະຫຼັບວັດໄດ້', 'error');
    redirect('/modules/temples/index.php');
}
