#!/bin/bash
# 🔍 ກວດສອບວ່າທຸກໄຟລ໌ຖືກແກ້ແລ້ວ - ພ້ອມ Deploy!

echo "╔════════════════════════════════════════╗"
echo "║  ກວດສອບການແກ້ໄຂທັງໝົດ              ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

passed=0
failed=0

check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
        ((passed++))
    else
        echo -e "${RED}❌ $1${NC}"
        ((failed++))
    fi
}

echo -e "${BLUE}📋 ກວດສອບການສະແດງລາຍການ${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 1. Income List
echo -n "1. Income list - ກວດສອບ 'i.id AS id': "
grep -q "i.id AS id" modules/income/list.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບບໍ່ມີ debug logs: "
! grep -q "error_log.*🔍 First income" modules/income/list.php
check "ສະອາດແລ້ວ"

# 2. Expense List  
echo -n "2. Expense list - ກວດສອບ 'e.id AS id': "
grep -q "e.id AS id" modules/expense/list.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບບໍ່ມີ debug logs: "
! grep -q "error_log.*🔍 First expense" modules/expense/list.php
check "ສະອາດແລ້ວ"

# 3. Users List
echo -n "3. Users list - ກວດສອບ u.email: "
grep -q "u.email" modules/users/list.php
check "พົບແລ້ວ"

email_count=$(grep -c "u.email" modules/users/list.php)
echo -n "   - ກວດສອບ email ໃນທຸກ query ($email_count queries): "
if [ "$email_count" -ge 3 ]; then
    echo -e "${GREEN}✅ ຄົບ${NC}"
    ((passed++))
else
    echo -e "${RED}❌ ຂາດ${NC}"
    ((failed++))
fi

echo ""
echo -e "${BLUE}📂 ກວດສອບການແກ້ category_id${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 4. Income Edit
echo -n "4. Income edit - ກວດສອບ 'WHERE category =': "
grep -q "WHERE category =" modules/categories/income_edit.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບໃຊ້ category['name']: "
grep -q "\$category\['name'\]" modules/categories/income_edit.php
check "ຖືກຕ້ອງ"

# 5. Expense Edit
echo -n "5. Expense edit - ກວດສອບ 'WHERE category =': "
grep -q "WHERE category =" modules/categories/expense_edit.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບໃຊ້ category['name']: "
grep -q "\$category\['name'\]" modules/categories/expense_edit.php
check "ຖືກຕ້ອງ"

echo ""
echo -e "${BLUE}🗑️  ກວດສອບການລຶບ Categories${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 6. Income Category Delete
echo -n "6. Income list - ກວດສອບ confirmDeleteCategory: "
grep -q "confirmDeleteCategory" modules/categories/income_list.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບ type=\"button\": "
grep -q 'type="button".*onclick="confirmDeleteCategory' modules/categories/income_list.php
check "ຖືກຕ້ອງ"

echo -n "   - ກວດສອບມີ debug logging: "
grep -q "error_log.*🗑️ Delete request" modules/categories/income_list.php
check "ມີແລ້ວ"

# 7. Expense Category Delete
echo -n "7. Expense list - ກວດສອບ confirmDeleteCategory: "
grep -q "confirmDeleteCategory" modules/categories/expense_list.php
check "พົບແລ້ວ"

echo -n "   - ກວດສອບ type=\"button\": "
grep -q 'type="button".*onclick="confirmDeleteCategory' modules/categories/expense_list.php
check "ຖືກຕ້ອງ"

echo -n "   - ກວດສອບມີ debug logging: "
grep -q "error_log.*🗑️ Delete request" modules/categories/expense_list.php
check "ມີແລ້ວ"

echo ""
echo -e "${BLUE}📁 ກວດສອບໄຟລ໌ທີ່ຕ້ອງ Upload${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

files=(
    "modules/income/list.php"
    "modules/expense/list.php"
    "modules/users/list.php"
    "modules/categories/income_edit.php"
    "modules/categories/expense_edit.php"
    "modules/categories/income_list.php"
    "modules/categories/expense_list.php"
)

for file in "${files[@]}"; do
    echo -n "   $(basename $file): "
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ ມີ${NC}"
        ((passed++))
    else
        echo -e "${RED}❌ ຂາດ${NC}"
        ((failed++))
    fi
done

echo ""
echo "╔════════════════════════════════════════╗"
echo "║           ສະຫຼຸບຜົນການກວດສອບ          ║"
echo "╚════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}✅ ຜ່ານ: $passed${NC}"
echo -e "${RED}❌ ບໍ່ຜ່ານ: $failed${NC}"
echo ""

if [ $failed -eq 0 ]; then
    echo -e "${GREEN}┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓${NC}"
    echo -e "${GREEN}┃  🎉 ພ້ອມ Upload ແລ້ວ!          ┃${NC}"
    echo -e "${GREEN}┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛${NC}"
    echo ""
    echo "📦 ໄຟລ໌ທີ່ຕ້ອງ Upload:"
    echo ""
    for file in "${files[@]}"; do
        echo "   ✓ $file"
    done
    echo ""
    echo "📖 ອ່ານລາຍລະອຽດໃນ: DEPLOY_CHECKLIST.md"
    echo ""
    exit 0
else
    echo -e "${RED}┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓${NC}"
    echo -e "${RED}┃  ⚠️  ມີບັນຫາ! ກວດສອບອີກຄັ້ງ    ┃${NC}"
    echo -e "${RED}┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛${NC}"
    echo ""
    exit 1
fi
