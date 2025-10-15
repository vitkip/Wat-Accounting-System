# 🏛️ ລະບົບບັນຊີວັດ (Wat Accounting System)# 🏛️ ລະບົບບັນຊີວັດ (Wat Accounting System)



[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://www.php.net/)ລະບົບຈັດການບັນຊີວັດອອນໄລນ໌ ພັດທະນາດ້ວຍ PHP + MySQL + TailwindCSS

[![MySQL Version](https://img.shields.io/badge/MySQL-%3E%3D5.7-orange.svg)](https://www.mysql.com/)

[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)**ເວີຊັນ:** 1.0.0 | **ສ້າງເມື່ອ:** 14 ຕຸລາ 2025 | **ສະຖານະ:** ✅ ພ້ອມໃຊ້ງານ

[![Version](https://img.shields.io/badge/Version-2.0.0-brightgreen.svg)](https://github.com/yourusername/wat-accounting)

[![Mobile Responsive](https://img.shields.io/badge/Mobile-Responsive-success.svg)](https://github.com/yourusername/wat-accounting)---



**ເວີຊັນ:** 2.0.0 | **ອັບເດດລ່າສຸດ:** 15 ຕຸລາ 2025 | **ສະຖານະ:** ✅ ພ້ອມໃຊ້ງານ## 📋 ຄຸນສົມບັດຫຼັກ



ລະບົບບັນຊີວັດແມ່ນຊອຟແວເພື່ອຈັດການບັນຊີ, ລາຍຮັບ-ລາຍຈ່າຍ, ແລະ ລາຍງານການເງິນສຳລັບວັດຕ່າງໆ ພາຍໃນປະເທດລາວ. ລະບົບມີຄວາມສາມາດໃນການຮອງຮັບຫຼາຍວັດ (Multi-Temple System) ພ້ອມກັບການແຍກຂໍ້ມູນລະຫວ່າງວັດຢ່າງເຂັ້ມງວດ, ລະບົບສິດ 3 ລະດັບ (Super Admin, Admin, User), ແລະ UI/UX ທີ່ຮອງຮັບ mobile devices ທຸກຂະໜາດ.### 🔐 ລະບົບຄວາມປອດໄພ

- ✅ ລະບົບເຂົ້າສູ່ລະບົບທີ່ປອດໄພ (Session + CSRF Protection)

---- ✅ Password Hashing (password_hash/verify)

- ✅ CSRF Token Protection ທຸກຟອມ

## ✨ ມີຫຍັງໃໝ່ໃນເວີຊັນ 2.0.0- ✅ XSS Prevention (htmlspecialchars)

- ✅ SQL Injection Protection (PDO Prepared Statements)

- **ລະບົບຫຼາຍວັດ (Multi-Temple System):** ຮອງຮັບການຈັດການຫຼາຍວັດພາຍໃນລະບົບດຽວ, ໂດຍຂໍ້ມູນຂອງແຕ່ລະວັດຈະຖືກແຍກອອກຈາກກັນຢ່າງຊັດເຈນ.- ✅ Session Security (HttpOnly, Secure Cookies)

- **ລະບົບສິດທິ 3 ລະດັບ:**- ✅ Session Timeout (ປັບແຕ່ງໄດ້)

    - **Super Admin:** ຈັດການລະບົບທັງໝົດ, ເບິ່ງຂໍ້ມູນທຸກວັດ, ແລະ ສ້າງວັດໃໝ່.- ✅ Audit Log ບັນທຶກທຸກການກະທຳ

    - **Admin:** ຈັດການຂໍ້ມູນສະເພາະວັດຂອງຕົນເອງ.

    - **User:** ເບິ່ງ ແລະ ບັນທຶກຂໍ້ມູນພາຍໃນວັດຂອງຕົນເອງຕາມສິດທີ່ໄດ້ຮັບ.### 💰 ການຈັດການລາຍຮັບ-ລາຍຈ່າຍ

- **UI/UX ຮອງຮັບ Mobile:** ໜ້າຕ່າງໆ ເຊັ່ນ: ການເພີ່ມລາຍຮັບ-ລາຍຈ່າຍ ໄດ້ຖືກປັບປຸງໃຫ້ສາມາດໃຊ້ງານເທິງມືຖືໄດ້ສະດວກ.- ✅ ເພີ່ມ, ແກ້ໄຂ, ລຶບລາຍຮັບ

- **ການປັບປຸງລະບົບລາຍງານ:** ລາຍງານທຸກປະເພດ (ປະຈຳເດືອນ, ປະຈຳປີ) ຈະສະແດງຂໍ້ມູນຕາມສິດ ແລະ ວັດຂອງຜູ້ໃຊ້.- ✅ ເພີ່ມ, ແກ້ໄຂ, ລຶບລາຍຈ່າຍ

- **Dynamic Temple Name:** ຫົວຂອງໜ້າລາຍງານຕ່າງໆຈະສະແດງຊື່ວັດອັດຕະໂນມັດຕາມຂໍ້ມູນຂອງວັດນັ້ນໆ.- ✅ ຄົ້ນຫາ ແລະ ກັ່ນຕອງຂໍ້ມູນ

- ✅ ຟໍແມັດຕົວເລກອັດຕະໂນມັດ (1.000.000 ກີບ)

---- ✅ ຄຳນວນຍອດລວມອັດຕະໂນມັດ

- ✅ ການຢັ້ງຢືນກ່ອນລຶບ (SweetAlert2)

## 📋 ຄຸນສົມບັດຫຼັກ

### 🏷️ ລະບົບຈັດການໝວດໝູ່

### 🏛️ ລະບົບຈັດການຫຼາຍວັດ- ✅ ຈັດການໝວດໝູ່ລາຍຮັບ (ແຍກຕ່າງຫາກ)

- ✅ ສ້າງ, ແກ້ໄຂ, ແລະ ຈັດການວັດໄດ້ບໍ່ຈຳກັດ.- ✅ ຈັດການໝວດໝູ່ລາຍຈ່າຍ (ແຍກຕ່າງຫາກ)

- ✅ ຂໍ້ມູນທັງໝົດ (ລາຍຮັບ, ລາຍຈ່າຍ, ຜູ້ໃຊ້, ໝວດໝູ່) ຖືກແຍກຕາມແຕ່ລະວັດ (Data Isolation).- ✅ ສະແດງສະຖິຕິການໃຊ້ງານແຕ່ລະໝວດໝູ່

- ✅ Super Admin ສາມາດສະຫຼັບ (Switch) ເພື່ອເບິ່ງ ແລະ ຈັດການຂໍ້ມູນຂອງແຕ່ລະວັດໄດ້.- ✅ ປ້ອງກັນການລຶບໝວດໝູ່ທີ່ກຳລັງຖືກໃຊ້ງານ

- ✅ ສະແດງສະຖິຕິລວມຂອງທຸກວັດ ແລະ ສະຖິຕິແຍກຕາມແຕ່ລະວັດ.- ✅ ແກ້ໄຂຊື່ ແລະ ລາຍລະອຽດໄດ້



### 🔐 ລະບົບຄວາມປອດໄພ ແລະ ສິດທິຜູ້ໃຊ້### 📊 ລາຍງານທີ່ຄົບຖ້ວນ

- ✅ ລະບົບສິດທິ 3 ລະດັບ: Super Admin, Admin, ແລະ User.- ✅ ລາຍງານປະຈຳເດືອນ (ລາຍລະອຽດ)

- ✅ ລະບົບເຂົ້າສູ່ລະບົບທີ່ປອດໄພ (Password Hashing, Session Protection).- ✅ **ລາຍງານປະຈຳປີ 12 ເດືອນ** (ສະຫຼຸບທັງປີ) ⭐ ໃໝ່!

- ✅ ປ້ອງກັນ CSRF, XSS, ແລະ SQL Injection.- ✅ ກາຟ Chart.js (Bar Chart, Line Chart)

- ✅ Audit Log ບັນທຶກທຸກການເຄື່ອນໄຫວສຳຄັນພາຍໃນລະບົບ.- ✅ ການວິເຄາະຕາມໝວດໝູ່

- ✅ ພິມລາຍງານແບບມືອາຊີບ (A4 Portrait/Landscape)

### 💰 ການຈັດການລາຍຮັບ-ລາຍຈ່າຍ- ✅ ຕາຕະລາງຂໍ້ມູນທີ່ສະອາດງາມ

- ✅ ເພີ່ມ, ແກ້ໄຂ, ລຶບລາຍຮັບ-ລາຍຈ່າຍ ໂດຍຂໍ້ມູນຈະຜູກກັບວັດຂອງຜູ້ໃຊ້.- ✅ ລາຍເຊັນສຳລັບເຈົ້າອະທິການ ແລະ ຜູ້ກ່ຽວຂ້ອງ

- ✅ ຄົ້ນຫາ ແລະ ກັ່ນຕອງຂໍ້ມູນຕາມວັນທີ, ໝວດໝູ່, ຫຼື ຄຳຄົ້ນ.

- ✅ ຟໍແມັດຕົວເລກອັດຕະໂນມັດເພື່ອให้อ่านง่าย.### 👥 ການຈັດການຜູ້ໃຊ້

- ✅ ເພີ່ມ, ແກ້ໄຂ, ລຶບຜູ້ໃຊ້ (ສຳລັບແອດມິນ)

### 📊 ລາຍງານທີ່ຄົບຖ້ວນ ແລະ ຖືກຕ້ອງຕາມສິດ- ✅ ສິດຂອງຜູ້ໃຊ້ 2 ລະດັບ (Admin, User)

- ✅ ລາຍງານສະແດງຂໍ້ມູນຕາມສິດ (Super Admin ເຫັນລວມທຸກວັດ, Admin/User ເຫັນສະເພາະວັດຕົນເອງ).- ✅ ປ່ຽນລະຫັດຜ່ານໄດ້

- ✅ ລາຍງານປະຈຳເດືອນ ແລະ ປະຈຳປີ (12 ເດືອນ).- ✅ ບັນທຶກການເຄື່ອນໄຫວຂອງຜູ້ໃຊ້

- ✅ ກາຟ Chart.js ສຳລັບການວິເຄາະຂໍ້ມູນ.

- ✅ ພິມລາຍງານແບບມືອາຊີບພ້ອມສະແດງຊື່ວັດອັດຕະໂນມັດ.### 🎨 UI/UX ທີ່ທັນສະໄໝ

- ✅ SweetAlert2 ສຳລັບ Alert/Confirm ທີ່ສວຍງາມ

### 📱 UI/UX ທີ່ທັນສະໄໝ ແລະ ຮອງຮັບ Mobile- ✅ Responsive Design (Desktop, Tablet, Mobile)

- ✅ ອອກແບບໃຫ້ສາມາດໃຊ້ງານໄດ້ດີທັງໃນ Desktop ແລະ Mobile (Responsive Design).- ✅ ພາສາລາວທັງລະບົບ

- ✅ ຟອມຕ່າງໆ ເຊັ່ນ: ເພີ່ມລາຍຮັບ-ລາຍຈ່າຍ ປັບ layout ເປັນ 1 column ໃນມືຖື.- ✅ ຟ້ອນ Phetsarath (Google Fonts)

- ✅ ປຸ່ມ ແລະ form elements ມີຂະໜາດທີ່ເໝາະສົມກັບການสัมผัส (Touch-Friendly).- ✅ Tailwind CSS 3.x

- ✅ Icons SVG ທີ່ສວຍງາມ

---

---

## 🚀 ການຕິດຕັ້ງ ແລະ ອັບເກຣດ

## 🔒 ຄວາມປອດໄພລະດັບສູງ

### ຄວາມຕ້ອງການຂອງລະບົບ

- **PHP:** 7.4 ຫຼືສູງກວ່າ| ຄຸນສົມບັດ | ລາຍລະອຽດ |

- **MySQL:** 5.7 ຫຼືສູງກວ່າ (ຮອງຮັບ MariaDB)|-----------|----------|

- **Web Server:** Apache ຫຼື Nginx| 🔐 **Password Security** | `password_hash()` + Bcrypt algorithm |

- **Browser:** Chrome, Firefox, Safari, Edge ເວີຊັນລ່າສຸດ| 🔐 **SQL Injection** | PDO + Prepared Statements |

| 🔐 **CSRF Protection** | Token validation ທຸກຟອມ |

### ຂັ້ນຕອນການຕິດຕັ້ງໃໝ່ ຫຼື ອັບເກຣດຈາກເວີຊັນເກົ່າ| 🔐 **XSS Prevention** | `htmlspecialchars()` + `e()` helper |

1.  **Backup ຂໍ້ມູນ:** (ສຳຄັນຫຼາຍ!) ສຳຮອງຖານຂໍ້ມູນ ແລະ ໄຟລ໌ໂຄດທັງໝົດຂອງທ່ານໄວ້ກ່ອນ.| 🔐 **Session Security** | HttpOnly, Secure Cookies, SameSite |

2.  **Upload ໄຟລ໌:** ນຳໄຟລ໌ໂຄດເວີຊັນໃໝ່ໄປວາງທັບໄຟລ໌ເກົ່າ.| 🔐 **Session Timeout** | ປັບແຕ່ງໄດ້ (ເລີ່ມຕົ້ນ 1 ຊົ່ວໂມງ) |

3.  **ເປີດສະຄຣິບອັບເກຣດ:** ເຂົ້າໄປທີ່ URL: `http://your-domain/check_and_upgrade.php`| 🔐 **Audit Logging** | ບັນທຶກທຸກການກະທຳສຳຄັນ |

4.  **ກວດສອບ:** ສະຄຣິບຈະກວດສອບ ແລະ ສ້າງຕາຕະລາງ, columns, ແລະ ຂໍ້ມູນທີ່ຈຳເປັນສຳລັບລະບົບ Multi-Temple ໂດຍອັດຕະໂນມັດ.| 🔐 **Error Handling** | ຂໍ້ຜິດພາດບໍ່ສະແດງລາຍລະອຽດລະບົບ |

    - ຈະສ້າງຕາຕະລາງ `temples`.

    - ຈະເພີ່ມ column `temple_id` ແລະ `is_super_admin` ໃນຕາຕະລາງ `users`.---

    - ຈະເພີ່ມ column `temple_id` ໃນຕາຕະລາງ `income`, `expense`, ແລະ `categories`.

    - ຈະສ້າງບັນຊີ `superadmin` ຂຶ້ນມາ.## 📦 ຄວາມຕ້ອງການລະບົບ

5.  **ບັນທຶກຂໍ້ມູນ Super Admin:**

    - **Username:** `superadmin`### ຂັ້ນຕ່ຳ (Minimum Requirements)

    - **Password:** `admin123`- **PHP:** 7.4 ຫຼືສູງກວ່າ

    > **ຄຳເຕືອນ:** ກະລຸນາປ່ຽນລະຫັດຜ່ານທັນທີຫຼັງຈາກເຂົ້າສູ່ລະບົບຄັ້ງທຳອິດ!- **MySQL:** 5.7 ຫຼືສູງກວ່າ (ແນະນຳ MySQL 8.0+)

6.  **ລຶບໄຟລ໌:** ເພື່ອຄວາມປອດໄພ, ໃຫ້ລຶບໄຟລ໌ `check_and_upgrade.php` ອອກຫຼັງຈາກຕິດຕັ້ງສຳເລັດ.- **Web Server:** Apache 2.4+ ຫຼື Nginx 1.18+

- **PHP Extensions:**

---  - PDO + PDO_MySQL

  - mbstring

## 🗂️ ໂຄງສ້າງຖານຂໍ້ມູນ (Database Schema)  - session

  - json

### ຕາຕະລາງໃໝ່- **RAM:** ຢ່າງໜ້ອຍ 512MB

- `temples`: ເກັບຂໍ້ມູນຫຼັກຂອງແຕ່ລະວັດ.- **Disk Space:** 100MB+

- `temple_settings`: ເກັບຄ່າການຕັ້ງຄ່າຕ່າງໆຂອງແຕ່ລະວັດ.

### ແນະນຳ (Recommended)

### Columns ທີ່ເພີ່ມໃສ່ຕາຕະລາງເກົ່າ- **PHP:** 8.0+

- `users`:- **MySQL:** 8.0+

    - `temple_id` (INT): ລະຫັດຂອງວັດທີ່ຜູ້ໃຊ້ນັ້ນສັງກັດ (NULL ສຳລັບ Super Admin).- **RAM:** 1GB+

    - `is_super_admin` (TINYINT): ບອກວ່າເປັນ Super Admin (1) ຫຼືບໍ່ (0).- **SSD Storage:** 500MB+

- `income`, `expense`, `income_categories`, `expense_categories`, `audit_log`:

    - `temple_id` (INT): ລະຫັດຂອງວັດທີ່ຂໍ້ມູນນັ້ນເປັນເຈົ້າຂອງ.---



---## 🚀 ວິທີການຕິດຕັ້ງ



## 🔧 ການແກ້ໄຂບັນຫາ (Troubleshooting)### วิธีที่ 1: ຕິດຕັ້ງດ້ວຍ XAMPP (Windows/Mac/Linux)



- **ບັນຫາ: ຫຼັງອັບເກຣດ, ເຂົ້າລະບົບດ້ວຍບັນຊີເກົ່າແລ້ວບໍ່ເຫັນຂໍ້ມູນ.**#### ຂັ້ນຕອນທີ 1: ດາວໂຫຼດ XAMPP

  - **ສາເຫດ:** ຂໍ້ມູນເກົ່າທັງໝົດຈະຖືກย้ายไปอยู่ภายใต้ "ວັດເລີ່ມຕົ້ນ" (ID 1). ບັນຊີຂອງທ່ານອາດຈະບໍ່ໄດ້ຜູກກັບວັດນີ້.1. ໄປທີ່: https://www.apachefriends.org/

  - **ວິທີແກ້:** ໃຫ້ Super Admin ເຂົ້າໄປກວດສອບ ແລະ กำหนด `temple_id` ໃຫ້ກັບບັນຊີຂອງທ່ານໃນໜ້າຈັດການຜູ້ໃຊ້.2. ດາວໂຫຼດເວີຊັນທີ່ເໝາະສົມກັບລະບົບປະຕິບັດການຂອງທ່ານ

- **ບັນຫາ: "Access Denied" ຫຼື "ບໍ່ມີສິດเข้าถึง".**3. ຕິດຕັ້ງ XAMPP ຕາມຂັ້ນຕອນ

  - **ສາເຫດ:** ທ່ານກຳລັງພະຍາຍາມເຂົ້າເຖິງຂໍ້ມູນຂອງວັດອື່ນທີ່ທ່ານບໍ່ມີສິດ.

  - **ວິທີແກ້:** ກວດສອບວ່າທ່ານໄດ້ເຂົ້າສູ່ລະບົບຖືກຕ້ອງ ແລະ ບໍ່ໄດ້ພະຍາຍາມປ້ອນ URL ຂອງຂໍ້ມູນທີ່ບໍ່ใช่ของคุณโดยตรง.#### ຂັ້ນຕອນທີ 2: ວາງໄຟລ໌ລະບົບ

- **ບັນຫາ: ໜ້າເພີ່ມລາຍຮັບ/ລາຍຈ່າຍ ເພี้ยนໃນມືຖື.**Copy ໂຟລເດີ `watsystem` ໄປໃສ່:

  - **ສາເຫດ:** Cache ຂອງ Browser ອາດຈະຍັງຈື່ CSS ເວີຊັນເກົ່າ.

  - **ວິທີແກ້:** ລອງ Clear Cache ຂອງ Browser (Ctrl+Shift+R ຫຼື Cmd+Shift+R).```bash

# Windows

---C:\xampp\htdocs\watsystem



## 💻 ເທັກໂນໂລຢີທີ່ໃຊ້# Mac

- **Backend:** PHP 7.4+/Applications/XAMPP/xamppfiles/htdocs/watsystem

- **Database:** MySQL / MariaDB

- **Frontend:** HTML, TailwindCSS v3, JavaScript (ES6)# Linux

- **Libraries:** Chart.js, SweetAlert2, jQuery, Date-fns/opt/lampp/htdocs/watsystem

```

---

#### ຂັ້ນຕອນທີ 3: Start Services

## 📝 License1. ເປີດ XAMPP Control Panel

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.2. Start Apache

3. Start MySQL

---4. ກວດເບີ່ງວ່າທັງສອງເປັນສີຂຽວ ✅



**ພັດທະນາໂດຍ:** GitHub Copilot & Ananthasak Phommasone | **ຕິດຕໍ່:** ananthasak.p@gmail.com#### ຂັ້ນຕອນທີ 4: ສ້າງຖານຂໍ້ມູນ
1. ເປີດເວັບບຣາວເຊີ ແລະ ໄປທີ່: `http://localhost/phpmyadmin`
2. ຄລິກ **"New"** (ຊ້າຍມື)
3. **Database name:** `wat_accounting`
4. **Collation:** `utf8mb4_unicode_ci`
5. ຄລິກ **"Create"**

#### ຂັ້ນຕອນທີ 5: Import ຂໍ້ມູນ
1. ເລືອກຖານຂໍ້ມູນ `wat_accounting` ທີ່ຫາກໍສ້າງ
2. ຄລິກແຖບ **"Import"**
3. ຄລິກ **"Choose File"**
4. ເລືອກໄຟລ໌: `watsystem/database.sql`
5. ຄລິກ **"Go"** (ລຸ່ມສຸດ)
6. ລໍຖ້າຈົນກວ່າຈະຂຶ້ນຂໍ້ຄວາມສີຂຽວ ✅

#### ຂັ້ນຕອນທີ 6: ແກ້ໄຂການຕັ້ງຄ່າ (ຖ້າຈຳເປັນ)
ເປີດໄຟລ໌ `watsystem/config.php` ແລະກວດສອບ:

```php
define('DB_HOST', 'localhost');      // ປົກກະຕິບໍ່ຕ້ອງປ່ຽນ
define('DB_NAME', 'wat_accounting'); // ຊື່ຖານຂໍ້ມູນ
define('DB_USER', 'root');           // ຊື່ຜູ້ໃຊ້ MySQL (ເລີ່ມຕົ້ນ: root)
define('DB_PASS', '');               // ລະຫັດຜ່ານ MySQL (ເລີ່ມຕົ້ນ: ເປົ່າ)
define('SITE_NAME', 'ວັດປ່າໜອງບົວທອງໃຕ້'); // ປ່ຽນເປັນຊື່ວັດຂອງທ່ານ
define('BASE_URL', 'http://localhost/watsystem'); // URL ລະບົບ
```

#### ຂັ້ນຕອນທີ 7: ເຂົ້າໃຊ້ລະບົບ
1. ເປີດເວັບບຣາວເຊີ
2. ໄປທີ່: `http://localhost/watsystem`
3. ເຂົ້າສູ່ລະບົບດ້ວຍບັນຊີທົດສອບ (ເບີ່ງລຸ່ມນີ້)

---

### ວິທີທີ 2: ຕິດຕັ້ງດ້ວຍ Laragon (Windows)

Laragon ເປັນເຄື່ອງມືທີ່ງ່າຍກວ່າແລະໄວກວ່າສຳລັບ Windows:

1. **ດາວໂຫຼດ Laragon:** https://laragon.org/download/
2. **ຕິດຕັ້ງ Laragon** (Full version ແນະນຳ)
3. **Copy ໂຟລເດີ:** `C:\laragon\www\watsystem`
4. **Start Laragon:** ຄລິກປຸ່ມ "Start All"
5. **ເປີດ phpMyAdmin:** ຄລິກຂວາ Laragon icon → Quick app → phpMyAdmin
6. **ສ້າງຖານຂໍ້ມູນ ແລະ Import** ຕາມຂັ້ນຕອນຂ້າງເທິງ
7. **ເຂົ້າໃຊ້:** `http://watsystem.test` (Laragon ສ້າງ virtual host ອັດຕະໂນມັດ)

---

## 👤 ບັນຊີເຂົ້າໃຊ້ງານ

### 🔑 ບັນຊີແອດມິນ (ສິດເຕັມ)
```
ຊື່ຜູ້ໃຊ້: admin
ລະຫັດຜ່ານ: admin123
```
**ສິດ:** ເຂົ້າເຖິງທຸກໜ້າ, ຈັດການຜູ້ໃຊ້, ແກ້ໄຂ/ລຶບທຸກລາຍການ

### 👤 ບັນຊີຜູ້ໃຊ້ທົ່ວໄປ
```
ຊື່ຜູ້ໃຊ້: user1
ລະຫັດຜ່ານ: admin123
```
**ສິດ:** ເບີ່ງຂໍ້ມູນ, ເພີ່ມລາຍການ, ບໍ່ສາມາດຈັດການຜູ້ໃຊ້

⚠️ **ສຳຄັญຫຼາຍ:** ກະລຸນາປ່ຽນລະຫັດຜ່ານທັນທີຫຼັງຈາກເຂົ້າໃຊ້ງານຄັ້ງທຳອິດ!

---

## 📁 ໂຄງສ້າງໂຟລເດີ

```
watsystem/
├── 📄 config.php                    # ການຕັ້ງຄ່າລະບົບທັງໝົດ
├── 📄 database.sql                  # ໄຟລ໌ສ້າງຖານຂໍ້ມູນ
├── 📄 index.php                     # ໜ້າຫຼັກ Dashboard
├── 📄 login.php                     # ໜ້າເຂົ້າສູ່ລະບົບ
├── 📄 logout.php                    # ອອກຈາກລະບົບ
├── 📄 README.md                     # ເອກະສານນີ້
│
├── 📁 includes/                     # ໄຟລ໌ສ່ວນກາງ
│   ├── header.php                   # Header + Navigation Menu
│   ├── footer.php                   # Footer
│   └── csrf.php                     # CSRF Protection Functions
│
└── 📁 modules/                      # ໂມດູນຕ່າງໆ
    │
    ├── 📁 income/                   # ໂມດູນລາຍຮັບ
    │   ├── add.php                  # ເພີ່ມລາຍຮັບ
    │   ├── edit.php                 # ແກ້ໄຂລາຍຮັບ
    │   ├── delete.php               # ລຶບລາຍຮັບ
    │   └── list.php                 # ລາຍການລາຍຮັບ
    │
    ├── 📁 expense/                  # ໂມດູນລາຍຈ່າຍ
    │   ├── add.php                  # ເພີ່ມລາຍຈ່າຍ
    │   ├── edit.php                 # ແກ້ໄຂລາຍຈ່າຍ
    │   ├── delete.php               # ລຶບລາຍຈ່າຍ
    │   └── list.php                 # ລາຍການລາຍຈ່າຍ
    │
    ├── 📁 categories/               # ໂມດູນຈັດການໝວດໝູ່
    │   ├── index.php                # ໜ້າຫຼັກໝວດໝູ່
    │   ├── income_list.php          # ລາຍການໝວດໝູ່ລາຍຮັບ
    │   ├── income_add.php           # ເພີ່ມໝວດໝູ່ລາຍຮັບ
    │   ├── income_edit.php          # ແກ້ໄຂໝວດໝູ່ລາຍຮັບ
    │   ├── expense_list.php         # ລາຍການໝວດໝູ່ລາຍຈ່າຍ
    │   ├── expense_add.php          # ເພີ່ມໝວດໝູ່ລາຍຈ່າຍ
    │   └── expense_edit.php         # ແກ້ໄຂໝວດໝູ່ລາຍຈ່າຍ
    │
    ├── 📁 report/                   # ໂມດູນລາຍງານ
    │   ├── index.php                # ໜ້າຫຼັກລາຍງານ (ເລືອກເດືອນ)
    │   ├── summary.php              # ລາຍງານປະຈຳເດືອນ (ລາຍລະອຽດ)
    │   └── annual_summary.php       # ⭐ ລາຍງານປະຈຳປີ 12 ເດືອນ (ໃໝ່!)
    │
    └── 📁 users/                    # ໂມດູນຜູ້ໃຊ້ (Admin only)
        ├── add.php                  # ເພີ່ມຜູ້ໃຊ້
        ├── edit.php                 # ແກ້ໄຂຜູ້ໃຊ້
        ├── delete.php               # ລຶບຜູ້ໃຊ້
        └── list.php                 # ລາຍການຜູ້ໃຊ້
```

---

## 🎨 ເຕັກໂນໂລຊີທີ່ໃຊ້

| ປະເພດ | ເຕັກໂນໂລຊີ | ເວີຊັນ | ໝາຍເຫດ |
|-------|------------|--------|---------|
| **Backend** | PHP | 7.4+ | ແນະນຳ PHP 8.0+ |
| **Database** | MySQL | 5.7+ | ແນະນຳ MySQL 8.0+ |
| **CSS Framework** | TailwindCSS | 3.x | ຜ່ານ CDN |
| **Charts** | Chart.js | 4.x | ກາຟ Bar, Line, Pie |
| **Alerts** | SweetAlert2 | 11.x | Alert/Confirm ສວຍງາມ |
| **Font** | Phetsarath | - | Google Fonts (ລາວ) |
| **Icons** | SVG Icons | - | Built-in |
| **Security** | PDO, CSRF | - | Built-in |

### � CDN ທີ່ໃຊ້:
```html
<!-- TailwindCSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Phetsarath Font -->
<link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap">
```

---

## �📊 ຕາຕະລາງຖານຂໍ້ມູນ

### 1️⃣ **users** - ຂໍ້ມູນຜູ້ໃຊ້ລະບົບ
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR 50, UNIQUE)
- password (VARCHAR 255, Hashed)
- full_name (VARCHAR 100)
- role (ENUM: 'admin', 'user')
- created_at (TIMESTAMP)
```

### 2️⃣ **income_categories** - ໝວດໝູ່ລາຍຮັບ
```sql
- id (INT, PRIMARY KEY)
- name (VARCHAR 100, UNIQUE)
- description (TEXT, NULLABLE)
- created_at (TIMESTAMP)
```

### 3️⃣ **expense_categories** - ໝວດໝູ່ລາຍຈ່າຍ
```sql
- id (INT, PRIMARY KEY)
- name (VARCHAR 100, UNIQUE)
- description (TEXT, NULLABLE)
- created_at (TIMESTAMP)
```

### 4️⃣ **income** - ລາຍຮັບ
```sql
- id (INT, PRIMARY KEY)
- date (DATE)
- category (VARCHAR 100) -- ເຊື່ອມຕໍ່ກັບ income_categories.name
- amount (DECIMAL 15,2)
- description (TEXT)
- receipt_number (VARCHAR 50, NULLABLE)
- donor_name (VARCHAR 100, NULLABLE)
- created_by (INT, FK → users.id)
- created_at (TIMESTAMP)
```

### 5️⃣ **expense** - ລາຍຈ່າຍ
```sql
- id (INT, PRIMARY KEY)
- date (DATE)
- category (VARCHAR 100) -- ເຊື່ອມຕໍ່ກັບ expense_categories.name
- amount (DECIMAL 15,2)
- description (TEXT)
- receipt_number (VARCHAR 50, NULLABLE)
- vendor_name (VARCHAR 100, NULLABLE)
- created_by (INT, FK → users.id)
- created_at (TIMESTAMP)
```

### 6️⃣ **audit_log** - ບັນທຶກການເຄື່ອນໄຫວ
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FK → users.id)
- action (VARCHAR 50) -- INSERT, UPDATE, DELETE
- table_name (VARCHAR 50)
- record_id (INT)
- old_data (TEXT, JSON)
- new_data (TEXT, JSON)
- ip_address (VARCHAR 45)
- user_agent (VARCHAR 255)
- created_at (TIMESTAMP)
```

**ໝາຍເຫດສຳຄັນ:**
- ການເຊື່ອມຕໍ່ລະຫວ່າງ `income/expense` ກັບ `categories` ໃຊ້ **ຊື່ໝວດໝູ່** (name) ແທນ Foreign Key ID
- ຈຸດປະສົງ: ຖ້າລຶບໝວດໝູ່ທີ່ບໍ່ໄດ້ໃຊ້ ຈະບໍ່ກະທົບຕໍ່ລາຍການທີ່ມີຢູ່ແລ້ວ

---

## 🔧 ການແກ້ໄຂບັນຫາທົ່ວໄປ

### 🚨 ບັນຫາ 1: ບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້

**ອາການ:** ຂຶ້ນຂໍ້ຄວາມ "ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນຜິດພາດ"

**ວິທີແກ້ໄຂ:**
1. ກວດສອບວ່າ MySQL ເປີດແລ້ວບໍ່ (XAMPP Control Panel → MySQL ຕ້ອງເປັນສີຂຽວ)
2. ກວດສອບການຕັ້ງຄ່າໃນ `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'wat_accounting');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // ເປົ່າສຳລັບ XAMPP
   ```
3. ກວດວ່າຖານຂໍ້ມູນ `wat_accounting` ຖືກສ້າງແລ້ວ (ເຂົ້າ phpMyAdmin ເບີ່ງ)
4. ກວດວ່າ Import ໄຟລ໌ `database.sql` ສຳເລັດແລ້ວ

---

### 🚨 ບັນຫາ 2: ຟອນລາວບໍ່ສະແດງຜົນ

**ອາການ:** ຕົວອັກສອນລາວປະກົດເປັນເຄື່ອງໝາຍ □□□

**ວິທີແກ້ໄຂ:**
1. **ກວດເບີ່ງການເຊື່ອມຕໍ່ອິນເຕີເນັດ** (ຕ້ອງການສຳລັບ Google Fonts)
2. **ລອງໃໝ່:** Hard Refresh ດ້ວຍ Ctrl+F5 (Windows) ຫຼື Cmd+Shift+R (Mac)
3. **ວິທີສຳຮອງ:** ດາວໂຫຼດຟອນ Phetsarath ມາໃສ່ໃນໂຟລເດີ໣ `assets/fonts/`

---

### 🚨 ບັນຫາ 3: CSRF Token Error

**ອາການ:** ຂຶ້ນຂໍ້ຄວາມ "ຂໍ້ຜິດພາດຄວາມປອດໄພ: CSRF Token ບໍ່ຖືກຕ້ອງ"

**ວິທີແກ້ໄຂ:**
1. **ລ້າງ Cache ເວັບບຣາວເຊີ:**
   - Chrome: Ctrl+Shift+Delete → Clear cache
   - Firefox: Ctrl+Shift+Delete → Clear cache
2. **ລ້າງ Session:**
   - ອອກຈາກລະບົບ → ປິດເວັບບຣາວເຊີ → ເປີດໃໝ່
3. **ກວດສອບວ່າ Session ເຮັດວຽກບໍ່:**
   ```php
   <?php
   session_start();
   echo session_status() === PHP_SESSION_ACTIVE ? 'Session OK' : 'Session Error';
   ```
4. **Hard Refresh:** Ctrl+F5

---

### 🚨 ບັນຫາ 4: Session Timeout ໄວເກີນໄປ

**ອາການ:** ອອກຈາກລະບົບອັດຕະໂນມັດບໍ່ເຖິງ 5 ນາທີ

**ວິທີແກ້ໄຂ:**
ແກ້ໄຂຄ່າ `SESSION_LIFETIME` ໃນ `config.php`:
```php
define('SESSION_LIFETIME', 7200);  // 2 ຊົ່ວໂມງ (7200 ວິນາທີ)
// ຫຼື
define('SESSION_LIFETIME', 28800); // 8 ຊົ່ວໂມງ (ເຮັດວຽກທັງວັນ)
```

---

### 🚨 ບັນຫາ 5: SweetAlert2 ບໍ່ທຳງານທັນທີ

**ອາການ:** ກົດປຸ່ມລຶບ/ບັນທຶກ ຄັ້ງທຳອິດບໍ່ມີ alert, ຕ້ອງ refresh ກ່ອນ

**ສາເຫດ:** CDN Loading ຊ້າກວ່າ JavaScript ຂອງລະບົບ

**ວິທີແກ້ໄຂ:**
ລະບົບໄດ້ແກ້ໄຂແລ້ວໃນ `includes/header.php` ດ້ວຍການ:
1. ໃຊ້ IIFE (Immediately Invoked Function Expression)
2. ກວດສອບວ່າ `Swal` ໂຫຼດແລ້ວກ່ອນໃຊ້ງານ
3. ມີ retry mechanism ຖ້າຍັງບໍ່ໂຫຼດ

**ການທົດສອບ:**
1. ເປີດ Console (F12)
2. ຄວນເຫັນຂໍ້ຄວາມ: `✅ SweetAlert2 helpers loaded successfully`
3. ທົດສອບປຸ່ມລຶບ → ຄວນອອກ confirm dialog ທັນທີ

**ຖ້າຍັງບໍ່ໄດ້:**
- Clear Cache: Ctrl+Shift+Delete
- Hard Refresh: Ctrl+F5
- ກວດເບີ່ງ Network tab ວ່າ sweetalert2 ໂຫຼດ Status 200

---

### 🚨 ບັນຫາ 6: ຈຳນວນເງິນບໍ່ຖືກຕ້ອງ (Number Format)

**ອາການ:** ພິມ "1,000,000" ແຕ່ລະບົບບໍ່ຮັບ ຫຼື ບັນທຶກເປັນ 1 ກີບ

**ວິທີແກ້ໄຂ:**
ລະບົບໄດ້ແກ້ໄຂແລ້ວ! ຕອນນີ້:
- ພິມຕົວເລ input ຈະຟໍແມັດອັດຕະໂນມັດເປັນ `1.000.000`
- ລະບົບຈະເກັບຄ່າຈິງໄວ້ໃນ hidden input
- PHP ຮັບຄ່າທີ່ສະອາດແລ້ວ (1000000)

**ວິທີໃຊ້:**
- ພິມເລກ: `1000000` → ຈະກາຍເປັນ `1.000.000` ອັດຕະໂນມັດ
- ພິມມີຈຸດ: `1.000.000` → ລະບົບແປງເປັນເລກກ່ອນບັນທຶກ
- ສະກຸນເງິນ "ກີບ" ຈະຖືກເພີ່ມອັດຕະໂນມັດ

---

### 🚨 ບັນຫາ 7: ລາຍງານປະຈຳປີບໍ່ສະແດງກາຟ

**ອາການ:** ເປີດໜ້າ `annual_summary.php` ແຕ່ບໍ່ເຫັນກາຟ Bar ແລະ Line

**ວິທີແກ້ໄຂ:**
1. **ກວດການເຊື່ອມຕໍ່ອິນເຕີເນັດ** (Chart.js ໂຫຼດຈາກ CDN)
2. **ກວດ Console (F12)** ມີ error ບໍ່
3. **ກວດວ່າມີຂໍ້ມູນບໍ່:**
   - ຕ້ອງມີຂໍ້ມູນລາຍຮັບ ຫຼື ລາຍຈ່າຍໃນປີນັ້ນ
   - ຖ້າບໍ່ມີຂໍ້ມູນ ກາຟຈະບໍ່ສະແດງ
4. **Hard Refresh:** Ctrl+F5

---

### 🚨 ບັນຫາ 8: ພິມລາຍງານແລ້ວບໍ່ສວຍ

**ອາການ:** ພິມອອກເຈ້ຍແລ້ວ Layout ບໍ່ຖືກຕ້ອງ

**ວິທີແກ້ໄຂ:**
1. **ເລືອກໂໝດທີ່ຖືກຕ້ອງ:**
   - ລາຍງານເດືອນ: **Portrait** (ແນວຕັ້ງ)
   - ລາຍງານປີ: **Landscape** (ແນວນອນ)
2. **ຂະໜາດເຈ້ຍ:** A4
3. **Margins:** Default
4. **ຖອດ Headers/Footers** ຂອງບຣາວເຊີອອກ

**ການຕັ້ງຄ່າແນະນຳ:**
```
Paper size: A4
Orientation: Landscape (ສຳລັບລາຍງານປີ)
Margins: None / Minimal
Scale: 100%
Background graphics: Checked
```

---

### 🚨 ບັນຫາ 9: ບໍ່ສາມາດອັບໂຫຼດໄຟລ໌ຂະໜາດໃຫຍ່

**ອາການ:** ອັບໂຫຼດໄຟລ໌ SQL ຂະໜາດໃຫຍ່ໃນ phpMyAdmin ບໍ່ໄດ້

**ວິທີແກ້ໄຂ:**
ແກ້ໄຂໄຟລ໌ `php.ini` (ຢູ່ໃນໂຟລເດີ XAMPP):
```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 256M
```
ຫຼັງຈາກແກ້ແລ້ວ Restart Apache

---

### 🚨 ບັນຫາ 10: Port 80 ຖືກໃຊ້ງານແລ້ວ

**ອາການ:** Apache ບໍ່ສາມາດ Start ໄດ້ ເພາະ Port 80 ຖືກໃຊ້

**ວິທີແກ້ໄຂ:**

**Windows:**
```cmd
netstat -ano | findstr :80
taskkill /PID [PID_NUMBER] /F
```

**Mac/Linux:**
```bash
sudo lsof -i :80
sudo kill -9 [PID]
```

**ຫຼື ປ່ຽນ Port:**
1. ເປີດ `httpd.conf` ໃນ XAMPP
2. ຊອກຫາ `Listen 80`
3. ປ່ຽນເປັນ `Listen 8080`
4. Restart Apache
5. ເຂົ້າລະບົບດ້ວຍ: `http://localhost:8080/watsystem`

---

## 💡 Tips & Tricks

### 🎯 ເພີ່ມປະສິດທິພາບ

1. **ເປີດ PHP OPcache:**
   ```ini
   ; ໃນ php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Index Database:**
   ```sql
   -- ເພີ່ມ index ເພື່ອຄວາມໄວ
   CREATE INDEX idx_income_date ON income(date);
   CREATE INDEX idx_expense_date ON expense(date);
   CREATE INDEX idx_income_category ON income(category);
   CREATE INDEX idx_expense_category ON expense(category);
   ```

3. **Backup ອັດຕະໂນມັດ:**
   ```bash
   # ໃຊ້ cron job (Linux/Mac)
   0 2 * * * mysqldump -u root wat_accounting > backup_$(date +\%Y\%m\%d).sql
   ```

### 🔐 ຄວາມປອດໄພເພີ່ມເຕີມ

1. **ປ່ຽນ DB Password:**
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_strong_password';
   ```
   ຫຼັງຈາກນັ້ນອັບເດດ `config.php`

2. **ປິດ phpMyAdmin ໃນ Production:**
   - Rename ໂຟລເດີ phpmyadmin
   - ຫຼື ຕັ້ງ password protection

3. **ເປີດ HTTPS:**
   - ໃຊ້ SSL Certificate (Let's Encrypt ຟຣີ)
   - ແກ້ `config.php`: `BASE_URL` ເປັນ `https://`

---

## 🚀 ການພັດທະນາຕໍ່

ຄຸນສົມບັດທີ່ສາມາດເພີ່ມເຕີມ:

### ✅ ສຳເລັດແລ້ວ (v1.0.0)
- [x] ລະບົບເຂົ້າສູ່ລະບົບທີ່ປອດໄພ
- [x] ຈັດການລາຍຮັບ-ລາຍຈ່າຍ
- [x] ຈັດການໝວດໝູ່ (ແຍກລາຍຮັບ-ລາຍຈ່າຍ)
- [x] ລາຍງານປະຈຳເດືອນ
- [x] ລາຍງານປະຈຳປີ 12 ເດືອນ ⭐
- [x] ກາຟ Chart.js
- [x] ຟໍແມັດຕົວເລກອັດຕະໂນມັດ
- [x] SweetAlert2 Integration
- [x] CSRF Protection
- [x] Responsive Design
- [x] Audit Logging

### 📝 ແຜນການອະນາຄົດ (v2.0.0+)

#### 🎯 ສຳຄັນສູງ
- [ ] Export ເປັນ Excel/PDF
- [ ] Backup ອັດຕະໂນມັດ
- [ ] Dashboard Real-time
- [ ] ການກັ່ນຕອງລາຍງານແບບກຳນົດເອງ

#### 🎨 UI/UX
- [ ] Dark Mode (ໂໝດມືດ)
- [ ] Multi-language (ລາວ, ໄທ, English)
- [ ] Progressive Web App (PWA)

#### 📊 ລາຍງານຂັ້ນສູງ

#### � ລາຍງານຂັ້ນສູງ
- [ ] ການວິເຄາະແນວໂນ້ມ (Trend Analysis)
- [ ] ການຄາດການລ່ວງໜ້າ (Prediction)
- [ ] ປຽບທຽບງົບປະມານ vs ຈິງ

#### 🔔 Features ເພີ່ມເຕີມ
- [ ] ລະບົບແຈ້ງເຕືອນອີເມລ
- [ ] ການຈັດການງົບປະມານ
- [ ] Import/Export ຈາກ Excel/CSV
- [ ] ອັບໂຫຼດເອກະສານແນບ

---

## �📱 ຄຸນສົມບັດພິເສດ

### 💰 ລະບົບຟໍແມັດຕົວເລກອັດຕະໂນມັດ
- ພິມ: `1000000` → ສະແດງ: `1.000.000 ກີບ`
- ຟໍແມັດລາວ: ໃຊ້ຈຸດ (.) ແທນຈຸດຄ່ອມ
- ເກັບຄ່າຈິງໄວ້ໃນ hidden input
- ບໍ່ມີບັນຫາເວລາບັນທຶກ

### 📊 ລາຍງານປະຈຳປີ (Annual Summary) ⭐ ໃໝ່!
**ຄຸນສົມບັດ:**
- ✅ ສະແດງຂໍ້ມູນ 12 ເດືອນໃນໜ້າດຽວ
- ✅ Summary Cards: ລວມລາຍຮັບ, ລາຍຈ່າຍ, ຍອດຄົງເຫຼືອ, ສະເລ່ຍ/ເດືອນ
- ✅ ຕາຕະລາງ 12 ແຖວ: ລາຍຮັບ, ລາຍຈ່າຍ, ຍອດຄົງເຫຼືອ, ສະຖານະແຕ່ລະເດືອນ
- ✅ Bar Chart: ປຽບທຽບລາຍຮັບ-ລາຍຈ່າຍແຕ່ລະເດືອນ
- ✅ Line Chart: ກາຟຄວາມເຄື່ອນໄຫວຍອດຄົງເຫຼືອ
- ✅ ການວິເຄາະໝວດໝູ່: ສະແດງ % ແຕ່ລະໝວດໝູ່ດ້ວຍ Progress Bar
- ✅ ພິມແບບມືອາຊີບ: A4 Landscape, ມີລາຍເຊັນເຈົ້າອະທິການ
- ✅ ເລືອກປີໄດ້: ເບີ່ງຂໍ້ມູນປີກ່ອນໆ

**ວິທີໃຊ້:**
1. ໄປເມນູ "ລາຍງານ"
2. ເລືອກປີ
3. ກົດ "ລາຍງານປະຈຳປີ (12 ເດືອນ)"
4. ກົດ "ພິມລາຍງານ" ເພື່ອພິມອອກເຈ້ຍ

### 🏷️ ລະບົບໝວດໝູ່ສອງຊັ້ນ
- ແຍກໝວດໝູ່ລາຍຮັບ ແລະ ລາຍຈ່າຍ
- ສະແດງສະຖິຕິການໃຊ້ງານ
- ປ້ອງກັນການລຶບໝວດໝູ່ທີ່ກຳລັງໃຊ້ງານ
- ອັບເດດຊື່ໝວດໝູ່ຈະມີຜົນກັບລາຍການທັງໝົດອັດຕະໂນມັດ

### 🔐 ຄວາມປອດໄພຫຼາຍຊັ້ນ
1. **ຊັ້ນທີ 1:** Session Security (HttpOnly, Secure, SameSite)
2. **ຊັ້ນທີ 2:** CSRF Token Protection (ທຸກຟອມ)
3. **ຊັ້ນທີ 3:** SQL Injection Protection (PDO Prepared Statements)
4. **ຊັ້ນທີ 4:** XSS Prevention (htmlspecialchars + e() helper)
5. **ຊັ້ນທີ 5:** Password Hashing (Bcrypt)
6. **ຊັ້ນທີ 6:** Audit Logging (ບັນທຶກທຸກການກະທຳ)

### 🎨 UI/UX ທີ່ດີເລີດ
- **SweetAlert2:** Alert/Confirm ສວຍງາມ
- **Responsive:** ໃຊ້ໄດ້ທຸກອຸປະກອນ
- **ຟອນລາວ:** Phetsarath (Google Fonts)
- **Icons:** SVG Icons ຄົບຄຸມ
- **Loading States:** ສະແດງສະຖານະກຳລັງໂຫຼດ
- **Error Messages:** ພາສາລາວທັງໝົດ

---

## 📚 ເອກະສານເພີ່ມເຕີມ

### 📖 Documentation
- [SweetAlert2 Docs](https://sweetalert2.github.io/)
- [TailwindCSS Docs](https://tailwindcss.com/)
- [Chart.js Docs](https://www.chartjs.org/)
- [PHP PDO Manual](https://www.php.net/manual/en/book.pdo.php)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)

### 🛠️ Tools ທີ່ແນະນຳ
- **VS Code:** Code Editor ທີ່ດີທີ່ສຸດ
- **XAMPP:** Local Development Environment
- **HeidiSQL:** MySQL GUI Client
- **Git:** Version Control

---

## 📞 ການຊ່ວຍເຫຼືອ & ຕິດຕໍ່

### 🆘 ຕ້ອງການຄວາມຊ່ວຍເຫຼືອ?

1. **ອ່ານເອກະສານກ່ອນ:** ເບີ່ງພາກ "ການແກ້ໄຂບັນຫາ" ຂ້າງເທິງ
2. **ກວດເບີ່ງ Console:** F12 → Console (ເບີ່ງ error messages)
3. **ກວດເບີ່ງ Network:** F12 → Network (ເບີ່ງການໂຫຼດໄຟລ໌)
4. **Clear Cache:** Ctrl+Shift+Delete ແລ້ວລອງໃໝ່

---

## � ຂໍບຸນ

**ຂໍໃຫ້ລະບົບນີ້ເປັນປະໂຫຍດໃນການຈັດການບັນຊີວັດຂອງທ່ານ**

ໝາກຜົນບຸນທີ່ໄດ້ຈາກການພັດທະນາລະບົບນີ້  
ຂໍໃຫ້ສ່ວນກຸສົນໄປເຖິງ:
- ພໍ່ແມ່ຄູບາອາຈານທັງຫຼາຍ
- ຜູ້ມີພະຄຸນທັງປວງ
- ເທວະດາທັງຫຼາຍຈົ່ງໄດ້ໃຫ້ອະນຸໂມທະນາ

---

## 👨‍💻 ຜູ້ພັດທະນາ

**ພັດທະນາໂດຍ:** GitHub Copilot  
**ສ້າງໂດຍ:** ປອ.ອານັນທະສັກ ພັດທະສີລາ  
**ສ້າງເມື່ອ:** 14 ຕຸລາ 2025  
**ເວີຊັນ:** 1.0.0  
**ສະຖານະ:** ✅ ພ້ອມໃຊ້ງານ  
**License:** MIT License - ໃຊ້ໄດ້ຟຣີ  

---

## 📄 License

```
MIT License

Copyright (c) 2025 Wat Accounting System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

<div align="center">

### 🏛️ ລະບົບບັນຊີວັດ
### Wat Accounting System

**ຈັດການບັນຊີວັດຢ່າງເປັນມືອາຊີບ**

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=flat&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat)](LICENSE)

**ເວີຊັນ 1.0.0** | **14 ຕຸລາ 2025** | **✅ ພ້ອມໃຊ້ງານ**

---

**ສາທຸ ສາທຸ ສາທຸ** 🙏

</div>
