<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->nama_kelas) && !empty($data->tanggal) && !empty($data->created_by)) {
        $query = "INSERT INTO kelas (nama_kelas, tanggal, created_by, status) 
                 VALUES (:nama_kelas, :tanggal, :created_by, 'active')";
        
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':nama_kelas', $data->nama_kelas);
        $stmt->bindParam(':tanggal', $data->tanggal);
        $stmt->bindParam(':created_by', $data->created_by);
        
        if ($stmt->execute()) {
            $kelas_id = $conn->lastInsertId();
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Kelas berhasil dibuat',
                'data' => array(
                    'kelas_id' => $kelas_id,
                    'nama_kelas' => $data->nama_kelas,
                    'tanggal' => $data->tanggal
                )
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Gagal membuat kelas'
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