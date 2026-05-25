import Swal from 'sweetalert2';

const swalClasses = {
    popup: 'catasky-swal-popup',
    title: 'catasky-swal-title',
    htmlContainer: 'catasky-swal-text',
    confirmButton: 'catasky-swal-button catasky-swal-button-primary',
    cancelButton: 'catasky-swal-button catasky-swal-button-secondary',
};

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3200,
    timerProgressBar: true,
    showClass: { popup: 'swal2-show catasky-toast-in' },
    hideClass: { popup: 'swal2-hide catasky-toast-out' },
    customClass: {
        popup: 'catasky-toast-popup',
        title: 'catasky-toast-title',
    },
});

const normalizeMessage = (title, message = '') => ({
    title: String(title || ''),
    text: String(message || ''),
});

const alertService = {
    successAlert(title, message = '') {
        return Swal.fire({
            ...normalizeMessage(title, message),
            icon: 'success',
            iconColor: '#16a34a',
            timer: 2600,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: swalClasses,
        });
    },

    errorAlert(title, message = '') {
        return Swal.fire({
            ...normalizeMessage(title, message),
            icon: 'error',
            iconColor: '#dc2626',
            confirmButtonText: 'Got it',
            customClass: swalClasses,
        });
    },

    warningAlert(title, message = '') {
        return Swal.fire({
            ...normalizeMessage(title, message),
            icon: 'warning',
            iconColor: '#d97706',
            confirmButtonText: 'Continue',
            customClass: swalClasses,
        });
    },

    infoAlert(title, message = '') {
        return Swal.fire({
            ...normalizeMessage(title, message),
            icon: 'info',
            iconColor: '#2563eb',
            confirmButtonText: 'OK',
            customClass: swalClasses,
        });
    },

    promptText({
        title = 'Enter details',
        message = '',
        placeholder = '',
        confirmText = 'Continue',
        input = 'text',
    } = {}) {
        return Swal.fire({
            title,
            text: message,
            input,
            inputPlaceholder: placeholder,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            customClass: swalClasses,
            inputValidator: (value) => (!value || !String(value).trim() ? 'This field is required.' : undefined),
        });
    },

    confirmAction({
        title = 'Are you sure?',
        message = 'Please confirm this action.',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        icon = 'warning',
        danger = false,
    } = {}) {
        return Swal.fire({
            title,
            text: message,
            icon,
            iconColor: danger ? '#dc2626' : '#d97706',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            focusCancel: danger,
            customClass: {
                ...swalClasses,
                confirmButton: danger
                    ? 'catasky-swal-button catasky-swal-button-danger'
                    : swalClasses.confirmButton,
            },
        });
    },

    confirmDelete(title = 'Are you sure?', message = 'This action cannot be undone.') {
        return this.confirmAction({
            title,
            message,
            confirmText: 'Delete',
            cancelText: 'Cancel',
            danger: true,
        });
    },

    loadingAlert(title = 'Processing...', message = 'Please wait.') {
        return Swal.fire({
            title,
            text: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: swalClasses,
            didOpen: () => Swal.showLoading(),
        });
    },

    close() {
        Swal.close();
    },

    toastSuccess(message, title = '') {
        return toast.fire({ icon: 'success', iconColor: '#16a34a', title: title || message });
    },

    toastError(message, title = '') {
        return toast.fire({ icon: 'error', iconColor: '#dc2626', title: title || message });
    },

    toastWarning(message, title = '') {
        return toast.fire({ icon: 'warning', iconColor: '#d97706', title: title || message });
    },

    toastInfo(message, title = '') {
        return toast.fire({ icon: 'info', iconColor: '#2563eb', title: title || message });
    },

    notifySuccess(message) {
        return this.toastSuccess(message);
    },

    notifyError(message) {
        return this.toastError(message);
    },
};

export default alertService;
