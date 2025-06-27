<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->kelas_id) && !empty($data->status)) {
        // Validasi status
        $allowed_statuses = ['active', 'inactive'];
        if (!in_array($data->status, $allowed_statuses)) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Status tidak valid. Gunakan "active" atau "inactive".'
            ));
            exit();
        }

        try {
            $query = "UPDATE kelas SET status = :status WHERE id = :kelas_id";
            $stmt = $conn->prepare($query);
            
            $stmt->bindParam(':status', $data->status);
            $stmt->bindParam(':kelas_id', $data->kelas_id);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo json_encode(array(
                        'status' => 'success',
                        'message' => 'Status kelas berhasil diperbarui.'
                    ));
                } else {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Kelas tidak ditemukan atau tidak ada perubahan status.'
                    ));
                }
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui status kelas.'
                ));
            }
        } catch (PDOException $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Data tidak lengkap. Diperlukan kelas_id dan status.'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Metode tidak diizinkan.'
    ));
}
?> 