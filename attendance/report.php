<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : null;
    
    if ($kelas_id) {
        // Get class details
        $kelas_query = "SELECT k.*, u.nama as created_by_name 
                       FROM kelas k 
                       LEFT JOIN users u ON k.created_by = u.id 
                       WHERE k.id = :kelas_id";
        $kelas_stmt = $conn->prepare($kelas_query);
        $kelas_stmt->bindParam(':kelas_id', $kelas_id);
        $kelas_stmt->execute();
        
        if ($kelas_stmt->rowCount() > 0) {
            $kelas_data = $kelas_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get attendance details with GPS and photo
            $attendance_query = "SELECT a.*, u.nama, u.email 
                               FROM absensi a 
                               LEFT JOIN users u ON a.user_id = u.id 
                               WHERE a.kelas_id = :kelas_id 
                               ORDER BY a.waktu DESC";
            $attendance_stmt = $conn->prepare($attendance_query);
            $attendance_stmt->bindParam(':kelas_id', $kelas_id);
            $attendance_stmt->execute();
            
            $attendance_list = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate statistics
            $total_hadir = 0;
            $total_izin = 0;
            $total_sakit = 0;
            
            foreach ($attendance_list as $attendance) {
                switch ($attendance['status']) {
                    case 'hadir':
                        $total_hadir++;
                        break;
                    case 'izin':
                        $total_izin++;
                        break;
                    case 'sakit':
                        $total_sakit++;
                        break;
                }
            }
            
            echo json_encode(array(
                'status' => 'success',
                'data' => array(
                    'kelas' => $kelas_data,
                    'statistik' => array(
                        'total_hadir' => $total_hadir,
                        'total_izin' => $total_izin,
                        'total_sakit' => $total_sakit,
                        'total_siswa' => count($attendance_list)
                    ),
                    'detail_absensi' => $attendance_list
                )
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan'
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'ID Kelas diperlukan'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Metode tidak diizinkan'
    ));
}
?> 