$(document).ready(function () {
    try {
        // Setup Ajax
        Utils.setupAjax();

        // Initialize post manager
        PostManager.init();

        console.log('App initialized successfully');

    } catch (error) {
        console.error('App initialization failed:', error);
        Utils.notify.error('Failed to initialize application');
    }
});

window.addEventListener('error', function (event) {
    console.error('Global error:', event.error);
});

window.addEventListener('unhandledrejection', function (event) {
    console.error('Unhandled rejection:', event.reason);
    event.preventDefault();
});