<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Ticket Configuration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Link trong roboto, serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- IT Ticket UI Utilities -->
    <script src="<?= base_url('assets/it_ticket/js/ui-utils.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/ValidationRulesServiceGroup.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/ValidationRulesWorkflows.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/FormValidationManager.js') ?>"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Roboto", serif;
            background: #f5f7fa;
        }

        /* Header */
        .top-header {
            background: #1e293b;
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .top-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .top-header p {
            font-size: 14px;
            color: #94a3b8;
        }

        /* Main Container */
        .main-container-ticket {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Stats Cards */
        .stats-section {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card-icon.blue {
            background: #dbeafe;
            color: #3b82f6;
        }

        .stat-card-icon.green {
            background: #d1fae5;
            color: #10b981;
        }

        .stat-card-icon.yellow {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-card-icon.purple {
            background: #e9d5ff;
            color: #a855f7;
        }

        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .stat-card-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .stat-card-trend {
            font-size: 12px;
            margin-top: 10px;
        }

        .stat-card-trend.up {
            color: #10b981;
        }

        .stat-card-trend.down {
            color: #ef4444;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .chart-header {
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .chart-header p {
            font-size: 14px;
            color: #6b7280;
        }

        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        /* Tab Navigation */
        .tab-navigation {
            background: white;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .tab-list {
            display: flex;
            overflow-x: auto;
            /* border-bottom: 2px solid #f3f4f6; */
        }

        .tab-list::-webkit-scrollbar {
            height: 4px;
        }

        .tab-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .tab-item {
            flex: 0 0 auto;
            padding: 18px 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .tab-item:hover {
            background: #f9fafb;
            color: #3b82f6;
        }

        .tab-item.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: #eff6ff;
        }

        .tab-item i {
            font-size: 16px;
        }

        /* Tab Content */
        .tab-content {
            display: block;
            animation: fadeIn 0.3s;
            min-height: 400px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading State */
        .loading-overlay {
            position: relative;
            pointer-events: none;
        }

        .loading-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            z-index: 999;
            border-radius: 12px;
        }

        .loading-overlay::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            z-index: 1000;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 1001;
            pointer-events: none;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            position: relative;
        }

        .loading-text {
            color: #1f2937;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .loading-subtext {
            color: #6b7280;
            font-size: 13px;
        }

        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '';
            }

            40% {
                content: '.';
            }

            60% {
                content: '..';
            }

            80%,
            100% {
                content: '...';
            }
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            /* border-bottom: 2px solid #f3f4f6; */
        }

        .card-header h3 {
            font-size: 18px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        .data-table thead th {
            background: antiquewhite;
            padding: 12px 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            /* color: #6b7280; */
            text-align: left;
            letter-spacing: 0.5px;

            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }

        .data-table thead th:last-child {
            border-right: none;
        }

        .data-table thead th:first-child {
            border-top-left-radius: 10px;
        }

        .data-table thead th:last-child {
        border-top-right-radius: 10px;
        }

        .data-table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #eef0f2;
            border-right: 1px solid #eef0f2;
        }

        .data-table tbody td:last-child {
            border-right: none;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary-it-ticket {
            background: #00c1ef;
            color: white;
        }

        .btn-primary-it-ticket:hover {
            background: #08aad3ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-success-it-ticket {
            background: #1fde9eff;
            color: white;
        }

        .btn-success-it-ticket:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-danger-it-ticket {
            background: #e56060;
            color: white;
        }

        .btn-danger-it-ticket:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-secondary-it-ticket {
            background: #6b7280;
            color: white;
        }

        .btn-secondary-it-ticket:hover {
            background: #4b5563;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            color: #1f2937;
        }

        .modal-close {
            background: #f3f4f6;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            font-size: 20px;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            background: #f9fafb;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Tree Structure */
        .tree-item {
            margin-left: 30px;
            margin-top: 10px;
            padding: 15px;
            background: #f9fafb;
            border-left: 3px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .tree-item:hover {
            background: #f3f4f6;
            border-left-color: #3b82f6;
        }

        .tree-item.level-2 {
            margin-left: 60px;
            border-left-color: #3b82f6;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Filter Section */
        .filter-section {
            margin-bottom: 20px;
            /* padding: 15px;
            background: #f9fafb; */
            border-radius: 8px;
        }

        .filter-section label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #374151;
        }

        .filter-section select {
            max-width: 300px;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        /* Pagination */
        .pagination-container {
            user-select: none;
        }

        .pagination-buttons {
            display: flex;
            gap: 5px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #f3f4f6;
            border-color: #00c1ef;
            color: #00c1ef;
        }

        .pagination-btn.active {
            background: #00c1ef;
            color: white;
            border-color: #00c1ef;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Tab Content Container - CRITICAL FIX */
        #tab-content {
            min-height: 400px;
        }

        .tab-content {
            display: block !important;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Top Header -->
    <div class="top-header">
        <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
        <p>IT Ticket System Configuration & Management</p>
    </div>

    <!-- Main Container -->
    <div class="main-container-ticket">

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon blue">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">8</div>
                    <div class="stat-card-label">Service Groups</div>
                    <div class="stat-card-trend up">
                        <i class="fas fa-arrow-up"></i> 2 just created this week
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon green">
                            <i class="fas fa-ticket"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">24</div>
                    <div class="stat-card-label">Ticket Types</div>
                    <div class="stat-card-trend up">
                        <i class="fas fa-arrow-up"></i> 5 active workflows
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon yellow">
                            <i class="fas fa-list-check"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">156</div>
                    <div class="stat-card-label">IT Service</div>
                    <div class="stat-card-trend up">
                        <i class="fas fa-arrow-up"></i> 12 with sub-it-service
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon purple">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">12</div>
                    <div class="stat-card-label">Support Teams</div>
                    <div class="stat-card-trend">
                        <i class="fas fa-check-circle"></i> 95% uptime
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> System Overview & Performance</h3>
                    <p>Real-time system performance overview</p>
                </div>
                <div class="chart-placeholder">
                    <i class="fas fa-chart-bar" style="font-size: 48px; opacity: 0.8;"></i>
                    <span style="margin-left: 20px;">Statistical chart</span>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <div class="tab-list">
                <?php foreach ($tab_config as $key => $label): ?>
                    <div class="tab-item <?= ($tab == $key) ? 'active' : '' ?>"
                        data-tab="<?= $key ?>">
                        <span><?= $label ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab Content -->
        <div id="tab-content" class="tab-content">
            <?php if (!empty($tab_view)) $this->load->view($tab_view); ?>
        </div>

    </div>

    <!-- ===================== JAVASCRIPT ===================== -->
    <script>
        // Tab Manager Object
        const TabManager = {
            currentTab: '<?= $tab ?>',
            baseUrl: '<?= base_url("it-ticket") ?>',

            init() {
                // Setup tab click handlers
                document.querySelectorAll('.tab-item').forEach(item => {
                    item.addEventListener('click', (e) => {
                        const tabName = item.getAttribute('data-tab');
                        this.switchTab(tabName, item);
                    });
                });

                // Initialize current tab's scripts
                setTimeout(() => {
                    this.initTabScripts(this.currentTab);
                }, 100);

                // Handle browser back/forward
                window.addEventListener('popstate', (e) => {
                    if (e.state && e.state.tab) {
                        const tabElement = document.querySelector(`.tab-item[data-tab="${e.state.tab}"]`);
                        if (tabElement) {
                            this.switchTab(e.state.tab, tabElement);
                        }
                    }
                });
            },

            switchTab(tabName, element) {
                if (this.currentTab === tabName) return;

                // Cleanup old tab
                this.cleanupTabScripts(this.currentTab);

                // Remove active class from all tabs
                document.querySelectorAll('.tab-item').forEach(item => {
                    item.classList.remove('active');
                });

                // Add active class to clicked tab
                element.classList.add('active');

                // Show loading state
                const tabContent = document.getElementById('tab-content');
                this.showLoading(tabContent);

                // Fetch tab content via AJAX
                fetch(this.baseUrl + '?tab=' + tabName, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.text();
                    })
                    .then(html => {
                        setTimeout(() => {
                            // Update content
                            tabContent.innerHTML = html;

                            // CRITICAL: Execute inline scripts
                            this.executeScripts(tabContent);

                            // Update current tab
                            this.currentTab = tabName;

                            // Update URL without reload
                            const newUrl = this.baseUrl + '?tab=' + tabName;
                            history.pushState({
                                tab: tabName
                            }, '', newUrl);

                            // Remove loading state
                            this.hideLoading(tabContent);

                            // Initialize new tab's scripts
                            setTimeout(() => {
                                this.initTabScripts(tabName);
                            }, 100);
                        }, 300);
                    })
                    .catch(err => {
                        console.error('Error loading tab:', err);
                        tabContent.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #ef4444;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                                <p style="font-size: 16px; font-weight: 600;">Could not load content</p>
                                <p style="font-size: 14px; color: #6b7280; margin-top: 10px;">Please try again later</p>
                            </div>
                        `;
                        this.hideLoading(tabContent);
                    });
            },

            executeScripts(container) {
                // Find all script tags in loaded content
                const scripts = container.querySelectorAll('script');

                scripts.forEach(oldScript => {
                    // Create new script element
                    const newScript = document.createElement('script');

                    // Copy attributes
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });

                    // Copy script content
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));

                    // Replace old script with new one
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                console.log('Executed', scripts.length, 'scripts from tab content');
            },

            showLoading(container) {
                container.classList.add('loading-overlay');

                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'loading-content';
                loadingDiv.innerHTML = `
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Loading data<span class="loading-dots"></span></div>
                    <div class="loading-subtext">Please wait a moment</div>
                `;

                container.appendChild(loadingDiv);
            },

            hideLoading(container) {
                container.classList.remove('loading-overlay');

                const loadingContent = container.querySelector('.loading-content');
                if (loadingContent) {
                    loadingContent.remove();
                }
            },

            initTabScripts(tabName) {
                const initFunctionName = 'init' + this.capitalize(tabName) + 'Tab';

                if (typeof window[initFunctionName] === 'function') {
                    console.log('Initializing tab:', tabName);
                    window[initFunctionName]();
                } else {
                    console.warn('Init function not found:', initFunctionName);
                }
            },

            cleanupTabScripts(tabName) {
                const cleanupFunctionName = 'cleanup' + this.capitalize(tabName) + 'Tab';

                if (typeof window[cleanupFunctionName] === 'function') {
                    console.log('Cleaning up tab:', tabName);
                    window[cleanupFunctionName]();
                }
            },

            capitalize(str) {
                return str.charAt(0).toUpperCase() + str.slice(1).replace(/-/g, '');
            }
        };

        // Initialize when DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            TabManager.init();
        });
    </script>

</body>

</html>