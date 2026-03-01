# 🔧 Cara Memperbaiki Login - Sistem Parkir

## Masalah
Login gagal dengan pesan "Username atau password salah" meskipun sudah menggunakan kredensial yang benar.

## Solusi

### **Langkah 1: Pastikan Database Sudah Di-Import**

1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Cek apakah database `parkir_wisnu_db` sudah ada
3. Jika belum ada:
   - Klik "New" untuk buat database baru
   - Nama database: `parkir_wisnu_db`
   - Klik "Create"
4. Klik database `parkir_wisnu_db`
5. Klik tab "Import"
6. Pilih file `database.sql` dari folder `c:\xampp\htdocs\parkir\database.sql`
7. Klik "Go" untuk import

### **Langkah 2: Jalankan Script Fix Password**

1. Buka browser
2. Akses URL: **http://localhost/parkir/fix_database.php**
3. Script akan otomatis:
   - Cek koneksi database
   - Tampilkan struktur tabel
   - Update semua password dengan hash yang benar
   - Verifikasi password sudah cocok

### **Langkah 3: Login**

Setelah script selesai, gunakan kredensial berikut:

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Petugas | `petugas` | `petugas123` |
| Owner | `owner` | `owner123` |

Akses: **http://localhost/parkir**

---

## Jika Masih Error

### Error: "Unknown database 'parkir_wisnu_db'"
- Database belum dibuat
- Ikuti Langkah 1 di atas

### Error: "Table 'tb_user' doesn't exist"
- Tabel belum di-import
- Import file `database.sql` di phpMyAdmin (Langkah 1)

### Error: "Column 'id' not found"
- Struktur tabel tidak sesuai
- Drop database `parkir_wisnu_db` yang lama
- Import ulang file `database.sql`

### Login masih gagal setelah fix
1. Buka phpMyAdmin
2. Pilih database `parkir_wisnu_db`
3. Klik tabel `tb_user`
4. Klik tab "Browse"
5. Cek apakah ada 3 user (admin, petugas, owner)
6. Jika tidak ada, jalankan SQL berikut di tab "SQL":

```sql
-- Hapus data lama jika ada
TRUNCATE TABLE tb_user;

-- Insert user baru
INSERT INTO tb_user (user, nama_lengkap, username, password, role) VALUES
('admin', 'Administrator', 'admin', 'admin123', 'admin'),
('petugas', 'Petugas Parkir', 'petugas', 'petugas123', 'petugas'),
('owner', 'Owner Parkir', 'owner', 'owner123', 'owner');
```

7. Setelah itu, jalankan lagi: http://localhost/parkir/fix_database.php

---

## Troubleshooting Cepat

**XAMPP tidak running?**
- Buka XAMPP Control Panel
- Start Apache dan MySQL

**Port 80 sudah digunakan?**
- Stop aplikasi lain yang menggunakan port 80
- Atau ubah port Apache di XAMPP

**File tidak ditemukan?**
- Pastikan folder ada di: `c:\xampp\htdocs\parkir`
- Cek semua file sudah ter-copy dengan benar

---

## Kontak Jika Masih Bermasalah

Jika masih ada error, screenshot error yang muncul dan beri tahu saya:
1. Error message yang muncul
2. Halaman mana yang error (login, fix_database, dll)
3. Apakah database sudah di-import atau belum

---

**Semoga berhasil! 🚀**
