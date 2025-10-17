#!/bin/bash

###############################################################################
# ສະຄຣິບຊ່ວຍໃນການຕຽມໂປເຈກສຳລັບ Production Server
# ລະບົບບັນຊີວັດ (Wat Accounting System)
###############################################################################

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ສະຄຣິບຕຽມໂປເຈກສຳລັບ Production Server             ║"
echo "║   ລະບົບບັນຊີວັດ v2.0                                    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# ສີສຳລັບການສະແດງຜົນ
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ຟັງຊັນແສດງຂໍ້ຄວາມ
success() {
    echo -e "${GREEN}✓${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

info() {
    echo -e "ℹ $1"
}

###############################################################################
# 1. ກວດສອບໂຟນເດີ
###############################################################################
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. ກວດສອບໂຟນເດີແລະໄຟລ໌"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

required_dirs=("includes" "modules" "logs")
for dir in "${required_dirs[@]}"; do
    if [ -d "$dir" ]; then
        success "ໂຟນເດີ $dir ມີຢູ່"
    else
        error "ໂຟນເດີ $dir ບໍ່ມີ!"
    fi
done

required_files=("config.php" "database.sql" ".htaccess" "login.php" "index.php")
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        success "ໄຟລ໌ $file ມີຢູ່"
    else
        error "ໄຟລ໌ $file ບໍ່ມີ!"
    fi
done

###############################################################################
# 2. ຕັ້ງສິດທິໄຟລ໌ແລະໂຟນເດີ
###############################################################################
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2. ຕັ້ງສິດທິໄຟລ໌ແລະໂຟນເດີ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

info "ກຳລັງຕັ້ງສິດທິ..."

# ຕັ້ງສິດທິໂຟນເດີ (755)
find . -type d -exec chmod 755 {} \; 2>/dev/null
success "ຕັ້ງສິດທິໂຟນເດີເປັນ 755"

# ຕັ້ງສິດທິໄຟລ໌ (644)
find . -type f -exec chmod 644 {} \; 2>/dev/null
success "ຕັ້ງສິດທິໄຟລ໌ເປັນ 644"

# ຕັ້ງສິດທິພິເສດສຳລັບ logs
chmod 755 logs/ 2>/dev/null
success "ຕັ້ງສິດທິໂຟນເດີ logs/ ເປັນ 755"

if [ -f "logs/php-error.log" ]; then
    chmod 666 logs/php-error.log
    success "ຕັ້ງສິດທິ logs/php-error.log ເປັນ 666"
fi

###############################################################################
# 3. ກວດສອບ config.php
###############################################################################
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3. ກວດສອບການຕັ້ງຄ່າ config.php"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if grep -q "localhost/watsystem" config.php; then
    warning "BASE_URL ຍັງເປັນ development URL"
    warning "ກະລຸນາແກ້ໄຂ config.php ໃຫ້ກົງກັບ production"
else
    success "BASE_URL ດູເໝາະສົມແລ້ວ"
fi

if grep -q "DB_PASS', ''" config.php; then
    warning "Database password ເປົ່າ!"
    warning "ກະລຸນາຕັ້ງ password ທີ່ເຂັ້ມແຂງ"
else
    success "Database password ຖືກຕັ້ງແລ້ວ"
fi

###############################################################################
# 4. ສ້າງໄຟລ໌ທີ່ຈຳເປັນ
###############################################################################
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4. ສ້າງໄຟລ໌ທີ່ຈຳເປັນ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ສ້າງ .gitignore
cat > .gitignore << 'EOF'
# Config files
config.php
config.production.php

# Logs
logs/*.log
logs/*.txt

# Backup files
*.sql.bak
backup_*.sql

# OS files
.DS_Store
Thumbs.db

# IDE files
.vscode/
.idea/
*.swp
*.swo

# Temporary files
*.tmp
*.bak
*~

# Test files
test_*.php
EOF

success "ສ້າງໄຟລ໌ .gitignore"

# ສ້າງ robots.txt
cat > robots.txt << 'EOF'
User-agent: *
Disallow: /includes/
Disallow: /modules/
Disallow: /logs/
Disallow: /config.php
Disallow: /database.sql
Disallow: *.sql
EOF

success "ສ້າງໄຟລ໌ robots.txt"

###############################################################################
# 5. ກວດສອບໄຟລ໌ທີ່ອາດເປັນອັນຕະລາຍ
###############################################################################
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5. ກວດສອບໄຟລ໌ທີ່ອາດເປັນອັນຕະລາຍ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

dangerous_files=("phpinfo.php" "info.php" "test.php" "debug.php")
for file in "${dangerous_files[@]}"; do
    if [ -f "$file" ]; then
        warning "ພົບໄຟລ໌ອັນຕະລາຍ: $file"
        read -p "ລຶບໄຟລ໌ນີ້ບໍ? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm "$file"
            success "ລຶບ $file ແລ້ວ"
        fi
    fi
done

###############################################################################
# 6. ສ້າງ Checklist
###############################################################################
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "6. Production Deployment Checklist"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

cat > PRODUCTION_CHECKLIST.txt << 'EOF'
╔════════════════════════════════════════════════════════════════╗
║          PRODUCTION DEPLOYMENT CHECKLIST                       ║
║          ລະບົບບັນຊີວັດ v2.0                                   ║
╚════════════════════════════════════════════════════════════════╝

PRE-DEPLOYMENT:
[ ] ອັບໂຫຼດໄຟລ໌ທັງໝົດຂຶ້ນ server
[ ] ຕັ້ງສິດທິໄຟລ໌/ໂຟນເດີຖືກຕ້ອງ
[ ] ລຶບໄຟລ໌ທີ່ບໍ່ຈຳເປັນອອກ (test_*.php, README.md)

DATABASE:
[ ] ສ້າງ database ແລ້ວ
[ ] ສ້າງ user ແລະຕັ້ງສິດທິແລ້ວ
[ ] Import database.sql ສຳເລັດ
[ ] ກວດສອບ VIEW ສ້າງສຳເລັດ
[ ] ທົດສອບ query ພື້ນຖານ

CONFIGURATION:
[ ] ແກ້ໄຂ config.php:
    - DB_HOST
    - DB_NAME
    - DB_USER
    - DB_PASS (ໃຊ້ password ທີ່ເຂັ້ມແຂງ)
    - BASE_URL
[ ] ຕັ້ງ error_log path
[ ] ປິດ display_errors
[ ] ເປີດ log_errors

SECURITY:
[ ] HTTPS/SSL ເຮັດວຽກແລ້ວ
[ ] .htaccess ບລັອກໄຟລ໌ສຳຄັນ
[ ] ປ່ຽນ admin password default
[ ] Session cookie_secure = 1
[ ] ລຶບ test_connection.php

TESTING:
[ ] ທົດສອບການເຊື່ອມຕໍ່ database
[ ] ທົດສອບ login
[ ] ທົດສອບໜ້າຕ່າງໆທັງໝົດ
[ ] ທົດສອບ modules/temples/index.php
[ ] ກວດສອບ error logs

POST-DEPLOYMENT:
[ ] ຕັ້ງ backup ອັດຕະໂນມັດ
[ ] Monitor error logs
[ ] ທົດສອບປະສິດທິພາບ
[ ] ສ້າງເອກະສານສຳລັບຜູ້ໃຊ້

NOTES:
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

Deployed by: ________________  Date: ______________
EOF

success "ສ້າງໄຟລ໌ PRODUCTION_CHECKLIST.txt"

###############################################################################
# 7. ສະຫຼຸບ
###############################################################################
echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                    ສຳເລັດ!                               ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
info "ໂປເຈກພ້ອມສຳລັับການ deploy ແລ້ວ!"
echo ""
echo "ຂັ້ນຕອນຕໍ່ໄປ:"
echo "1. ແກ້ໄຂ config.php ໃຫ້ກົງກັບ production environment"
echo "2. ອັບໂຫຼດໄຟລ໌ທັງໝົດຂຶ້ນ production server"
echo "3. Import database.sql ເຂົ້າ production database"
echo "4. ເຂົ້າເບິ່ງ test_connection.php ເພື່ອທົດສອບ"
echo "5. ລຶບ test_connection.php ອອກທັນທີຫຼັງທົດສອບ"
echo "6. ກວດສອບ PRODUCTION_CHECKLIST.txt"
echo ""
warning "ຢ່າລືມ: ປ່ຽນ password default ທັນທີຫຼັງ deploy!"
echo ""
