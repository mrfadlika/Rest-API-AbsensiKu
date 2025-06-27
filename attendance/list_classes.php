<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    // Query ini sekarang selalu mengambil SEMUA kelas.
    // Pemfilteran status (aktif/tidak aktif) akan dilakukan di sisi frontend.
    $query = "SELECT k.id, k.nama_kelas, k.tanggal, k.status, u.nama as created_by_name 
              FROM kelas k
              LEFT JOIN users u ON k.created_by = u.id
              ORDER BY k.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $kelasList = array();
        $kelasList['data'] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $kelasItem = array(
                'id' => $id,
                'nama_kelas' => $nama_kelas,
                'tanggal' => $tanggal,
                'status' => $status,
                'created_by' => $created_by_name
            );
            
            array_push($kelasList['data'], $kelasItem);
        }
        
        $kelasList['status'] = 'success';
        echo json_encode($kelasList);

    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Tidak ada kelas yang ditemukan.',
            'data' => []
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Gagal mengambil data kelas: ' . $e->getMessage()
    ));
}
?> 