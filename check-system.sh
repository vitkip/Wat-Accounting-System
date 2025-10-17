#!/bin/bash
# =====================================================
# ສະຄຣິບກວດສອບລະບົບກ່ອນ Deploy ໃສ່ Production
# =====================================================

echo "🔍 ກຳລັງກວດສອບລະບົບບັນຊີວັດ..."
echo "===================================="
echo ""

# ສີສຳລັບການສະແດງຜົນ
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# ຟັງຊັນກວດສອບ
check_pass() {
    echo -e "${GREEN}✅ $1${NC}"
}

check_fail() {
    echo -e "${RED}❌ $1${NC}"
    ((ERRORS++))
}

check_warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
    ((WARNINGS++))
}

# 1. ກວດສອບ PHP Version
echo "1️⃣  ກວດສອບ PHP Version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -ge 7 ] && [ "$PHP_MINOR" -ge 4 ]; then
    check_pass "PHP Version: $PHP_VERSION (OK)"
else
    check_fail "PHP Version: $PHP_VERSION (ຕ້ອງການ 7.4 ຫຼືສູງກວ່າ)"
fi

# 2. ກວດສອບ PHP Extensions
echo ""
echo "2️⃣  ກວດສອບ PHP Extensions..."

REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "json" "session")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        check_pass "$ext extension ຕິດຕັ້ງແລ້ວ"
    else
        check_fail "$ext extension ບໍ່ໄດ້ຕິດຕັ້ງ"
    fi
done

# 3. ກວດສອບໂຟນເດີ ແລະ ໄຟລ໌
echo ""
echo "3️⃣  ກວດສອບໂຄງສ້າງໄຟລ໌..."

REQUIRED_FILES=(
    "config.php"
    "index.php"
    "login.php"
    "logout.php"
    "database.sql"
    ".htaccess"
    "includes/header.php"
    "includes/footer.php"
    "includes/temple_functions.php"
    "modules/temples/index.php"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        check_pass "$file ມີແລ້ວ"
    else
        check_fail "$file ບໍ່ພົບ"
    fi
done

# 4. ກວດສອບໂຟນເດີ logs
echo ""
echo "4️⃣  ກວດສອບໂຟນເດີ logs..."

if [ -d "logs" ]; then
    check_pass "ໂຟນເດີ logs ມີແລ້ວ"
    
    if [ -w "logs" ]; then
        check_pass "ໂຟນເດີ logs ສາມາດຂຽນໄດ້"
    else
        check_fail "ໂຟນເດີ logs ບໍ່ສາມາດຂຽນໄດ້"
    fi
else
    check_warn "ໂຟນເດີ logs ບໍ່ມີ (ຈະຖືກສ້າງອັດຕະໂນມັດ)"
    mkdir -p logs
    chmod 755 logs
fi

# 5. ກວດສອບການຕັ້ງຄ່າ config.php
echo ""
echo "5️⃣  ກວດສອບການຕັ້ງຄ່າ config.php..."

if grep -q "define('DB_HOST'" config.php; then
    check_pass "DB_HOST ຖືກກຳນົດແລ້ວ"
else
    check_fail "DB_HOST ບໍ່ໄດ້ກຳນົດ"
fi

if grep -q "define('BASE_URL'" config.php; then
    check_pass "BASE_URL ຖືກກຳນົດແລ້ວ"
else
    check_fail "BASE_URL ບໍ່ໄດ້ກຳນົດ"
fi

# ກວດສອບວ່າ display_errors ປິດຢູ່ສຳລັບ production
if grep -q "ini_set('display_errors', 0)" config.php || grep -q "ini_set('display_errors',0)" config.php; then
    check_pass "display_errors ປິດແລ້ວ (production ready)"
else
    check_warn "display_errors ຍັງເປີດຢູ່ (ຄວນປິດສຳລັບ production)"
fi

# 6. ກວດສອບ .htaccess
echo ""
echo "6️⃣  ກວດສອບ .htaccess..."

if [ -f ".htaccess" ]; then
    if grep -q "RewriteEngine On" .htaccess; then
        check_pass ".htaccess ມີການຕັ້ງຄ່າ rewrite"
    else
        check_warn ".htaccess ບໍ່ມີການຕັ້ງຄ່າ rewrite"
    fi
    
    if grep -q "Options -Indexes" .htaccess; then
        check_pass ".htaccess ປ້ອງກັນ directory listing"
    else
        check_warn ".htaccess ບໍ່ໄດ້ປ້ອງກັນ directory listing"
    fi
else
    check_fail ".htaccess ບໍ່ພົບ"
fi

# 7. ກວດສອບສິດທິໄຟລ໌
echo ""
echo "7️⃣  ກວດສອບສິດທິໄຟລ໌..."

CONFIG_PERM=$(stat -c "%a" config.php 2>/dev/null || stat -f "%A" config.php 2>/dev/null)
if [ "$CONFIG_PERM" = "644" ] || [ "$CONFIG_PERM" = "600" ]; then
    check_pass "config.php ສິດທິຖືກຕ້ອງ ($CONFIG_PERM)"
else
    check_warn "config.php ສິດທິ: $CONFIG_PERM (ແນະນຳ: 644 ຫຼື 600)"
fi

# 8. ກວດສອບການເຊື່ອມຕໍ່ Database (ຖ້າເປັນ production)
echo ""
echo "8️⃣  ກວດສອບການເຊື່ອມຕໍ່ Database..."

# ສ້າງສະຄຣິບ PHP ຊົ່ວຄາວ
cat > /tmp/test_db_connection.php << 'EOF'
<?php
require_once 'config.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT 1");
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
EOF

DB_TEST=$(php /tmp/test_db_connection.php 2>&1)
rm -f /tmp/test_db_connection.php

if [[ $DB_TEST == "SUCCESS" ]]; then
    check_pass "ເຊື່ອມຕໍ່ database ສຳເລັດ"
else
    check_fail "ບໍ່ສາມາດເຊື່ອມຕໍ່ database: $DB_TEST"
fi

# 9. ກວດສອບ temple_statistics VIEW
echo ""
echo "9️⃣  ກວດສອບ Database Views..."

cat > /tmp/test_view.php << 'EOF'
<?php
require_once 'config.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM temple_statistics LIMIT 1");
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
EOF

VIEW_TEST=$(php /tmp/test_view.php 2>&1)
rm -f /tmp/test_view.php

if [[ $VIEW_TEST == "SUCCESS" ]]; then
    check_pass "VIEW temple_statistics ສ້າງແລ້ວ"
else
    check_warn "VIEW temple_statistics ຍັງບໍ່ທັນສ້າງ (ຕ້ອງ import database.sql)"
fi

# 10. ກວດສອບ HTTPS (ຖ້າເປັນ production)
echo ""
echo "🔟  ກວດສອບການຕັ້ງຄ່າ HTTPS..."

if grep -q "https://" config.php; then
    check_pass "BASE_URL ໃຊ້ HTTPS"
else
    check_warn "BASE_URL ບໍ່ໄດ້ໃຊ້ HTTPS (ແນະນຳສຳລັບ production)"
fi

# ສະຫຼຸບຜົນ
echo ""
echo "===================================="
echo "📊 ສະຫຼຸບຜົນການກວດສອບ"
echo "===================================="

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}🎉 ລະບົບພ້ອມສຳລັບ Production!${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  ມີຂໍ້ແນະນຳ: $WARNINGS ຂໍ້${NC}"
    echo "ລະບົບສາມາດໃຊ້ງານໄດ້ ແຕ່ຄວນແກ້ໄຂຂໍ້ແນະນຳເພື່ອຄວາມປອດໄພ"
    exit 0
else
    echo -e "${RED}❌ ພົບຂໍ້ຜິດພາດ: $ERRORS ຂໍ້${NC}"
    echo -e "${YELLOW}⚠️  ຂໍ້ແນະນຳ: $WARNINGS ຂໍ້${NC}"
    echo ""
    echo "ກະລຸນາແກ້ໄຂຂໍ້ຜິດພາດກ່ອນ deploy ໄປ production"
    exit 1
fi
