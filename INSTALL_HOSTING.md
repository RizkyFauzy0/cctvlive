# Panduan Instalasi di Hosting dan aaPanel

Panduan lengkap untuk menginstal Live CCTV Manager di hosting shared dan aaPanel.

---

## 📦 Instalasi di Shared Hosting

### Persyaratan Minimum

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Akses cPanel atau File Manager
- phpMyAdmin atau MySQL Database Manager

### Langkah-langkah Instalasi

#### 1. Upload File ke Hosting

**Melalui cPanel File Manager:**

1. Login ke cPanel
2. Buka **File Manager**
3. Navigasi ke folder `public_html` (atau folder domain Anda)
4. Upload semua file proyek:
   - Bisa upload sebagai ZIP lalu extract
   - Atau upload file satu per satu

**Melalui FTP (FileZilla):**

1. Buka FileZilla atau FTP client lainnya
2. Koneksi ke hosting menggunakan kredensial FTP
3. Upload semua file ke folder `public_html` atau `www`

**Struktur folder setelah upload:**
```
public_html/
├── config/
├── api/
├── assets/
├── includes/
├── index.php
├── admin.php
├── view.php
├── database.sql
└── ...
```

#### 2. Buat Database MySQL

**Melalui cPanel:**

1. Login ke cPanel
2. Cari dan buka **MySQL Databases**
3. Pada bagian "Create New Database":
   - Database Name: `cctvlive` (atau nama lain)
   - Klik **Create Database**
4. Scroll ke bawah ke bagian "MySQL Users"
5. Buat user baru:
   - Username: `cctvuser` (atau nama lain)
   - Password: (buat password yang kuat)
   - Klik **Create User**
6. Scroll ke "Add User to Database"
   - Pilih user yang baru dibuat
   - Pilih database yang baru dibuat
   - Klik **Add**
7. Pada halaman privileges, pilih **ALL PRIVILEGES**
8. Klik **Make Changes**

**Catat informasi berikut:**
- Database Host: `localhost` (biasanya)
- Database Name: `namauser_cctvlive` (dengan prefix user)
- Database Username: `namauser_cctvuser`
- Database Password: (yang Anda buat)

#### 3. Import Database Schema

**Melalui phpMyAdmin:**

1. Buka **phpMyAdmin** dari cPanel
2. Pilih database yang baru dibuat di panel kiri
3. Klik tab **Import**
4. Klik **Choose File** dan pilih `database.sql`
5. Scroll ke bawah dan klik **Go**
6. Tunggu hingga proses selesai
7. Verifikasi: Anda harus melihat tabel `users` dan `cameras`

#### 4. Konfigurasi Database

1. Buka File Manager dan navigasi ke folder `config`
2. Edit file `database.php`
3. Update kredensial database:

```php
<?php
// Ganti dengan informasi database Anda
define('DB_HOST', 'localhost');
define('DB_NAME', 'namauser_cctvlive');  // Ganti dengan nama database
define('DB_USER', 'namauser_cctvuser');   // Ganti dengan username database
define('DB_PASS', 'password_anda');        // Ganti dengan password database
define('DB_CHARSET', 'utf8mb4');

// Sisanya tetap sama
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

function generateStreamKey() {
    return bin2hex(random_bytes(16));
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . "://" . $host . $script;
}
```

4. **Simpan** file

#### 5. Set Permission File (Opsional)

Untuk keamanan, set permission yang tepat:

1. File PHP: `644`
2. Folder: `755`
3. `config/database.php`: `640` (lebih aman)

Melalui File Manager:
- Klik kanan file → Change Permissions → Set ke 644 atau 755

#### 6. Akses Aplikasi

1. Buka browser
2. Akses domain Anda:
   ```
   http://namadomain.com
   ```
3. Jika berhasil, Anda akan melihat halaman home

#### 7. Login Admin

1. Akses admin panel:
   ```
   http://namadomain.com/admin.php
   ```
2. Login dengan kredensial default:
   - **Username:** `admin`
   - **Password:** `admin123`

⚠️ **PENTING:** Segera ganti password default!

### Troubleshooting Shared Hosting

#### Error: "Database connection failed"

**Solusi:**
1. Periksa kredensial di `config/database.php`
2. Pastikan database host adalah `localhost`
3. Cek apakah user memiliki privileges pada database
4. Verifikasi nama database include prefix user (misal: `user123_cctvlive`)

#### Error: "Internal Server Error" atau "500"

**Solusi:**
1. Cek PHP version di cPanel (minimal PHP 7.4)
2. Pastikan `.htaccess` terupload dengan benar
3. Cek error log di cPanel → Error Logs
4. Pastikan `mod_rewrite` enabled (biasanya sudah default)

#### File `.htaccess` tidak bekerja

**Solusi:**
1. Pastikan file `.htaccess` ada di root folder
2. Verifikasi isi file tidak corrupt
3. Contact hosting provider untuk enable `mod_rewrite`

#### Halaman blank/putih

**Solusi:**
1. Enable error reporting sementara
2. Edit `config/database.php`, tambahkan di awal:
   ```php
   <?php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
3. Refresh halaman untuk lihat error
4. Fix error yang muncul
5. **Jangan lupa** disable error reporting setelah selesai

---

## 🖥️ Instalasi di aaPanel

aaPanel adalah control panel hosting yang populer dan gratis. Berikut cara install di aaPanel.

### Persyaratan

- VPS/Server dengan aaPanel terinstall
- Akses ke aaPanel dashboard
- Domain atau subdomain (opsional)

### Langkah-langkah Instalasi

#### 1. Install Stack LNMP/LAMP

Pastikan komponen berikut terinstall di aaPanel:

1. Login ke aaPanel dashboard
2. Buka **App Store**
3. Install komponen berikut (jika belum):
   - **Nginx** atau **Apache** (pilih salah satu)
   - **MySQL 5.7+** atau **MariaDB**
   - **PHP 7.4+** atau **PHP 8.0+**
   - **phpMyAdmin** (untuk manage database)

**Cara install:**
- Cari nama software di App Store
- Klik **Install**
- Tunggu proses instalasi selesai

#### 2. Install PHP Extensions

1. Buka **App Store** → **Installed**
2. Cari **PHP** yang sudah terinstall
3. Klik **Settings** (ikon gear)
4. Buka tab **Install Extensions**
5. Install extensions berikut:
   - ✅ `pdo_mysql` (biasanya sudah ada)
   - ✅ `mysqli`
   - ✅ `openssl` (untuk random_bytes)
   - ✅ `fileinfo`

#### 3. Buat Website

1. Buka menu **Website**
2. Klik **Add Site**
3. Isi form:
   - **Domain:** `cctv.namadomain.com` atau `namadomain.com`
   - **Root Directory:** Biarkan default atau set custom
   - **PHP Version:** Pilih PHP 7.4 atau 8.0+
   - **Database:** ☑️ Centang "Create database"
   - **FTP:** (opsional)
4. Klik **Submit**

**Catat informasi database yang digenerate:**
- Database Name
- Database Username
- Database Password

#### 4. Upload File Aplikasi

**Melalui aaPanel File Manager:**

1. Buka **File** menu di sidebar
2. Navigasi ke folder website Anda:
   ```
   /www/wwwroot/namadomain.com/
   ```
3. **Hapus** file default (`index.html`, `404.html`, dll)
4. Upload file aplikasi:
   - Klik **Upload** button
   - Pilih **ZIP** file proyek (atau upload file satu per satu)
   - Jika ZIP, klik kanan file → **Extract**

**Melalui FTP/SFTP:**

1. Buka menu **FTP** di aaPanel
2. Buat FTP account jika belum ada
3. Gunakan FTP client (FileZilla) untuk upload
4. Gunakan kredensial FTP dari aaPanel

**Struktur akhir:**
```
/www/wwwroot/namadomain.com/
├── config/
├── api/
├── assets/
├── includes/
├── index.php
├── admin.php
├── view.php
├── database.sql
└── ...
```

#### 5. Import Database

**Melalui phpMyAdmin:**

1. Buka **Database** menu di aaPanel
2. Cari database website Anda
3. Klik **phpMyAdmin** button
4. Login (kredensial biasanya auto-login)
5. Pilih database di panel kiri
6. Klik tab **Import**
7. Choose file → Pilih `database.sql`
8. Klik **Go**
9. Verifikasi tabel `users` dan `cameras` berhasil dibuat

**Melalui aaPanel SQL Manager:**

1. Buka **Database** menu
2. Klik database yang dibuat
3. Klik **Import** button
4. Upload file `database.sql`
5. Klik **Import**

#### 6. Konfigurasi Database

1. Buka **File** menu di aaPanel
2. Navigasi ke `config/database.php`
3. Klik **Edit**
4. Update kredensial:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nama_database_dari_aapanel');
define('DB_USER', 'username_database_dari_aapanel');
define('DB_PASS', 'password_database_dari_aapanel');
define('DB_CHARSET', 'utf8mb4');

// Fungsi lainnya tetap sama
```

5. **Save**

#### 7. Set Permission & Ownership

1. Buka **File** menu
2. Pilih folder root website
3. Klik **Permission** button
4. Set permission:
   - **Folders:** `755`
   - **Files:** `644`
   - **config/database.php:** `640`
5. Centang **Apply to subdirectories**
6. Klik **Submit**

**Set ownership (jika perlu):**
1. Klik **More** → **Owner**
2. Set ke user `www` atau `www-data`
3. Apply

#### 8. Konfigurasi SSL (HTTPS) - Opsional tapi Direkomendasikan

1. Buka **Website** menu
2. Cari site Anda, klik **Settings**
3. Buka tab **SSL**
4. Pilih metode:
   
   **Let's Encrypt (Gratis):**
   - Klik **Let's Encrypt**
   - Pilih domain/subdomain
   - Klik **Apply**
   - Tunggu proses selesai
   
   **Custom Certificate:**
   - Upload certificate files
   - Paste certificate content
   - Apply

5. Setelah SSL active, enable **Force HTTPS**

#### 9. Konfigurasi Rewrite Rules

**Untuk Nginx (default di aaPanel):**

1. Buka **Website** → Site Settings
2. Klik tab **Config Files**
3. Pastikan sudah ada konfigurasi untuk PHP:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/tmp/php-cgi-80.sock;
    fastcgi_index index.php;
    include fastcgi.conf;
}

# Protect config directory
location ^~ /config/ {
    deny all;
}

# Protect .git directory
location ^~ /.git/ {
    deny all;
}
```

4. **Save**

**Untuk Apache:**

File `.htaccess` yang sudah ada di proyek akan otomatis bekerja.

#### 10. Test Aplikasi

1. Buka browser
2. Akses domain:
   ```
   https://namadomain.com
   ```
3. Verifikasi halaman home muncul

4. Akses admin panel:
   ```
   https://namadomain.com/admin.php
   ```

5. Login dengan:
   - Username: `admin`
   - Password: `admin123`

6. **Ganti password** segera!

### Troubleshooting aaPanel

#### Error: "502 Bad Gateway"

**Solusi:**
1. Restart PHP-FPM:
   - Buka **App Store** → **Installed**
   - Cari PHP → Klik **Restart**
2. Check PHP error log:
   - Website Settings → **Logs** tab
   - Lihat error_log

#### Error: "Permission denied"

**Solusi:**
1. Set ownership ke `www`:
   ```bash
   # Via SSH (jika ada akses)
   chown -R www:www /www/wwwroot/namadomain.com
   ```
2. Atau via aaPanel File Manager → Owner

#### Database connection failed

**Solusi:**
1. Verifikasi kredensial database di aaPanel → **Database**
2. Reset password database jika perlu
3. Update `config/database.php` dengan kredensial yang benar
4. Test koneksi via phpMyAdmin

#### PHP version terlalu rendah

**Solusi:**
1. Buka **Website** → Site Settings
2. Tab **General** atau **PHP Version**
3. Pilih PHP 7.4 atau 8.0+
4. Save dan restart

---

## 🔐 Keamanan Setelah Instalasi

### Wajib Dilakukan:

1. ✅ **Ganti password admin default**
   - Login ke admin panel
   - Ganti password `admin123`
   - Gunakan password yang kuat

2. ✅ **Enable HTTPS/SSL**
   - Gunakan Let's Encrypt (gratis)
   - Force redirect HTTP ke HTTPS

3. ✅ **Protect config directory**
   - File `.htaccess` sudah include proteksi
   - Verifikasi tidak bisa diakses via browser

4. ✅ **Backup database secara rutin**
   - Setup cron job untuk backup otomatis
   - Simpan backup di lokasi aman

5. ✅ **Update kredensial database**
   - Jangan gunakan password default
   - Gunakan password yang kuat

### Rekomendasi Tambahan:

- 🔒 Install firewall (CSF, ConfigServer Firewall)
- 🔒 Enable fail2ban untuk proteksi brute force
- 🔒 Limit akses admin panel by IP (opsional)
- 🔒 Regular update PHP dan MySQL
- 🔒 Monitor log file secara berkala

---

## 📱 Akses Mobile

Aplikasi sudah responsive dan bisa diakses via mobile:

1. Buka browser di smartphone
2. Akses domain website
3. Bookmark untuk akses cepat
4. Atau tambahkan ke Home Screen

---

## 🆘 Bantuan Tambahan

### Kontak Support Hosting

Jika mengalami masalah teknis:
1. Contact support hosting provider
2. Tanyakan tentang:
   - PHP version dan extensions
   - MySQL access
   - mod_rewrite status
   - Error logs location

### Community Support

- GitHub Issues: [Repository Issues](https://github.com/RizkyFauzy0/cctvlive/issues)
- Dokumentasi lengkap: Lihat `README.md`

---

## ✅ Checklist Instalasi

Gunakan checklist ini untuk memastikan instalasi lengkap:

### Shared Hosting
- [ ] File terupload ke `public_html`
- [ ] Database dibuat di cPanel
- [ ] Database schema diimport via phpMyAdmin
- [ ] `config/database.php` dikonfigurasi
- [ ] File permission diset
- [ ] Aplikasi bisa diakses via browser
- [ ] Login admin berhasil
- [ ] Password admin diganti

### aaPanel
- [ ] Stack LNMP/LAMP terinstall
- [ ] PHP extensions terinstall
- [ ] Website dibuat di aaPanel
- [ ] File terupload
- [ ] Database diimport
- [ ] `config/database.php` dikonfigurasi
- [ ] Permission & ownership diset
- [ ] SSL/HTTPS dikonfigurasi (opsional)
- [ ] Rewrite rules dikonfigurasi
- [ ] Aplikasi bisa diakses
- [ ] Login admin berhasil
- [ ] Password admin diganti

---

**Selamat! Aplikasi Live CCTV Manager sudah terinstall dan siap digunakan! 🎉**
