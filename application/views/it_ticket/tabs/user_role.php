<div id="tab-user-role" class="tab-content">
    <style>
        /* ============================================ */
        /* SHARED CSS VARIABLES */
        /* ============================================ */
        #tab-user-role * {
            box-sizing: border-box;
        }

        #tab-user-role {
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

        /* ============================================ */
        /* HEADER SECTION */
        /* ============================================ */
        #tab-user-role .user-permissions-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        #tab-user-role .user-permissions-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        #tab-user-role .user-permissions-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }

        #tab-user-role .user-permissions-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
        }

        /* ============================================ */
        /* STATISTICS CARDS */
        /* ============================================ */
        #tab-user-role .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        #tab-user-role .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            padding: 1.75rem;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        #tab-user-role .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        #tab-user-role .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        #tab-user-role .stat-icon.primary {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        #tab-user-role .stat-icon.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        #tab-user-role .stat-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        #tab-user-role .stat-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        #tab-user-role .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        #tab-user-role .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 600;
        }

        /* ============================================ */
        /* VIEW TOGGLE */
        /* ============================================ */
        #tab-user-role .view-toggle {
            display: flex;
            gap: 0.5rem;
            padding: 0.375rem;
            background: var(--light-gray);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        #tab-user-role .view-toggle button {
            flex: 1;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            background: transparent;
            color: var(--gray);
        }

        #tab-user-role .view-toggle button.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        #tab-user-role .view-toggle button:hover:not(.active) {
            color: var(--dark);
        }

        /* ============================================ */
        /* ACTION BAR */
        /* ============================================ */
        #tab-user-role .action-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        #tab-user-role .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        #tab-user-role .search-box input {
            width: 100%;
            padding: 0.875rem 3rem 0.875rem 3rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--white);
        }

        #tab-user-role .search-box input:focus {
            border-color: var(--success);
            outline: none;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        #tab-user-role .search-box .search-icon,
        #tab-user-role .search-box .clear-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            transition: var(--transition);
        }

        #tab-user-role .search-box .search-icon {
            left: 1rem;
        }

        #tab-user-role .search-box .clear-icon {
            right: 1rem;
            cursor: pointer;
            display: none;
        }

        #tab-user-role .search-box .clear-icon:hover {
            color: var(--danger);
        }

        #tab-user-role .btn-add-role {
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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

        #tab-user-role .btn-add-role:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        #tab-user-role .btn-add-role:active {
            transform: translateY(0);
        }

        /* ============================================ */
        /* FILTERS BAR */
        /* ============================================ */
        #tab-user-role .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        #tab-user-role .filter-group {
            flex: 1;
            min-width: 200px;
        }

        #tab-user-role .filter-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        #tab-user-role .filter-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            transition: var(--transition);
        }

        #tab-user-role .filter-group select:focus {
            border-color: var(--success);
            outline: none;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        /* ============================================ */
        /* USER CARDS */
        /* ============================================ */
        #tab-user-role .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
        }

        #tab-user-role .user-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        #tab-user-role .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
        }

        #tab-user-role .user-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--success);
        }

        #tab-user-role .user-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid var(--light-gray);
        }

        #tab-user-role .user-avatar {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 700;
        }

        #tab-user-role .user-info {
            flex: 1;
        }

        #tab-user-role .user-id {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        #tab-user-role .user-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        #tab-user-role .user-status.active {
            background: #d1fae5;
            color: #065f46;
        }

        #tab-user-role .user-status.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Role Badges */
        #tab-user-role .role-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            min-height: 42px;
        }

        #tab-user-role .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: var(--transition);
        }

        #tab-user-role .role-badge.active {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 1px solid #3b82f6;
        }

        #tab-user-role .role-badge.inactive {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
            opacity: 0.6;
        }

        #tab-user-role .role-badge .remove-role {
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
        }

        #tab-user-role .role-badge .remove-role:hover {
            opacity: 1;
            color: var(--danger);
        }

        #tab-user-role .add-role-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border: 2px dashed var(--border);
            border-radius: 20px;
            background: transparent;
            color: var(--gray);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        #tab-user-role .add-role-btn:hover {
            border-color: var(--success);
            color: var(--success);
            background: rgba(16, 185, 129, 0.05);
        }

        /* Quick Actions */
        #tab-user-role .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.625rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-gray);
        }

        #tab-user-role .quick-actions button {
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

        #tab-user-role .btn-action-primary {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: var(--white);
        }

        #tab-user-role .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        #tab-user-role .btn-action-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: var(--white);
        }

        #tab-user-role .btn-action-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        #tab-user-role .btn-action-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--white);
        }

        #tab-user-role .btn-action-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* ============================================ */
        /* TABLE VIEW */
        /* ============================================ */
        #tab-user-role .assignments-table {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        #tab-user-role .assignments-table table {
            width: 100%;
            border-collapse: collapse;
        }

        #tab-user-role .assignments-table thead {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        #tab-user-role .assignments-table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        #tab-user-role .assignments-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--light-gray);
        }

        #tab-user-role .assignments-table tbody tr {
            transition: var(--transition);
        }

        #tab-user-role .assignments-table tbody tr:hover {
            background: rgba(16, 185, 129, 0.05);
        }

        /* ============================================ */
        /* ROLE SELECTOR */
        /* ============================================ */
        #tab-user-role .role-selector {
            max-height: 400px;
            overflow-y: auto;
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 10px;
        }

        #tab-user-role .role-option {
            padding: 1rem;
            background: var(--white);
            border-radius: 8px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        #tab-user-role .role-option:hover {
            border-color: var(--success);
            transform: translateX(4px);
        }

        #tab-user-role .role-option.selected {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }

        #tab-user-role .role-option-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        #tab-user-role .role-option input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--success);
        }

        #tab-user-role .role-option-name {
            font-weight: 700;
            color: var(--dark);
        }

        #tab-user-role .role-option-desc {
            font-size: 0.8125rem;
            color: var(--gray);
            padding-left: 2rem;
        }

        /* ============================================ */
        /* EMPTY STATE */
        /* ============================================ */
        #tab-user-role .empty-state {
            text-align: center;
            padding: 5rem 1.25rem;
            color: var(--gray);
        }

        #tab-user-role .empty-state i {
            font-size: 4rem;
            opacity: 0.5;
            margin-bottom: 1.25rem;
            display: block;
            color: var(--success);
        }

        #tab-user-role .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.625rem;
        }

        #tab-user-role .empty-state-text {
            font-size: 0.9375rem;
        }

        /* ============================================ */
        /* MODAL STYLES */
        /* ============================================ */

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
            background: white;
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
            /* color: var(--white); */
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

        #tab-user-role .form-group {
            margin-bottom: 1.5rem;
        }

        #tab-user-role .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.625rem;
            font-size: 0.9375rem;
        }

        #tab-user-role .form-group .required {
            color: var(--danger);
        }

        #tab-user-role .form-group input,
        #tab-user-role .form-group select,
        #tab-user-role .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            transition: var(--transition);
            font-family: inherit;
        }

        #tab-user-role .form-group input:focus,
        #tab-user-role .form-group select:focus,
        #tab-user-role .form-group textarea:focus {
            border-color: var(--success);
            outline: none;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        #tab-user-role .form-group small {
            color: var(--gray);
            font-size: 0.8125rem;
            display: block;
            margin-top: 0.375rem;
        }

        #tab-user-role .modal-footer {
            padding: 1.5rem 2rem;
            background: var(--light-gray);
            border-radius: 0 0 20px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        #tab-user-role .modal-footer button {
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

        #tab-user-role .btn-cancel {
            background: var(--white);
            color: var(--dark);
            border: 2px solid var(--border);
        }

        #tab-user-role .btn-cancel:hover {
            background: var(--light-gray);
        }

        #tab-user-role .btn-save {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }

        #tab-user-role .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        #tab-user-role .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        #tab-user-role .modal-content::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        #tab-user-role .modal-content::-webkit-scrollbar-thumb {
            background: var(--success);
            border-radius: 4px;
        }

        #tab-user-role .modal-content::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }

        /* ============================================ */
        /* LOADING OVERLAY */
        /* ============================================ */
        #tab-user-role .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        #tab-user-role .loading-spinner {
            text-align: center;
        }

        #tab-user-role .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--light-gray);
            border-top-color: var(--success);
            border-radius: 50%;
            animation: spinUserPerms 0.8s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spinUserPerms {
            to {
                transform: rotate(360deg);
            }
        }

        #tab-user-role .loading-text {
            color: var(--success);
            font-weight: 600;
            font-size: 1.125rem;
        }

        /* ============================================ */
        /* RESPONSIVE */
        /* ============================================ */
        @media (max-width: 768px) {
            #tab-user-role .users-grid {
                grid-template-columns: 1fr;
            }

            #tab-user-role .stats-grid {
                grid-template-columns: 1fr;
            }

            #tab-user-role .view-toggle {
                flex-direction: column;
            }

            #tab-user-role .action-bar {
                flex-direction: column;
            }

            #tab-user-role .search-box {
                min-width: 100%;
            }

            #tab-user-role .quick-actions {
                grid-template-columns: 1fr;
            }

            #tab-user-role .modal-content {
                border-radius: 12px;
            }

            #tab-user-role .modal-header {
                border-radius: 12px 12px 0 0;
                padding: 1.5rem;
            }

            #tab-user-role .modal-body {
                padding: 1.5rem;
            }

            #tab-user-role .modal-footer {
                padding: 1rem 1.5rem;
                flex-direction: column;
            }

            #tab-user-role .modal-footer button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <!-- Header -->
    <div class="user-permissions-header">
        <h2>
            <i class="fas fa-user-lock"></i>
            User Permissions Management
        </h2>
        <p>Manage role assignments and permissions for employees</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid" id="statsContainer">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value" id="statTotalUsers">-</div>
            <div class="stat-label">Total Users with Roles</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value" id="statActiveAssignments">-</div>
            <div class="stat-label">Active Assignments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-value" id="statTotalRoles">-</div>
            <div class="stat-label">Available Roles</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-value" id="statInactiveAssignments">-</div>
            <div class="stat-label">Inactive Assignments</div>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="view-toggle">
        <button class="active" onclick="UserPermissionsManager.switchView('users')">
            <i class="fas fa-users"></i> View by Users
        </button>
        <button onclick="UserPermissionsManager.switchView('roles')">
            <i class="fas fa-shield-alt"></i> View by Roles
        </button>
        <button onclick="UserPermissionsManager.switchView('table')">
            <i class="fas fa-table"></i> Table View
        </button>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchUserPermissions" placeholder="Search by employee ID or role..." oninput="UserPermissionsManager.filterData()">
            <i class="fas fa-times clear-icon" id="clearSearchUserPermissions" onclick="UserPermissionsManager.clearSearch()"></i>
        </div>
        <button class="btn-add-role" onclick="UserPermissionsManager.openAssignModal()">
            <i class="fas fa-user-plus"></i>
            Assign Roles to User
        </button>
    </div>

    <!-- Filters (show in table view) -->
    <div class="filters-bar" id="filtersBar" style="display: none;">
        <div class="filter-group">
            <label>Filter by Role</label>
            <select id="filterByRole" onchange="UserPermissionsManager.filterData()">
                <option value="">All Roles</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Filter by Status</label>
            <select id="filterByStatus" onchange="UserPermissionsManager.filterData()">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Content Container -->
    <div id="userPermissionsContent">
        <div class="empty-state">
            <i class="fas fa-user-lock"></i>
            <div class="empty-state-title">Loading User Permissions...</div>
            <div class="empty-state-text">Please wait while we fetch the data</div>
        </div>
    </div>

    <!-- Assign Role Modal -->
    <div class="modal-overlay" id="userPermAssignRoleModal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                <h3>
                    <i class="fas fa-user-plus"></i>
                    Assign Roles to User
                </h3>
                <button class="modal-close" onclick="UserPermissionsManager.closeAssignModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm">
                    <div class="form-group">
                        <label>Employee <span class="required">*</span></label>
                        <select id="assignEmployeeId">
                            <option value="">Select Employee</option>
                        </select>
                        <small>Select the employee to assign roles</small>
                    </div>

                    <div class="form-group">
                        <label>Select Roles <span class="required">*</span></label>
                        <div class="role-selector" id="roleSelectorList">
                            <!-- Roles will be loaded here -->
                        </div>
                        <small>Select one or more roles to assign</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" type="button" onclick="UserPermissionsManager.closeAssignModal()">Cancel</button>
                <button class="btn-save" type="button" onclick="UserPermissionsManager.saveAssignment()">
                    <i class="fas fa-save"></i> Assign Roles
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Roles Modal -->
    <div class="modal-overlay" id="userPermEditUserRolesModal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
                <h3>
                    <i class="fas fa-user-edit"></i>
                    Edit User Roles: <span id="editUserIdDisplay"></span>
                </h3>
                <button class="modal-close" onclick="UserPermissionsManager.closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editUserId">

                <div class="form-group">
                    <label>Current Roles</label>
                    <div id="currentRolesList" style="margin-bottom: 1.5rem;">
                        <!-- Current roles will be displayed here -->
                    </div>
                </div>

                <div class="form-group">
                    <label>Available Roles</label>
                    <div class="role-selector" id="editRoleSelectorList">
                        <!-- Available roles will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" type="button" onclick="UserPermissionsManager.closeEditModal()">Cancel</button>
                <button class="btn-save" type="button" onclick="UserPermissionsManager.saveEdit()">
                    <i class="fas fa-save"></i> Update Roles
                </button>
            </div>
        </div>
    </div>

    <!-- ✅ NEW MODAL 1: View Role Users Modal -->
    <div class="modal-overlay" id="userPermViewRoleUsersModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-users"></i>
                    Users with Role: <span id="viewRoleUsersRoleName"></span>
                </h3>
                <button class="modal-close" onclick="UserPermissionsManager.closeRoleUsersModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="roleUsersTableContainer">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer" style="background: var(--white); border-top: 2px solid var(--light-gray);">
                <button class="btn-cancel" onclick="UserPermissionsManager.closeRoleUsersModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- ✅ NEW MODAL 2: View User Details Modal -->
    <div class="modal-overlay" id="userPermViewUserDetailsModal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                <h3>
                    <i class="fas fa-user"></i>
                    User Details: <span id="viewUserDetailsEmployeeId"></span>
                </h3>
                <button class="modal-close" onclick="UserPermissionsManager.closeUserDetailsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    const UserPermissionsManager = {
        baseUrl: '<?= base_url() ?>',
        currentView: 'users',
        allData: [],
        filteredData: [],
        statistics: {},
        availableRoles: [],
        currentEditUser: null,
        isInitialized: false,
        isLoading: false,

        /**
         *  MAIN INITIALIZATION
         */
        async init() {
            console.log('[UserPermissions] Init started...');

            //  Prevent duplicate initialization
            if (this.isInitialized) {
                console.log('[UserPermissions] Already initialized');
                return;
            }

            if (this.isLoading) {
                console.log('[UserPermissions] Already loading');
                return;
            }

            this.isLoading = true;
            this.showLoading();

            try {
                //  Sequential loading with proper error handling
                console.log('Loading statistics...');
                await this.loadStatistics();

                console.log('Loading available roles...');
                await this.loadAvailableRoles();

                console.log('Loading users data...');
                await this.loadData();

                console.log('Loading users dropdown...');
                await this.loadUsersDropdowns();

                this.isInitialized = true;
                console.log(' [UserPermissions] Initialization complete');
                console.log(`   - Roles: ${this.availableRoles.length}`);
                console.log(`   - Users: ${this.allData.length}`);
                console.log(`   - Stats:`, this.statistics);

            } catch (error) {
                console.error('[UserPermissions] Initialization failed:', error);
                this.showError('Failed to initialize User Permissions. Please refresh the page.');
            } finally {
                this.isLoading = false;
                this.hideLoading();
            }
        },

        /**
         *  LOAD STATISTICS
         */
        async loadStatistics() {
            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/statistics`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Statistics response:', data);

                if (data.success && data.data) {
                    this.statistics = data.data;
                    this.updateStatistics();
                    console.log(' Statistics loaded successfully');
                } else {
                    console.warn('Statistics API returned no data');
                    this.statistics = {
                        unique_employees: 0,
                        active_assignments: 0,
                        total_assignments: 0,
                        roles_usage: []
                    };
                    this.updateStatistics();
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                // Set default values
                this.statistics = {
                    unique_employees: 0,
                    active_assignments: 0,
                    total_assignments: 0,
                    roles_usage: []
                };
                this.updateStatistics();
            }
        },

        /**
         *  UPDATE STATISTICS UI
         */
        updateStatistics() {
            const stats = this.statistics;

            document.getElementById('statTotalUsers').textContent = stats.unique_employees || 0;
            document.getElementById('statActiveAssignments').textContent = stats.active_assignments || 0;
            document.getElementById('statTotalRoles').textContent = stats.roles_usage?.length || 0;
            document.getElementById('statInactiveAssignments').textContent =
                (stats.total_assignments || 0) - (stats.active_assignments || 0);

            console.log('Statistics updated in UI');
        },

        /**
         *  LOAD AVAILABLE ROLES
         */
        async loadAvailableRoles() {
            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/available-roles`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Roles API response:', data);

                if (data.success && Array.isArray(data.data)) {
                    this.availableRoles = data.data;
                    console.log(` Loaded ${this.availableRoles.length} roles`);

                    if (this.availableRoles.length > 0) {
                        console.log('   First role:', this.availableRoles[0]);
                        this.populateRoleFilters();
                    } else {
                        console.warn('No roles available in system');
                    }
                } else {
                    throw new Error('Invalid roles data structure');
                }
            } catch (error) {
                console.error('Error loading roles:', error);
                this.availableRoles = [];
                this.showRolesWarning();
            }
        },

        /**
         *  SHOW WARNING WHEN NO ROLES
         */
        showRolesWarning() {
            console.warn('No roles available');

            const filterSelect = document.getElementById('filterByRole');
            if (filterSelect) {
                filterSelect.innerHTML = '<option value="">No roles available</option>';
            }
        },

        /**
         *  POPULATE ROLE FILTERS
         */
        populateRoleFilters() {
            const filterSelect = document.getElementById('filterByRole');
            if (!filterSelect) {
                console.warn('filterByRole element not found');
                return;
            }

            filterSelect.innerHTML = '<option value="">All Roles</option>';

            if (this.availableRoles.length === 0) {
                console.warn('No roles to populate');
                return;
            }

            this.availableRoles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.display_name;
                filterSelect.appendChild(option);
            });

            console.log(` Populated ${this.availableRoles.length} roles in filter`);
        },

        /**
         *  LOAD USERS DROPDOWN
         */
        async loadUsersDropdowns() {
            try {
                const response = await fetch(`${this.baseUrl}employees/active`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success && Array.isArray(data.data)) {
                    const options = data.data.map(user =>
                        `<option value="${user.employee_id}">${user.fullname} (${user.department_code || 'N/A'})</option>`
                    ).join('');

                    const dropdown = document.getElementById('assignEmployeeId');
                    if (dropdown) {
                        dropdown.innerHTML = '<option value="">Select Employee</option>' + options;
                    }

                    console.log(` Loaded ${data.data.length} users in dropdown`);
                }
            } catch (error) {
                console.error('Error loading users dropdown:', error);
            }
        },

        /**
         *  LOAD USER PERMISSIONS DATA
         */
        async loadData() {
            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/by-users?per_page=100`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success && Array.isArray(data.data)) {
                    this.allData = data.data;
                    this.filteredData = [...this.allData];

                    console.log(` Loaded ${this.allData.length} users with roles`);

                    this.renderCurrentView();
                } else {
                    console.warn('No user permissions data');
                    this.allData = [];
                    this.filteredData = [];
                    this.renderCurrentView();
                }
            } catch (error) {
                console.error('Error loading user permissions:', error);
                this.showError('Failed to load user permissions data');
            }
        },

        /**
         *  SWITCH VIEW
         */
        switchView(view) {
            console.log(`🔄 Switching to ${view} view`);

            this.currentView = view;

            // Update active button
            document.querySelectorAll('#tab-user-role .view-toggle button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('button').classList.add('active');

            // Show/hide filters
            const filtersBar = document.getElementById('filtersBar');
            if (filtersBar) {
                filtersBar.style.display = view === 'table' ? 'flex' : 'none';
            }

            this.renderCurrentView();
        },

        /**
         *  RENDER CURRENT VIEW
         */
        renderCurrentView() {
            switch (this.currentView) {
                case 'users':
                    this.renderUsersView();
                    break;
                case 'roles':
                    this.renderRolesView();
                    break;
                case 'table':
                    this.renderTableView();
                    break;
            }
        },

        /**
         *  RENDER USERS VIEW
         */
        renderUsersView() {
            const container = document.getElementById('userPermissionsContent');

            if (this.filteredData.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <div class="empty-state-title">No Users Found</div>
                        <div class="empty-state-text">No users have been assigned roles yet</div>
                    </div>
                `;
                return;
            }

            const html = `
                <div class="users-grid">
                    ${this.filteredData.map(user => this.createUserCard(user)).join('')}
                </div>
            `;

            container.innerHTML = html;
        },

        /**
         *  CREATE USER CARD
         */
        createUserCard(user) {
            const activeRoles = user.roles.filter(r => r.is_active);
            const inactiveRoles = user.roles.filter(r => !r.is_active);
            const initial = user.employee_id.substring(0, 2).toUpperCase();

            return `
                <div class="user-card">
                    <div class="user-header">
                        <div class="user-avatar"><i class="fas fa-user"></i></div>
                        <div class="user-info">
                            <div class="user-id">ID: ${user.employee_id}</div>
                            <span class="user-status ${activeRoles.length > 0 ? 'active' : 'inactive'}">
                                <i class="fas fa-circle"></i>
                                ${activeRoles.length} Active Role${activeRoles.length !== 1 ? 's' : ''}
                            </span>
                        </div>
                    </div>
                    
                    <div class="role-badges">
                        ${activeRoles.map(role => `
                            <span class="role-badge active" title="${role.role_description || ''}">
                                <i class="fas fa-shield-alt"></i>
                                ${role.role_display_name}
                                <i class="fas fa-times remove-role" 
                                   onclick="UserPermissionsManager.removeRole(${role.id}, '${user.employee_id}', '${role.role_display_name}')"></i>
                            </span>
                        `).join('')}
                        ${inactiveRoles.map(role => `
                            <span class="role-badge inactive" title="Inactive - ${role.role_description || ''}">
                                <i class="fas fa-ban"></i>
                                ${role.role_display_name}
                            </span>
                        `).join('')}
                        <button class="add-role-btn" onclick="UserPermissionsManager.openEditModal('${user.employee_id}')">
                            <i class="fas fa-plus"></i>
                            Add Role
                        </button>
                    </div>
                    
                    <div class="quick-actions">
                        <button class="btn-action-primary" onclick="UserPermissionsManager.openEditModal('${user.employee_id}')">
                            <i class="fas fa-edit"></i> Manage Roles
                        </button>
                        <button class="btn-action-success" onclick="UserPermissionsManager.viewUserDetails('${user.employee_id}')">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         *  RENDER ROLES VIEW
         */
        renderRolesView() {
            const container = document.getElementById('userPermissionsContent');

            if (this.availableRoles.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-shield-alt"></i>
                        <div class="empty-state-title">No Roles Available</div>
                        <div class="empty-state-text">Please create roles first in the Roles tab</div>
                    </div>
                `;
                return;
            }

            const html = `
                <div class="users-grid">
                    ${this.availableRoles.map(role => this.createRoleCard(role)).join('')}
                </div>
            `;

            container.innerHTML = html;

            // Load user counts for each role
            this.loadRoleUserCounts();
        },

        /**
         *  CREATE ROLE CARD
         */
        createRoleCard(role) {
            return `
                <div class="user-card">
                    <div class="user-header">
                        <div class="user-avatar" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-id" title="${role.display_name}">${role.display_name}</div>
                            <span class="user-status active">
                                <i class="fas fa-users"></i>
                                <span id="role-${role.id}-count">-</span> Users
                            </span>
                        </div>
                    </div>
                    
                    <div class="role-description" style="margin: 1rem 0; color: var(--gray); font-size: 0.875rem; min-height: 40px;">
                        ${role.description || 'No description'}
                    </div>
                    
                    <div class="quick-actions" style="grid-template-columns: 1fr;">
                        <button class="btn-action-primary" onclick="UserPermissionsManager.viewRoleUsers(${role.id}, '${role.display_name.replace(/'/g, "\\'")}')">
                            <i class="fas fa-users"></i> View Assigned Users
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         *  LOAD ROLE USER COUNTS
         */
        async loadRoleUserCounts() {
            for (const role of this.availableRoles) {
                try {
                    const response = await fetch(`${this.baseUrl}api/user-roles/role/${role.id}/users?active_only=1`);
                    const data = await response.json();

                    if (data.success) {
                        const countEl = document.getElementById(`role-${role.id}-count`);
                        if (countEl) {
                            countEl.textContent = data.count || 0;
                        }
                    }
                } catch (error) {
                    console.error(`Error loading count for role ${role.id}:`, error);
                }
            }
        },

        async viewRoleUsers(roleId, roleName) {
            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/role/${roleId}/users?active_only=1`);
                const data = await response.json();

                if (!data.success) {
                    alert('Failed to load users for this role');
                    return;
                }

                const users = data.data || [];

                // Update modal title
                document.getElementById('viewRoleUsersRoleName').textContent = roleName;

                // Update modal content
                const container = document.getElementById('roleUsersTableContainer');

                if (users.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <div class="empty-state-title">No Users Assigned</div>
                            <div class="empty-state-text">No active users have this role</div>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="assignments-table" style="border: none; box-shadow: none;">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Employee ID</th>
                                        <th style="width: 30%;">Assigned Date</th>
                                        <th style="width: 25%;">Assigned By</th>
                                        <th style="width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${users.map(user => `
                                        <tr>
                                            <td><strong>${user.employee_id}</strong></td>
                                            <td>${new Date(user.assigned_at).toLocaleString('en-US', {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}</td>
                                            <td>${user.assigned_by || 'System'}</td>
                                            <td>
                                                <button class="btn-action-danger" 
                                                        style="padding: 0.5rem 0.75rem; font-size: 0.8125rem;"
                                                        onclick="UserPermissionsManager.removeRoleFromModal(${user.id}, '${user.employee_id}', '${roleName.replace(/'/g, "\\'")}', ${roleId})">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                // Show modal
                document.getElementById('userPermViewRoleUsersModal').classList.add('active');

            } catch (error) {
                console.error('Error viewing role users:', error);
                alert('Error loading users for this role');
            }
        },

        closeRoleUsersModal() {
            document.getElementById('userPermViewRoleUsersModal').classList.remove('active');
        },
        async removeRoleFromModal(assignmentId, employeeId, roleName, roleId) {
            if (!confirm(`Remove role "${roleName}" from user ${employeeId}?`)) {
                return;
            }

            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/${assignmentId}/remove`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    alert('Role removed successfully!');

                    // Reload data
                    await this.loadData();
                    await this.loadStatistics();

                    // Reopen modal with updated data
                    const role = this.availableRoles.find(r => r.id === roleId);
                    if (role) {
                        await this.viewRoleUsers(roleId, role.display_name);
                    } else {
                        this.closeRoleUsersModal();
                    }
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error removing role:', error);
                alert('Error removing role. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        /**
         *  RENDER TABLE VIEW
         */
        renderTableView() {
            const container = document.getElementById('userPermissionsContent');

            const tableData = [];
            this.filteredData.forEach(user => {
                user.roles.forEach(role => {
                    tableData.push({
                        employee_id: user.employee_id,
                        role_id: role.role_id,
                        role_name: role.role_display_name,
                        role_code: role.role_name,
                        is_active: role.is_active,
                        assigned_at: role.assigned_at,
                        assigned_by: role.assigned_by,
                        assignment_id: role.id
                    });
                });
            });

            if (tableData.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-table"></i>
                        <div class="empty-state-title">No Data Available</div>
                        <div class="empty-state-text">No role assignments found</div>
                    </div>
                `;
                return;
            }

            const html = `
                <div class="assignments-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                                <th>Assigned By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableData.map(item => `
                                <tr>
                                    <td><strong>${item.employee_id}</strong></td>
                                    <td>
                                        <span class="role-badge ${item.is_active ? 'active' : 'inactive'}">
                                            <i class="fas fa-shield-alt"></i>
                                            ${item.role_name}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="user-status ${item.is_active ? 'active' : 'inactive'}">
                                            <i class="fas fa-circle"></i>
                                            ${item.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td>${new Date(item.assigned_at).toLocaleString()}</td>
                                    <td>${item.assigned_by || 'System'}</td>
                                    <td>
                                        <div class="quick-actions" style="margin: 0; padding: 0; border: none;">
                                            ${item.is_active ? `
                                                <button class="btn-action-danger" 
                                                        onclick="UserPermissionsManager.removeRole(${item.assignment_id}, '${item.employee_id}', '${item.role_name}')">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            ` : `
                                                <button class="btn-action-success" 
                                                        onclick="UserPermissionsManager.toggleRole(${item.assignment_id})">
                                                    <i class="fas fa-check"></i> Activate
                                                </button>
                                            `}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
        },

        /**
         *  FILTER DATA
         */
        filterData() {
            const search = document.getElementById('searchUserPermissions').value.toLowerCase();
            const roleFilter = document.getElementById('filterByRole')?.value;
            const statusFilter = document.getElementById('filterByStatus')?.value;

            const clearIcon = document.getElementById('clearSearchUserPermissions');
            if (clearIcon) {
                clearIcon.style.display = search ? 'block' : 'none';
            }

            this.filteredData = this.allData.filter(user => {
                const matchesSearch = !search ||
                    user.employee_id.toLowerCase().includes(search) ||
                    user.roles.some(r =>
                        r.role_name.toLowerCase().includes(search) ||
                        r.role_display_name.toLowerCase().includes(search)
                    );

                if (!matchesSearch) return false;

                if (roleFilter) {
                    const hasRole = user.roles.some(r => r.role_id == roleFilter);
                    if (!hasRole) return false;
                }

                if (statusFilter !== '' && statusFilter !== null) {
                    const hasActiveRole = user.roles.some(r => r.is_active == statusFilter);
                    if (!hasActiveRole) return false;
                }

                return true;
            });

            this.renderCurrentView();
        },

        /**
         *  CLEAR SEARCH
         */
        clearSearch() {
            document.getElementById('searchUserPermissions').value = '';
            this.filterData();
        },

        /**
         *  OPEN ASSIGN MODAL
         */
        async openAssignModal() {
            console.log('🔓 Opening Assign Modal');
            await this.loadUsersDropdowns();

            // Reset form
            const employeeSelect = document.getElementById('assignEmployeeId');
            if (employeeSelect) employeeSelect.value = '';

            //  Check if roles are loaded
            if (!this.availableRoles || this.availableRoles.length === 0) {
                console.warn('No roles available, loading...');

                const container = document.getElementById('roleSelectorList');
                if (container) {
                    container.innerHTML = `
                        <div style="padding: 2rem; text-align: center;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--success);"></i>
                            <p style="margin-top: 1rem; color: var(--gray);">Loading roles...</p>
                        </div>
                    `;
                }

                await this.loadAvailableRoles();
            }

            // Load roles into modal
            this.loadRolesForAssignment();

            // Show modal
            document.getElementById('userPermAssignRoleModal').classList.add('active');
        },

        /**
         *  CLOSE ASSIGN MODAL
         */
        closeAssignModal() {
            document.getElementById('userPermAssignRoleModal').classList.remove('active');
        },

        /**
         *  LOAD ROLES FOR ASSIGNMENT MODAL
         */
        loadRolesForAssignment() {
            const container = document.getElementById('roleSelectorList');

            if (!container) {
                console.error('#roleSelectorList not found');
                return;
            }

            console.log(`Rendering ${this.availableRoles.length} roles for assignment`);

            if (this.availableRoles.length === 0) {
                container.innerHTML = `
                    <div style="padding: 2rem; text-align: center; color: #ef4444; background: #fee2e2; border-radius: 10px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <p style="margin: 0; font-weight: 600; color: #991b1b;">No Roles Available</p>
                        <small style="color: #991b1b;">Please create roles in the Roles tab first</small>
                    </div>
                `;
                return;
            }

            const html = this.availableRoles.map(role => `
                <div class="role-option" onclick="this.classList.toggle('selected'); this.querySelector('input').click();">
                    <div class="role-option-header">
                        <input type="checkbox" 
                               name="selected_roles" 
                               value="${role.id}"
                               onclick="event.stopPropagation()">
                        <span class="role-option-name">
                            ${role.display_name}
                            ${role.is_system == 1 ? '<span style="font-size: 0.65rem; margin-left: 0.5rem; color: var(--warning);"><i class="fas fa-lock"></i> SYSTEM</span>' : ''}
                        </span>
                    </div>
                    ${role.description ? `
                        <div class="role-option-desc">${role.description}</div>
                    ` : ''}
                </div>
            `).join('');

            container.innerHTML = html;
            console.log(' Roles rendered in modal');
        },

        /**
         *  SAVE ASSIGNMENT
         */
        async saveAssignment() {
            const employeeId = document.getElementById('assignEmployeeId').value.trim();
            const selectedRoles = Array.from(document.querySelectorAll('input[name="selected_roles"]:checked'))
                .map(cb => parseInt(cb.value));

            if (!employeeId) {
                alert('Please select an employee');
                return;
            }

            if (selectedRoles.length === 0) {
                alert('Please select at least one role');
                return;
            }

            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/bulk-assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        role_ids: selectedRoles
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Roles assigned successfully!');
                    this.closeAssignModal();
                    await this.loadData();
                    await this.loadStatistics();
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error assigning roles:', error);
                alert('Error assigning roles. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        /**
         *  OPEN EDIT MODAL
         */
        async openEditModal(employeeId) {
            this.currentEditUser = employeeId;
            document.getElementById('editUserId').value = employeeId;
            document.getElementById('editUserIdDisplay').textContent = employeeId;

            const user = this.allData.find(u => u.employee_id === employeeId);

            if (user) {
                const currentRolesHtml = user.roles
                    .filter(r => r.is_active)
                    .map(role => `
                        <span class="role-badge active">
                            <i class="fas fa-shield-alt"></i>
                            ${role.role_display_name}
                            <i class="fas fa-times remove-role" 
                               onclick="UserPermissionsManager.removeRole(${role.id}, '${employeeId}', '${role.role_display_name}')"></i>
                        </span>
                    `).join('');

                document.getElementById('currentRolesList').innerHTML = currentRolesHtml ||
                    '<p style="color: var(--gray);">No active roles</p>';
            }

            const assignedRoleIds = user ? user.roles.filter(r => r.is_active).map(r => r.role_id) : [];

            const container = document.getElementById('editRoleSelectorList');
            container.innerHTML = this.availableRoles.map(role => {
                const isAssigned = assignedRoleIds.includes(role.id);
                return `
                    <div class="role-option ${isAssigned ? 'selected' : ''}" 
                         onclick="this.classList.toggle('selected'); this.querySelector('input').click();">
                        <div class="role-option-header">
                            <input type="checkbox" 
                                   name="edit_selected_roles" 
                                   value="${role.id}"
                                   ${isAssigned ? 'checked' : ''}
                                   onclick="event.stopPropagation()">
                            <span class="role-option-name">${role.display_name}</span>
                        </div>
                        ${role.description ? `
                            <div class="role-option-desc">${role.description}</div>
                        ` : ''}
                    </div>
                `;
            }).join('');

            document.getElementById('userPermEditUserRolesModal').classList.add('active');
        },

        /**
         *  CLOSE EDIT MODAL
         */
        closeEditModal() {
            document.getElementById('userPermEditUserRolesModal').classList.remove('active');
        },

        /**
         *  SAVE EDIT
         */
        async saveEdit() {
            const employeeId = document.getElementById('editUserId').value;
            const selectedRoles = Array.from(document.querySelectorAll('input[name="edit_selected_roles"]:checked'))
                .map(cb => parseInt(cb.value));

            if (selectedRoles.length === 0) {
                if (!confirm('Remove all roles from this user?')) {
                    return;
                }
            }

            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/bulk-assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        role_ids: selectedRoles
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Roles updated successfully!');
                    this.closeEditModal();
                    await this.loadData();
                    await this.loadStatistics();
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error updating roles:', error);
                alert('Error updating roles. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        /**
         *  REMOVE ROLE
         */
        async removeRole(assignmentId, employeeId, roleName) {
            if (!confirm(`Remove role "${roleName}" from user ${employeeId}?`)) {
                return;
            }

            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/${assignmentId}/remove`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    alert('Role removed successfully!');
                    await this.loadData();
                    await this.loadStatistics();
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error removing role:', error);
                alert('Error removing role. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        /**
         *  TOGGLE ROLE STATUS
         */
        async toggleRole(assignmentId) {
            this.showLoading();

            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/${assignmentId}/toggle`, {
                    method: 'PUT'
                });

                const result = await response.json();

                if (result.success) {
                    alert('Role status toggled!');
                    await this.loadData();
                    await this.loadStatistics();
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error toggling role:', error);
                alert('Error toggling role status. Please try again.');
            } finally {
                this.hideLoading();
            }
        },

        async viewUserDetails(employeeId) {
            try {
                const response = await fetch(`${this.baseUrl}api/user-roles/employee/${employeeId}`);
                const data = await response.json();

                if (!data.success) {
                    alert('Failed to load user details');
                    return;
                }

                const roles = data.data || [];
                const activeRoles = roles.filter(r => r.is_active);
                const inactiveRoles = roles.filter(r => !r.is_active);

                // Update modal title
                document.getElementById('viewUserDetailsEmployeeId').textContent = employeeId;

                // Update modal content
                const container = document.getElementById('userDetailsContent');
                container.innerHTML = `
                    <h4 style="margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.625rem;">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Active Roles (${activeRoles.length})
                    </h4>
                    ${activeRoles.length > 0 ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 2rem;">
                            ${activeRoles.map(role => `
                                <div class="role-badge active" style="flex-direction: column; align-items: flex-start; padding: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-shield-alt"></i>
                                        <strong>${role.role_display_name}</strong>
                                    </div>
                                    <small style="color: #6b7280;">${role.role_description || 'No description'}</small>
                                    <small style="color: #9ca3af; margin-top: 0.5rem;">
                                        <i class="fas fa-clock"></i> ${new Date(role.assigned_at).toLocaleDateString()}
                                    </small>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p style="color: var(--gray); margin-bottom: 2rem;">No active roles</p>'}
                    
                    ${inactiveRoles.length > 0 ? `
                        <h4 style="margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.625rem;">
                            <i class="fas fa-ban" style="color: var(--gray);"></i>
                            Inactive Roles (${inactiveRoles.length})
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                            ${inactiveRoles.map(role => `
                                <div class="role-badge inactive" style="flex-direction: column; align-items: flex-start; padding: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-shield-alt"></i>
                                        <strong>${role.role_display_name}</strong>
                                    </div>
                                    <small style="color: #9ca3af;">Removed/Deactivated</small>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                    
                    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--light-gray); display: flex; gap: 0.75rem;">
                        <button onclick="UserPermissionsManager.openEditModal('${employeeId}'); UserPermissionsManager.closeUserDetailsModal();" 
                                class="btn-action-primary" style="flex: 1; padding: 1rem;">
                            <i class="fas fa-edit"></i> Manage Roles
                        </button>
                    </div>
                `;

                // Show modal
                document.getElementById('userPermViewUserDetailsModal').classList.add('active');

            } catch (error) {
                console.error('Error viewing user details:', error);
                alert('Error loading user details');
            }
        },

        closeUserDetailsModal() {
            document.getElementById('userPermViewUserDetailsModal').classList.remove('active');
        },

        /**
         *  SHOW LOADING
         */
        showLoading() {
            // Remove existing overlay if any
            this.hideLoading();

            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.id = 'userPermLoadingOverlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <div class="loading-text">Processing...</div>
                </div>
            `;
            document.getElementById('tab-user-role').appendChild(overlay);
        },

        /**
         *  HIDE LOADING
         */
        hideLoading() {
            const overlay = document.getElementById('userPermLoadingOverlay');
            if (overlay) overlay.remove();
        },

        /**
         *  SHOW ERROR
         */
        showError(message) {
            document.getElementById('userPermissionsContent').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    <div class="empty-state-title">Error</div>
                    <div class="empty-state-text">${message}</div>
                </div>
            `;
        }
    };

    /**
     *  TAB INITIALIZATION FUNCTION
     */
    function initUser_roleTab() {
        UserPermissionsManager.init();
    }

    function cleanupUser_roleTab() {
        console.log('🧹 [Tab] Cleaning up User Role tab');

        // Close ALL modals with proper selectors
        document.querySelectorAll('#tab-user-role .modal-overlay').forEach(modal => {
            modal.classList.remove('active');
        });

        // Also close any dynamically created modals
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            if (modal.id.startsWith('userPerm') || modal.className.includes('role-users-modal')) {
                modal.remove();
            }
        });

        // Remove loading overlay
        UserPermissionsManager.hideLoading();
    }
</script>