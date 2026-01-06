<div id="tab-roles" class="tab-content">
    <style>
        * {
            box-sizing: border-box;
        }

        /* Modern Color Palette */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border: #e5e7eb;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header Section */
        .roles-header {
            background: linear-gradient(135deg, #020218 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .roles-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .roles-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }

        .roles-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.875rem 3rem 0.875rem 3rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--white);
        }

        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .search-box .search-icon,
        .search-box .clear-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            transition: var(--transition);
        }

        .search-box .search-icon {
            left: 1rem;
        }

        .search-box .clear-icon {
            right: 1rem;
            cursor: pointer;
            display: none;
        }

        .search-box .clear-icon:hover {
            color: var(--danger);
        }

        .btn-add-role {
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #3232dd 0%, var(--secondary) 100%);
            border: none;
            border-radius: 12px;
            color: var(--white);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            white-space: nowrap;
        }

        .btn-add-role:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-add-role:active {
            transform: translateY(0);
        }

        /* Roles Grid */
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .role-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .role-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .role-icon {
            /* width: 64px;
            height: 64px; */
            /* background: linear-gradient(135deg, #000004 0%, #8650ff 100%); */
            border-radius: 10px;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #10b981;
            margin-bottom: 1.25rem;
            border: #3232dd 1px solid;
            /* box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); */
        }

        .role-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.375rem;
        }

        .role-display-name {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 600;
            font-family: 'Courier New', monospace;
            background: var(--light-gray);
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            /* display: inline-block; */
            margin-bottom: 1rem;
        }

        .role-description {
            color: var(--gray);
            font-size: 0.875rem;
            line-height: 1.6;
            margin: 1rem 0;
            padding: 1rem;
            background: linear-gradient(to right, rgba(99, 102, 241, 0.05), transparent);
            border-radius: 10px;
            border-left: 3px solid var(--primary);
        }

        .permission-group {
            margin-bottom: 0.75rem;
        }

        .permission-group-name {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .permission-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
        }

        .permission-tag {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .permission-tag.granted {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .role-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.625rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-gray);
        }

        .role-actions button {
            padding: 0.625rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .role-actions-view {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.625rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-gray);
        }

        .role-actions-view button {
            padding: 0.625rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: var(--white);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--white);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: var(--white);
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            width: 100%;
            max-width: 1100px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #020218 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 2rem;
            /* border-radius: 20px 20px 0 0; */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: var(--white);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.25rem;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.625rem;
            font-size: 0.9375rem;
        }

        .form-group .required {
            color: var(--danger);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group small {
            color: var(--gray);
            font-size: 0.8125rem;
            display: block;
            margin-top: 0.375rem;
        }

        .permissions-section {
            margin-top: 2rem;
        }

        .permissions-section h4 {
            font-size: 1.125rem;
            color: var(--dark);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .permission-module {
            background: cornsilk;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            border-left: 4px solid var(--warning);
            box-shadow: var(--shadow-sm);
        }

        .permission-module-header {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .permission-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.75rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            background: var(--white);
            border-radius: 8px;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: var(--transition);
        }

        .checkbox-wrapper:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateX(4px);
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            cursor: pointer;
            flex: 1;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            background: var(--light-gray);
            border-radius: 0 0 20px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-footer button {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cancel {
            background: var(--white);
            color: var(--dark);
            border: 2px solid var(--border);
        }

        .btn-cancel:hover {
            background: var(--light-gray);
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 1.25rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.5;
            margin-bottom: 1.25rem;
            display: block;
            color: var(--primary);
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.625rem;
        }

        .empty-state-text {
            font-size: 0.9375rem;
        }

        /* Loading */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            text-align: center;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--light-gray);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.125rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .roles-grid {
                grid-template-columns: 1fr;
            }

            .action-bar {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .role-actions {
                grid-template-columns: 1fr;
            }

            .permission-checkboxes {
                grid-template-columns: 1fr;
            }

            .modal-content {
                border-radius: 12px;
            }

            .modal-header {
                border-radius: 12px 12px 0 0;
            }
        }

        /* Scrollbar Styling */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* System Role Badge */
        .badge-system {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            font-size: 0.7rem;
            font-weight: 700;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 0.5rem;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.3);
        }

        .badge-system i {
            font-size: 0.65rem;
        }

        /* Disabled button for system roles */
        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
            background: #d1d5db !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn-delete:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Lock icon for system role name field */
        .input-locked {
            background: #f3f4f6 !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }

        .input-locked-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--warning);
            font-size: 1rem;
        }

        /* Warning box for system roles */
        .system-role-warning {
            padding: 1rem;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid var(--warning);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .system-role-warning i {
            color: var(--warning);
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }

        .system-role-warning-content {
            flex: 1;
        }

        .system-role-warning-title {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 0.25rem;
        }

        .system-role-warning-text {
            font-size: 0.875rem;
            color: #78350f;
            line-height: 1.5;
        }

        /* Dependencies info badge */
        .dependencies-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .dependencies-badge.has-deps {
            background: #fee2e2;
            color: #991b1b;
        }
        
    </style>

    <!-- Header -->
    <div class="roles-header">
        <h2>
            <i class="fas fa-user-shield"></i>
            Role Management
        </h2>
        <p>Manage system roles and permissions for IT Ticket System</p>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchRoles" placeholder="Search roles by name or description..." oninput="RolesManager.filterRoles()">
            <i class="fas fa-times clear-icon" id="clearSearch" onclick="RolesManager.clearSearch()"></i>
        </div>
        <button class="btn-add-role" onclick="RolesManager.openAddModal()">
            <i class="fas fa-plus-circle"></i>
            Add New Role
        </button>
    </div>

    <!-- Roles Container -->
    <div id="rolesContainer">
        <div class="empty-state">
            <i class="fas fa-shield-alt"></i>
            <div class="empty-state-title">Loading Roles...</div>
            <div class="empty-state-text">Please wait while we fetch the roles data</div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="roleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">
                    <i class="fas fa-user-plus"></i>
                    Add New Role
                </h3>
                <button class="modal-close" onclick="RolesManager.closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="roleId">

                    <div class="form-group">
                        <label>Role Name <span class="required">*</span></label>
                        <input type="text" id="roleName" placeholder="e.g., support_agent" required>
                    </div>

                    <div class="form-group">
                        <label>Display Name <span class="required">*</span></label>
                        <input type="text" id="roleDisplayName" placeholder="e.g., Support Agent" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="roleDescription" placeholder="Describe what this role can do..."></textarea>
                    </div>

                    <div class="permissions-section">
                        <h4>
                            <i class="fas fa-key"></i>
                            Permissions
                        </h4>

                        <!-- Tickets Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-ticket-alt"></i> Tickets
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_view">
                                    <label for="perm_tickets_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_create">
                                    <label for="perm_tickets_create">Create</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_edit">
                                    <label for="perm_tickets_edit">Edit</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_delete">
                                    <label for="perm_tickets_delete">Delete</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_assign">
                                    <label for="perm_tickets_assign">Assign</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_tickets_close">
                                    <label for="perm_tickets_close">Close</label>
                                </div>
                            </div>
                        </div>

                        <!-- Services Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-cogs"></i> Services
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_services_view">
                                    <label for="perm_services_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_services_create">
                                    <label for="perm_services_create">Create</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_services_edit">
                                    <label for="perm_services_edit">Edit</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_services_delete">
                                    <label for="perm_services_delete">Delete</label>
                                </div>
                            </div>
                        </div>

                        <!-- Workflows Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-project-diagram"></i> Workflows
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_workflows_view">
                                    <label for="perm_workflows_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_workflows_create">
                                    <label for="perm_workflows_create">Create</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_workflows_edit">
                                    <label for="perm_workflows_edit">Edit</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_workflows_delete">
                                    <label for="perm_workflows_delete">Delete</label>
                                </div>
                            </div>
                        </div>

                        <!-- Users Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-users"></i> Users
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_users_view">
                                    <label for="perm_users_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_users_create">
                                    <label for="perm_users_create">Create</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_users_edit">
                                    <label for="perm_users_edit">Edit</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_users_delete">
                                    <label for="perm_users_delete">Delete</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_users_assign_roles">
                                    <label for="perm_users_assign_roles">Assign Roles</label>
                                </div>
                            </div>
                        </div>

                        <!-- Reports Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-chart-bar"></i> Reports
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_reports_view">
                                    <label for="perm_reports_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_reports_export">
                                    <label for="perm_reports_export">Export</label>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Module -->
                        <div class="permission-module">
                            <div class="permission-module-header">
                                <i class="fas fa-cog"></i> Settings
                            </div>
                            <div class="permission-checkboxes">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_settings_view">
                                    <label for="perm_settings_view">View</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="perm_settings_edit">
                                    <label for="perm_settings_edit">Edit</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" type="button" onclick="RolesManager.closeModal()">Cancel</button>
                <button class="btn-save" type="button" onclick="RolesManager.saveRole()">
                    <i class="fas fa-save"></i> Save Role
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const RolesManager = {
        baseUrl: '<?= base_url() ?>',
        roles: [],
        allRoles: [],
        currentRole: null,

        async init() {
            console.log('Initializing Roles Tab...');

            this.validator = new FormValidationManager(ValidationRoles.roles);
            this.validator.setupFormValidation([{
                    fieldId: 'roleName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'roleDisplayName',
                    fieldName: 'display_name',
                },
                {
                    fieldId: 'roleDescription',
                    fieldName: 'description'
                }
            ]);

            await this.loadRoles();
        },

        async loadRoles() {
            this.showLoading();
            try {
                const response = await fetch(`${this.baseUrl}api/roles`);
                const data = await response.json();

                if (data.success) {
                    this.allRoles = data.data || [];
                    this.roles = [...this.allRoles];
                    this.renderRoles();
                } else {
                    TicketNotifier.showError('Failed to load roles');
                }
            } catch (error) {
                console.error('Error loading roles:', error);
                TicketNotifier.showError('Error loading roles. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        renderRoles() {
            const container = document.getElementById('rolesContainer');

            if (this.roles.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-shield-alt"></i>
                        <div class="empty-state-title">No Roles Found</div>
                        <div class="empty-state-text">Click "Add New Role" to create your first role</div>
                    </div>
                `;
                return;
            }

            const html = `
                <div class="roles-grid">
                    ${this.roles.map(role => this.createRoleCard(role)).join('')}
                </div>
            `;

            container.innerHTML = html;
        },

        createRoleCard(role) {
            const permissions = role.permissions || {};
            const isSystem = role.is_system == 1;
            const canDelete = role.can_delete !== false; // Default true if not set
            const deps = role.dependencies || {};
            const totalDeps = deps.total || 0;

            return `
                <div class="role-card">
                    <div class="role-icon">
                        <i class="fas fa-${this.getRoleIcon(role.name)}"></i>
                        ${isSystem ? '<span class="badge-system"><i class="fas fa-lock"></i> SYSTEM</span>' : ''}
                    </div>
                    <div class="role-name">${role.name}</div>
                    <div class="role-display-name">
                        ${role.display_name}
                        ${totalDeps > 0 ? `<span class="dependencies-badge has-deps"><i class="fas fa-link"></i> ${totalDeps} dependencies</span>` : ''}
                    </div>
                    ${role.description ? `
                        <div class="role-description">
                            <i class="fas fa-info-circle"></i> ${role.description}
                        </div>
                    ` : ''}
                    <div class="role-permissions">
                        ${this.renderPermissionsSummary(permissions)}
                    </div>
                    <div class="role-actions">
                        <button class="btn-view" onclick="RolesManager.viewRole(${role.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn-edit" onclick="RolesManager.editRole(${role.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-delete" 
                                onclick="RolesManager.deleteRole(${role.id}, '${role.name}')"
                                ${!canDelete || isSystem ? 'disabled' : ''}
                                title="${isSystem ? 'System roles cannot be deleted' : totalDeps > 0 ? 'Remove all dependencies first' : 'Delete role'}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        },

        getRoleIcon(roleName) {
            const icons = {
                admin: 'user-shield',
                support_agent: 'headset',
                user: 'user',
                team_lead: 'user-tie',
                manager: 'user-cog'
            };
            return icons[roleName] || 'user-shield';
        },

        renderPermissionsSummary(permissions) {
            const modules = Object.keys(permissions);
            if (modules.length === 0) {
                return '<div style="color: #9ca3af; font-size: 0.875rem;">No permissions set</div>';
            }

            const summary = modules.slice(0, 3).map(module => {
                const perms = permissions[module];
                const granted = Object.values(perms).filter(Boolean).length;
                const total = Object.keys(perms).length;

                if (granted === 0) return '';

                return `
                    <div class="permission-group">
                        <div class="permission-group-name">
                            <i class="fas fa-${this.getModuleIcon(module)}"></i>
                            ${module}
                        </div>
                        <div class="permission-tags">
                            <span class="permission-tag granted">${granted}/${total} granted</span>
                        </div>
                    </div>
                `;
            }).filter(Boolean).join('');

            const remaining = modules.length > 3 ?
                `<div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.5rem;">+${modules.length - 3} more modules</div>` :
                '';

            return summary + remaining;
        },

        getModuleIcon(module) {
            const icons = {
                tickets: 'ticket-alt',
                services: 'cogs',
                workflows: 'project-diagram',
                users: 'users',
                reports: 'chart-bar',
                settings: 'cog'
            };
            return icons[module] || 'cube';
        },

        filterRoles() {
            const search = document.getElementById('searchRoles').value.toLowerCase();
            const clearIcon = document.getElementById('clearSearch');

            clearIcon.style.display = search ? 'block' : 'none';

            this.roles = search ?
                this.allRoles.filter(role =>
                    role.name.toLowerCase().includes(search) ||
                    role.display_name.toLowerCase().includes(search) ||
                    (role.description && role.description.toLowerCase().includes(search))
                ) :
                [...this.allRoles];

            this.renderRoles();
        },

        clearSearch() {
            document.getElementById('searchRoles').value = '';
            this.filterRoles();
        },

        openAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New Role';
            document.getElementById('roleForm').reset();
            document.getElementById('roleId').value = '';

            document.querySelectorAll('[id^="perm_"]').forEach(cb => cb.checked = false);

            this.currentRole = null;
            this.validator.reset(['roleName', 'roleDisplayName', 'roleDescription']);
            document.getElementById('roleModal').classList.add('active');
        },

        closeModal() {
            this.validator.reset(['roleName', 'roleDisplayName', 'roleDescription']);
            document.getElementById('roleModal').classList.remove('active');
        },

        async viewRole(id) {
            const role = this.allRoles.find(r => r.id == id);
            if (!role) {
                TicketNotifier.showError('Role not found');
                return;
            }

            const isSystem = role.is_system == 1;
            const deps = role.dependencies || {};
            const permissions = role.permissions || {};

            const permissionsHtml = Object.entries(permissions).map(([module, perms]) => {
                const granted = Object.entries(perms).filter(([, v]) => v);
                const denied = Object.entries(perms).filter(([, v]) => !v);

                return `
                    <div class="permission-module" style="margin-bottom: 1rem;">
                        <div class="permission-module-header">
                            <i class="fas fa-${this.getModuleIcon(module)}"></i> ${module.toUpperCase()}
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem;">
                            ${granted.map(([p]) => `<span class="permission-tag granted"><i class="fas fa-check"></i> ${p}</span>`).join('')}
                            ${denied.map(([p]) => `<span class="permission-tag" style="background: #fee2e2; color: #991b1b;"><i class="fas fa-times"></i> ${p}</span>`).join('')}
                        </div>
                    </div>
                `;
            }).join('');

            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display: flex !important;';
            modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>
                                <i class="fas fa-${this.getRoleIcon(role.name)}"></i> ${role.display_name}
                                ${isSystem ? '<span class="badge-system" style="margin-left: 1rem;"><i class="fas fa-lock"></i> SYSTEM</span>' : ''}
                            </h3>
                            <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${isSystem ? `
                                <div class="system-role-warning" style="margin-bottom: 1.5rem;">
                                    <i class="fas fa-shield-alt"></i>
                                    <div class="system-role-warning-content">
                                        <div class="system-role-warning-title">System Role</div>
                                        <div class="system-role-warning-text">
                                            This role is protected by the system and cannot be deleted. 
                                            The role name is locked but other properties can be modified.
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        
                        ${deps.total > 0 ? `
                            <div style="background: #dbeafe; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid var(--info);">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <i class="fas fa-link" style="color: var(--info);"></i>
                                    <strong>Dependencies</strong>
                                </div>
                                <ul style="margin: 0; padding-left: 1.5rem; color: var(--dark);">
                                    ${deps.user_roles > 0 ? `<li>${deps.user_roles} user assignment(s)</li>` : ''}
                                    ${deps.workflow_transitions > 0 ? `<li>${deps.workflow_transitions} workflow transition(s)</li>` : ''}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <div style="background: var(--light-gray); padding: 1.25rem; border-radius: 10px; border-left: 4px solid var(--primary); margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                                <strong>Description</strong>
                            </div>
                            <p style="margin: 0; color: var(--gray); line-height: 1.6;">${role.description || 'No description'}</p>
                        </div>
                        
                        <h3 style="margin: 0 0 1.25rem 0; display: flex; align-items: center; gap: 0.625rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--light-gray);">
                            <i class="fas fa-key"></i> Permissions
                        </h3>
                        ${permissionsHtml || '<p style="color: var(--gray);">No permissions assigned</p>'}
                        
                        <div class="role-actions-view">
                            <button onclick="RolesManager.editRole(${role.id}); this.closest('.modal-overlay').remove();" 
                                    class="btn-edit" style="flex: 1; padding: 1rem;">
                                <i class="fas fa-edit"></i> Edit Role
                            </button>
                            <button onclick="RolesManager.deleteRole(${role.id}, '${role.name}'); this.closest('.modal-overlay').remove();" 
                                    class="btn-delete" 
                                    style="flex: 1; padding: 1rem;"
                                    ${!role.can_delete || isSystem ? 'disabled' : ''}
                                    title="${isSystem ? 'System role cannot be deleted' : deps.total > 0 ? 'Remove dependencies first' : 'Delete role'}">
                                <i class="fas fa-trash"></i> Delete Role
                            </button>
                        </div>
                    </div>
                </div>
            `;

            modal.addEventListener('click', e => {
                if (e.target === modal) modal.remove();
            });

            document.body.appendChild(modal);
        },

        async editRole(id) {
            const role = this.allRoles.find(r => r.id == id);
            if (!role) {
                TicketNotifier.showError('Role not found');
                return;
            }

            this.currentRole = role;
            const isSystem = role.is_system == 1;

            document.getElementById('modalTitle').innerHTML = `
                <i class="fas fa-edit"></i> Edit Role
                ${isSystem ? '<span class="badge-system" style="margin-left: 1rem;"><i class="fas fa-lock"></i> SYSTEM</span>' : ''}
            `;

            document.getElementById('roleId').value = role.id;

            const roleNameInput = document.getElementById('roleName');
            roleNameInput.value = role.name;

            // Lock name field for system roles
            if (isSystem) {
                roleNameInput.disabled = true;
                roleNameInput.classList.add('input-locked');
                roleNameInput.parentElement.style.position = 'relative';
                roleNameInput.parentElement.insertAdjacentHTML('beforeend',
                    '<i class="fas fa-lock input-locked-icon" title="System role name cannot be changed"></i>'
                );
            } else {
                roleNameInput.disabled = false;
                roleNameInput.classList.remove('input-locked');
                const lockIcon = roleNameInput.parentElement.querySelector('.input-locked-icon');
                if (lockIcon) lockIcon.remove();
            }

            document.getElementById('roleDisplayName').value = role.display_name;
            document.getElementById('roleDescription').value = role.description || '';

            // Add system role warning in modal body
            const modalBody = document.querySelector('#roleModal .modal-body');
            const existingWarning = modalBody.querySelector('.system-role-warning');
            if (existingWarning) existingWarning.remove();

            if (isSystem) {
                const warningHtml = `
                    <div class="system-role-warning">
                        <i class="fas fa-shield-alt"></i>
                        <div class="system-role-warning-content">
                            <div class="system-role-warning-title">Protected System Role</div>
                            <div class="system-role-warning-text">
                                This is a system role. The role name cannot be changed. 
                                You can update the display name, description, and permissions.
                            </div>
                        </div>
                    </div>
                `;
                modalBody.insertAdjacentHTML('afterbegin', warningHtml);
            }

            // Load permissions
            document.querySelectorAll('[id^="perm_"]').forEach(cb => cb.checked = false);

            const permissions = role.permissions || {};
            Object.entries(permissions).forEach(([module, perms]) => {
                Object.entries(perms).forEach(([action, value]) => {
                    const cb = document.getElementById(`perm_${module}_${action}`);
                    if (cb) cb.checked = value;
                });
            });

            document.getElementById('roleModal').classList.add('active');
        },

        async saveRole() {
            const id = document.getElementById('roleId').value;
            const name = document.getElementById('roleName').value.trim();
            const displayName = document.getElementById('roleDisplayName').value.trim();
            const description = document.getElementById('roleDescription').value.trim();

            const permissions = {
                tickets: this.getModulePermissions('tickets', ['view', 'create', 'edit', 'delete', 'assign', 'close']),
                services: this.getModulePermissions('services', ['view', 'create', 'edit', 'delete']),
                workflows: this.getModulePermissions('workflows', ['view', 'create', 'edit', 'delete']),
                users: this.getModulePermissions('users', ['view', 'create', 'edit', 'delete', 'assign_roles']),
                reports: this.getModulePermissions('reports', ['view', 'export']),
                settings: this.getModulePermissions('settings', ['view', 'edit'])
            };

            const formData = {
                name,
                display_name: displayName,
                description,
                permissions
            };

            const fieldMap = { 
                name: 'roleName', 
                display_name: 'roleDisplayName', 
                description: 'roleDescription' 
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            this.showLoading();

            try {
                const url = id ?
                    `${this.baseUrl}api/roles/${id}/update` :
                    `${this.baseUrl}api/roles/create`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    TicketNotifier.showSuccess(id ? 'Role updated successfully!' : 'Role created successfully!');
                    this.closeModal();
                    await this.loadRoles();
                } else {
                    TicketNotifier.showError('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving role:', error);
            } finally {
                this.hideLoading();
            }
        },

        getModulePermissions(module, actions) {
            return actions.reduce((acc, action) => {
                acc[action] = document.getElementById(`perm_${module}_${action}`).checked;
                return acc;
            }, {});
        },

        async deleteRole(id, name) {
            const confirmed = await TicketNotifier.confirm(
                'Confirm Action',
                `Are you sure you want to delete role ${name}?\n\nThis action cannot be undone.`,
                'Yes, proceed'
            );
            if (!confirmed) return;

            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/roles/${id}/delete`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    TicketNotifier.showSuccess('Role deleted successfully!');
                    await this.loadRoles();
                } else {
                    TicketNotifier.showError('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting role:', error);
            } finally {
                this.hideLoading();
            }
        },

        showLoading() {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.id = 'loadingOverlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <div class="loading-text">Processing...</div>
                </div>
            `;
            document.body.appendChild(overlay);
        },

        hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.remove();
        },

        showError(message) {
            document.getElementById('rolesContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    <div class="empty-state-title">Error</div>
                    <div class="empty-state-text">${message}</div>
                </div>
            `;
        }
    };

    function initRolesTab() {
        console.log('Initializing Roles Tab...');
        RolesManager.init();
    }

    function cleanupRolesTab() {
        console.log('Cleaning up Roles Tab...');
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }
</script>