<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->email) && !empty($data->password)) {
        $query = "SELECT id, nama, email, password, role FROM users WHERE email = :email LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $data->email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($data->password, $row['password'])) {
                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Login berhasil',
                    'data' => array(
                        'id' => $row['id'],
                        'nama' => $row['nama'],
                        'email' => $row['email'],
                        'role' => $row['role']
                    )
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Password salah'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Email tidak ditemukan'
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