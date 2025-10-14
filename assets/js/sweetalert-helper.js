/**
 * SweetAlert2 Helper Functions
 * ຟັງຊັນຊ່ວຍສຳລັບສະແດງ alerts ທີ່ສວຍງາມ
 */

// ຟັງຊັນສະແດງຄວາມສຳເລັດ
function showSuccess(message, title = 'ສຳເລັດ!') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'ຕົກລົງ',
        confirmButtonColor: '#10b981'
    });
}

// ຟັງຊັນສະແດງຂໍ້ຜິດພາດ
function showError(message, title = 'ເກີດຂໍ້ຜິດພາດ!') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'ຕົກລົງ',
        confirmButtonColor: '#ef4444'
    });
}

// ຟັງຊັນສະແດງຄຳເຕືອນ
function showWarning(message, title = 'ແຈ້ງເຕືອນ!') {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'ຕົກລົງ',
        confirmButtonColor: '#f59e0b'
    });
}

// ຟັງຊັນສະແດງຂໍ້ມູນ
function showInfo(message, title = 'ຂໍ້ມູນ') {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: 'ຕົກລົງ',
        confirmButtonColor: '#3b82f6'
    });
}

// ຟັງຊັນຢືນຢັນການລຶບ
function confirmDelete(message = 'ທ່ານຕ້ອງການລຶບຂໍ້ມູນນີ້ແທ້ບໍ?') {
    return Swal.fire({
        icon: 'warning',
        title: 'ຢືນຢັນການລຶບ',
        text: message,
        showCancelButton: true,
        confirmButtonText: 'ລຶບ',
        cancelButtonText: 'ຍົກເລີກ',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });
}

// ຟັງຊັນຢືນຢັນທົ່ວໄປ
function confirmAction(message, title = 'ຢືນຢັນ') {
    return Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: 'ຕົກລົງ',
        cancelButtonText: 'ຍົກເລີກ',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });
}

// ຟັງຊັນສະແດງ loading
function showLoading(message = 'ກຳລັງປະມວນຜົນ...') {
    return Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// ຟັງຊັນປິດ loading
function closeLoading() {
    Swal.close();
}

// ຟັງຊັນສະແດງຜົນສຳເລັດພ້ອມ redirect
function showSuccessAndRedirect(message, redirectUrl, delay = 1500) {
    Swal.fire({
        icon: 'success',
        title: 'ສຳເລັດ!',
        text: message,
        timer: delay,
        showConfirmButton: false,
        timerProgressBar: true
    }).then(() => {
        window.location.href = redirectUrl;
    });
}

// ຟັງຊັນສະແດງ Toast notification
function showToast(message, icon = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    return Toast.fire({
        icon: icon,
        title: message
    });
}
