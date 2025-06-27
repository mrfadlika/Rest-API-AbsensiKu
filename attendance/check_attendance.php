<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    $kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : null;

    if (!$user_id || !$kelas_id) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'user_id dan kelas_id diperlukan'
        ));
        exit();
    }

    // Check if user already took attendance for this class
    $query = "SELECT id, status, waktu FROM absensi WHERE user_id = :user_id AND kelas_id = :kelas_id LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':kelas_id', $kelas_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(array(
            'status' => 'success',
            'data' => array(
                'has_attendance' => true,
                'attendance_status' => $attendance['status'],
                'attendance_time' => $attendance['waktu']
            )
        ));
    } else {
        echo json_encode(array(
            'status' => 'success',
            'data' => array(
                'has_attendance' => false
            )
        ));
    }

} catch (PDOException $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ));
}
?> 