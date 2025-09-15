window.AppConfig = window.AppConfig || {};

Object.assign(window.AppConfig, {
    // API Configuration
    API: {
        BASE_URL: '/api/posts',
        TIMEOUT: 30000,
        PER_PAGE: 5
    },

    // CSRF Configuration
    CSRF: {
        COOKIE_NAME: document.querySelector('meta[name="csrf-cookie-name"]')?.content || 'csrf_cookie_name',
        HEADER_NAME: 'X-CSRF-TOKEN'
    },

    // Validation Rules
    VALIDATION: {
        TITLE: { MIN: 2, MAX: 255 },
        DESCRIPTION: { MIN: 10, MAX: 65000 }
    },

    // UI Settings
    UI: {
        LOADING_TIMEOUT: 15000,
        NOTIFICATION_TIMER: 3000,
        TRUNCATE_TITLE: 30,
        TRUNCATE_DESC: 50
    }
});