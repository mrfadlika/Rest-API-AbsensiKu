<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    $stats = [];

    // Total Kelas Hari Ini
    $query_kelas = "SELECT COUNT(id) as total FROM kelas WHERE DATE(created_at) = CURDATE()";
    $stmt_kelas = $conn->prepare($query_kelas);
    $stmt_kelas->execute();
    $stats['total_kelas_today'] = (int)$stmt_kelas->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Hadir Hari Ini
    $query_hadir = "SELECT COUNT(id) as total FROM absensi WHERE status = 'hadir' AND DATE(waktu) = CURDATE()";
    $stmt_hadir = $conn->prepare($query_hadir);
    $stmt_hadir->execute();
    $stats['total_hadir_today'] = (int)$stmt_hadir->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Tidak Hadir (Izin/Sakit) Hari Ini
    $query_tidakhadir = "SELECT COUNT(id) as total FROM absensi WHERE status IN ('izin', 'sakit') AND DATE(waktu) = CURDATE()";
    $stmt_tidakhadir = $conn->prepare($query_tidakhadir);
    $stmt_tidakhadir->execute();
    $stats['total_izin_sakit_today'] = (int)$stmt_tidakhadir->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Siswa (role 'user')
    $query_users = "SELECT COUNT(id) as total FROM users WHERE role = 'user'";
    $stmt_users = $conn->prepare($query_users);
    $stmt_users->execute();
    $stats['total_users'] = (int)$stmt_users->fetch(PDO::FETCH_ASSOC)['total'];

    // Alpha - tidak bisa dihitung akurat dengan skema saat ini.
    $stats['total_alpha_today'] = 0;
    
    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 