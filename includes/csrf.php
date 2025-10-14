<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ການຈັດການ CSRF Token
 */

// ສ້າງ CSRF Token
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// ກວດສອບ CSRF Token
function validateCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// ສ້າງ HTML Input ສຳລັບ CSRF Token
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// ກວດສອບ CSRF Token ຈາກ Request
function checkCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validateCSRFToken($token)) {
            return false;
        }
        return true;
    }
    return true; // ຖ້າບໍ່ແມ່ນ POST ໃຫ້ຜ່ານ
}

// ຟັງຊັນກວດສອບ CSRF ແລະສະແດງໜ້າ Error
function checkCSRFOrDie() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validateCSRFToken($token)) {
            http_response_code(403);
            die('
                <!DOCTYPE html>
                <html lang="lo">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>ຂໍ້ຜິດພາດຄວາມປອດໄພ</title>
                    <script src="https://cdn.tailwindcss.com"></script>
                    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                    <style>
                        body { font-family: "Noto Sans Lao", sans-serif; }
                    </style>
                </head>
                <body class="bg-gray-100">
                    <div class="min-h-screen flex items-center justify-center px-4">
                        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
                            <div class="text-red-500 mb-4">
                                <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">ຂໍ້ຜິດພາດຄວາມປອດໄພ</h1>
                            <p class="text-gray-600 mb-6">CSRF Token ບໍ່ຖືກຕ້ອງ. ກະລຸນາລອງໃໝ່ອີກຄັ້ງ.</p>
                            <a href="javascript:history.back()" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200">
                                ກັບຄືນ
                            </a>
                        </div>
                    </div>
                </body>
                </html>
            ');
        }
    }
}
