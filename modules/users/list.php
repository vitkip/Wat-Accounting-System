<?php
/**
 * ລະບົບບັນຊີວັດ (Wat Accounting System)
 * ລາຍການຜູ້ໃຊ້
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/temple_functions.php';

requireAdmin();

$db = getDB();

// ກວດສອບສິດຂອງຜູ້ໃຊ້ປັດຈຸບັນ
$currentUser = $_SESSION;
$isSuperAdmin = ($currentUser['is_super_admin'] ?? 0) == 1;
$currentTempleId = getCurrentTempleId();
$isMultiTemple = isMultiTempleEnabled();

// ດຶງຂໍ້ມູນຜູ້ໃຊ້ຕາມສິດ
if ($isSuperAdmin) {
    // Super admin ເຫັນຜູ້ໃຊ້ທັງໝົດ
    $sql = "SELECT u.*, t.temple_name, t.temple_name_lao 
            FROM users u 
            LEFT JOIN temples t ON u.temple_id = t.id 
            ORDER BY u.created_at DESC";
    $stmt = $db->query($sql);
} else {
    // Admin ທົ່ວໄປເຫັນສະເພາະຜູ້ໃຊ້ໃນວັດຂອງຕົນ
    if ($isMultiTemple && $currentTempleId) {
        $sql = "SELECT u.*, t.temple_name, t.temple_name_lao 
                FROM users u 
                LEFT JOIN temples t ON u.temple_id = t.id 
                WHERE u.temple_id = :temple_id 
                ORDER BY u.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':temple_id' => $currentTempleId]);
    } else {
        $sql = "SELECT u.*, NULL as temple_name, NULL as temple_name_lao 
                FROM users u 
                ORDER BY u.created_at DESC";
        $stmt = $db->query($sql);
    }
}
$users = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">ລາຍການຜູ້ໃຊ້</h1>
            <p class="text-gray-600">ຈັດການຜູ້ໃຊ້ໃນລະບົບ</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/modules/users/add.php" 
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            ເພີ່ມຜູ້ໃຊ້
        </a>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Users Table -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ລຳດັບ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຊື່ຜູ້ໃຊ້</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຊື່ເຕັມ</th>
                    <?php if ($isSuperAdmin): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ວັດ</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ສິດ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ວັນທີ່ສ້າງ</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ການຈັດການ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="<?php echo $isSuperAdmin ? 7 : 6; ?>" class="px-6 py-8 text-center text-gray-500">
                        ບໍ່ມີຂໍ້ມູນຜູ້ໃຊ້
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $index + 1; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo e($user['username']); ?>
                            <?php if ($user['is_super_admin'] == 1): ?>
                                <span class="ml-2 px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                    Super Admin
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($user['full_name']); ?>
                        </td>
                        <?php if ($isSuperAdmin): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php 
                            if ($user['is_super_admin'] == 1) {
                                echo '<span class="text-purple-600 font-medium">ທຸກວັດ</span>';
                            } elseif ($user['temple_name_lao'] || $user['temple_name']) {
                                echo e($user['temple_name_lao'] ?: $user['temple_name']);
                            } else {
                                echo '<span class="text-gray-400">-</span>';
                            }
                            ?>
                        </td>
                        <?php endif; ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                    ແອດມິນ
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    ຜູ້ໃຊ້
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo formatDate($user['created_at']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/modules/users/edit.php?id=<?php echo $user['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">ແກ້ໄຂ</a>
                            <?php if ($user['id'] != $_SESSION['user_id'] && !($user['is_super_admin'] == 1 && !$isSuperAdmin)): ?>
                                <a href="#" 
                                   onclick="confirmDeleteUser(<?php echo $user['id']; ?>); return false;"
                                   class="text-red-600 hover:text-red-900">ລຶບ</a>
                            <?php else: ?>
                                <span class="text-gray-400">ລຶບ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Info Card -->
<div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>ໝາຍເຫດ:</strong> ແອດມິນມີສິດເຂົ້າເຖິງທຸກໜ້າທີ່ໃນລະບົບ ລວມທັງການຈັດການຜູ້ໃຊ້. 
                ຜູ້ໃຊ້ທົ່ວໄປສາມາດເພີ່ມ/ແກ້ໄຂລາຍຮັບ-ລາຍຈ່າຍ ແລະ ເບິ່ງລາຍງານໄດ້ເທົ່ານັ້ນ.
            </p>
        </div>
    </div>
</div>

<script>
function confirmDeleteUser(id) {
    confirmDelete('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້? ການກະທຳນີ້ບໍ່ສາມາດຍົກເລີກໄດ້!').then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/users/delete.php?id=' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
