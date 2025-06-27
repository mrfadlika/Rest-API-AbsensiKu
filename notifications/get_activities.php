<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    // Mengambil aktivitas absensi pengguna
    $query_absensi = "SELECT 
                        'absensi' as type, 
                        u.nama as user_name, 
                        k.nama_kelas, 
                        a.status, 
                        a.waktu as timestamp 
                      FROM absensi a 
                      JOIN users u ON a.user_id = u.id 
                      JOIN kelas k ON a.kelas_id = k.id";

    // Mengambil aktivitas pembuatan kelas oleh admin
    $query_kelas = "SELECT 
                      'buat_kelas' as type, 
                      u.nama as user_name, 
                      k.nama_kelas, 
                      k.status, 
                      k.created_at as timestamp 
                    FROM kelas k 
                    JOIN users u ON k.created_by = u.id";

    // Menggabungkan kedua query
    $query = "($query_absensi) UNION ALL ($query_kelas) ORDER BY timestamp DESC LIMIT 50";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $activities_arr = array();
        $activities_arr['data'] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $message = '';
            if ($type === 'absensi') {
                $message = "$user_name telah mengambil absensi di kelas $nama_kelas dengan status $status.";
            } else if ($type === 'buat_kelas') {
                $message = "$user_name telah membuat kelas baru: $nama_kelas.";
            }

            $activity_item = array(
                "type" => $type,
                "message" => $message,
                "timestamp" => $timestamp
            );
            array_push($activities_arr['data'], $activity_item);
        }
        $activities_arr['status'] = 'success';
        echo json_encode($activities_arr);
    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Tidak ada aktivitas terbaru.',
            'data' => []
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ));
}
?> 