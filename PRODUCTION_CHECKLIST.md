# Production Deployment Checklist

## ✅ ໄຟລ໌ທີ່ລຶບອອກແລ້ວ
- [x] `test_connection.php` - ໄຟລ໌ທົດສອບການເຊື່ອມຕໍ່
- [x] `debug_edit.php` - ໄຟລ໌ debug temple edit
- [x] Screenshots - ໄຟລ໌ຮູບພາບທີ່ບໍ່ຈຳເປັນ
- [x] `.DS_Store` - ໄຟລ໌ລະບົບ macOS
- [x] `Thumbs.db` - ໄຟລ໌ລະບົບ Windows

## 📋 ກ່ອນ Deploy ຕ້ອງກວດສອບ

### 1. ການຕັ້ງຄ່າຖານຂໍ້ມູນ
- [ ] ອັບເດດ `config.production.php` ດ້ວຍຂໍ້ມູນຖານຂໍ້ມູນຈິງ
- [ ] Import `database.sql` ເຂົ້າຖານຂໍ້ມູນ production
- [ ] ສ້າງ Super Admin user ຄົນທຳອິດ

### 2. ການຕັ້ງຄ່າຄວາມປອດໄພ
- [ ] ປ່ຽນ `DB_PASSWORD` ໃນ config.production.php
- [ ] ປ່ຽນ secret keys ທັງໝົດ
- [ ] ຕັ້ງ `error_reporting(0)` ໃນ production
- [ ] ປິດ display_errors

### 3. File Permissions
```bash
# Set correct permissions
chmod 755 /path/to/watsystem
chmod 644 /path/to/watsystem/*.php
chmod 755 /path/to/watsystem/modules
chmod 755 /path/to/watsystem/includes
chmod 777 /path/to/watsystem/logs
chmod 644 /path/to/watsystem/logs/.htaccess
```

### 4. Apache/Nginx Configuration
- [ ] ຕັ້ງຄ່າ Virtual Host
- [ ] Enable mod_rewrite (Apache)
- [ ] ຕັ້ງຄ່າ SSL certificate
- [ ] Configure .htaccess rules

### 5. PHP Configuration
- [ ] PHP >= 7.4
- [ ] Enable PDO MySQL extension
- [ ] Set appropriate memory_limit
- [ ] Set upload_max_filesize
- [ ] Set post_max_size

### 6. Backup Strategy
- [ ] ຕັ້ງຄ່າ automated database backup
- [ ] ຕັ້ງຄ່າ file backup
- [ ] ທົດສອບ restore process

### 7. Testing
- [ ] ທົດສອບ Login/Logout
- [ ] ທົດສອບສ້າງລາຍຮັບ/ລາຍຈ່າຍ
- [ ] ທົດສອບລາຍງານ
- [ ] ທົດສອບການຈັດການຜູ້ໃຊ້
- [ ] ທົດສອບການຈັດການວັດ (Super Admin)
- [ ] ທົດສອບໃນ Mobile devices

### 8. Performance
- [ ] Enable PHP OPcache
- [ ] Enable Gzip compression
- [ ] Optimize images in assets/
- [ ] Configure browser caching

### 9. Monitoring
- [ ] ຕັ້ງຄ່າ error logging
- [ ] ຕັ້ງຄ່າ access logging
- [ ] Monitor disk space
- [ ] Monitor database size

### 10. Documentation
- [ ] Update README.md with production info
- [ ] Document admin credentials (securely)
- [ ] Create user manual
- [ ] Create backup/restore procedures

## 🚀 Deployment Steps

1. **Backup Current System** (if updating)
```bash
mysqldump -u root -p wat_accounting > backup_$(date +%Y%m%d).sql
tar -czf watsystem_backup_$(date +%Y%m%d).tar.gz /path/to/watsystem
```

2. **Upload Files**
```bash
rsync -avz --exclude='.git' --exclude='logs/*.log' \
  /local/watsystem/ user@server:/var/www/html/watsystem/
```

3. **Set Permissions**
```bash
cd /var/www/html/watsystem
chmod 755 .
chmod -R 644 *.php
chmod -R 755 modules includes assets
chmod 777 logs
```

4. **Import Database**
```bash
mysql -u root -p wat_accounting < database.sql
```

5. **Update Configuration**
```bash
cp config.production.php config.php
# Edit config.php with real credentials
```

6. **Test Access**
- Visit: https://yourdomain.com/watsystem
- Login with Super Admin
- Test all features

## ⚠️ Security Reminders

1. **ບໍ່ມີໄຟລ໌ debug ໃນ production**
2. **ປ່ຽນ passwords ທັງໝົດ**
3. **Enable HTTPS**
4. **ປິດ error display**
5. **Restrict database access**
6. **Regular security updates**
7. **Monitor access logs**
8. **Backup regularly**

## 📞 Support Contacts

- Developer: [Your Contact]
- Server Admin: [Contact]
- Database Admin: [Contact]

## 📝 Version History

- v1.0.0 - Initial Production Release (2025-10-17)
  - User Management
  - Income/Expense Tracking
  - Reports
  - Temple Management (Super Admin)
  - Multi-temple Support
