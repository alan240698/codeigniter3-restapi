/**
 * Configuration file for IT Ticket System (No DataTables)
 */

const CONFIG = {
    // API Endpoints
    API: {
        LIST: BASE_URL      + 'tickets/list',
        CREATE: BASE_URL    + 'tickets/create',
        VIEW: BASE_URL      + 'tickets/view/',
        REOPEN: BASE_URL    + 'tickets/reopen/',
        DOWNLOAD: BASE_URL  + 'tickets/downloadTicketDocument/'
    },

    // File Upload Settings
    FILE_UPLOAD: {
        MAX_FILES: 3,
        MAX_SIZE: 10485760, // 10MB in bytes
        MAX_SIZE_MB: 10,
        ALLOWED_TYPES: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ALLOWED_MIMES: [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]
    },

    // Description Settings
    DESCRIPTION: {
        MIN_LENGTH: 10,
        MAX_LENGTH: 1000
    },

    // Table Settings
    TABLE: {
        PAGE_LENGTH: 10,
        SORT_ORDER: 'desc',
        SORT_COLUMN: 'created_date'
    },

    // Status Colors
    STATUS_COLORS: {
        'processing': 'success',
        'new': 'info',
        'assigned': 'primary',
        'pending': 'warning',
        'solved': 'success',
        'closed': 'secondary',
        'rejected': 'danger'
    },

    // Messages
    MESSAGES: {
        SUCCESS: {
            CREATE: 'Ticket created successfully!',
            UPDATE: 'Ticket updated successfully!',
            REOPEN: 'Ticket reopened successfully!'
        },
        ERROR: {
            CREATE: 'Failed to create ticket. Please try again.',
            LOAD: 'Failed to load ticket details.',
            NETWORK: 'Network error. Please check your connection.',
            GENERIC: 'An error occurred. Please try again later.'
        },
        CONFIRM: {
            REOPEN: 'Are you sure you want to reopen this ticket?'
        },
        VALIDATION: {
            CATEGORY: 'Please select a category',
            SUBCATEGORY_OR_DESC: 'Please fill in at least Subcategory or Description',
            DESC_MIN: 'Description must have at least 10 characters',
            DESC_MAX: 'Description must be less than 1000 characters',
            FILE_COUNT: 'Maximum 3 files allowed',
            FILE_SIZE: 'File size must not exceed 10MB',
            FILE_TYPE: 'Invalid file type'
        }
    },

    // Country
    COUNTRY: typeof COUNTRY !== 'undefined' ? COUNTRY : 'Vietnam'
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}