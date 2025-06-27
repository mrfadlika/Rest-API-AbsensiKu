-- Update schema untuk fitur GPS dan Foto
-- Jalankan query ini untuk menambah kolom baru ke tabel absensi

ALTER TABLE absensi 
ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER waktu,
ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude,
ADD COLUMN photo_url VARCHAR(255) NULL AFTER longitude,
ADD COLUMN attendance_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER photo_url;

-- Index untuk optimasi query berdasarkan koordinat
CREATE INDEX idx_absensi_coordinates ON absensi(latitude, longitude);
CREATE INDEX idx_absensi_time ON absensi(attendance_time);

-- Tabel untuk menyimpan informasi lokasi kelas (opsional)
CREATE TABLE IF NOT EXISTS kelas_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kelas_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_meters INT DEFAULT 100,
    location_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

-- Index untuk optimasi query lokasi kelas
CREATE INDEX idx_kelas_locations_kelas_id ON kelas_locations(kelas_id); 