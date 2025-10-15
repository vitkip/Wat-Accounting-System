<?php
/**
 * ລະບົບບັນຊີວັດ - ຟັງຊັນຈັດການວັດ (Temple Functions)
 * ສຳລັບລະບົບຫຼາຍວັດ (Multi-Temple System)
 */

/**
 * ດຶງຂໍ້ມູນວັດທັງໝົດ
 */
function getAllTemples($activeOnly = false) {
    $db = getDB();
    $sql = "SELECT * FROM temples";
    if ($activeOnly) {
        $sql .= " WHERE status = 'active'";
    }
    $sql .= " ORDER BY temple_code";
    
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

/**
 * ດຶງຂໍ້ມູນວັດຈາກ ID
 */
function getTempleById($templeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM temples WHERE id = ?");
    $stmt->execute([$templeId]);
    return $stmt->fetch();
}

/**
 * ດຶງຂໍ້ມູນວັດຈາກລະຫັດວັດ
 */
function getTempleByCode($templeCode) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM temples WHERE temple_code = ?");
    $stmt->execute([$templeCode]);
    return $stmt->fetch();
}

/**
 * ດຶງຂໍ້ມູນວັດຂອງຜູ້ໃຊ້ປະຈຸບັນ
 */
function getCurrentUserTemple() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user = getCurrentUser();
    
    // ຖ້າເປັນ Super Admin ບໍ່ມີວັດສະເພາະ
    if ($user['is_super_admin']) {
        return null;
    }
    
    if (empty($user['temple_id'])) {
        return null;
    }
    
    return getTempleById($user['temple_id']);
}

/**
 * ກວດສອບວ່າຜູ້ໃຊ້ເປັນ Super Admin ຫຼືບໍ່
 */
function isSuperAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    return !empty($user['is_super_admin']);
}

/**
 * ກວດສອບວ່າຜູ້ໃຊ້ສາມາດເຂົ້າເຖິງວັດນີ້ໄດ້ຫຼືບໍ່
 */
function canAccessTemple($templeId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Super Admin ເຂົ້າເຖິງວັດໃດກໍ່ໄດ້
    if (isSuperAdmin()) {
        return true;
    }
    
    $user = getCurrentUser();
    return $user['temple_id'] == $templeId;
}

/**
 * ກວດສອບວ່າຜູ້ໃຊ້ສາມາດສ້າງວັດໃໝ່ໄດ້ຫຼືບໍ່
 */
function canCreateTemple() {
    return isSuperAdmin();
}

/**
 * ສ້າງວັດໃໝ່
 */
function createTemple($data) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO temples (
                temple_code, temple_name, temple_name_lao, 
                abbot_name, address, district, province, 
                phone, email, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['temple_code'],
            $data['temple_name'],
            $data['temple_name_lao'],
            $data['abbot_name'] ?? null,
            $data['address'] ?? null,
            $data['district'] ?? null,
            $data['province'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'active'
        ]);
        
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating temple: " . $e->getMessage());
        return false;
    }
}

/**
 * ອັບເດດຂໍ້ມູນວັດ
 */
function updateTemple($templeId, $data) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            UPDATE temples SET
                temple_name = ?,
                temple_name_lao = ?,
                abbot_name = ?,
                address = ?,
                district = ?,
                province = ?,
                phone = ?,
                email = ?,
                status = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['temple_name'],
            $data['temple_name_lao'],
            $data['abbot_name'] ?? null,
            $data['address'] ?? null,
            $data['district'] ?? null,
            $data['province'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'active',
            $templeId
        ]);
    } catch (PDOException $e) {
        error_log("Error updating temple: " . $e->getMessage());
        return false;
    }
}

/**
 * ດຶງສະຖິຕິວັດ
 */
function getTempleStatistics($templeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM temple_statistics WHERE temple_id = ?");
    $stmt->execute([$templeId]);
    return $stmt->fetch();
}

/**
 * ດຶງສະຖິຕິທຸກວັດ (ສຳລັບ Super Admin)
 */
function getAllTempleStatistics() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM temple_statistics ORDER BY temple_code");
    return $stmt->fetchAll();
}

/**
 * ດຶງການຕັ້ງຄ່າວັດ
 */
function getTempleSetting($templeId, $key, $default = null) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT setting_value 
        FROM temple_settings 
        WHERE temple_id = ? AND setting_key = ?
    ");
    $stmt->execute([$templeId, $key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : $default;
}

/**
 * ບັນທຶກການຕັ້ງຄ່າວັດ
 */
function setTempleSetting($templeId, $key, $value) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO temple_settings (temple_id, setting_key, setting_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        
        return $stmt->execute([$templeId, $key, $value]);
    } catch (PDOException $e) {
        error_log("Error setting temple setting: " . $e->getMessage());
        return false;
    }
}

/**
 * ນັບຈຳນວນຜູ້ໃຊ້ໃນວັດ
 */
function countTempleUsers($templeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE temple_id = ?");
    $stmt->execute([$templeId]);
    return $stmt->fetch()['count'];
}

/**
 * ດຶງຜູ້ໃຊ້ທັງໝົດຂອງວັດ
 */
function getTempleUsers($templeId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, username, full_name, role, created_at 
        FROM users 
        WHERE temple_id = ? 
        ORDER BY role DESC, full_name
    ");
    $stmt->execute([$templeId]);
    return $stmt->fetchAll();
}

/**
 * ສະຫຼັບວັດ (ສຳລັບ Super Admin)
 * ເກັບວັດປະຈຸບັນໄວ້ໃນ Session
 */
function switchTemple($templeId) {
    if (!isSuperAdmin()) {
        return false;
    }
    
    $temple = getTempleById($templeId);
    if (!$temple) {
        return false;
    }
    
    $_SESSION['active_temple_id'] = $templeId;
    return true;
}

/**
 * ດຶງວັດທີ່ກຳລັງເບີ່ງຢູ່ (ສຳລັບ Super Admin)
 */
function getActiveTempleId() {
    // Super Admin ສາມາດສະຫຼັບວັດໄດ້
    if (isSuperAdmin() && isset($_SESSION['active_temple_id'])) {
        return $_SESSION['active_temple_id'];
    }
    
    // ຜູ້ໃຊ້ທົ່ວໄປໃຊ້ວັດຂອງຕົນເອງ
    $user = getCurrentUser();
    return $user['temple_id'] ?? null;
}

/**
 * Alias ຂອງ getActiveTempleId() ເພື່ອຄວາມສະດວກ
 */
function getCurrentTempleId() {
    return getActiveTempleId();
}

/**
 * ກວດສອບວ່າລະບົບ Multi-Temple ໄດ້ຖືກຕິດຕັ້ງແລ້ວຫຼືບໍ່
 */
function isMultiTempleEnabled() {
    static $enabled = null;
    
    if ($enabled === null) {
        try {
            $db = getDB();
            // ກວດສອບວ່າມີຕາຕະລາງ temples ຫຼືບໍ່
            $stmt = $db->query("SHOW TABLES LIKE 'temples'");
            $enabled = $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $enabled = false;
        }
    }
    
    return $enabled;
}

/**
 * ດຶງວັດທີ່ກຳລັງເບີ່ງຢູ່
 */
function getActiveTemple() {
    $templeId = getActiveTempleId();
    if (!$templeId) {
        return null;
    }
    
    return getTempleById($templeId);
}

/**
 * ສ້າງລະຫັດວັດອັດຕະໂນມັດ
 */
function generateTempleCode() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as count FROM temples");
    $count = $stmt->fetch()['count'];
    
    return 'WAT' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

/**
 * Filter ຂໍ້ມູນຕາມວັດ (ໃຊ້ໃນ SQL Query)
 */
function getTempleFilter() {
    $templeId = getActiveTempleId();
    
    if (!$templeId) {
        return ['', []]; // Empty condition
    }
    
    return ['temple_id = ?', [$templeId]];
}

/**
 * ດຶງໝວດໝູ່ລາຍຮັບຂອງວັດ (ລວມໝວດໝູ່ທົ່ວໄປ)
 */
function getTempleIncomeCategories($templeId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM income_categories 
        WHERE temple_id = ? OR temple_id IS NULL 
        ORDER BY name
    ");
    $stmt->execute([$templeId]);
    return $stmt->fetchAll();
}

/**
 * ດຶງໝວດໝູ່ລາຍຈ່າຍຂອງວັດ (ລວມໝວດໝູ່ທົ່ວໄປ)
 */
function getTempleExpenseCategories($templeId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM expense_categories 
        WHERE temple_id = ? OR temple_id IS NULL 
        ORDER BY name
    ");
    $stmt->execute([$templeId]);
    return $stmt->fetchAll();
}
