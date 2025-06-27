<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

try {
    // Ambil user_id dari parameter (opsional untuk kompatibilitas)
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    // Jika ada user_id, ambil role dari database
    if ($user_id) {
        $user_query = "SELECT role FROM users WHERE id = :user_id LIMIT 1";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
        
        if ($user_stmt->rowCount() > 0) {
            $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
            $user_role = $user_data['role'];
        } else {
            $user_role = 'user'; // Default role jika user tidak ditemukan
        }
    } else {
        // Jika tidak ada user_id, tampilkan semua kelas (untuk kompatibilitas dengan absensi_pbl)
        // Aplikasi frontend akan melakukan filtering sendiri
        $user_role = 'admin';
    }
    
    // Query berdasarkan role user
    if ($user_role === 'admin') {
        // Admin bisa melihat semua kelas (aktif dan tidak aktif)
        $query = "SELECT k.id, k.nama_kelas, k.tanggal, k.status, u.nama as created_by_name 
                  FROM kelas k
                  LEFT JOIN users u ON k.created_by = u.id
                  ORDER BY k.created_at DESC";
    } else {
        // User biasa hanya melihat kelas yang aktif
        $query = "SELECT k.id, k.nama_kelas, k.tanggal, k.status, u.nama as created_by_name 
                  FROM kelas k
                  LEFT JOIN users u ON k.created_by = u.id
                  WHERE k.status = 'active'
                  ORDER BY k.created_at DESC";
    }
    
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
        if ($user_id) {
            $kelasList['user_role'] = $user_role;
        }
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