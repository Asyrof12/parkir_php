# Sistem Parkir

Sistem manajemen parkir berbasis web dengan PHP yang memiliki 3 role pengguna dengan fitur berbeda.

## 📋 Fitur

### 👨‍💼 Admin
- ✅ Login/Logout
- 👥 CRUD User (Create, Read, Update, Delete)
- 💰 CRUD Tarif Parkir
- 🅿️ CRUD Area Parkir
- 🚗 CRUD Kendaraan
- 📊 Akses Log Aktivitas

### 👮 Petugas
- ✅ Login/Logout
- 🎫 Cetak Struk Parkir (Input kendaraan masuk)
- 💳 Transaksi (Input kendaraan keluar & hitung biaya)

### 🏢 Owner
- ✅ Login/Logout
- 📈 Rekap Transaksi berdasarkan periode waktu

## 🛠️ Teknologi

- **Backend**: PHP (Native)
- **Database**: MySQL
- **Frontend**: HTML, CSS (Custom)
- **Server**: XAMPP (Apache + MySQL)

## 📦 Instalasi

### 1. Persiapan

Pastikan sudah terinstall:
- XAMPP (atau stack PHP + MySQL lainnya)
- Browser modern (Chrome, Firefox, dll)

### 2. Clone/Copy Project

Copy folder `parkir` ke dalam folder `htdocs` XAMPP Anda:
```
c:\xampp\htdocs\parkir
```

### 3. Import Database

1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru bernama `parkir_wisnu_db`
3. Import file `database.sql` yang ada di root project
4. Atau jalankan SQL dari file tersebut di tab SQL di phpMyAdmin

### 4. Konfigurasi Database (Opsional)

Jika menggunakan username/password MySQL yang berbeda, edit file:
```php
config/database.php
```

Ubah bagian:
```php
private $username = "root";      // Sesuaikan
private $password = "";          // Sesuaikan
```

### 5. Jalankan Aplikasi

Buka browser dan akses:
```
http://localhost/parkir
```

## 👤 Default Login

Setelah import database, gunakan kredensial berikut:

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | 12345678 |
| Petugas | petugas | 12345678 |
| Owner | owner | 12345678 |

## 📁 Struktur Folder

```
parkir/
├── admin/                  # Modul Admin
│   ├── dashboard.php
│   ├── users/             # CRUD Users
│   ├── tarif/             # CRUD Tarif
│   ├── area/              # CRUD Area Parkir
│   ├── kendaraan/         # CRUD Kendaraan
│   └── log/               # Log Aktivitas
├── petugas/               # Modul Petugas
│   ├── dashboard.php
│   ├── cetak_struk.php    # Input kendaraan masuk
│   └── transaksi.php      # Input kendaraan keluar
├── owner/                 # Modul Owner
│   ├── dashboard.php
│   └── rekap.php          # Rekap transaksi
├── auth/                  # Autentikasi
│   ├── login.php
│   └── logout.php
├── config/                # Konfigurasi
│   ├── database.php       # Koneksi database
│   └── config.php         # Config aplikasi
├── includes/              # File include
│   ├── functions.php      # Helper functions
│   ├── header.php         # Header layout
│   └── footer.php         # Footer layout
├── middleware/            # Middleware
│   └── auth_check.php     # Role-based access control
├── assets/                # Static files
│   └── css/
│       └── style.css      # CSS styling
├── database.sql           # SQL database
├── index.php             # Landing page
└── README.md             # Dokumentasi
```

## 🗄️ Database Schema

### Tabel Utama

1. **tb_user** - Data user dengan role (admin/petugas/owner)
2. **tb_area_parkir** - Data area parkir dengan kapasitas
3. **tb_tarif** - Tarif parkir per jenis kendaraan
4. **tb_kendaraan** - Data kendaraan terdaftar
5. **tb_transaksi** - Data transaksi parkir masuk/keluar
6. **tb_log_aktivitas** - Log aktivitas user

### Relasi

- `tb_kendaraan.id_user` → `tb_user.id`
- `tb_transaksi.id_user` → `tb_user.id`
- `tb_transaksi.id_area` → `tb_area_parkir.id`
- `tb_transaksi.id_tarif` → `tb_tarif.id`
- `tb_log_aktivitas.id_user` → `tb_user.id`

## 🚀 Cara Penggunaan

### Sebagai Admin

1. Login dengan username `admin`
2. Kelola master data:
   - Tambah/Edit/Hapus User
   - Atur Tarif Parkir
   - Atur Area Parkir
   - Data Kendaraan Terdaftar
3. Pantau Log Aktivitas semua user

### Sebagai Petugas

1. Login dengan username `petugas`
2. **Input Kendaraan Masuk**:
   - Pilih menu "Cetak Struk"
   - Masukkan nomor polisi
   - Pilih jenis kendaraan & area parkir
   - Cetak struk untuk diberikan ke customer
3. **Input Kendaraan Keluar**:
   - Pilih menu "Transaksi"
   - Masukkan nomor polisi
   - Sistem otomatis hitung durasi & biaya
   - Proses pembayaran

### Sebagai Owner

1. Login dengan username `owner`
2. Pilih menu "Rekap Transaksi"
3. Pilih periode tanggal
4. Lihat laporan:
   - Total transaksi
   - Total pendapatan
   - Detail per transaksi

## 💡 Fitur Unggulan

- ✨ **Role-Based Access Control**: Setiap user hanya akses menu sesuai rolenya
- 🎨 **Modern UI**: Design gradient & responsive
- 📝 **Activity Logging**: Semua aktivitas tercatat
- 💰 **Auto Calculate**: Perhitungan biaya parkir otomatis
- 🖨️ **Print Receipt**: Struk parkir bisa dicetak
- 📊 **Reporting**: Laporan transaksi dengan filter tanggal

## 🔒 Keamanan

- Password di-hash menggunakan `password_hash()` PHP
- Middleware untuk role-based access control
- Prepared statements untuk mencegah SQL injection
- Session management untuk autentikasi

## 📞 Support

Jika ada pertanyaan atau issues, silakan:
- Cek dokumentasi di README ini
- Review kode di folder yang sesuai
- Cek file `database.sql` untuk struktur database

## 📝 License

Project ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

---

**Happy Coding! 🚀**
