<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if file was uploaded
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Tidak ada file foto yang diupload atau terjadi error'
        ));
        exit();
    }
    
    $file = $_FILES['photo'];
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Tipe file tidak diizinkan. Gunakan JPG, JPEG, atau PNG'
        ));
        exit();
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Ukuran file terlalu besar. Maksimal 5MB'
        ));
        exit();
    }
    
    // Create upload directory if not exists
    $upload_dir = '../uploads/photos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'attendance_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Generate public URL
        $photo_url = 'https://belajar.novatix.site/backend_absensi/uploads/photos/' . $filename;
        
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Foto berhasil diupload',
            'data' => array(
                'photo_url' => $photo_url,
                'filename' => $filename,
                'file_size' => $file['size'],
                'file_type' => $file['type']
            )
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Gagal menyimpan file foto'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Metode tidak diizinkan'
    ));
}
?> 