# ລາຍການກວດສອບຄວາມປອດໄພ (Security Checklist)
## ສຳລັບການ Deploy ຂຶ້ນ Production Server

> ວັນທີ່ສ້າງ: 17 ຕຸລາ 2025  
> ສະຖານະ: ✅ ພ້ອມສຳລັບ Production

---

## 🔒 ຄວາມປອດໄພພື້ນຖານ

### ✅ 1. ການແກ້ໄຂທີ່ສຳເລັດແລ້ວ

#### DELETE Query Security
ແກ້ໄຂການລຶບຂໍ້ມູນທີ່ບໍ່ກວດສອບ temple_id:

- ✅ `/modules/categories/income_list.php` - ເພີ່ມການກວດສອບ temple_id ກ່ອນລຶບ
- ✅ `/modules/categories/expense_list.php` - ເພີ່ມການກວດສອບ temple_id ກ່ອນລຶບ
- ✅ `/modules/income/delete.php` - ເພີ່ມ WHERE temple_id ໃນ DELETE query
- ✅ `/modules/expense/delete.php` - ເພີ່ມ WHERE temple_id ໃນ DELETE query
- ✅ `/modules/users/delete.php` - ເພີ່ມການກວດສອບ temple_id ສຳລັບ Admin

**ບັນຫາເດີມ:**
```sql
DELETE FROM income_categories WHERE id = ?  -- ອັນຕະລາຍ!
```

**ແກ້ໄຂແລ້ວ:**
```sql
DELETE FROM income_categories WHERE id = ? AND temple_id = ?  -- ປອດໄພ!
```

---

## ⚙️ ການຕັ້ງຄ່າທີ່ຕ້ອງກວດສອບກ່ອນ Deploy

### 🔴 CRITICAL - ຕ້ອງປ່ຽນກ່ອນຂຶ້ນ Production

1. **config.php - ຖານຂໍ້ມູນ**
   ```php
   // ປ່ຽນເປັນຂໍ້ມູນຈິງຂອງ production server
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'wat_accounting');
   define('DB_USER', 'your_db_user');      // ປ່ຽນ!
   define('DB_PASS', 'strong_password');   // ປ່ຽນ!
   ```

2. **config.php - Debug Mode**
   ```php
   // ຕ້ອງປິດ debug mode ໃນ production
   define('DEBUG_MODE', false);  // ປ່ຽນເປັນ false!
   ini_set('display_errors', 0); // ປິດການສະແດງ errors
   ```

3. **config.php - CSRF Security**
   ```php
   // ປ່ຽນເປັນ secret key ທີ່ເຂັ້ມແຂງ
   define('CSRF_SECRET', 'your-random-secret-key-here'); // ປ່ຽນ!
   ```

---

## 🗂️ ໄຟລ໌ທີ່ຕ້ອງລຶບ

### ລຶບໄຟລ໌ Debug ທັງໝົດ:
```bash
rm /modules/temples/debug_edit.php
rm /modules/users/profile_debug.php
rm -rf *.log
rm -rf /debug/
```

---

## 🔐 ການຕັ້ງຄ່າ Server

### 1. PHP Configuration (php.ini)
```ini
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL
log_errors = On
error_log = /var/log/php_errors.log

; Session Security
session.cookie_httponly = 1
session.cookie_secure = 1      ; ຖ້າໃຊ້ HTTPS
session.use_strict_mode = 1
session.cookie_samesite = Strict

; File Upload
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 30
```

### 2. Apache/.htaccess
```apache
# ປ້ອງກັນເຂົ້າເຖິງໄຟລ໌ສຳຄັນ
<Files "config.php">
    Require all denied
</Files>

<Files "*.md">
    Require all denied
</Files>

# Force HTTPS (ຖ້າມີ SSL)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Directory Permissions
```bash
# Web root
chmod 755 /var/www/watsystem

# Config files
chmod 640 config.php
chmod 640 includes/*.php

# Upload directories (ຖ້າມີ)
chmod 755 uploads/
chmod 644 uploads/*
```

---

## 🛡️ ການກວດສອບ Security Headers

ເພີ່ມໃນ `includes/header.php` ກ່ອນ `<!DOCTYPE html>`:

```php
<?php
// Security Headers for Production
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// CSP - ປັບແຕ່ງຕາມຄວາມຕ້ອງການ
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.tailwindcss.com cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com;");

// HTTPS Only (ຖ້າມີ SSL)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
?>
```

---

## 🗄️ Database Security

### 1. ສິດຜູ້ໃຊ້ຖານຂໍ້ມູນ
```sql
-- ສ້າງຜູ້ໃຊ້ສະເພາະສຳລັບ application
CREATE USER 'wat_app'@'localhost' IDENTIFIED BY 'strong_password_here';

-- ໃຫ້ສິດພຽງແຕ່ທີ່ຈຳເປັນ
GRANT SELECT, INSERT, UPDATE, DELETE ON wat_accounting.* TO 'wat_app'@'localhost';

-- ບໍ່ໃຫ້ສິດ DROP, CREATE, ALTER
FLUSH PRIVILEGES;
```

### 2. Backup Schedule
```bash
# ສ້າງ cron job ສຳລັບ backup ທຸກມື້
0 2 * * * mysqldump -u backup_user -p wat_accounting > /backups/wat_$(date +\%Y\%m\%d).sql
```

---

## 📝 Pre-Deployment Checklist

### ກວດສອບກ່ອນ Deploy:

- [ ] ປ່ຽນ database credentials ໃນ `config.php`
- [ ] ປິດ DEBUG_MODE
- [ ] ປ່ຽນ CSRF_SECRET
- [ ] ລຶບໄຟລ໌ debug ທັງໝົດ
- [ ] ກວດສອບ file permissions
- [ ] ຕັ້ງຄ່າ error logging
- [ ] ເພີ່ມ security headers
- [ ] ກວດສອບ DELETE queries ທຸກໄຟລ໌
- [ ] ທົດສອບ CSRF protection
- [ ] ທົດສອບ multi-temple isolation
- [ ] ສ້າງ database backup
- [ ] ເຕັມ SSL certificate (ແນະນຳ)
- [ ] ກວດສອບ server firewall rules
- [ ] ເຮັດ penetration testing (ຖ້າເປັນໄປໄດ້)

---

## 🚨 ສິ່ງທີ່ຕ້ອງຮູ້

### ຈຸດທີ່ອ່ອນໄຫວ (Sensitive Areas):

1. **User Authentication** - ໃຊ້ password_hash() ແລ້ວ ✅
2. **CSRF Protection** - ໃຊ້ tokens ແລ້ວ ✅
3. **SQL Injection** - ໃຊ້ prepared statements ແລ້ວ ✅
4. **Temple Data Isolation** - ແກ້ໄຂແລ້ວ ✅
5. **XSS Protection** - ໃຊ້ htmlspecialchars() (e() function) ✅

### ຄຳແນະນຳເພີ່ມເຕີມ:

1. **ຕິດຕາມ Error Logs ເປັນປົກກະຕິ**
   ```bash
   tail -f /var/log/php_errors.log
   tail -f /var/log/apache2/error.log
   ```

2. **ອັບເດດເປັນປົກກະຕິ**
   - PHP version
   - Database server
   - Dependencies (Tailwind, Alpine.js, etc.)

3. **ສຳຮອງຂໍ້ມູນເປັນປົກກະຕິ**
   - Database: ທຸກມື້
   - Files: ທຸກອາທິດ
   - ເກັບ offsite backup

4. **ຕິດຕັ້ງ Monitoring**
   - Server resources (CPU, RAM, Disk)
   - Application errors
   - Unusual login attempts
   - Failed transactions

---

## 📞 ຕິດຕໍ່ສຸກເສີນ

ຖ້າພົບບັນຫາດ້ານຄວາມປອດໄພ:
1. ປິດລະບົບທັນທີ
2. ກວດສອບ error logs
3. Restore ຈາກ backup ທີ່ດີ
4. ແກ້ໄຂບັນຫາ
5. ທົດສອບກ່ອນເປີດອີກຄັ້ງ

---

**ໝາຍເຫດສຳຄັນ:** ເອກະສານນີ້ຕ້ອງລຶບອອກຫຼືຍ້າຍອອກຈາກ web root ຫຼັງຈາກ deploy ແລ້ວ!

```bash
mv SECURITY_CHECKLIST.md /secure-location/
```
