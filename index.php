<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once 'config/database.php';

try {
    $query = "SELECT a.id as absensi_id, k.nama_kelas, u.nama as nama_user, a.status, a.waktu 
              FROM absensi a
              LEFT JOIN kelas k ON a.kelas_id = k.id
              LEFT JOIN users u ON a.user_id = u.id
              ORDER BY a.waktu DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $absensi_arr = array();
        $absensi_arr['data'] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $absensi_item = array(
                'id' => $absensi_id,
                'nama_kelas' => $nama_kelas,
                'nama_user' => $nama_user,
                'status' => $status,
                'waktu' => $waktu
            );
            
            array_push($absensi_arr['data'], $absensi_item);
        }
        
        $absensi_arr['status'] = 'success';
        echo json_encode($absensi_arr);
    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Tidak ada data absensi yang ditemukan.',
            'data' => []
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Gagal mengambil data: ' . $e->getMessage()
    ));
}
?> 