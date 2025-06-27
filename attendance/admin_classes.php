<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    // Ambil admin_id dari parameter untuk verifikasi (opsional untuk kompatibilitas)
    $admin_id = isset($_GET['admin_id']) ? $_GET['admin_id'] : null;
    
    // Jika ada admin_id, verifikasi bahwa user adalah admin
    if ($admin_id) {
        $admin_check = "SELECT role FROM users WHERE id = :admin_id AND role = 'admin' LIMIT 1";
        $admin_stmt = $conn->prepare($admin_check);
        $admin_stmt->bindParam(':admin_id', $admin_id);
        $admin_stmt->execute();
        
        if ($admin_stmt->rowCount() == 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Hanya admin yang dapat mengakses data ini'
            ));
            exit();
        }
    }
    
    // Query untuk mendapatkan semua kelas dengan informasi lengkap
    $query = "SELECT k.id, k.nama_kelas, k.tanggal, k.status, k.created_at,
                     u.nama as created_by_name,
                     (SELECT COUNT(*) FROM absensi WHERE kelas_id = k.id) as total_absensi
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
                'created_by' => $created_by_name,
                'created_at' => $created_at,
                'total_absensi' => $total_absensi,
                'status_text' => ($status === 'active') ? 'Aktif' : 'Tidak Aktif'
            );
            
            array_push($kelasList['data'], $kelasItem);
        }
        
        $kelasList['status'] = 'success';
        $kelasList['total_kelas'] = $num;
        echo json_encode($kelasList);

    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Tidak ada kelas yang ditemukan.',
            'data' => [],
            'total_kelas' => 0
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Gagal mengambil data kelas: ' . $e->getMessage()
    ));
}
?> 