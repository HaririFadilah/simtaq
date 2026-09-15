import Swal from 'sweetalert2';

// Emerald theme color matching SIMTAQ branding
const PRIMARY_COLOR = '#0D7C66';
const DANGER_COLOR = '#EF4444';
const WARNING_COLOR = '#F59E0B';
const INFO_COLOR = '#3B82F6';

/**
 * Toast Notification instance (top-end floating pill)
 */
export const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3500,
  timerProgressBar: true,
  customClass: {
    popup: 'swal2-simtaq-toast'
  },
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  }
});

export const toastSuccess = (title, text = '') => {
  return Toast.fire({
    icon: 'success',
    title: title,
    text: text,
    iconColor: PRIMARY_COLOR,
  });
};

export const toastError = (title, text = '') => {
  return Toast.fire({
    icon: 'error',
    title: title,
    text: text,
    iconColor: DANGER_COLOR,
  });
};

export const toastWarning = (title, text = '') => {
  return Toast.fire({
    icon: 'warning',
    title: title,
    text: text,
    iconColor: WARNING_COLOR,
  });
};

export const toastInfo = (title, text = '') => {
  return Toast.fire({
    icon: 'info',
    title: title,
    text: text,
    iconColor: INFO_COLOR,
  });
};

/**
 * Modern Confirmation Dialog (SweetAlert2)
 */
export const confirmDialog = async ({
  title = 'Apakah Anda yakin?',
  text = 'Tindakan ini tidak dapat dibatalkan.',
  confirmButtonText = 'Ya, Lanjutkan',
  cancelButtonText = 'Batal',
  confirmButtonColor,
  cancelButtonColor,
  icon = 'warning',
  isDanger = false
}) => {
  const isDestructive = isDanger || confirmButtonColor === DANGER_COLOR || confirmButtonColor === '#EF4444';
  
  return Swal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonColor: confirmButtonColor || (isDestructive ? DANGER_COLOR : PRIMARY_COLOR),
    cancelButtonColor: cancelButtonColor || '#F8FAFC',
    confirmButtonText,
    cancelButtonText,
    reverseButtons: true,
    focusCancel: isDestructive,
    customClass: {
      popup: 'swal2-simtaq-modal',
      confirmButton: isDestructive ? 'swal2-btn-danger' : 'swal2-btn-primary',
      cancelButton: 'swal2-btn-cancel'
    }
  });
};

/**
 * Standard Alert Modal: Success
 */
export const alertSuccess = (title, text = '') => {
  return Swal.fire({
    icon: 'success',
    title,
    text,
    confirmButtonColor: PRIMARY_COLOR,
    confirmButtonText: 'Selesai',
    customClass: {
      popup: 'swal2-simtaq-modal',
      confirmButton: 'swal2-btn-primary'
    }
  });
};

/**
 * Standard Alert Modal: Error
 */
export const alertError = (title, text = '') => {
  return Swal.fire({
    icon: 'error',
    title,
    text,
    confirmButtonColor: DANGER_COLOR,
    confirmButtonText: 'Tutup',
    customClass: {
      popup: 'swal2-simtaq-modal',
      confirmButton: 'swal2-btn-danger'
    }
  });
};

/**
 * Standard Alert Modal: Warning
 */
export const alertWarning = (title, text = '') => {
  return Swal.fire({
    icon: 'warning',
    title,
    text,
    confirmButtonColor: WARNING_COLOR,
    confirmButtonText: 'Mengerti',
    customClass: {
      popup: 'swal2-simtaq-modal',
      confirmButton: 'swal2-btn-warning'
    }
  });
};

export default {
  toastSuccess,
  toastError,
  toastWarning,
  toastInfo,
  confirmDialog,
  alertSuccess,
  alertError,
  alertWarning
};
