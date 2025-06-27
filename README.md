PluginError: Failed to resolve plugin for module "expo-camera" relative to "D:\CodeFiles\absensi_pbl". Do you have node modules installed?# Backend Absensi API

Sistem backend untuk aplikasi absensi dengan manajemen kelas aktif/inaktif. **Kompatibel dengan aplikasi absensi_pbl**.

## Database Schema

### Tabel Users
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `nama` (VARCHAR(100), NOT NULL)
- `email` (VARCHAR(100), NOT NULL, UNIQUE)
- `password` (VARCHAR(255), NOT NULL)
- `role` (ENUM('admin', 'user'), DEFAULT 'user')
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### Tabel Kelas
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `nama_kelas` (VARCHAR(100), NOT NULL)
- `tanggal` (DATE, NOT NULL)
- `created_by` (INT, FOREIGN KEY ke users.id)
- `status` (ENUM('active', 'inactive'), DEFAULT 'active')
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### Tabel Absensi
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `kelas_id` (INT, FOREIGN KEY ke kelas.id)
- `user_id` (INT, FOREIGN KEY ke users.id)
- `status` (ENUM('hadir', 'izin', 'sakit'), NOT NULL)
- `waktu` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

## API Endpoints

### Authentication
- `POST /auth/login.php` - Login user
- `POST /auth/register.php` - Register user baru

### Kelas Management (Kompatibel dengan absensi_pbl)
- `GET /attendance/list_classes.php` - Daftar semua kelas (untuk kompatibilitas)
- `GET /attendance/list_classes.php?user_id={id}` - Daftar kelas berdasarkan role user
- `POST /attendance/take_attendance.php` - Ambil absensi (hanya untuk kelas aktif)

### Kelas Management (Admin)
- `GET /attendance/admin_classes.php` - Daftar semua kelas untuk admin (tanpa verifikasi)
- `GET /attendance/admin_classes.php?admin_id={id}` - Daftar semua kelas dengan verifikasi admin
- `POST /attendance/update_status.php` - Update status kelas (aktif/inaktif)
- `POST /attendance/create.php` - Buat kelas baru

### Reports
- `GET /attendance/history.php` - Riwayat absensi
- `GET /attendance/report.php` - Laporan absensi

## Fitur Keamanan

### Manajemen Status Kelas
1. **User Biasa**: Hanya dapat melihat dan melakukan absensi pada kelas dengan status 'active'
2. **Admin**: Dapat melihat semua kelas (aktif dan tidak aktif) dan mengubah status kelas

### Validasi
- Semua endpoint menggunakan prepared statements untuk mencegah SQL injection
- Password di-hash menggunakan bcrypt
- Validasi role untuk akses admin (opsional untuk kompatibilitas)
- Validasi status kelas sebelum absensi

## Kompatibilitas dengan absensi_pbl

Backend ini telah disesuaikan agar kompatibel dengan aplikasi `absensi_pbl`:

### Endpoint yang Kompatibel
- `GET /attendance/list_classes.php` - Tanpa parameter, mengembalikan semua kelas
- `POST /attendance/update_status.php` - Tanpa admin_id, untuk kompatibilitas
- `GET /attendance/admin_classes.php` - Tanpa admin_id, untuk kompatibilitas

### Pola Penggunaan
Aplikasi `absensi_pbl` menggunakan endpoint yang sama dengan pola:
```javascript
const API_BASE_URL = 'https://belajar.novatix.site';

// Mengambil daftar kelas
fetch(`${API_BASE_URL}/attendance/list_classes.php`)

// Update status kelas
fetch(`${API_BASE_URL}/attendance/update_status.php`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ kelas_id: 1, status: 'inactive' })
})
```

## Cara Penggunaan

### Untuk User Biasa
```javascript
// Ambil daftar kelas (hanya yang aktif)
fetch('/attendance/list_classes.php?user_id=123')
  .then(response => response.json())
  .then(data => console.log(data));

// Ambil absensi (hanya untuk kelas aktif)
fetch('/attendance/take_attendance.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    kelas_id: 1,
    user_id: 123,
    status: 'hadir'
  })
});
```

### Untuk Admin (Kompatibel dengan absensi_pbl)
```javascript
// Ambil semua kelas untuk manajemen
fetch('/attendance/admin_classes.php')
  .then(response => response.json())
  .then(data => console.log(data));

// Update status kelas (tanpa admin_id)
fetch('/attendance/update_status.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    kelas_id: 1,
    status: 'inactive'
  })
});

// Update status kelas (dengan admin_id untuk keamanan tambahan)
fetch('/attendance/update_status.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    kelas_id: 1,
    status: 'inactive',
    admin_id: 1
  })
});
```

## Response Format

### Success Response
```json
{
  "status": "success",
  "message": "Pesan sukses",
  "data": [...],
  "user_role": "admin" // untuk list_classes.php dengan user_id
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Pesan error"
}
```

## Default Admin Account
- Email: admin@admin.com
- Password: admin123
- Role: admin

## Setup Database
1. Import file `database.sql` ke MySQL/MariaDB
2. Konfigurasi koneksi database di `config/database.php`
3. Pastikan web server dapat mengakses direktori ini

## Keamanan Tambahan
- Semua endpoint menggunakan CORS headers
- Validasi input di setiap endpoint
- Error handling yang konsisten
- Logging untuk debugging (dapat ditambahkan sesuai kebutuhan)
- Kompatibilitas dengan aplikasi existing tanpa mengorbankan keamanan 