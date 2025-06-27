<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->kelas_id) && !empty($data->user_id) && !empty($data->status)) {
        // Check if class exists and is active
        $check_query = "SELECT id FROM kelas WHERE id = :kelas_id AND status = 'active' LIMIT 1";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':kelas_id', $data->kelas_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan atau tidak aktif'
            ));
            exit();
        }
        
        // Check if user already took attendance
        $check_attendance = "SELECT id FROM absensi WHERE kelas_id = :kelas_id AND user_id = :user_id LIMIT 1";
        $check_att_stmt = $conn->prepare($check_attendance);
        $check_att_stmt->bindParam(':kelas_id', $data->kelas_id);
        $check_att_stmt->bindParam(':user_id', $data->user_id);
        $check_att_stmt->execute();
        
        if ($check_att_stmt->rowCount() > 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Anda sudah melakukan absensi untuk kelas ini'
            ));
            exit();
        }
        
        // Insert attendance
        $query = "INSERT INTO absensi (kelas_id, user_id, status, waktu) VALUES (:kelas_id, :user_id, :status, NOW())";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':kelas_id', $data->kelas_id);
        $stmt->bindParam(':user_id', $data->user_id);
        $stmt->bindParam(':status', $data->status);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Absensi berhasil dicatat'
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Gagal mencatat absensi'
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Data tidak lengkap'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Metode tidak diizinkan'
    ));
}
?> 