const BASE_URL = window.BASE_URL || '';

export const CONFIG = {
    BASE_URL,
    ENDPOINTS: {
        CREATE: `${BASE_URL}arche_ticket/ticket/create`,
        LIST: `${BASE_URL}arche_ticket/ticket/list`,
        VIEW: `${BASE_URL}arche_ticket/ticket/view/`,
        REOPEN: `${BASE_URL}arche_ticket/ticket/reopen/`,
        DOWNLOAD_DOCUMENT_TICKET: `${BASE_URL}arche_ticket/ticket/downloadTicketDocument/`
    },
    STATUS: {
        new: { icon: 'fa-certificate', label: 'New Tickets' },
        pending: { icon: 'fa-clock', label: 'Pending Tickets' },
        processing: { icon: 'fa-spinner', label: 'Processing Tickets' },
        solved: { icon: 'fa-check-circle', label: 'Solved Tickets' },
        closed: { icon: 'fa-archive', label: 'Closed Tickets' }
    },
    FILE: {
        MAX_FILES: 3,
        MAX_SIZE: 10 * 1024 * 1024,
        ALLOWED_EXTENSIONS: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'gif'],
        IMAGE_EXTENSIONS: ['jpg', 'jpeg', 'png', 'gif']
    },
    UI: {
        TOAST_DURATION: 3000,
        TIMESTAMP_INTERVAL: 1000
    },
    VALIDATION: {
        MIN_DESCRIPTION_LENGTH: 10
    }
};

export const DEFAULT_ACTIVE_CATEGORY = '__placeholder__';

export const FILE_ICONS = {
    pdf: 'fa-file-pdf',
    doc: 'fa-file-word',
    docx: 'fa-file-word',
    xls: 'fa-file-excel',
    xlsx: 'fa-file-excel',
    txt: 'fa-file-alt',
    jpg: 'fa-file-image',
    jpeg: 'fa-file-image',
    png: 'fa-file-image',
    gif: 'fa-file-image'
};

export const TOAST_ICONS = {
    success: 'fa-check-circle',
    error: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
};

export const PRIORITY_GLPI = {
    1: 'Very high',
    2: 'High',
    3: 'Medium',
    4: 'Low',
    5: 'Very low',
};