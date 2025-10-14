<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໄຟລ໌ຕັ້ງຄ່າລະບົບ
 */

// ການຕັ້ງຄ່າຖານຂໍ້ມູນ
define('DB_HOST', 'localhost');
define('DB_NAME', 'wat_accounting');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ການຕັ້ງຄ່າລະບົບ
define('SITE_NAME', 'ວັດປ່າໜອງບົວທອງໃຕ້');
define('BASE_URL', 'http://localhost/watsystem');
define('TIMEZONE', 'Asia/Vientiane');

// ການຕັ້ງຄ່າຄວາມປອດໄພ
define('SESSION_LIFETIME', 3600); // 1 ຊົ່ວໂມງ
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);

// ຕັ້ງເຂດເວລາ
date_default_timezone_set(TIMEZONE);

// ເລີ່ມ Session ກ່ອນການຕັ້ງຄ່າ
if (session_status() === PHP_SESSION_NONE) {
    // ການຕັ້ງຄ່າ Session ທີ່ປອດໄພ
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // ຖ້າໃຊ້ HTTPS ໃຫ້ເປີດການຕັ້ງຄ່ານີ້
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນດ້ວຍ PDO
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນຜິດພາດ: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// ຟັງຊັນກວດສອບການເຂົ້າສູ່ລະບົບ
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// ຟັງຊັນກວດສອບສິດແອດມິນ
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ຟັງຊັນບັງຄັບໃຫ້ເຂົ້າສູ່ລະບົບ
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

// ຟັງຊັນບັງຄັບສິດແອດມິນ
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

// ຟັງຊັນ redirect ທີ່ຮອງຮັບທັງ absolute ແລະ relative URL
function redirect($path) {
    // ຖ້າເປັນ URL ເຕັມຮູບແບບ (ມີ http:// ຫຼື https://)
    if (preg_match('/^https?:\/\//', $path)) {
        header('Location: ' . $path);
        exit();
    }
    
    // ຖ້າເລີ່ມດ້ວຍ / ແມ່ນ absolute path ຈາກ root
    if (strpos($path, '/') === 0) {
        header('Location: ' . BASE_URL . $path);
        exit();
    }
    
    // ຖ້າບໍ່ແມ່ນ relative path
    header('Location: ' . BASE_URL . '/' . $path);
    exit();
}

// ຟັງຊັນປ້ອງກັນ XSS
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ຟັງຊັນບັນທຶກ Audit Log
function logAudit($userId, $action, $tableName, $recordId, $oldValue = null, $newValue = null) {
    try {
        $db = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, ip_address) 
                VALUES (:user_id, :action, :table_name, :record_id, :old_value, :new_value, :ip_address)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':table_name' => $tableName,
            ':record_id' => $recordId,
            ':old_value' => $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
            ':new_value' => $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null,
            ':ip_address' => $ip
        ]);
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

// ຟັງຊັນບັນທຶກກິດຈະກຳ (alias ຂອງ logAudit)
function logActivity($userId, $action, $tableName, $recordId, $description = null) {
    return logAudit($userId, $action, $tableName, $recordId, null, $description);
}

// ຟັງຊັນຈັດຮູບແບບເງິນກີບ
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' ກີບ';
}

// ຟັງຊັນຈັດຮູບແບບວັນທີ່
function formatDate($date) {
    $months = [
        '01' => 'ມັງກອນ', '02' => 'ກຸມພາ', '03' => 'ມີນາ',
        '04' => 'ເມສາ', '05' => 'ພຶດສະພາ', '06' => 'ມິຖຸນາ',
        '07' => 'ກໍລະກົດ', '08' => 'ສິງຫາ', '09' => 'ກັນຍາ',
        '10' => 'ຕຸລາ', '11' => 'ພະຈິກ', '12' => 'ທັນວາ'
    ];
    
    $dateObj = new DateTime($date);
    $day = $dateObj->format('d');
    $month = $months[$dateObj->format('m')];
    $year = $dateObj->format('Y');
    
    return "{$day} {$month} {$year}";
}

// ຟັງຊັນສົ່ງຂໍ້ຄວາມແຈ້ງເຕືອນ
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// ຟັງຊັນສະແດງຂໍ້ຄວາມແຈ້ງເຕືອນ
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        
        $bgColor = $type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
        
        echo "<div class='{$bgColor} border-l-4 p-4 mb-4 rounded' role='alert'>";
        echo "<p>" . e($message) . "</p>";
        echo "</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// ກວດສອບ Session Timeout
if (isLoggedIn()) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// ສ້າງ alias ສຳລັບ CSRF functions ເພື່ອຄວາມສະດວກ
if (!function_exists('generateCSRF')) {
    function generateCSRF() {
        return generateCSRFToken();
    }
}

if (!function_exists('checkCSRFToken')) {
    function checkCSRFToken() {
        return checkCSRF();
    }
}

// ໂຫຼດຟັງຊັນ CSRF ກ່ອນທີ່ຈະໃຊ້ງານ
require_once __DIR__ . '/includes/csrf.php';
