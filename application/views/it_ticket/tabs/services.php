<div id="tab-services" class="tab-content active">
    <!-- Smart UX: Setup Progress Indicator -->
    <div class="setup-progress-container" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #002fff 0%, #18032dff 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-route"></i> Setup Progress
            <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Follow the order below</span>
        </h4>
        <div class="progress-steps" style="display: flex; gap: 15px;">
            <div id="step-service-groups" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; transition: all 0.3s ease;">
                <div class="step-icon" style="margin-bottom: 8px;">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Service Groups</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Start here</div>
            </div>

            <div id="step-ticket-types" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="margin-bottom: 8px;">
                    <i class="fas fa-ticket"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Ticket Types</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Locked</div>
            </div>

            <div id="step-issue-types" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="margin-bottom: 8px;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Issue Types</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Locked</div>
            </div>
        </div>
    </div>

    <!-- Service Groups Section -->
    <div class="card">
        <div class="card-header">
            <h3></i> Service Groups</h3>
            <button class="btn btn-primary-it-ticket" onclick="ServiceManager.openAddServiceGroup()">
                <i class="fas fa-plus"></i> Add Service Group
            </button>
        </div>

        <!-- Search & Filter for Service Groups -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <input type="text" id="searchServiceGroup" placeholder="Search service groups..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="ServiceManager.searchServiceGroups(this.value)">
            </div>
            <div>
                <select id="filterServiceGroupStatus" onchange="ServiceManager.filterServiceGroups()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div style="width: 100%; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="serviceGroupsTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                            <p style="margin-top: 10px; color: #6b7280;">Loading service groups...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination for Service Groups -->
        <div id="serviceGroupsPagination" class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; /*padding: 15px; background: #f9fafb;*/ border-radius: 8px;">
            <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
                Showing <strong id="sgShowingStart">0</strong> to <strong id="sgShowingEnd">0</strong> of <strong id="sgTotal">0</strong> entries
            </div>
            <div class="pagination-buttons" id="sgPaginationButtons">
                <!-- Pagination buttons will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Ticket Types Section -->
    <div class="card">
        <div class="card-header">
            <h3></i> Ticket Types</h3>
            <div>
                <button id="btnAddTicketType" class="btn btn-primary-it-ticket" disabled
                    onclick="TicketTypeManager.openAddTicketType()"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plus"></i> Add Ticket Type
                </button>
                <small id="hintTicketType" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Create at least 1 Service Group first
                </small>
            </div>
        </div>

        <!-- Search & Filter for Ticket Types -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <input type="text" id="searchTicketType" placeholder="Search ticket types..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="TicketTypeManager.searchTicketTypes(this.value)">
            </div>
            <div>
                <select id="filterTicketTypeServiceGroup" onchange="TicketTypeManager.filterTicketTypes()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 200px;">
                    <option value="">All Service Groups</option>
                </select>
            </div>
            <div>
                <select id="filterTicketTypeStatus" onchange="TicketTypeManager.filterTicketTypes()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div style="width: 100%; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Service Group</th>
                        <th>Ticket Type Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="ticketTypesTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                            <p style="margin-top: 10px; color: #6b7280;">Loading ticket types...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination for Ticket Types -->
        <div id="ticketTypesPagination" class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; /*padding: 15px; background: #f9fafb;*/ border-radius: 8px;">
            <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
                Showing <strong id="ttShowingStart">0</strong> to <strong id="ttShowingEnd">0</strong> of <strong id="ttTotal">0</strong> entries
            </div>
            <div class="pagination-buttons" id="ttPaginationButtons">
                <!-- Pagination buttons will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Issue Types Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list-tree"></i> Issue Types</h3>
            <div>
                <button id="btnAddIssueType" class="btn btn-primary-it-ticket" disabled
                    onclick="IssueTypeManager.openAddIssueType()"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plus"></i> Add Issue Type
                </button>
                <small id="hintIssueType" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Create at least 1 Ticket Type first
                </small>
            </div>
        </div>

        <!-- Search & Filter for Issue Types -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <input type="text" id="searchIssueType" placeholder="Search issue types..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="IssueTypeManager.searchIssueTypes(this.value)">
            </div>
            <div>
                <select id="filterIssueTypeTicketType" onchange="IssueTypeManager.filterIssueTypes()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 200px;">
                    <option value="">All Ticket Types</option>
                </select>
            </div>
        </div>

        <div id="issueTypesTree">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                <p style="margin-top: 10px; color: #6b7280;">Loading issue types...</p>
            </div>
        </div>

        <!-- Pagination for Issue Types -->
        <div id="issueTypesPagination" class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; /*padding: 15px; background: #f9fafb;*/ border-radius: 8px;">
            <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
                Showing <strong id="itShowingStart">0</strong> to <strong id="itShowingEnd">0</strong> of <strong id="itTotal">0</strong> entries
            </div>
            <div class="pagination-buttons" id="itPaginationButtons">
                <!-- Pagination buttons will be inserted here -->
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- MODAL: Add/Edit Service Group -->
<div id="modalServiceGroup" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalServiceGroupTitle">Add Service Group</h3>
            <button class="modal-close" onclick="ServiceManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="sgId">
            <div class="form-group">
                <label>Service Group Name <span class="required">*</span></label>
                <input type="text" id="sgName" placeholder="Enter service group name">
            </div>
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" id="sgCode" placeholder="e.g., IT_SUPPORT">
            </div>
            <div class="form-group">
                <label>Icon</label>
                <input type="text" id="sgIcon" placeholder="e.g., 💻" maxlength="2">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="sgDesc" placeholder="Enter description"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="sgStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="ServiceManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="ServiceManager.saveServiceGroup()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Add/Edit Ticket Type -->
<div id="modalTicketType" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTicketTypeTitle">Add Ticket Type</h3>
            <button class="modal-close" onclick="TicketTypeManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ttId">
            <div class="form-group">
                <label>Service Group <span class="required">*</span></label>
                <select id="ttServiceGroup">
                    <option value="">Select Service Group</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ticket Type Name <span class="required">*</span></label>
                <input type="text" id="ttName" placeholder="Enter ticket type name">
            </div>
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" id="ttCode" placeholder="e.g., IT_NETWORK">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="ttDesc" placeholder="Enter description"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="ttStatus">
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="TicketTypeManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="TicketTypeManager.saveTicketType()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Add/Edit Issue Type -->
<div id="modalIssueType" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalIssueTypeTitle">Add Issue Type</h3>
            <button class="modal-close" onclick="IssueTypeManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="itId">
            <input type="hidden" id="itParentId">
            <div class="form-group">
                <label>Ticket Type <span class="required">*</span></label>
                <select id="itTicketType">
                    <option value="">Select Ticket Type</option>
                </select>
            </div>
            <div class="form-group">
                <label>Issue Name <span class="required">*</span></label>
                <input type="text" id="itName" placeholder="Enter issue name">
            </div>
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" id="itCode" placeholder="e.g., VPN_ACCESS">
            </div>
            <div class="form-group">
                <label>Input Type</label>
                <select id="itInputType">
                    <option value="textarea">Tree</option>
                    <option value="radio">Radio</option>
                    <option value="dropdown">Dropdown</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="itDesc" placeholder="Enter description"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="IssueTypeManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="IssueTypeManager.saveIssueType()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<style>
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

    /* Modern Three-Dots Loading */
    .tab-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.98);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.2s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .loading-spinner {
        text-align: center;
    }

    .dots {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 20px;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: bounce 1.4s ease-in-out infinite;
    }

    .dot:nth-child(1) {
        animation-delay: 0s;
    }

    .dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes bounce {

        0%,
        80%,
        100% {
            transform: scale(0.8);
            opacity: 0.5;
        }

        40% {
            transform: scale(1.2);
            opacity: 1;
        }
    }

    .loading-spinner p {
        color: #667eea;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }
</style>

<script>
    // ==================== SERVICE GROUPS MANAGER ====================
    const ServiceManager = {
        baseUrl: '<?= base_url() ?>',
        currentPage: 1,
        perPage: 2,
        totalItems: 0,
        searchQuery: '',
        filterStatus: '',

        init() {
            // Init validator
            this.validator = new FormValidationManager(ValidationRulesServiceGroup?.serviceGroup);
            this.validator.setupFormValidation([
                { fieldId: 'sgName', fieldName: 'name' },
                { fieldId: 'sgCode', fieldName: 'code', autoUppercase: true },
                { fieldId: 'sgIcon', fieldName: 'icon' },
                { fieldId: 'sgDesc', fieldName: 'description' }
            ]);
            this.loadServiceGroups();
        },

        async loadServiceGroups() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.searchQuery,
                    status: this.filterStatus
                });

                const response = await fetch(`${this.baseUrl}service-groups?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.totalItems = data.total;
                    this.renderServiceGroups(data.data);
                    this.renderPagination();
                }
            } catch (error) {
                console.error('Error loading service groups:', error);
                this.showError('serviceGroupsTableBody');
            }
        },

        renderServiceGroups(serviceGroups) {
            const tbody = document.getElementById('serviceGroupsTableBody');

            if (serviceGroups.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                            <p style="margin-top: 10px;">No service groups found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            const startIndex = (this.currentPage - 1) * this.perPage;
            tbody.innerHTML = serviceGroups.map((sg, index) => `
                <tr>
                    <td>${startIndex + index + 1}</td>
                    <td><span">${sg.icon || '📁'}</span></td>
                    <td>${sg.name}</td>
                    <td><span class="badge badge-info">${sg.code}</span></td>
                    <td>${sg.description || '-'}</td>
                    <td><span class="badge badge-${sg.status === 'active' ? 'success' : 'danger'}">${sg.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary-it-ticket" onclick="ServiceManager.editServiceGroup(${sg.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger-it-ticket" onclick="ServiceManager.deleteServiceGroup(${sg.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        },

        renderPagination() {
            const totalPages = Math.ceil(this.totalItems / this.perPage);
            const start = (this.currentPage - 1) * this.perPage + 1;
            const end = Math.min(this.currentPage * this.perPage, this.totalItems);

            document.getElementById('sgShowingStart').textContent = start;
            document.getElementById('sgShowingEnd').textContent = end;
            document.getElementById('sgTotal').textContent = this.totalItems;

            const buttonsContainer = document.getElementById('sgPaginationButtons');
            let buttons = '';

            // Previous button
            buttons += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} 
                            onclick="ServiceManager.goToPage(${this.currentPage - 1})">
                            <i class="fas fa-chevron-left"></i>
                        </button>`;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                    buttons += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                    onclick="ServiceManager.goToPage(${i})">${i}</button>`;
                } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                    buttons += `<span style="padding: 8px;">...</span>`;
                }
            }

            // Next button
            buttons += `<button class="pagination-btn" ${this.currentPage === totalPages ? 'disabled' : ''} 
                            onclick="ServiceManager.goToPage(${this.currentPage + 1})">
                            <i class="fas fa-chevron-right"></i>
                        </button>`;

            buttonsContainer.innerHTML = buttons;
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadServiceGroups();
        },

        searchServiceGroups(query) {
            this.searchQuery = query;
            this.currentPage = 1;
            this.loadServiceGroups();
        },

        filterServiceGroups() {
            this.filterStatus = document.getElementById('filterServiceGroupStatus').value;
            this.currentPage = 1;
            this.loadServiceGroups();
        },

        openAddServiceGroup() {
            document.getElementById('modalServiceGroupTitle').textContent = 'Add Service Group';
            document.getElementById('sgId').value = '';
            document.getElementById('sgName').value = '';
            document.getElementById('sgCode').value = '';
            document.getElementById('sgIcon').value = '';
            document.getElementById('sgDesc').value = '';
            document.getElementById('sgStatus').value = 'active';

            this.validator.reset(['sgName', 'sgCode', 'sgIcon', 'sgDesc']);
            document.getElementById('modalServiceGroup').classList.add('active');
        },

        async editServiceGroup(id) {
            try {
                const response = await fetch(`${this.baseUrl}service-groups/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const sg = data.data;
                    document.getElementById('modalServiceGroupTitle').textContent = 'Edit Service Group';
                    document.getElementById('sgId').value       = sg.id;
                    document.getElementById('sgName').value     = sg.name;
                    document.getElementById('sgCode').value     = sg.code;
                    document.getElementById('sgIcon').value     = sg.icon          || '';
                    document.getElementById('sgDesc').value     = sg.description   || '';
                    document.getElementById('sgStatus').value = sg.status;
                    document.getElementById('modalServiceGroup').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading service group');
            }
        },

        async saveServiceGroup() {
            const id = document.getElementById('sgId').value;

            const formData = {
                name: document.getElementById('sgName').value,
                code: document.getElementById('sgCode').value,
                icon: document.getElementById('sgIcon').value,
                description: document.getElementById('sgDesc').value,
                status: document.getElementById('sgStatus').value
            };

            const fieldMap = {
                name: 'sgName',
                code: 'sgCode',
                icon: 'sgIcon',
                description: 'sgDesc'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}service-groups/update/${id}` : `${this.baseUrl}service-groups/store`;
                const method = 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Service Group updated successfully' : 'Service Group created successfully');
                    this.closeModal();
                    await this.loadServiceGroups();
                    await SmartUXManager.checkPrerequisites();
                    await TicketTypeManager.loadServiceGroupsDropdown();
                } else {
                    debugger;
                    TicketNotifier.showError(data.message || 'Error saving service group');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving service group');
            }
        },

        async deleteServiceGroup(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this service group?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}service-groups/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Service Group deleted successfully', async () => {
                        await this.loadServiceGroups();
                        await SmartUXManager.checkPrerequisites();
                        await TicketTypeManager.loadServiceGroupsDropdown();
                    });
                } else {
                    TicketNotifier.showError(data.message || 'Error deleting service group')
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting service group');
            }
        },

        closeModal() {
            this.validator.reset(['sgName', 'sgCode', 'sgIcon', 'sgDesc']);
            document.getElementById('modalServiceGroup').classList.remove('active');
        },

        showError(elementId) {
            document.getElementById(elementId).innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #ef4444;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                        <p style="margin-top: 10px;">Error loading data</p>
                    </td>
                </tr>
            `;
        }
    };

    // ==================== TICKET TYPES MANAGER ====================
    const TicketTypeManager = {
        baseUrl: '<?= base_url() ?>',
        currentPage: 1,
        perPage: 2,
        totalItems: 0,
        searchQuery: '',
        filterServiceGroup: '',
        filterStatus: '',

        init() {
            this.validator = new FormValidationManager(ValidationRulesServiceGroup.ticketType);

            this.validator.setupFormValidation([
                { fieldId: 'ttServiceGroup', fieldName: 'service_group_id' },
                { fieldId: 'ttName', fieldName: 'name' },
                { fieldId: 'ttCode', fieldName: 'code', autoUppercase: true },
                { fieldId: 'ttDesc', fieldName: 'description' }
            ]);

            this.loadTicketTypes();
            this.loadServiceGroupsDropdown();
        },

        async loadTicketTypes() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.searchQuery,
                    service_group_id: this.filterServiceGroup,
                    status: this.filterStatus
                });

                const response = await fetch(`${this.baseUrl}ticket-types?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.totalItems = data.total;
                    this.renderTicketTypes(data.data);
                    this.renderPagination();
                }
            } catch (error) {
                console.error('Error loading ticket types:', error);
                ServiceManager.showError('ticketTypesTableBody');
            }
        },

        async loadServiceGroupsDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}ticket-types/all-group`);
                const data = await response.json();

                if (data.success) {
                    const filterSelect = document.getElementById('filterTicketTypeServiceGroup');
                    const modalSelect = document.getElementById('ttServiceGroup');

                    const options = data.data.map(sg =>
                        `<option value="${sg.id}">${sg.icon || '📁'} ${sg.name}</option>`
                    ).join('');

                    filterSelect.innerHTML = '<option value="">All Service Groups</option>' + options;
                    modalSelect.innerHTML = '<option value="">Select Service Group</option>' + options;
                }
            } catch (error) {
                console.error('Error loading service groups:', error);
            }
        },

        renderTicketTypes(ticketTypes) {
            const tbody = document.getElementById('ticketTypesTableBody');

            if (ticketTypes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                            <p style="margin-top: 10px;">No ticket types found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            const startIndex = (this.currentPage - 1) * this.perPage;
            tbody.innerHTML = ticketTypes.map((tt, index) => `
                <tr>
                    <td>${startIndex + index + 1}</td>
                    <td>${tt.service_group_icon || '📁'} ${tt.service_group_name}</td>
                    <td>${tt.name}</td>
                    <td><span class="badge badge-info">${tt.code}</span></td>
                    <td>${tt.description || '-'}</td>
                    <td><span class="badge badge-${tt.status === 'active' ? 'success' : 'danger'}">${tt.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary-it-ticket" onclick="TicketTypeManager.editTicketType(${tt.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger-it-ticket" onclick="TicketTypeManager.deleteTicketType(${tt.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        },

        renderPagination() {
            const totalPages = Math.ceil(this.totalItems / this.perPage);
            const start = (this.currentPage - 1) * this.perPage + 1;
            const end = Math.min(this.currentPage * this.perPage, this.totalItems);

            document.getElementById('ttShowingStart').textContent = start;
            document.getElementById('ttShowingEnd').textContent = end;
            document.getElementById('ttTotal').textContent = this.totalItems;

            const buttonsContainer = document.getElementById('ttPaginationButtons');
            let buttons = '';

            buttons += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} 
                            onclick="TicketTypeManager.goToPage(${this.currentPage - 1})">
                            <i class="fas fa-chevron-left"></i>
                        </button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                    buttons += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                    onclick="TicketTypeManager.goToPage(${i})">${i}</button>`;
                } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                    buttons += `<span style="padding: 8px;">...</span>`;
                }
            }

            buttons += `<button class="pagination-btn" ${this.currentPage === totalPages ? 'disabled' : ''} 
                            onclick="TicketTypeManager.goToPage(${this.currentPage + 1})">
                            <i class="fas fa-chevron-right"></i>
                        </button>`;

            buttonsContainer.innerHTML = buttons;
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadTicketTypes();
        },

        searchTicketTypes(query) {
            this.searchQuery = query;
            this.currentPage = 1;
            this.loadTicketTypes();
        },

        filterTicketTypes() {
            this.filterServiceGroup = document.getElementById('filterTicketTypeServiceGroup').value;
            this.filterStatus = document.getElementById('filterTicketTypeStatus').value;
            this.currentPage = 1;
            this.loadTicketTypes();
        },

        openAddTicketType() {
            document.getElementById('modalTicketTypeTitle').textContent = 'Add Ticket Type';
            document.getElementById('ttId').value = '';
            document.getElementById('ttServiceGroup').value = '';
            document.getElementById('ttName').value = '';
            document.getElementById('ttCode').value = '';
            document.getElementById('ttDesc').value = '';
            document.getElementById('ttStatus').value = 'active';
            this.validator.reset(['ttServiceGroup', 'ttName', 'ttCode', 'ttDesc']);

            document.getElementById('modalTicketType').classList.add('active');
        },

        async editTicketType(id) {
            try {
                const response = await fetch(`${this.baseUrl}ticket-types/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const tt = data.data;
                    document.getElementById('modalTicketTypeTitle').textContent = 'Edit Ticket Type';
                    document.getElementById('ttId').value              = tt.id;
                    document.getElementById('ttServiceGroup').value    = tt.service_group_id;
                    document.getElementById('ttName').value            = tt.name;
                    document.getElementById('ttCode').value            = tt.code;
                    document.getElementById('ttDesc').value            = tt.description || '';
                    document.getElementById('ttStatus').value          = tt.status;
                    document.getElementById('modalTicketType').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading ticket type');
            }
        },

        async saveTicketType() {
            const id = document.getElementById('ttId').value;
            const formData = {
                service_group_id: document.getElementById('ttServiceGroup').value,
                name: document.getElementById('ttName').value,
                code: document.getElementById('ttCode').value,
                description: document.getElementById('ttDesc').value,
                status: document.getElementById('ttStatus').value
            };

            const fieldMap = { 
                service_group_id: 'ttServiceGroup',
                name: 'ttName', 
                code: 'ttCode', 
                description: 'ttDesc' 
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}ticket-types/update/${id}` : `${this.baseUrl}ticket-types/store`;
                const method = 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Ticket Type updated successfully' : 'Ticket Type created successfully');
                    this.closeModal();
                    await this.loadTicketTypes();
                    await SmartUXManager.checkPrerequisites();
                    await IssueTypeManager.loadTicketTypesDropdown();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving ticket type');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving ticket type');
            }
        },

        async deleteTicketType(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this ticket type?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}ticket-types/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Ticket Type deleted successfully', async () => {
                        await this.loadTicketTypes();
                        await SmartUXManager.checkPrerequisites();
                        await IssueTypeManager.loadTicketTypesDropdown();
                    });
                } else {
                    TicketNotifier.showError(data.message || 'Error deleting ticket type')
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting ticket type');
            }
        },

        closeModal() {
            this.validator.reset(['ttServiceGroup', 'ttName', 'ttCode', 'ttDesc']);
            document.getElementById('modalTicketType').classList.remove('active');
        }
    };

    // ==================== ISSUE TYPES MANAGER ====================
    const IssueTypeManager = {
        baseUrl: '<?= base_url() ?>',
        currentPage: 1,
        perPage: 10,
        totalItems: 0,
        searchQuery: '',
        filterTicketType: '',

        init() {
            this.validator = new FormValidationManager(ValidationRulesServiceGroup.issueType);
        
            this.validator.setupFormValidation([
                { fieldId: 'itTicketType', fieldName: 'ticket_type_id' },
                { fieldId: 'itName', fieldName: 'name' },
                { fieldId: 'itCode', fieldName: 'code', autoUppercase: true },
                { fieldId: 'itDesc', fieldName: 'description' }
            ]);

            this.loadIssueTypes();
            this.loadTicketTypesDropdown();
        },

        async loadIssueTypes() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.searchQuery,
                    ticket_type_id: this.filterTicketType
                });

                const response = await fetch(`${this.baseUrl}issue-types?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.totalItems = data.total;
                    this.renderIssueTypes(data.data);
                    this.renderPagination();
                }
            } catch (error) {
                console.error('Error loading issue types:', error);
                document.getElementById('issueTypesTree').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #ef4444;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                        <p style="margin-top: 10px;">Error loading data</p>
                    </div>
                `;
            }
        },

        async loadTicketTypesDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}issue-types/all-group-type`);
                const data = await response.json();

                if (data.success) {
                    const filterSelect = document.getElementById('filterIssueTypeTicketType');
                    const modalSelect = document.getElementById('itTicketType');

                    const options = data.data.map(tt =>
                        `<option value="${tt.id}">${tt.name}</option>`
                    ).join('');

                    filterSelect.innerHTML = '<option value="">All Ticket Types</option>' + options;
                    modalSelect.innerHTML = '<option value="">Select Ticket Type</option>' + options;
                }
            } catch (error) {
                console.error('Error loading ticket types:', error);
            }
        },

        renderIssueTypes(issueTypes) {
            const container = document.getElementById('issueTypesTree');

            if (issueTypes.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No issue types found</p>
                    </div>
                `;
                return;
            }

            // Group by parent
            const parents = issueTypes.filter(it => !it.parent_id);
            const children = issueTypes.filter(it => it.parent_id);

            container.innerHTML = parents.map(parent => {
                const parentChildren = children.filter(c => c.parent_id === parent.id);

                return `
                <div style="padding: 15px; background: antiquewhite; /* border-left: 4px solid #3b82f6;*/ margin-bottom: 8px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>${parent.name}</strong>
                            <span class="badge badge-info" style="margin-left: 10px;">${parent.code}</span>
                            <span class="badge badge-warning" style="margin-left: 5px;">${parent.input_type}</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary-it-ticket" onclick="IssueTypeManager.editIssueType(${parent.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-success-it-ticket" onclick="IssueTypeManager.openAddSubIssue(${parent.id})">
                                <i class="fas fa-plus"></i> Sub-Issue
                            </button>
                            <button class="btn btn-sm btn-danger-it-ticket" onclick="IssueTypeManager.deleteIssueType(${parent.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    ${parentChildren.map(child => `
                        <div class="tree-item level-2">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <i class="fas fa-arrow-turn-down-right" style="color: #9ca3af; margin-right: 10px;"></i>
                                            <strong>${child.name}</strong>
                                            <span class="badge badge-info" style="margin-left: 10px;">${child.code}</span>
                                        </div>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-primary-it-ticket" onclick="IssueTypeManager.editIssueType(${child.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger-it-ticket" onclick="IssueTypeManager.deleteIssueType(${child.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
            `;
            }).join('');
        },

        renderPagination() {
            const totalPages = Math.ceil(this.totalItems / this.perPage);
            const start = (this.currentPage - 1) * this.perPage + 1;
            const end = Math.min(this.currentPage * this.perPage, this.totalItems);

            document.getElementById('itShowingStart').textContent = start;
            document.getElementById('itShowingEnd').textContent = end;
            document.getElementById('itTotal').textContent = this.totalItems;

            const buttonsContainer = document.getElementById('itPaginationButtons');
            let buttons = '';

            buttons += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} 
                            onclick="IssueTypeManager.goToPage(${this.currentPage - 1})">
                            <i class="fas fa-chevron-left"></i>
                        </button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                    buttons += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                    onclick="IssueTypeManager.goToPage(${i})">${i}</button>`;
                } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                    buttons += `<span style="padding: 8px;">...</span>`;
                }
            }

            buttons += `<button class="pagination-btn" ${this.currentPage === totalPages ? 'disabled' : ''} 
                            onclick="IssueTypeManager.goToPage(${this.currentPage + 1})">
                            <i class="fas fa-chevron-right"></i>
                        </button>`;

            buttonsContainer.innerHTML = buttons;
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadIssueTypes();
        },

        searchIssueTypes(query) {
            this.searchQuery = query;
            this.currentPage = 1;
            this.loadIssueTypes();
        },

        filterIssueTypes() {
            this.filterTicketType = document.getElementById('filterIssueTypeTicketType').value;
            this.currentPage = 1;
            this.loadIssueTypes();
        },

        openAddIssueType() {
            document.getElementById('modalIssueTypeTitle').textContent = 'Add Issue Type';
            document.getElementById('itId').value = '';
            document.getElementById('itParentId').value = '';
            document.getElementById('itTicketType').value = '';
            document.getElementById('itName').value = '';
            document.getElementById('itCode').value = '';
            document.getElementById('itInputType').value = 'text';
            document.getElementById('itDesc').value = '';

            this.validator.reset(['itTicketType', 'itName', 'itCode', 'itDesc']);

            document.getElementById('modalIssueType').classList.add('active');
        },

        openAddSubIssue(parentId) {
            document.getElementById('modalIssueTypeTitle').textContent = 'Add Sub-Issue';
            document.getElementById('itId').value = '';
            document.getElementById('itParentId').value = parentId;
            document.getElementById('itTicketType').value = '';
            document.getElementById('itName').value = '';
            document.getElementById('itCode').value = '';
            document.getElementById('itInputType').value = 'text';
            document.getElementById('itDesc').value = '';
            document.getElementById('modalIssueType').classList.add('active');
        },

        async editIssueType(id) {
            try {
                const response = await fetch(`${this.baseUrl}issue-types/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const it = data.data;
                    document.getElementById('modalIssueTypeTitle').textContent = 'Edit Issue Type';
                    document.getElementById('itId').value = it.id;
                    document.getElementById('itParentId').value = it.parent_id || '';
                    document.getElementById('itTicketType').value = it.ticket_type_id;
                    document.getElementById('itName').value = it.name;
                    document.getElementById('itCode').value = it.code;
                    document.getElementById('itInputType').value = it.input_type;
                    document.getElementById('itDesc').value = it.description || '';
                    document.getElementById('modalIssueType').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading issue type');
            }
        },

        async saveIssueType() {
            const id = document.getElementById('itId').value;

            const formData = {
                ticket_type_id: document.getElementById('itTicketType').value,
                parent_id: document.getElementById('itParentId').value || null,
                name: document.getElementById('itName').value,
                code: document.getElementById('itCode').value,
                input_type: document.getElementById('itInputType').value,
                description: document.getElementById('itDesc').value
            };

            const fieldMap = { 
                ticket_type_id: 'itTicketType',
                name: 'itName', 
                code: 'itCode', 
                description: 'itDesc' 
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}issue-types/update/${id}` : `${this.baseUrl}issue-types/store`;
                const method = 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Issue Type updated successfully' : 'Issue Type created successfully');
                    this.closeModal();
                    await this.loadIssueTypes();
                    await SmartUXManager.checkPrerequisites();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving issue type');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving issue type');
            }
        },

        async deleteIssueType(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this issue type?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}issue-types/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Issue Type deleted successfully', async () => {
                        await this.loadIssueTypes();
                        await SmartUXManager.checkPrerequisites();
                    });
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting issue type');
            }
        },

        async loadTicketTypesDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}ticket-types`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(tt =>
                        `<option value="${tt.id}">${tt.name}</option>`
                    ).join('');

                    // Populate modal dropdown
                    document.getElementById('itTicketType').innerHTML =
                        '<option value="">Select Ticket Type</option>' + options;

                    // Populate filter dropdown
                    document.getElementById('filterIssueTypeTicketType').innerHTML =
                        '<option value="">All Ticket Types</option>' + options;
                }
            } catch (error) {
                console.error('Error loading ticket types dropdown:', error);
            }
        },

        closeModal() {
            this.validator.reset(['itTicketType', 'itName', 'itCode', 'itDesc']);
            document.getElementById('modalIssueType').classList.remove('active');
        }
    };

    // ==================== SMART UX MANAGER ====================
    const SmartUXManager = {
        baseUrl: '<?= base_url("") ?>',

        async checkPrerequisites() {
            try {
                // Check Service Groups
                const sgResponse = await fetch(`${this.baseUrl}service-groups`);
                const sgData = await sgResponse.json();
                const sgCount = sgData.data ? sgData.data.length : 0;

                // Check Ticket Types
                const ttResponse = await fetch(`${this.baseUrl}ticket-types`);
                const ttData = await ttResponse.json();
                const ttCount = ttData.data ? ttData.data.length : 0;

                // Check Issue Types
                const itResponse = await fetch(`${this.baseUrl}issue-types`);
                const itData = await itResponse.json();
                const itCount = itData.data ? itData.data.length : 0;

                // Update progress indicator
                this.updateProgress(sgCount, ttCount, itCount);

                // Update button states
                this.updateButtonStates(sgCount, ttCount);

                console.log(`Smart UX: SG=${sgCount}, TT=${ttCount}, IT=${itCount}`);
            } catch (error) {
                console.error('Smart UX Error:', error);
            }
        },

        updateProgress(sgCount, ttCount, itCount) {
            // Service Groups step
            const sgStep = document.getElementById('step-service-groups');
            if (sgStep) {
                sgStep.querySelector('.step-count').textContent = sgCount;
                if (sgCount > 0) {
                    UITicketStatus.updateStepStatus(sgStep, 'completed');
                    sgStep.style.opacity = '1';
                    sgStep.style.background = '#003961f0';
                }
            }

            // Ticket Types step
            const ttStep = document.getElementById('step-ticket-types');
            if (ttStep) {
                ttStep.querySelector('.step-count').textContent = ttCount;
                if (sgCount > 0) {
                    ttStep.style.opacity = '1';
                    if (ttCount > 0) {
                        UITicketStatus.updateStepStatus(ttStep, 'completed');
                        ttStep.style.background = 'rgba(0, 26, 8, 0.92)';
                    } else {
                        ttStep.querySelector('.step-status').textContent = '→ Create now';
                        ttStep.style.background = 'rgba(59, 130, 246, 0.3)';
                    }
                }
            }

            // Issue Types step
            const itStep = document.getElementById('step-issue-types');
            if (itStep) {
                itStep.querySelector('.step-count').textContent = itCount;
                if (ttCount > 0) {
                    itStep.style.opacity = '1';
                    if (itCount > 0) {
                        UITicketStatus.updateStepStatus(itStep, 'completed');
                        itStep.style.background = 'rgba(232, 239, 23, 0.3)';
                    } else {
                        itStep.querySelector('.step-status').textContent = '→ Create now';
                        itStep.style.background = 'rgba(59, 130, 246, 0.3)';
                    }
                }
            }
        },

        updateButtonStates(sgCount, ttCount) {
            // Ticket Type button
            const btnAddTicketType = document.getElementById('btnAddTicketType');
            const hintTicketType = document.getElementById('hintTicketType');

            if (btnAddTicketType && hintTicketType) {
                if (sgCount > 0) {
                    btnAddTicketType.disabled = false;
                    btnAddTicketType.style.opacity = '1';
                    btnAddTicketType.style.cursor = 'pointer';
                    hintTicketType.style.display = 'none';
                } else {
                    btnAddTicketType.disabled = true;
                    btnAddTicketType.style.opacity = '0.5';
                    btnAddTicketType.style.cursor = 'not-allowed';
                    hintTicketType.style.display = 'block';
                }
            }

            // Issue Type button
            const btnAddIssueType = document.getElementById('btnAddIssueType');
            const hintIssueType = document.getElementById('hintIssueType');

            if (btnAddIssueType && hintIssueType) {
                if (ttCount > 0) {
                    btnAddIssueType.disabled = false;
                    btnAddIssueType.style.opacity = '1';
                    btnAddIssueType.style.cursor = 'pointer';
                    hintIssueType.style.display = 'none';
                } else {
                    btnAddIssueType.disabled = true;
                    btnAddIssueType.style.opacity = '0.5';
                    btnAddIssueType.style.cursor = 'not-allowed';
                    hintIssueType.style.display = 'block';
                }
            }
        }
    };

    // Refresh Smart UX after any CRUD operation
    function refreshSmartUX() {
        SmartUXManager.checkPrerequisites();
    }

    // ==================== TAB INITIALIZATION ====================
    async function initServicesTab() {
        console.log('Initializing Services Tab...');

        // Show global loading overlay
        showTabLoading('tab-services');

        try {
            // Wait for ALL data to load in parallel
            await Promise.all([
                SmartUXManager.checkPrerequisites(),
                ServiceManager.init(),
                TicketTypeManager.init(),
                IssueTypeManager.init()
            ]);

            console.log('Services Tab fully loaded');
        } catch (error) {
            console.error('Error initializing Services Tab:', error);
        } finally {
            // Hide loading overlay after everything is done
            hideTabLoading('tab-services');
        }
    }

    function cleanupServicesTab() {
        console.log('Cleaning up Services Tab...');
        // Close any open modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }

    // ==================== GLOBAL LOADING OVERLAY ====================
    function showTabLoading(tabId) {
        const tab = document.getElementById(tabId);
        if (!tab) return;

        let overlay = tab.querySelector('.tab-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'tab-loading-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                    <p>Loading...</p>
                </div>
            `;
            tab.style.position = 'relative';
            tab.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideTabLoading(tabId) {
        const tab = document.getElementById(tabId);
        if (!tab) return;

        const overlay = tab.querySelector('.tab-loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
</script>