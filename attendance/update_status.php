<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->kelas_id) && !empty($data->status)) {
        // Untuk kompatibilitas dengan absensi_pbl, admin_id opsional
        // Jika tidak ada admin_id, kita asumsikan ini adalah request dari admin
        $admin_id = isset($data->admin_id) ? $data->admin_id : null;
        
        // Jika ada admin_id, verifikasi bahwa user adalah admin
        if ($admin_id) {
            $admin_check = "SELECT role FROM users WHERE id = :admin_id AND role = 'admin' LIMIT 1";
            $admin_stmt = $conn->prepare($admin_check);
            $admin_stmt->bindParam(':admin_id', $admin_id);
            $admin_stmt->execute();
            
            if ($admin_stmt->rowCount() == 0) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Hanya admin yang dapat mengubah status kelas'
                ));
                exit();
            }
        }
        
        // Validasi status yang diizinkan
        $allowed_status = ['active', 'inactive'];
        if (!in_array($data->status, $allowed_status)) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Status tidak valid. Gunakan "active" atau "inactive"'
            ));
            exit();
        }
        
        // Check if class exists
        $check_query = "SELECT id, nama_kelas FROM kelas WHERE id = :kelas_id LIMIT 1";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':kelas_id', $data->kelas_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan'
            ));
            exit();
        }
        
        $kelas_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update status kelas
        $query = "UPDATE kelas SET status = :status WHERE id = :kelas_id";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':status', $data->status);
        $stmt->bindParam(':kelas_id', $data->kelas_id);
        
        if ($stmt->execute()) {
            $status_text = ($data->status === 'active') ? 'aktif' : 'tidak aktif';
            echo json_encode(array(
                'status' => 'success',
                'message' => "Status kelas '{$kelas_data['nama_kelas']}' berhasil diubah menjadi {$status_text}",
                'data' => array(
                    'kelas_id' => $data->kelas_id,
                    'nama_kelas' => $kelas_data['nama_kelas'],
                    'status' => $data->status
                )
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Gagal mengubah status kelas'
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Data tidak lengkap. Diperlukan: kelas_id dan status'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Metode tidak diizinkan'
    ));
}
?> 