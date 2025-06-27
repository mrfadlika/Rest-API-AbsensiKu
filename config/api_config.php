<?php
/**
 * Konfigurasi API untuk Backend Absensi
 * Kompatibel dengan aplikasi absensi_pbl
 */

// Base URL untuk API (sesuaikan dengan server Anda)
define('API_BASE_URL', 'https://belajar.novatix.site');

// Konfigurasi CORS
define('ALLOWED_ORIGINS', [
    'https://belajar.novatix.site',
    'http://localhost:3000',
    'http://localhost:8081',
    'exp://localhost:8081'
]);

// Konfigurasi Keamanan
define('REQUIRE_ADMIN_VERIFICATION', false); // Set true untuk keamanan maksimal
define('ALLOW_ANONYMOUS_CLASS_LIST', true); // Set false untuk keamanan maksimal

// Konfigurasi Response
define('DEFAULT_RESPONSE_FORMAT', 'json');
define('INCLUDE_USER_ROLE_IN_RESPONSE', true);

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_absensi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Konfigurasi Logging
define('ENABLE_API_LOGGING', false);
define('LOG_FILE_PATH', '../logs/api.log');

/**
 * Fungsi untuk mendapatkan konfigurasi CORS
 */
function getCorsHeaders() {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if (in_array($origin, ALLOWED_ORIGINS) || in_array('*', ALLOWED_ORIGINS)) {
        header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Fungsi untuk validasi admin (opsional)
 */
function validateAdmin($admin_id = null) {
    if (!REQUIRE_ADMIN_VERIFICATION || !$admin_id) {
        return true; // Skip validation jika tidak diperlukan
    }
    
    global $conn;
    $admin_check = "SELECT role FROM users WHERE id = :admin_id AND role = 'admin' LIMIT 1";
    $admin_stmt = $conn->prepare($admin_check);
    $admin_stmt->bindParam(':admin_id', $admin_id);
    $admin_stmt->execute();
    
    return $admin_stmt->rowCount() > 0;
}

/**
 * Fungsi untuk logging API
 */
function logApiCall($endpoint, $method, $data = null, $response = null) {
    if (!ENABLE_API_LOGGING) return;
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'method' => $method,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_data' => $data,
        'response' => $response
    ];
    
    $log_dir = dirname(LOG_FILE_PATH);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents(LOG_FILE_PATH, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Fungsi untuk response standar
 */
function sendResponse($status, $message, $data = null, $user_role = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (INCLUDE_USER_ROLE_IN_RESPONSE && $user_role !== null) {
        $response['user_role'] = $user_role;
    }
    
    echo json_encode($response);
}

/**
 * Fungsi untuk mendapatkan role user
 */
function getUserRole($user_id) {
    if (!$user_id) return 'user';
    
    global $conn;
    $user_query = "SELECT role FROM users WHERE id = :user_id LIMIT 1";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();
    
    if ($user_stmt->rowCount() > 0) {
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        return $user_data['role'];
    }
    
    return 'user'; // Default role
}
?> 