<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->nama) && !empty($data->email) && !empty($data->password)) {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':email', $data->email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Email sudah terdaftar'
            ));
            exit();
        }
        
        // Hash password
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
        
        // Selalu set role sebagai 'admin' untuk pendaftaran melalui endpoint ini
        $role = 'admin';
        
        $query = "INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':nama', $data->nama);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Registrasi berhasil'
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Registrasi gagal'
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