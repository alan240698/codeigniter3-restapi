window.Utils = {
    // Get cookie
    getCookie(name) {
        if (!name) return '';
        const value = document.cookie
            .split('; ')
            .find(row => row.startsWith(name + '='));
        return value ? decodeURIComponent(value.split('=')[1]) : '';
    },

    // HTML escaping
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    // Text truncation
    truncate(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    },

    // Date formatting
    formatDate(dateString, locale = 'vi-VN') {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return isNaN(date) ? 'N/A' : date.toLocaleDateString(locale);
    },

    // Validation helpers
    validateId(id) {
        const num = parseInt(id);
        return !isNaN(num) && num > 0;
    },

    validatePageNumber(page, totalPages = 0) {
        const pageNum = parseInt(page);
        if (isNaN(pageNum) || pageNum < 1) return 1;
        if (totalPages > 0 && pageNum > totalPages) return totalPages;
        return pageNum;
    },

    // Form validation
    validatePostForm(data) {
        const errors = {};
        const title = (data.title || '').trim();
        const desc = (data.description || '').trim();
        const config = window.AppConfig.VALIDATION;

        // Title validation
        if (!title) {
            errors.title = 'Title is required.';
        } else if (title.length < config.TITLE.MIN) {
            errors.title = `Title must be at least ${config.TITLE.MIN} characters.`;
        } else if (title.length > config.TITLE.MAX) {
            errors.title = `Title must be less than or equal to ${config.TITLE.MAX} characters.`;
        }

        // Description validation  
        if (!desc) {
            errors.description = 'Description is required.';
        } else if (desc.length < config.DESCRIPTION.MIN) {
            errors.description = `Description must be at least ${config.DESCRIPTION.MIN} characters.`;
        } else if (desc.length > config.DESCRIPTION.MAX) {
            errors.description = `Description must be less than or equal to ${config.DESCRIPTION.MAX} characters.`;
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    },

    // Form on change validation
    validateOnChangePostForm(field) {
        const errors    = {};
        const fieldKey  = Object.keys(field)?.toString();

        if (!fieldKey) {
            return {
                isValid: Object.keys(errors).length === 0,
                errors: errors
            };
        }

        const fieldVal  = field[fieldKey];
        const config    = window.AppConfig.VALIDATION;

        switch (fieldKey) {
            case 'title':
                if (!fieldVal) {
                    errors.title = 'Title is required.';
                } else if (fieldVal.length < config.TITLE.MIN) {
                    errors.title = `Title must be at least ${config.TITLE.MIN} characters.`;
                } else if (fieldVal.length > config.TITLE.MAX) {
                    errors.title = `Title must be less than or equal to ${config.TITLE.MAX} characters.`;
                }
                break;

            case 'description':
                if (!fieldVal) {
                    errors.description = 'Description is required.';
                } else if (fieldVal.length < config.DESCRIPTION.MIN) {
                    errors.description = `Description must be at least ${config.DESCRIPTION.MIN} characters.`;
                } else if (fieldVal.length > config.DESCRIPTION.MAX) {
                    errors.description = `Description must be less than or equal to ${config.DESCRIPTION.MAX} characters.`;
                }

            default:
                break;
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    },

    // Notification
    notify: {
        success(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Success!',
                    text: message,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert('Success: ' + message);
            }
        },

        error(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Error: ' + message);
            }
        },

        info(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Information',
                    text: message,
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert('Info: ' + message);
            }
        },

        async confirm(message = 'Are you sure?') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel'
                });
                return result.isConfirmed;
            } else {
                return confirm(message);
            }
        }
    },

    // AJAX setup
    setupAjax() {
        $.ajaxSetup({
            xhrFields: { withCredentials: true },
            beforeSend: (xhr, settings) => {
                const method = (settings.type || '').toUpperCase();
                if (method !== 'GET' && method !== 'OPTIONS') {
                    const csrf = this.getCookie(window.AppConfig.CSRF.COOKIE_NAME);
                    if (csrf) {
                        xhr.setRequestHeader(window.AppConfig.CSRF.HEADER_NAME, csrf);
                    }
                }
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            error: (xhr, status, error) => {
                if (status !== 'abort') {
                    console.error('AJAX Error:', { status, error });
                }
            }
        });
    }
};