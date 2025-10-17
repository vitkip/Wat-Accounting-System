# ລະບົບບັນຊີວັດ - ຄູ່ມືການຕິດຕັ້ງສຳລັບ Production Server

## ຂັ້ນຕອນການຕິດຕັ້ງ

### 1. ກຽມ Server

#### ຕ້ອງການ:
- PHP 7.4 ຫຼື ສູງກວ່າ
- MySQL/MariaDB 5.7 ຫຼື ສູງກວ່າ
- Apache/Nginx web server
- SSL Certificate (HTTPS)

#### ກວດສອບ PHP Extensions:
```bash
php -m | grep -E 'pdo|mysqli|mbstring|json|session'
```

ຕ້ອງມີ:
- PDO
- pdo_mysql
- mbstring
- json
- session

### 2. ອັບໂຫຼດໄຟລ໌

```bash
# ອັບໂຫຼດໄຟລ໌ທັງໝົດໄປ server ໂດຍໃຊ້ FTP/SFTP/Git
# ຕົວຢ່າງດ້ວຍ Git:
cd /var/www/html/all
git clone https://github.com/vitkip/Wat-Accounting-System.git .
```

### 3. ຕັ້ງຄ່າສິດທິໄຟລ໌

```bash
# ໃຫ້ສິດອ່ານໄຟລ໌
find /var/www/html/all -type f -exec chmod 644 {} \;

# ໃຫ້ສິດເຂົ້າໂຟນເດີ
find /var/www/html/all -type d -exec chmod 755 {} \;

# ສ້າງໂຟນເດີ logs ແລະໃຫ້ສິດຂຽນ
mkdir -p /var/www/html/all/logs
chmod 755 /var/www/html/all/logs
chown www-data:www-data /var/www/html/all/logs
```

### 4. ສ້າງຖານຂໍ້ມູນ

```bash
# ເຂົ້າ MySQL
mysql -u root -p

# ສ້າງ database ແລະ user
CREATE DATABASE laotemples_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laotemples_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON laotemples_prod.* TO 'laotemples_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database schema
mysql -u laotemples_user -p laotemples_prod < /var/www/html/all/database.sql
```

### 5. ຕັ້ງຄ່າ Config

```bash
# ປ່ຽນຊື່ config.production.php ເປັນ config.php
cd /var/www/html/all
cp config.production.php config.php

# ແກ້ໄຂການຕັ້ງຄ່າ
nano config.php
```

ແກ້ໄຂຄ່າດັ່ງນີ້:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'laotemples_prod');
define('DB_USER', 'laotemples_user');
define('DB_PASS', 'YOUR_SECURE_PASSWORD');
define('BASE_URL', 'https://laotemples.com/all');
```

### 6. ຕັ້ງຄ່າ Apache Virtual Host (ຖ້າໃຊ້ Apache)

```bash
# ສ້າງ virtual host config
nano /etc/apache2/sites-available/laotemples.conf
```

ເພີ່ມເນື້ອຫາ:
```apache
<VirtualHost *:80>
    ServerName laotemples.com
    ServerAlias www.laotemples.com
    Redirect permanent / https://laotemples.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName laotemples.com
    ServerAlias www.laotemples.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html/all>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/laotemples-error.log
    CustomLog ${APACHE_LOG_DIR}/laotemples-access.log combined
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
    SSLCertificateChainFile /path/to/chain.pem
</VirtualHost>
```

ເປີດໃຊ້ງານ:
```bash
a2ensite laotemples.conf
a2enmod rewrite ssl
systemctl restart apache2
```

### 7. ຕັ້ງຄ່າ Nginx (ຖ້າໃຊ້ Nginx)

```bash
nano /etc/nginx/sites-available/laotemples
```

ເພີ່ມເນື້ອຫາ:
```nginx
server {
    listen 80;
    server_name laotemples.com www.laotemples.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name laotemples.com www.laotemples.com;
    
    root /var/www/html;
    index index.php index.html;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location /all {
        try_files $uri $uri/ /all/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~* \.(sql|md|log|ini)$ {
        deny all;
    }
}
```

ເປີດໃຊ້ງານ:
```bash
ln -s /etc/nginx/sites-available/laotemples /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

### 8. ກວດສອບການເຊື່ອມຕໍ່

ສ້າງໄຟລ໌ທົດສອບ:
```bash
nano /var/www/html/all/test-connection.php
```

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'laotemples_prod';
$user = 'laotemples_user';
$pass = 'YOUR_PASSWORD';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ ເຊື່ອມຕໍ່ຖານຂໍ້ມູນສຳເລັດ!<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM temples");
    $result = $stmt->fetch();
    echo "✅ ມີວັດທັງໝົດ: " . $result['count'] . " ວັດ<br>";
    
} catch (PDOException $e) {
    echo "❌ ຜິດພາດ: " . $e->getMessage();
}
?>
```

ເຂົ້າເບິ່ງ: `https://laotemples.com/all/test-connection.php`

**⚠️ ລຶບໄຟລ໌ນີ້ທິ້ງທັນທີຫຼັງທົດສອບສຳເລັດ!**

```bash
rm /var/www/html/all/test-connection.php
```

### 9. ສ້າງຜູ້ໃຊ້ Super Admin ຄົນທຳອິດ

```bash
mysql -u laotemples_user -p laotemples_prod
```

```sql
-- ກວດສອບວ່າມີວັດແລ້ວບໍ່
SELECT * FROM temples;

-- ຖ້າບໍ່ມີ, ໃຫ້ເພີ່ມວັດຕົວຢ່າງ
INSERT INTO temples (temple_code, temple_name, temple_name_lao, status) 
VALUES ('WAT001', 'Main Temple', 'ວັດຫຼວງ', 'active');

-- ສ້າງ Super Admin (password: admin123 - ຕ້ອງປ່ຽນພາຍຫຼັງ!)
INSERT INTO users (temple_id, username, password, full_name, role, is_super_admin) 
VALUES (
    1, 
    'superadmin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Super Administrator', 
    'admin', 
    1
);
```

### 10. ການຕັ້ງຄ່າຄວາມປອດໄພເພີ່ມເຕີມ

#### ປ້ອງກັນການເຂົ້າເຖິງໄຟລ໌ສຳຄັນ:
ແກ້ໄຂ `.htaccess`:
```apache
# Block access to sensitive files
<FilesMatch "^(config\.php|config\.production\.php|database\.sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Block directory listing
Options -Indexes

# Protect .git directory
RedirectMatch 404 /\.git
```

#### ຕັ້ງຄ່າ Firewall:
```bash
# ອະນຸຍາດພຽງ HTTP, HTTPS, SSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw enable
```

#### ປິດການສະແດງເວີຊັນ PHP:
```bash
nano /etc/php/7.4/apache2/php.ini
# ຫຼື
nano /etc/php/7.4/fpm/php.ini
```

ຊອກຫາ ແລະ ແກ້ໄຂ:
```ini
expose_php = Off
```

### 11. ກຳນົດເວລາ Backup ອັດຕະໂນມັດ

```bash
# ສ້າງ script backup
nano /usr/local/bin/backup-laotemples.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/laotemples"
DB_NAME="laotemples_prod"
DB_USER="laotemples_user"
DB_PASS="YOUR_PASSWORD"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/all

# ລຶບ backup ເກົ່າກວ່າ 30 ວັນ
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
chmod +x /usr/local/bin/backup-laotemples.sh

# ເພີ່ມເຂົ້າ crontab (ສຳຮອງທຸກວັນເວລາ 2:00 AM)
crontab -e
```

ເພີ່ມ:
```
0 2 * * * /usr/local/bin/backup-laotemples.sh >> /var/log/laotemples-backup.log 2>&1
```

### 12. ການຕິດຕາມ (Monitoring)

ສ້າງໄຟລ໌ health check:
```bash
nano /var/www/html/all/health.php
```

```php
<?php
header('Content-Type: application/json');

$status = ['status' => 'ok', 'checks' => []];

// Check database
try {
    require_once __DIR__ . '/config.php';
    $db = getDB();
    $db->query("SELECT 1");
    $status['checks']['database'] = 'ok';
} catch (Exception $e) {
    $status['status'] = 'error';
    $status['checks']['database'] = 'error';
}

// Check logs directory
if (is_writable(__DIR__ . '/logs')) {
    $status['checks']['logs'] = 'ok';
} else {
    $status['status'] = 'warning';
    $status['checks']['logs'] = 'not_writable';
}

echo json_encode($status);
?>
```

### 13. ກວດສອບສຸດທ້າຍ

- [ ] ເຂົ້າລະບົບດ້ວຍ Super Admin ໄດ້
- [ ] ສ້າງວັດໃໝ່ໄດ້
- [ ] ເພີ່ມລາຍຮັບ/ລາຍຈ່າຍໄດ້
- [ ] ເບິ່ງລາຍງານໄດ້
- [ ] ການຕັ້ງຄ່າວັດເຮັດວຽກ
- [ ] HTTPS ເຮັດວຽກ
- [ ] Error logs ຖືກບັນທຶກ
- [ ] Backup ອັດຕະໂນມັດເຮັດວຽກ

### 14. ຫຼັງການຕິດຕັ້ງ

1. **ປ່ຽນ password ຂອງ Super Admin ທັນທີ**
2. ລຶບໄຟລ໌ທົດສອບທັງໝົດ
3. ກວດສອບ error logs ເປັນປົກກະຕິ: `tail -f logs/php-error.log`
4. ຕິດຕາມ server resources (CPU, RAM, Disk)
5. ຕັ້ງຄ່າ SSL certificate renewal ອັດຕະໂນມັດ (Let's Encrypt)

## ການແກ້ໄຂບັນຫາ

### ບັນຫາ: HTTP 500 Error

1. ກວດສອບ error log:
```bash
tail -100 /var/www/html/all/logs/php-error.log
tail -100 /var/log/apache2/error.log  # ຫຼື nginx error.log
```

2. ກວດສອບສິດທິໄຟລ໌
3. ກວດສອບການເຊື່ອມຕໍ່ database
4. ກວດສອບວ່າ PHP extensions ຄົບ

### ບັນຫາ: ບໍ່ສາມາດເຂົ້າສູ່ລະບົບ

1. ກວດສອບ session directory writable
2. Reset password ໃນ database ໂດຍກົງ
3. ກວດສອບ cookie settings

### ບັນຫາ: ຊ້າ

1. ເປີດ PHP OPcache
2. ເພີ່ມ MySQL indexes
3. ໃຊ້ CDN ສຳລັບ static files
4. Optimize database queries

## ການສະໜັບສະໜູນ

ຖ້າມີບັນຫາ, ກະລຸນາ:
1. ກວດສອບ error logs
2. ເບິ່ງ README.md
3. ຕິດຕໍ່ທີມພັດທະນາ

---

**ຂໍໃຫ້ໂຊກດີກັບການນຳໃຊ້ລະບົບ! 🙏**
