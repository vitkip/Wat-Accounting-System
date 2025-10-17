<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ໜ້າຈັດການການຕັ້ງຄ່າວັດ (Temple Settings)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireLogin();

// ດຶງ ID ຂອງວັດທີ່ກຳລັງເບິ່ງຢູ່
$currentTempleId = getCurrentTempleId();

// ຖ້າບໍ່ມີວັດທີ່ເລືອກ ຫຼື ຜູ້ໃຊ້ບໍ່ມີສິດ, ໃຫ້ກັບໄປໜ້າຫຼັກ
if (!$currentTempleId || !canAccessTemple($currentTempleId)) {
    setFlashMessage('error', 'ບໍ່ສາມາດເຂົ້າເຖິງການຕັ້ງຄ່າໄດ້.');
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// ດຶງຂໍ້ມູນວັດ - ໃຊ້ $templeData ເພື່ອບໍ່ໃຫ້ຊ້ຳກັບ $temple ໃນ header.php
$templeData = getTempleById($currentTempleId);

// ຈັດການການບັນທຶກຂໍ້ມູນ (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $settings_to_update = [
        'currency_symbol',
        'date_format',
        'fiscal_year_start',
        'timezone'
    ];

    $all_successful = true;
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            if (!setTempleSetting($currentTempleId, $key, $value)) {
                $all_successful = false;
            }
        }
    }

    if ($all_successful) {
        setFlashMessage('success', 'ບັນທຶກການຕັ້ງຄ່າສຳເລັດແລ້ວ.');
    } else {
        setFlashMessage('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກການຕັ້ງຄ່າ.');
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// ດຶງຄ່າການຕັ້ງຄ່າຕ່າງໆມາສະແດງ
$currency_symbol = getTempleSetting($currentTempleId, 'currency_symbol', '₭');
$date_format = getTempleSetting($currentTempleId, 'date_format', 'd/m/Y');
$fiscal_year_start = getTempleSetting($currentTempleId, 'fiscal_year_start', '10');
$timezone = getTempleSetting($currentTempleId, 'timezone', 'Asia/Vientiane');

// ຕົວເລືອກສຳລັບ dropdowns
$date_formats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];
$timezones = timezone_identifiers_list();
$months = [];
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
}


$pageTitle = "ຕັ້ງຄ່າວັດ";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">ຕັ້ງຄ່າວັດ</h1>
    <p class="text-gray-600">ຈັດການການຕັ້ງຄ່າສະເພາະຂອງ: <span class="font-semibold text-blue-600"><?php echo e($templeData['temple_name_lao']); ?></span></p>
</div>

<?php displayFlashMessage(); ?>

<div class="bg-white rounded-2xl shadow-md p-6 lg:p-8">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Currency Symbol -->
            <div class="col-span-1">
                <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-1">ສັນຍາລັກສະກຸນເງິນ</label>
                <input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo e($currency_symbol); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น: ₭, ກີບ">
                <p class="text-xs text-gray-500 mt-1">ສັນຍາລັກທີ່ຈະສະແດງໃນລາຍງານຕ່າງໆ.</p>
            </div>

            <!-- Date Format -->
            <div class="col-span-1">
                <label for="date_format" class="block text-sm font-medium text-gray-700 mb-1">ຮູບແບບວັນທີ</label>
                <select id="date_format" name="date_format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($date_formats as $format): ?>
                        <option value="<?php echo $format; ?>" <?php echo ($date_format === $format) ? 'selected' : ''; ?>>
                            <?php echo date($format); ?> (<?php echo $format; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">ຮູບແບບການສະແດງວັນທີໃນທົ່ວລະບົບ.</p>
            </div>

            <!-- Fiscal Year Start -->
            <div class="col-span-1">
                <label for="fiscal_year_start" class="block text-sm font-medium text-gray-700 mb-1">ເດືອນເລີ່ມຕົ້ນປີງົບປະມານ</label>
                <select id="fiscal_year_start" name="fiscal_year_start" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo ($fiscal_year_start == $num) ? 'selected' : ''; ?>>
                            ເດືອນ <?php echo $num; ?> (<?php echo $name; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">ໃຊ້ສຳລັບການຄຳນວນລາຍງານປະຈຳປີ.</p>
            </div>

            <!-- Timezone -->
            <div class="col-span-1">
                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">ເຂດເວລາ</label>
                <select id="timezone" name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($timezones as $tz): ?>
                        <option value="<?php echo $tz; ?>" <?php echo ($timezone === $tz) ? 'selected' : ''; ?>>
                            <?php echo $tz; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">ເຂດເວລາຂອງເຊີບເວີ ແລະ ຂໍ້ມູນ.</p>
            </div>

        </div>

        <!-- Save Button -->
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                ບັນທຶກການຕັ້ງຄ່າ
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
