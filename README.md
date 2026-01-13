# Logbook RSIG

Aplikasi Logbook RSIG adalah sistem pencatatan kegiatan harian pegawai berbasis web.

## Panduan Pengembang & Penjelasan Kode

Dokumen ini memberikan penjelasan rinci mengenai arsitektur aplikasi Logbook RSIG, struktur kode, dan logika untuk memfasilitasi pengembangan lebih lanjut.

### 1. Ikhtisar Arsitektur

Aplikasi ini mengikuti pola desain **MVC (Model-View-Controller)**, standar untuk aplikasi web PHP modern.

*   **Model**: Mengelola data dan logika bisnis (Interaksi Database).
*   **View**: Menangani lapisan presentasi (HTML/CSS).
*   **Controller**: Menangani input pengguna, berinteraksi dengan Model, dan merender View.

#### Alur Permintaan (Request Flow)
1.  **Titik Masuk**: Semua permintaan masuk melalui `public/index.php`.
2.  **Routing (`App.php`)**: Mem-parsing URL (contoh: `/controller/method/params`) dan menginstansiasi Controller yang sesuai.
3.  **Controller**: Menjalankan method yang diminta (contoh: `index()`, `add()`).
4.  **Model**: Controller dapat meminta data dari Model.
5.  **View**: Controller memuat file View dan mengirimkan data untuk ditampilkan.

### 2. Struktur Direktori

```
logbook-rsig/
├── app/
│   ├── config/         # File konfigurasi (DB, Konstanta)
│   ├── controllers/    # Controller (Admin, Employee, Management, dll.)
│   ├── core/           # Kelas inti (App, Controller, Database, Model)
│   ├── models/         # Model database (User, Logbook, Unit, dll.)
│   └── views/          # Template tampilan (HTML/PHP)
├── assets/             # Aset statis (CSS, Gambar)
├── public/             # Root web (index.php, .htaccess)
└── vendor/             # Dependensi Composer (PhpSpreadsheet, dll.)
```

### 3. Komponen Inti (`app/core/`)

#### `App.php` (Router)
*   **Peran**: Mem-parsing URL dan mengarahkan lalu lintas.
*   **Logika**:
    *   Controller default: `AuthController`.
    *   Method default: `index`.
    *   Format URL: `domain.com/[Controller]/[Method]/[Param1]/[Param2]`
    *   Contoh: `domain.com/employee/logbook` -> Menginstansiasi `EmployeeController`, memanggil method `logbook()`.

#### `Controller.php` (Base Controller)
*   **Peran**: Kelas induk untuk semua controller.
*   **Method**:
    *   `model($model)`: Menginstansiasi dan mengembalikan objek model.
    *   `view($view, $data)`: Memuat file view dan mengirimkan data ke dalamnya.

#### `Database.php` (DB Wrapper)
*   **Peran**: Menangani koneksi database menggunakan PDO.
*   **Fitur**:
    *   Prepared statements (keamanan terhadap SQL injection).
    *   Method: `query()`, `bind()`, `execute()`, `resultSet()` (ambil semua), `single()` (ambil satu).

### 4. Fitur Utama & Implementasi

#### Autentikasi (`AuthController.php`)
*   **Login**: Memverifikasi username dan password (di-hash dengan `password_verify`).
*   **Sesi**: Menyimpan `user_id`, `role`, dan `user_name` di `$_SESSION`.
*   **Kontrol Akses**: Controller memeriksa `$_SESSION['role']` di method `__construct` mereka untuk membatasi akses.

#### Sistem Logbook
*   **Model**:
    *   `Logbook`: Mengelola header logbook (tanggal, user, status) dan detail (kegiatan).
    *   `ActivityType`: Data master untuk jenis kegiatan.
*   **Alur Kerja**:
    1.  **Draft**: Pegawai menginput kegiatan.
    2.  **Submitted**: Pegawai mengirim logbook. Status menjadi `submitted`. Terkunci untuk diedit.
    3.  **Validation**: Kepala Unit melihat logbook yang dikirim.
    4.  **Approval/Rejection**: Kepala menyetujui atau menolak.
    5.  **Revision**: Kepala meminta revisi. Status menjadi `revision`. Pegawai dapat mengedit dan mengirim ulang.

#### Pelaporan (Reporting)
*   **Dashboard Manajemen**:
    *   Menggunakan `Chart.js` di `views/management/charts.php`.
    *   Data diagregasi di model `Logbook` (`getLogbookCountsByUnit`).
*   **Ekspor Excel**:
    *   Menggunakan library `PhpOffice\PhpSpreadsheet`.
    *   Logika ada di `EmployeeController::export()`.

#### UI/UX
*   **Framework**: AdminLTE 3 (berbasis Bootstrap 4).
*   **Styling Kustom**: `assets/css/modern.css` menimpa gaya default untuk tampilan modern (Font Inter, bayangan lembut, sudut membulat).
*   **Layout**: `header.php` dan `footer.php` di `views/layouts/` memastikan konsistensi.

### 5. Cara Mengembangkan (Panduan Pengembangan)

#### Menambahkan Halaman Baru
1.  **Controller**: Buat method baru di Controller yang relevan (contoh: `public function halaman_baru()`).
2.  **View**: Buat file baru di `app/views/` (contoh: `halaman_baru.php`).
3.  **Rute**: Akses melalui `domain.com/controller/halaman_baru`.

#### Menambahkan Tabel Database Baru
1.  **Database**: Buat tabel di MySQL.
2.  **Model**: Buat kelas baru di `app/models/` yang mewarisi `Model`.
3.  **Method**: Tambahkan method untuk operasi CRUD (`getAll`, `add`, `update`, `delete`).

#### Memodifikasi Kontrol Akses
*   Saat ini, kontrol akses bersifat **Dinamis**:
    *   **Database**: Tabel `menus` dan `role_access` menyimpan konfigurasi.
    *   **Sidebar**: `app/views/layouts/header.php` memuat menu berdasarkan peran dari database.
    *   **Admin**: Halaman `admin/access_control` digunakan untuk mengatur hak akses.

#### Kustomisasi UI
*   Edit `assets/css/modern.css` untuk mengubah warna, font, atau gaya komponen.
*   Sistem menggunakan Variabel CSS (contoh: `--primary-color`) untuk kemudahan tema.

### 6. Masalah Umum & Debugging
*   **"Controller not found"**: Periksa kapitalisasi nama file (harus sesuai nama kelas) dan URL.
*   **"Method not found"**: Pastikan method di controller bersifat `public`.
*   **Error Database**: Periksa `app/config/config.php` untuk kredensial DB.
*   **Aset 404**: Pastikan `URLROOT` di `config.php` diatur dengan benar.

---
**Catatan**: Selalu backup database dan file Anda sebelum melakukan perubahan signifikan.
