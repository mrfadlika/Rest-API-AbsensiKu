# Backend Sistem Absensi

Backend untuk sistem absensi menggunakan PHP Native dan MySQL.

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)

## Instalasi

1. Clone repository ini ke direktori web server Anda
2. Import file `database.sql` ke MySQL untuk membuat database dan tabelnya
3. Sesuaikan konfigurasi database di file `config/database.php`

## Struktur Database

Database terdiri dari 3 tabel utama:
- `users`: Menyimpan data pengguna (admin dan user biasa)
- `kelas`: Menyimpan data kelas untuk absensi
- `absensi`: Menyimpan data absensi siswa

## API Endpoints

### Autentikasi
- `POST /auth/login.php`: Login user
- `POST /auth/register.php`: Registrasi user baru

### Manajemen Kelas
- `POST /attendance/create.php`: Membuat kelas baru
- `POST /attendance/take_attendance.php`: Mengambil absensi
- `GET /attendance/report.php`: Melihat laporan absensi

## Default Admin Account
- Email: admin@admin.com
- Password: admin123

## Format Request & Response

### Login
Request:
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

Response Success:
```json
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "id": 1,
        "nama": "User Name",
        "email": "user@example.com",
        "role": "user"
    }
}
```

### Create Attendance
Request:
```json
{
    "nama_kelas": "Matematika A",
    "tanggal": "2024-03-20",
    "created_by": 1
}
```

Response Success:
```json
{
    "status": "success",
    "message": "Kelas berhasil dibuat",
    "data": {
        "kelas_id": 1,
        "nama_kelas": "Matematika A",
        "tanggal": "2024-03-20"
    }
}
```

### Take Attendance
Request:
```json
{
    "kelas_id": 1,
    "user_id": 2,
    "status": "hadir"
}
```

Response Success:
```json
{
    "status": "success",
    "message": "Absensi berhasil dicatat"
}
```

## Keamanan
- Semua password di-hash menggunakan algoritma bcrypt
- Menggunakan prepared statements untuk mencegah SQL injection
- Validasi input untuk semua request
- CORS enabled untuk integrasi dengan frontend

## Catatan
- Pastikan folder memiliki permission yang tepat untuk PHP
- Aktifkan error reporting di development, matikan di production
- Backup database secara berkala 