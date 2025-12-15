<div id="tab-workflows" class="tab-content active">
    
    <style>
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
        from { opacity: 0; }
        to { opacity: 1; }
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
        0%, 80%, 100% {
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
    
    <!-- Smart UX: Setup Progress Indicator -->
    <div class="setup-progress-container" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-route"></i> Workflow Setup Progress
            <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Follow the order below</span>
        </h4>
        <div class="progress-steps" style="display: flex; gap: 15px;">
            <div id="wf-step-workflows" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">1. Workflows</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Start here</div>
            </div>
            
            <div id="wf-step-states" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-circle-nodes"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">2. States</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 Locked</div>
            </div>
            
            <div id="wf-step-transitions" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-arrows-alt"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">3. Transitions</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 Locked</div>
            </div>
        </div>
    </div>
    
    <!-- Workflows Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-project-diagram"></i> Workflows</h3>
            <button class="btn btn-primary" onclick="WorkflowManager.openAddWorkflow()">
                <i class="fas fa-plus"></i> Add Workflow
            </button>
        </div>

        <!-- Search & Filter -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <input type="text" id="searchWorkflow" placeholder="Search workflows..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="WorkflowManager.searchWorkflows(this.value)">
            </div>
            <div>
                <select id="filterWorkflowStatus" onchange="WorkflowManager.filterWorkflows()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Workflow Name</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>States Count</th>
                    <th>Status</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody id="workflowsTableBody">
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                        <p style="margin-top: 10px; color: #6b7280;">Loading workflows...</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div id="workflowsPagination" class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
            <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
                Showing <strong id="wfShowingStart">0</strong> to <strong id="wfShowingEnd">0</strong> of <strong id="wfTotal">0</strong> entries
            </div>
            <div class="pagination-buttons" id="wfPaginationButtons"></div>
        </div>
    </div>

    <!-- Workflow States Section (Shows when workflow selected) -->
    <div class="card" id="statesSection" style="display: none;">
        <div class="card-header">
            <h3>
                <i class="fas fa-list-check"></i> 
                <span id="statesWorkflowTitle">Workflow States</span>
            </h3>
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <button class="btn btn-secondary btn-sm" onclick="WorkflowManager.closeStatesSection()">
                    <i class="fas fa-arrow-left"></i> Back to Workflows
                </button>
                <div>
                    <button id="btnAddState" class="btn btn-primary" disabled
                            onclick="StateManager.openAddState()"
                            style="opacity: 0.5; cursor: not-allowed;">
                        <i class="fas fa-plus"></i> Add State
                    </button>
                    <small id="hintState" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> Create Workflows first
                    </small>
                </div>
            </div>
        </div>

        <!-- Visual Workflow Designer -->
        <div style="padding: 20px; background: #f9fafb; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px; color: #374151;">
                <i class="fas fa-diagram-project"></i> Workflow Flow
            </h4>
            <div id="workflowVisualizer" style="display: flex; gap: 15px; overflow-x: auto; padding: 20px; background: white; border-radius: 8px; min-height: 150px;">
                <!-- Visual workflow will be rendered here -->
            </div>
        </div>

        <!-- States Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>State Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>SLA Hours</th>
                    <th>Color</th>
                    <th>Sort Order</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody id="statesTableBody">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #6b7280;">
                        Select a workflow to view states
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Transitions Section (Shows when state management opened) -->
    <div class="card" id="transitionsSection" style="display: none;">
        <div class="card-header">
            <h3><i class="fas fa-arrows-turn-right"></i> State Transitions</h3>
            <button class="btn btn-primary" onclick="TransitionManager.openAddTransition()">
                <i class="fas fa-plus"></i> Add Transition
            </button>
        </div>

        <!-- Transitions Flow Chart -->
        <div style="padding: 20px; background: #f9fafb; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px; color: #374151;">
                <i class="fas fa-route"></i> Transition Flow
            </h4>
            <div id="transitionsVisualizer" style="padding: 20px; background: white; border-radius: 8px; min-height: 200px;">
                <!-- Transitions flow will be rendered here -->
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Transition Name</th>
                    <th>From State</th>
                    <th style="width: 50px;"><i class="fas fa-arrow-right"></i></th>
                    <th>To State</th>
                    <th>Required Role</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="transitionsTableBody">
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #6b7280;">
                        No transitions configured
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- MODAL: Add/Edit Workflow -->
<div id="modalWorkflow" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalWorkflowTitle">Add Workflow</h3>
            <button class="modal-close" onclick="WorkflowManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="wfId">
            <div class="form-group">
                <label>Workflow Name <span class="required">*</span></label>
                <input type="text" id="wfName" placeholder="Enter workflow name">
            </div>
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" id="wfCode" placeholder="e.g., STANDARD_WORKFLOW">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="wfDesc" placeholder="Enter description"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="wfStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="WorkflowManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="WorkflowManager.saveWorkflow()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Add/Edit State -->
<div id="modalState" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalStateTitle">Add State</h3>
            <button class="modal-close" onclick="StateManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="stateId">
            <input type="hidden" id="stateWorkflowId">
            
            <div class="form-group">
                <label>State Name <span class="required">*</span></label>
                <input type="text" id="stateName" placeholder="e.g., New, In Progress, Resolved">
            </div>
            
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" id="stateCode" placeholder="e.g., new, in_progress, resolved">
            </div>
            
            <div class="form-group">
                <label>State Type <span class="required">*</span></label>
                <select id="stateType">
                    <option value="initial">Initial (Starting state)</option>
                    <option value="intermediate">Intermediate (Working state)</option>
                    <option value="final">Final (Completed)</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Color <span class="required">*</span></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" id="stateColor" value="#3b82f6" 
                        style="width: 60px; height: 40px; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer;">
                    <input type="text" id="stateColorHex" value="#3b82f6" placeholder="#3b82f6"
                        style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                        oninput="document.getElementById('stateColor').value = this.value">
                </div>
            </div>
            
            <div class="form-group">
                <label>SLA Hours</label>
                <input type="number" id="stateSlaHours" placeholder="e.g., 24" min="0">
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    Leave empty if no SLA for this state
                </small>
            </div>
            
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" id="stateSortOrder" value="0" min="0">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea id="stateDesc" placeholder="Enter description"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="StateManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="StateManager.saveState()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Add/Edit Transition -->
<div id="modalTransition" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTransitionTitle">Add Transition</h3>
            <button class="modal-close" onclick="TransitionManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="transId">
            <input type="hidden" id="transWorkflowId">
            
            <div class="form-group">
                <label>Transition Name <span class="required">*</span></label>
                <input type="text" id="transName" placeholder="e.g., Start Working, Resolve, Close">
            </div>
            
            <div class="form-group">
                <label>From State</label>
                <select id="transFromState">
                    <option value="">Any State (Initial transition)</option>
                </select>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    Leave empty for initial state creation
                </small>
            </div>
            
            <div class="form-group">
                <label>To State <span class="required">*</span></label>
                <select id="transToState">
                    <option value="">Select state</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Required Role</label>
                <select id="transRequiredRole">
                    <option value="">Any Role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="tech_l1">L1 Support</option>
                    <option value="tech_l2">L2 Support</option>
                    <option value="tech_l3">L3 Support</option>
                    <option value="user">End User</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Conditions (JSON)</label>
                <textarea id="transConditions" placeholder='{"priority": "high", "requires_approval": true}'
                    style="font-family: monospace; font-size: 13px;"></textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    Optional: JSON rules for this transition
                </small>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea id="transDesc" placeholder="Enter description"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="TransitionManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="TransitionManager.saveTransition()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<style>
/* Workflow Visual Styles */
.workflow-state-box {
    min-width: 150px;
    padding: 15px 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: all 0.3s;
}

.workflow-state-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.workflow-state-box .state-name {
    font-weight: 600;
    font-size: 14px;
    color: white;
    margin-bottom: 5px;
}

.workflow-state-box .state-code {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    font-family: monospace;
}

.workflow-state-box .state-type {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    margin-top: 5px;
}

.workflow-arrow {
    display: flex;
    align-items: center;
    color: #9ca3af;
    font-size: 24px;
}

/* Transition Visual Styles */
.transition-row {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.transition-row:hover {
    background: #f3f4f6;
}

.transition-state {
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    color: white;
}

.transition-arrow {
    margin: 0 15px;
    color: #6b7280;
    font-size: 20px;
}

.transition-label {
    flex: 1;
    padding: 10px 15px;
    background: white;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

.transition-label .name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 3px;
}

.transition-label .role {
    font-size: 12px;
    color: #6b7280;
}

/* Color Input */
#stateColor {
    cursor: pointer;
}

#stateColor::-webkit-color-swatch-wrapper {
    padding: 2px;
}

#stateColor::-webkit-color-swatch {
    border: none;
    border-radius: 6px;
}
</style>

<script>
// ==================== WORKFLOW MANAGER ====================
const WorkflowManager = {
    baseUrl: '<?= base_url() ?>',
    currentPage: 1,
    perPage: 10,
    totalItems: 0,
    searchQuery: '',
    filterStatus: '',
    selectedWorkflowId: null,

    init() {
        this.loadWorkflows();
    },

    async loadWorkflows() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.searchQuery,
                status: this.filterStatus
            });

            const response = await fetch(`${this.baseUrl}workflows?${params}`);
            const data = await response.json();

            if (data.success) {
                this.totalItems = data.total;
                this.renderWorkflows(data.data);
                this.renderPagination();
            }
        } catch (error) {
            console.error('Error loading workflows:', error);
            this.showError('workflowsTableBody', 7);
        }
    },

    renderWorkflows(workflows) {
        const tbody = document.getElementById('workflowsTableBody');

        if (workflows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No workflows found</p>
                    </td>
                </tr>
            `;
            return;
        }

        const startIndex = (this.currentPage - 1) * this.perPage;
        tbody.innerHTML = workflows.map((wf, index) => `
            <tr>
                <td>${startIndex + index + 1}</td>
                <td><strong>${wf.name}</strong></td>
                <td><span class="badge badge-info">${wf.code}</span></td>
                <td>${wf.description || '-'}</td>
                <td>
                    <span class="badge badge-success">${wf.states_count || 0} states</span>
                </td>
                <td><span class="badge badge-${wf.status === 'active' ? 'success' : 'danger'}">${wf.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-success" onclick="WorkflowManager.viewStates(${wf.id}, '${wf.name}')">
                            <i class="fas fa-sitemap"></i> States
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="WorkflowManager.editWorkflow(${wf.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="WorkflowManager.deleteWorkflow(${wf.id})">
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

        document.getElementById('wfShowingStart').textContent = start;
        document.getElementById('wfShowingEnd').textContent = end;
        document.getElementById('wfTotal').textContent = this.totalItems;

        const buttonsContainer = document.getElementById('wfPaginationButtons');
        let buttons = '';

        buttons += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} 
            onclick="WorkflowManager.goToPage(${this.currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                buttons += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                    onclick="WorkflowManager.goToPage(${i})">${i}</button>`;
            } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                buttons += `<span style="padding: 8px;">...</span>`;
            }
        }

        buttons += `<button class="pagination-btn" ${this.currentPage === totalPages ? 'disabled' : ''} 
            onclick="WorkflowManager.goToPage(${this.currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>`;

        buttonsContainer.innerHTML = buttons;
    },

    goToPage(page) {
        this.currentPage = page;
        this.loadWorkflows();
    },

    searchWorkflows(query) {
        this.searchQuery = query;
        this.currentPage = 1;
        this.loadWorkflows();
    },

    filterWorkflows() {
        this.filterStatus = document.getElementById('filterWorkflowStatus').value;
        this.currentPage = 1;
        this.loadWorkflows();
    },

    openAddWorkflow() {
        document.getElementById('modalWorkflowTitle').textContent = 'Add Workflow';
        document.getElementById('wfId').value = '';
        document.getElementById('wfName').value = '';
        document.getElementById('wfCode').value = '';
        document.getElementById('wfDesc').value = '';
        document.getElementById('wfStatus').value = 'active';
        document.getElementById('modalWorkflow').classList.add('active');
    },

    async editWorkflow(id) {
        try {
            const response = await fetch(`${this.baseUrl}workflows/show/${id}`);
            const data = await response.json();

            if (data.success) {
                const wf = data.data;
                document.getElementById('modalWorkflowTitle').textContent = 'Edit Workflow';
                document.getElementById('wfId').value = wf.id;
                document.getElementById('wfName').value = wf.name;
                document.getElementById('wfCode').value = wf.code;
                document.getElementById('wfDesc').value = wf.description || '';
                document.getElementById('wfStatus').value = wf.status;
                document.getElementById('modalWorkflow').classList.add('active');
            }
        } catch (error) {
            ITTicketUI.showError('Error loading workflow');
        }
    },

    async saveWorkflow() {
        const id = document.getElementById('wfId').value;
        const formData = {
            name: document.getElementById('wfName').value,
            code: document.getElementById('wfCode').value,
            description: document.getElementById('wfDesc').value,
            status: document.getElementById('wfStatus').value
        };

        if (!formData.name || !formData.code) {
            ITTicketUI.showValidationError('Please fill in required fields');
            return;
        }

        try {
            const url = id ? `${this.baseUrl}workflows/update/${id}` : `${this.baseUrl}workflows/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess(id ? 'Workflow updated successfully' : 'Workflow created successfully');
                this.closeModal();
                this.loadWorkflows();
            } else {
                ITTicketUI.showError(data.message || 'Error saving workflow');
            }
        } catch (error) {
            ITTicketUI.showError('Error saving workflow');
        }
    },

    async deleteWorkflow(id) {
        const confirmed = await ITTicketUI.confirm('Confirm Action', 'Are you sure you want to delete this workflow?', 'Yes, proceed'); if (!confirmed) return;

        try {
            const response = await fetch(`${this.baseUrl}workflows/delete/${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess('Workflow deleted successfully', () => this.loadWorkflows());
            }
        } catch (error) {
            ITTicketUI.showError('Error deleting workflow');
        }
    },

    viewStates(workflowId, workflowName) {
        this.selectedWorkflowId = workflowId;
        document.getElementById('statesWorkflowTitle').innerHTML = 
            `<i class="fas fa-list-check"></i> States for: <span style="color: #3b82f6;">${workflowName}</span>`;
        document.getElementById('statesSection').style.display = 'block';
        document.getElementById('transitionsSection').style.display = 'block';
        StateManager.loadStates(workflowId);
        TransitionManager.loadTransitions(workflowId);
    },

    closeStatesSection() {
        document.getElementById('statesSection').style.display = 'none';
        document.getElementById('transitionsSection').style.display = 'none';
        this.selectedWorkflowId = null;
    },

    closeModal() {
        document.getElementById('modalWorkflow').classList.remove('active');
    },

    showError(elementId, colspan) {
        document.getElementById(elementId).innerHTML = `
            <tr>
                <td colspan="${colspan}" style="text-align: center; padding: 40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                    <p style="margin-top: 10px;">Error loading data</p>
                </td>
            </tr>
        `;
    }
};

// ==================== STATE MANAGER ====================
const StateManager = {
    baseUrl: '<?= base_url() ?>',
    currentWorkflowId: null,

    async loadStates(workflowId) {
        this.currentWorkflowId = workflowId;
        try {
            const response = await fetch(`${this.baseUrl}workflow-states?workflow_id=${workflowId}`);
            const data = await response.json();

            if (data.success) {
                this.renderStates(data.data);
                this.renderVisualWorkflow(data.data);
            }
        } catch (error) {
            console.error('Error loading states:', error);
        }
    },

    renderStates(states) {
        const tbody = document.getElementById('statesTableBody');

        if (states.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 36px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No states found. Add your first state!</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = states.map((state, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${state.name}</strong></td>
                <td><span class="badge badge-info">${state.code}</span></td>
                <td><span class="badge badge-warning">${state.state_type}</span></td>
                <td>${state.sla_hours ? state.sla_hours + ' hours' : '-'}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 30px; height: 30px; background: ${state.color}; border-radius: 6px; border: 2px solid #e5e7eb;"></div>
                        <code style="font-size: 11px;">${state.color}</code>
                    </div>
                </td>
                <td>${state.sort_order}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="StateManager.editState(${state.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="StateManager.deleteState(${state.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderVisualWorkflow(states) {
        const container = document.getElementById('workflowVisualizer');
        
        if (states.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; width: 100%; padding: 30px; color: #9ca3af;">
                    <i class="fas fa-diagram-project" style="font-size: 36px;"></i>
                    <p style="margin-top: 10px;">Add states to visualize workflow</p>
                </div>
            `;
            return;
        }

        // Sort states by sort_order
        const sortedStates = [...states].sort((a, b) => a.sort_order - b.sort_order);

        container.innerHTML = sortedStates.map((state, index) => `
            <div class="workflow-state-box" style="background: ${state.color};">
                <div class="state-name">${state.name}</div>
                <div class="state-code">${state.code}</div>
                <div class="state-type">${state.state_type}</div>
                ${state.sla_hours ? `<div style="font-size: 10px; color: rgba(255,255,255,0.8); margin-top: 5px;">SLA: ${state.sla_hours}h</div>` : ''}
            </div>
            ${index < sortedStates.length - 1 ? '<div class="workflow-arrow"><i class="fas fa-arrow-right"></i></div>' : ''}
        `).join('');
    },

    openAddState() {
        if (!this.currentWorkflowId) {
            ITTicketUI.showValidationError('Please select a workflow first');
            return;
        }

        document.getElementById('modalStateTitle').textContent = 'Add State';
        document.getElementById('stateId').value = '';
        document.getElementById('stateWorkflowId').value = this.currentWorkflowId;
        document.getElementById('stateName').value = '';
        document.getElementById('stateCode').value = '';
        document.getElementById('stateType').value = 'intermediate';
        document.getElementById('stateColor').value = '#3b82f6';
        document.getElementById('stateColorHex').value = '#3b82f6';
        document.getElementById('stateSlaHours').value = '';
        document.getElementById('stateSortOrder').value = '0';
        document.getElementById('stateDesc').value = '';
        document.getElementById('modalState').classList.add('active');
    },

    async editState(id) {
        try {
            const response = await fetch(`${this.baseUrl}workflow-states/show/${id}`);
            const data = await response.json();

            if (data.success) {
                const state = data.data;
                document.getElementById('modalStateTitle').textContent = 'Edit State';
                document.getElementById('stateId').value = state.id;
                document.getElementById('stateWorkflowId').value = state.workflow_id;
                document.getElementById('stateName').value = state.name;
                document.getElementById('stateCode').value = state.code;
                document.getElementById('stateType').value = state.state_type;
                document.getElementById('stateColor').value = state.color || '#3b82f6';
                document.getElementById('stateColorHex').value = state.color || '#3b82f6';
                document.getElementById('stateSlaHours').value = state.sla_hours || '';
                document.getElementById('stateSortOrder').value = state.sort_order || 0;
                document.getElementById('stateDesc').value = state.description || '';
                document.getElementById('modalState').classList.add('active');
            }
        } catch (error) {
            ITTicketUI.showError('Error loading state');
        }
    },

    async saveState() {
        const id = document.getElementById('stateId').value;
        const workflowId = document.getElementById('stateWorkflowId').value;

        const formData = {
            workflow_id: workflowId,
            name: document.getElementById('stateName').value,
            code: document.getElementById('stateCode').value,
            state_type: document.getElementById('stateType').value,
            color: document.getElementById('stateColor').value,
            sla_hours: document.getElementById('stateSlaHours').value || null,
            sort_order: document.getElementById('stateSortOrder').value || 0,
            description: document.getElementById('stateDesc').value
        };

        if (!formData.name || !formData.code || !formData.state_type) {
            ITTicketUI.showValidationError('Please fill in required fields');
            return;
        }

        try {
            const url = id ? `${this.baseUrl}workflow-states/update/${id}` : `${this.baseUrl}workflow-states/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess(id ? 'State updated successfully' : 'State created successfully');
                this.closeModal();
                this.loadStates(workflowId);
                // Reload transitions to update dropdowns
                TransitionManager.loadTransitions(workflowId);
            } else {
                ITTicketUI.showError(data.message || 'Error saving state');
            }
        } catch (error) {
            ITTicketUI.showError('Error saving state');
        }
    },

    async deleteState(id) {
        const confirmed = await ITTicketUI.confirm('Confirm Action', 'Are you sure you want to delete this state? This will also delete related transitions.', 'Yes, proceed'); if (!confirmed) return;

        try {
            const response = await fetch(`${this.baseUrl}workflow-states/delete/${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess('State deleted successfully');
                this.loadStates(this.currentWorkflowId);
                TransitionManager.loadTransitions(this.currentWorkflowId);
            }
        } catch (error) {
            ITTicketUI.showError('Error deleting state');
        }
    },

    closeModal() {
        document.getElementById('modalState').classList.remove('active');
    }
};

// ==================== TRANSITION MANAGER ====================
const TransitionManager = {
    baseUrl: '<?= base_url() ?>',
    currentWorkflowId: null,
    states: [],

    async loadTransitions(workflowId) {
        this.currentWorkflowId = workflowId;
        
        try {
            // Load states first for dropdowns
            const statesResponse = await fetch(`${this.baseUrl}workflow-states?workflow_id=${workflowId}`);
            const statesData = await statesResponse.json();
            
            if (statesData.success) {
                this.states = statesData.data;
                this.updateStateDropdowns();
            }

            // Load transitions
            const response = await fetch(`${this.baseUrl}workflow-transitions?workflow_id=${workflowId}`);
            const data = await response.json();

            if (data.success) {
                this.renderTransitions(data.data);
                this.renderTransitionsVisualizer(data.data);
            }
        } catch (error) {
            console.error('Error loading transitions:', error);
        }
    },

    updateStateDropdowns() {
        const fromSelect = document.getElementById('transFromState');
        const toSelect = document.getElementById('transToState');

        const options = this.states.map(state => 
            `<option value="${state.id}">${state.name} (${state.code})</option>`
        ).join('');

        fromSelect.innerHTML = '<option value="">Any State (Initial transition)</option>' + options;
        toSelect.innerHTML = '<option value="">Select state</option>' + options;
    },

    renderTransitions(transitions) {
        const tbody = document.getElementById('transitionsTableBody');

        if (transitions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 36px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No transitions configured</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = transitions.map((trans, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${trans.name}</strong></td>
                <td>${trans.from_state_name || '<span class="badge badge-info">Any State</span>'}</td>
                <td style="text-align: center; color: #3b82f6;"><i class="fas fa-arrow-right"></i></td>
                <td><strong>${trans.to_state_name}</strong></td>
                <td>${trans.required_role ? '<span class="badge badge-warning">' + trans.required_role + '</span>' : '-'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="TransitionManager.editTransition(${trans.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="TransitionManager.deleteTransition(${trans.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderTransitionsVisualizer(transitions) {
        const container = document.getElementById('transitionsVisualizer');

        if (transitions.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #9ca3af;">
                    <i class="fas fa-route" style="font-size: 36px;"></i>
                    <p style="margin-top: 10px;">Add transitions to visualize flow</p>
                </div>
            `;
            return;
        }

        container.innerHTML = transitions.map(trans => {
            const fromState = this.states.find(s => s.id == trans.from_state_id);
            const toState = this.states.find(s => s.id == trans.to_state_id);

            return `
                <div class="transition-row">
                    <div class="transition-state" style="background: ${fromState ? fromState.color : '#6b7280'};">
                        ${trans.from_state_name || 'Any State'}
                    </div>
                    <div class="transition-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="transition-label">
                        <div class="name">${trans.name}</div>
                        ${trans.required_role ? `<div class="role"><i class="fas fa-user-shield"></i> ${trans.required_role}</div>` : ''}
                    </div>
                    <div class="transition-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="transition-state" style="background: ${toState ? toState.color : '#6b7280'};">
                        ${trans.to_state_name}
                    </div>
                </div>
            `;
        }).join('');
    },

    openAddTransition() {
        if (!this.currentWorkflowId) {
            ITTicketUI.showValidationError('Please select a workflow first');
            return;
        }

        if (this.states.length < 2) {
            ITTicketUI.showValidationError('Please create at least 2 states before adding transitions');
            return;
        }

        document.getElementById('modalTransitionTitle').textContent = 'Add Transition';
        document.getElementById('transId').value = '';
        document.getElementById('transWorkflowId').value = this.currentWorkflowId;
        document.getElementById('transName').value = '';
        document.getElementById('transFromState').value = '';
        document.getElementById('transToState').value = '';
        document.getElementById('transRequiredRole').value = '';
        document.getElementById('transConditions').value = '';
        document.getElementById('transDesc').value = '';
        document.getElementById('modalTransition').classList.add('active');
    },

    async editTransition(id) {
        try {
            const response = await fetch(`${this.baseUrl}workflow-transitions/show/${id}`);
            const data = await response.json();

            if (data.success) {
                const trans = data.data;
                document.getElementById('modalTransitionTitle').textContent = 'Edit Transition';
                document.getElementById('transId').value = trans.id;
                document.getElementById('transWorkflowId').value = trans.workflow_id;
                document.getElementById('transName').value = trans.name;
                document.getElementById('transFromState').value = trans.from_state_id || '';
                document.getElementById('transToState').value = trans.to_state_id;
                document.getElementById('transRequiredRole').value = trans.required_role || '';
                document.getElementById('transConditions').value = trans.conditions || '';
                document.getElementById('transDesc').value = trans.description || '';
                document.getElementById('modalTransition').classList.add('active');
            }
        } catch (error) {
            ITTicketUI.showError('Error loading transition');
        }
    },

    async saveTransition() {
        const id = document.getElementById('transId').value;
        const workflowId = document.getElementById('transWorkflowId').value;

        const formData = {
            workflow_id: workflowId,
            name: document.getElementById('transName').value,
            from_state_id: document.getElementById('transFromState').value || null,
            to_state_id: document.getElementById('transToState').value,
            required_role: document.getElementById('transRequiredRole').value || null,
            conditions: document.getElementById('transConditions').value || null,
            description: document.getElementById('transDesc').value
        };

        if (!formData.name || !formData.to_state_id) {
            ITTicketUI.showValidationError('Please fill in required fields');
            return;
        }

        // Validate JSON if conditions provided
        if (formData.conditions) {
            try {
                JSON.parse(formData.conditions);
            } catch (e) {
                ITTicketUI.showValidationError('Invalid JSON format in conditions');
                return;
            }
        }

        try {
            const url = id ? `${this.baseUrl}workflow-transitions/update/${id}` : `${this.baseUrl}workflow-transitions/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess(id ? 'Transition updated successfully' : 'Transition created successfully');
                this.closeModal();
                this.loadTransitions(workflowId);
            } else {
                ITTicketUI.showError(data.message || 'Error saving transition');
            }
        } catch (error) {
            ITTicketUI.showError('Error saving transition');
        }
    },

    async deleteTransition(id) {
        const confirmed = await ITTicketUI.confirm('Confirm Action', 'Are you sure you want to delete this transition?', 'Yes, proceed'); if (!confirmed) return;

        try {
            const response = await fetch(`${this.baseUrl}workflow-transitions/delete/${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                ITTicketUI.showSuccess('Transition deleted successfully');
                this.loadTransitions(this.currentWorkflowId);
            }
        } catch (error) {
            ITTicketUI.showError('Error deleting transition');
        }
    },

    closeModal() {
        document.getElementById('modalTransition').classList.remove('active');
    }
};

// ==================== TAB INITIALIZATION ====================
function initWorkflowsTab() {
    console.log('Initializing Workflows Tab...');
    WorkflowManager.init();
}

function cleanupWorkflowsTab() {
    console.log('Cleaning up Workflows Tab...');
    // Close any open modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
    // Hide state and transition sections
    document.getElementById('statesSection').style.display = 'none';
    document.getElementById('transitionsSection').style.display = 'none';
}

// ==================== WORKFLOWS SMART UX MANAGER ====================
const WorkflowsSmartUX = {
    baseUrl: '<?= base_url("") ?>',
    
    async checkPrerequisites() {
        try {
            // Check Workflows count
            const wfResponse = await fetch(`${this.baseUrl}workflows`);
            const wfData = await wfResponse.json();
            const wfCount = wfData.data ? wfData.data.length : 0;
            
            // Check States count (if API exists)
            let stCount = 0;
            try {
                const stResponse = await fetch(`${this.baseUrl}workflow-states`);
                const stData = await stResponse.json();
                stCount = stData.data ? stData.data.length : 0;
            } catch (e) {
                console.log('States API not available');
            }
            
            // Update progress indicator
            this.updateProgress(wfCount, stCount);
            
            // Update button states
            this.updateButtonStates(wfCount);
            
            console.log(`📊 Workflows Smart UX: WF=${wfCount}, States=${stCount}`);
        } catch (error) {
            console.error('Workflows Smart UX Error:', error);
        }
    },
    
    updateProgress(wfCount, stCount) {
        // Workflows step
        const wfStep = document.getElementById('wf-step-workflows');
        if (wfStep) {
            wfStep.querySelector('.step-count').textContent = wfCount;
            if (wfCount > 0) {
                wfStep.querySelector('.step-status').textContent = '✓ Completed';
                wfStep.style.opacity = '1';
                wfStep.style.background = 'rgba(16, 185, 129, 0.3)';
            }
        }
        
        // States step
        const stStep = document.getElementById('wf-step-states');
        if (stStep && wfCount > 0) {
            stStep.querySelector('.step-count').textContent = stCount;
            stStep.style.opacity = '1';
            if (stCount > 0) {
                stStep.querySelector('.step-status').textContent = '✓ Completed';
                stStep.style.background = 'rgba(16, 185, 129, 0.3)';
            } else {
                stStep.querySelector('.step-status').textContent = '→ Create now';
                stStep.style.background = 'rgba(59, 130, 246, 0.3)';
            }
        }
        
        // Transitions step (if exists)
        const trStep = document.getElementById('wf-step-transitions');
        if (trStep && stCount > 0) {
            trStep.style.opacity = '1';
            trStep.querySelector('.step-status').textContent = '→ Ready';
            trStep.style.background = 'rgba(59, 130, 246, 0.3)';
        }
    },
    
    updateButtonStates(wfCount) {
        // Add State button
        const btnState = document.getElementById('btnAddState');
        const hintState = document.getElementById('hintState');
        
        if (btnState && hintState) {
            if (wfCount > 0) {
                btnState.disabled = false;
                btnState.style.opacity = '1';
                btnState.style.cursor = 'pointer';
                hintState.style.display = 'none';
            } else {
                btnState.disabled = true;
                btnState.style.opacity = '0.5';
                btnState.style.cursor = 'not-allowed';
                hintState.style.display = 'block';
            }
        }
    }
};

// Refresh Workflows Smart UX after any CRUD operation
function refreshWorkflowsSmartUX() {
    WorkflowsSmartUX.checkPrerequisites();
}

async function initWorkflowsTab() {
    console.log('Initializing Workflows Tab...');
    showTabLoading('tab-workflows');
    
    try {
        await Promise.all([
            WorkflowsSmartUX.checkPrerequisites(),
            WorkflowManager.loadWorkflows()
        ]);
        console.log('✅ Workflows Tab fully loaded');
    } catch (error) {
        console.error('Error initializing Workflows Tab:', error);
    } finally {
        hideTabLoading('tab-workflows');
    }
}

function cleanupWorkflowsTab() {
    console.log('Cleaning up Workflows Tab...');
    document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
}

function showTabLoading(tabId) {
    const tab = document.getElementById(tabId);
    if (!tab) return;
    let overlay = tab.querySelector('.tab-loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'tab-loading-overlay';
        overlay.innerHTML = `<div class="loading-spinner"><div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div><p>Loading data...</p></div>`;
        tab.style.position = 'relative';
        tab.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}

function hideTabLoading(tabId) {
    const tab = document.getElementById(tabId);
    if (!tab) return;
    const overlay = tab.querySelector('.tab-loading-overlay');
    if (overlay) overlay.style.display = 'none';
}
</script>