<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

    $query = "SELECT 
                a.id as attendance_id, 
                a.status, 
                a.waktu, 
                k.id as kelas_id, 
                k.nama_kelas, 
                k.tanggal, 
                u.id as user_id, 
                u.nama as user_name 
              FROM absensi a 
              JOIN kelas k ON a.kelas_id = k.id 
              JOIN users u ON a.user_id = u.id";

    if ($user_id !== null) {
        $query .= " WHERE a.user_id = :user_id";
    }

    $query .= " ORDER BY a.waktu DESC";

    $stmt = $conn->prepare($query);

    if ($user_id !== null) {
        $stmt->bindParam(':user_id', $user_id);
    }

    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $history_arr = array();
        $history_arr['data'] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $history_item = array(
                "attendance_id" => $attendance_id,
                "status" => $status,
                "waktu" => $waktu,
                "kelas_id" => $kelas_id,
                "nama_kelas" => $nama_kelas,
                "tanggal" => $tanggal,
                "user_id" => $user_id,
                "user_name" => $user_name
            );
            array_push($history_arr['data'], $history_item);
        }
        $history_arr['status'] = 'success';
        echo json_encode($history_arr);
    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Tidak ada riwayat absensi ditemukan.',
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