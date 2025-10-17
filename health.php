<?php
/**
 * Health Check Script
 * ໃຊ້ສຳລັບກວດສອບສະຖານະລະບົບ
 */

header('Content-Type: application/json');

$checks = [];
$overallStatus = 'healthy';

// 1. Check PHP version
$phpVersion = PHP_VERSION;
$checks['php_version'] = [
    'status' => version_compare($phpVersion, '7.4.0', '>=') ? 'ok' : 'warning',
    'value' => $phpVersion,
    'required' => '7.4.0+'
];

// 2. Check required PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$checks['php_extensions'] = [
    'status' => empty($missingExtensions) ? 'ok' : 'error',
    'missing' => $missingExtensions
];

if (!empty($missingExtensions)) {
    $overallStatus = 'error';
}

// 3. Check database connection
try {
    require_once __DIR__ . '/config.php';
    $db = getDB();
    $stmt = $db->query("SELECT 1");
    
    // Check if temple_statistics view exists
    $viewCheck = $db->query("SHOW TABLES LIKE 'temple_statistics'");
    $viewExists = $viewCheck->rowCount() > 0;
    
    // Check temples table
    $templeCheck = $db->query("SELECT COUNT(*) as count FROM temples");
    $templeCount = $templeCheck->fetch()['count'];
    
    $checks['database'] = [
        'status' => 'ok',
        'connection' => 'connected',
        'view_exists' => $viewExists,
        'temple_count' => $templeCount
    ];
    
    if (!$viewExists) {
        $checks['database']['warning'] = 'temple_statistics view not found';
        if ($overallStatus === 'healthy') {
            $overallStatus = 'warning';
        }
    }
    
} catch (Exception $e) {
    $checks['database'] = [
        'status' => 'error',
        'error' => 'Connection failed'
    ];
    $overallStatus = 'error';
}

// 4. Check writable directories
$writableDirs = [
    'logs' => __DIR__ . '/logs'
];

foreach ($writableDirs as $name => $path) {
    if (!file_exists($path)) {
        @mkdir($path, 0755, true);
    }
    
    $isWritable = is_writable($path);
    $checks['writable_dirs'][$name] = [
        'status' => $isWritable ? 'ok' : 'warning',
        'path' => $path,
        'writable' => $isWritable
    ];
    
    if (!$isWritable && $overallStatus === 'healthy') {
        $overallStatus = 'warning';
    }
}

// 5. Check config file
$configExists = file_exists(__DIR__ . '/config.php');
$checks['config_file'] = [
    'status' => $configExists ? 'ok' : 'error',
    'exists' => $configExists
];

if (!$configExists) {
    $overallStatus = 'error';
}

// 6. Check .htaccess
$htaccessExists = file_exists(__DIR__ . '/.htaccess');
$checks['htaccess'] = [
    'status' => $htaccessExists ? 'ok' : 'warning',
    'exists' => $htaccessExists
];

// 7. System info
$checks['system_info'] = [
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'php_sapi' => php_sapi_name(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];

// Response
$response = [
    'status' => $overallStatus,
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => $checks
];

// Set HTTP status code based on health
http_response_code($overallStatus === 'error' ? 503 : 200);

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
