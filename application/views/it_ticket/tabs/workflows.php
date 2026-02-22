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

        /* Service Group Headers */
        select option.service-group-header {
            font-weight: bold;
            color: #1e40af;
            background: #eff6ff;
            padding: 6px 10px;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Parent Services (have children) - cannot choose */
        select option.service-parent {
            font-weight: 600;
            color: #4b5563;
            padding-left: 20px;
            background: #f9fafb;
        }

        /* Child Services - can choose */
        select option.service-child {
            padding-left: 40px;
            color: #111827;
        }

        /* Leaf Services (no children) - can choose */
        select option.service-leaf {
            padding-left: 20px;
            color: #111827;
            font-weight: 500;
        }

        /* Hover effect */
        select option:not([disabled]):hover {
            background: #3b82f6 !important;
            color: white !important;
        }
    </style>

    <!-- Smart UX: Setup Progress Indicator -->
    <div class="setup-progress-container" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #060606 0%, #4a1085 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-route"></i> Workflow Setup Progress
            <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Follow the order below</span>
        </h4>
        <div class="progress-steps" style="display: flex; gap: 15px; flex-wrap: wrap;">
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

            <div id="wf-step-mappings" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-link"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">4. Mappings</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 Locked</div>
            </div>
        </div>
    </div>

    <!-- Workflows Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-project-diagram"></i> Workflows</h3>
            <button class="btn btn-primary-it-ticket" onclick="WorkflowManager.openAddWorkflow()">
                <i class="fas fa-plus"></i> Add Workflow
            </button>
        </div>

        <!-- Search & Filter -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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

        <div style="width: 100%; overflow-x: auto;">
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
        </div>

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
                    <button id="btnAddState" class="btn btn-primary-it-ticket" disabled
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

        <div style="width: 100%; overflow-x: auto;">
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
    </div>

    <!-- Transitions Section (Shows when state management opened) -->
    <div class="card" id="transitionsSection" style="display: none;">
        <div class="card-header">
            <h3><i class="fas fa-arrows-turn-right"></i> State Transitions</h3>
            <button class="btn btn-primary-it-ticket" onclick="TransitionManager.openAddTransition()">
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

        <div style="width: 100%; overflow-x: auto;">
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

    <!-- IT Services Workflow Mapping Section -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h3><i class="fas fa-link"></i> IT Services Workflow Mapping</h3>
            <div>
                <button id="btnAddWorkflowMapping" class="btn btn-primary-it-ticket" disabled
                    onclick="WorkflowMappingManager.openAddMapping()"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plus"></i> Add Mapping
                </button>
                <small id="hintWorkflowMapping" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Create Workflows first
                </small>
            </div>
        </div>

        <div class="filter-section">
            <select id="filterMappingWorkflowItService" onchange="WorkflowMappingManager.filterMappings()"
                style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                <option value="">All IT Service</option>
            </select>
        </div>

        <div style="width: 100%; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>IT Service</th>
                        <th>Workflow</th>
                        <th>States Count</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="workflowMappingTableBody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
            <button class="btn btn-primary-it-ticket" onclick="WorkflowManager.saveWorkflow()">
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
                    <option value="initial">Initial (Start state)</option>
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
            <button class="btn btn-primary-it-ticket" onclick="StateManager.saveState()">
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

            <!-- <div class="form-group">
                <label>From State</label>
                <select id="transFromState">
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
            </div> -->

            <div class="form-group">
                <label>From State <span class="required">*</span></label>
                <select id="transFromState" required>
                    <option value="">Select from state</option>
                </select>
                <small id="transitionHelperText" style="color: #6b7280; display: block; margin-top: 5px;">
                    <!-- Dynamic helper text will appear here -->
                </small>
            </div>

            <div class="form-group">
                <label>To State <span class="required">*</span></label>
                <select id="transToState" disabled required>
                    <option value="">Select from state first</option>
                </select>
                <small id="transitionWarningText" style="color: #dc2626; display: none; margin-top: 5px;">
                    <!-- Dynamic warning text will appear here -->
                </small>
            </div>

            <div class="form-group">
                <label>Required Role</label>
                <select id="transRequiredRole">
                    <option value="">Select Role</option>
                </select>
            </div>

            <div class="form-group">
                <label>Conditions (JSON)</label>
                <textarea id="transConditions" placeholder='{"priority": "high", "requires_approval": true}'
                    style="font-family: monospace; font-size: 13px;"></textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    Optional: JSON rules for this transition {&quot;priority&quot;: &quot;high&quot;, &quot;requires_approval&quot;: true}
                </small>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="transDesc" placeholder="Enter description"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="TransitionManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="TransitionManager.saveTransition()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Workflow Mapping -->
<div id="modalWorkflowMapping" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalWorkflowMappingTitle">Add Workflow Mapping</h3>
            <button class="modal-close" onclick="WorkflowMappingManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="wfMappingId">

            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="wfMappingItService">
                    <option value="">Select IT Service</option>
                </select>
            </div>

            <div class="form-group">
                <label>Workflow <span class="required">*</span></label>
                <select id="wfMappingWorkflow">
                    <option value="">Select Workflow</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="wfMappingStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div style="padding: 15px; background: #fef3c7; border-radius: 8px; margin-top: 15px;">
                <div style="display: flex; align-items: start; gap: 10px;">
                    <i class="fas fa-info-circle" style="color: #f59e0b; font-size: 18px; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <strong style="color: #92400e; display: block; margin-bottom: 5px;">Note:</strong>
                        <p style="font-size: 13px; color: #92400e; margin: 0;">
                            Each IT Service can only have ONE active workflow. If you assign a new workflow,
                            the previous one will be automatically deactivated.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="WorkflowMappingManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="WorkflowMappingManager.saveMapping()">
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
            this.validator = new FormValidationManager(ValidationRulesWorkflows?.workflows);
            this.validator.setupFormValidation([{
                    fieldId: 'wfName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'wfCode',
                    fieldName: 'code',
                    autoUppercase: true
                },
                {
                    fieldId: 'wfDesc',
                    fieldName: 'description'
                }
            ]);
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
                        <button class="btn btn-sm btn-success-it-ticket" onclick="WorkflowManager.viewStates(${wf.id}, '${wf.name}')">
                            <i class="fas fa-sitemap"></i> States
                        </button>
                        <button class="btn btn-sm btn-primary-it-ticket" onclick="WorkflowManager.editWorkflow(${wf.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger-it-ticket" onclick="WorkflowManager.deleteWorkflow(${wf.id})">
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

            this.validator.reset(['wfName', 'wfCode', 'wfDesc']);
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
                TicketNotifier.showError('Error loading workflow');
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

            const fieldMap = {
                name: 'wfName',
                code: 'wfCode',
                description: 'wfDesc'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}workflows/update/${id}` : `${this.baseUrl}workflows/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Workflow updated successfully' : 'Workflow created successfully');
                    this.closeModal();
                    this.loadWorkflows();
                    refreshWorkflowsSmartUX();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving workflow');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving workflow');
            }
        },

        async deleteWorkflow(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this workflow?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}workflows/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Workflow deleted successfully', () => {
                        this.loadWorkflows();
                        refreshWorkflowsSmartUX();
                    });
                } else {
                    TicketNotifier.showError(data.message || 'Error deleting workflow')
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting workflow');
            }
        },

        viewStates(workflowId, workflowName) {
            this.selectedWorkflowId = workflowId;
            document.getElementById('statesWorkflowTitle').innerHTML =
                `States for: <span style="color: #3b82f6;">${workflowName}</span>`;
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
            this.validator.reset(['wfName', 'wfCode', 'wfDesc']);
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

        init() {
            this.validator = new FormValidationManager(ValidationRulesWorkflows?.states);

            this.validator.setupFormValidation([{
                    fieldId: 'stateName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'stateCode',
                    fieldName: 'code',
                    autoUppercase: true
                },
                {
                    fieldId: 'stateType',
                    fieldName: 'state_type',
                },
                {
                    fieldId: 'stateColor',
                    fieldName: 'color',
                },
                {
                    fieldId: 'stateSlaHours',
                    fieldName: 'sla_hours'
                },
                {
                    fieldId: 'stateDesc',
                    fieldName: 'description'
                }
            ]);
        },

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
                        <button class="btn btn-sm btn-primary-it-ticket" onclick="StateManager.editState(${state.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger-it-ticket" onclick="StateManager.deleteState(${state.id})">
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
                TicketNotifier.showValidationError('Please select a workflow first');
                return;
            }

            document.getElementById('modalStateTitle').textContent = 'Add State';
            document.getElementById('stateId').value = '';
            document.getElementById('stateWorkflowId').value = this.currentWorkflowId;
            document.getElementById('stateName').value = '';
            document.getElementById('stateCode').value = '';
            document.getElementById('stateType').value = 'initial';
            document.getElementById('stateColor').value = '#3b82f6';
            document.getElementById('stateColorHex').value = '#3b82f6';
            document.getElementById('stateSlaHours').value = '';
            document.getElementById('stateSortOrder').value = '0';
            document.getElementById('stateDesc').value = '';

            this.validator.reset(['stateName', 'stateCode', 'stateType', 'stateColor', 'stateSlaHours', 'stateDesc']);
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
                    document.getElementById('stateCode').value = state.code.toUpperCase();
                    document.getElementById('stateType').value = state.state_type;
                    document.getElementById('stateColor').value = state.color || '#3b82f6';
                    document.getElementById('stateColorHex').value = state.color || '#3b82f6';
                    document.getElementById('stateSlaHours').value = state.sla_hours || '';
                    document.getElementById('stateSortOrder').value = state.sort_order || 0;
                    document.getElementById('stateDesc').value = state.description || '';
                    document.getElementById('modalState').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading state');
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

            const fieldMap = {
                name: 'stateName',
                code: 'stateCode',
                state_type: 'stateType',
                color: 'stateColor',
                sla_hours: 'stateSlaHours',
                description: 'stateDesc'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}workflow-states/update/${id}` : `${this.baseUrl}workflow-states/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'State updated successfully' : 'State created successfully');
                    this.closeModal();
                    this.loadStates(workflowId);
                    TransitionManager.loadTransitions(workflowId);
                    refreshWorkflowsSmartUX();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving state');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving state');
            }
        },

        async deleteState(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this state? This will also delete related transitions.', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}workflow-states/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('State deleted successfully');
                    this.loadStates(this.currentWorkflowId);
                    TransitionManager.loadTransitions(this.currentWorkflowId);
                    refreshWorkflowsSmartUX();
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting state');
            }
        },

        closeModal() {
            this.validator.reset(['stateName', 'stateCode', 'stateType', 'stateColor', 'stateSlaHours', 'stateDesc']);
            document.getElementById('modalState').classList.remove('active');
        }
    };

    // ==================== TRANSITION MANAGER ====================
    const TransitionManager = {
        baseUrl: '<?= base_url() ?>',
        currentWorkflowId: null,
        states: [],

        init() {
            this.validator = new FormValidationManager(ValidationRulesWorkflows?.transitions);
            this.validator.setupFormValidation([{
                    fieldId: 'transName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'transFromState',
                    fieldName: 'from_state_id',
                },
                {
                    fieldId: 'transToState',
                    fieldName: 'to_state_id',
                },
                {
                    fieldId: 'transRequiredRole',
                    fieldName: 'required_role',
                },
                {
                    fieldId: 'transConditions',
                    fieldName: 'conditions'
                },
                {
                    fieldId: 'transDesc',
                    fieldName: 'description'
                }
            ]);
        },

        async loadTransitions(workflowId) {
            this.currentWorkflowId = workflowId;

            try {
                // Load states first for dropdowns
                const statesResponse = await fetch(`${this.baseUrl}workflow-states?workflow_id=${workflowId}`);
                const statesData = await statesResponse.json();

                if (statesData.success) {
                    this.states = statesData.data.map(state => ({
                        ...state,
                        type: state.state_type
                    }));

                    console.log('States loaded:', this.states);
                    this.updateStateDropdowns();
                    this.setupStateChangeHandlers();
                }

                // Load transitions
                const response = await fetch(`${this.baseUrl}workflow-transitions?workflow_id=${workflowId}`);
                const data = await response.json();

                if (data.success) {
                    this.transitions = data.data; // Store transitions for filtering
                    this.renderTransitions(data.data);
                    this.renderTransitionsVisualizer(data.data);
                }
            } catch (error) {
                console.error('Error loading transitions:', error);
            }
        },

        async loadTicketRolesDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}api/roles/all`);
                const data = await response.json();

                if (data.success) {
                    const options = data?.data.map(role =>
                        `<option value="${role.name}">${role.display_name}</option>`
                    ).join('');

                    document.getElementById('transRequiredRole').innerHTML =
                        '<option value="">Select Role</option>' + options;
                }
            } catch (error) {
                console.error('Error loading roles:', error);
            }
        },

        setupStateChangeHandlers() {
            const fromSelect = document.getElementById('transFromState');
            const toSelect = document.getElementById('transToState');
            const helperText = document.getElementById('transitionHelperText');
            const warningText = document.getElementById('transitionWarningText');

            fromSelect.addEventListener('change', (e) => {
                const fromStateId = e.target.value;
                // debugger;
                if (!fromStateId) {
                    // Reset to state dropdown when from state is cleared
                    toSelect.disabled = true;
                    toSelect.innerHTML = '<option value="">Select from state first</option>';
                    helperText.textContent = '';
                    warningText.style.display = 'none';
                    return;
                }

                // Enable to state dropdown
                toSelect.disabled = false;
                
                // Get the selected from state
                const fromState = this.states.find(s => s.id == fromStateId);
                
                // Get available transitions from this state
                const availableToStates = this.getAvailableToStates(fromStateId);
                
                // Update helper text
                helperText.textContent = `Select the state to transition from "${fromState.name}"`;
                // debugger;
                // Populate to state dropdown with only valid options
                if (availableToStates.length === 0) {
                    toSelect.innerHTML = '<option value="">No valid transitions available</option>';
                    warningText.textContent = `No transitions can be created from "${fromState.name}" state.`;
                    warningText.style.display = 'block';
                } else {
                    const options = availableToStates.map(state =>
                        `<option value="${state.id}">${state.name} (${state.code})</option>`
                    ).join('');
                    
                    toSelect.innerHTML = '<option value="">Select to state</option>' + options;
                    warningText.style.display = 'none';
                }
            });

            // Optional: Clear warning when to state is selected
            toSelect.addEventListener('change', () => {
                if (toSelect.value) {
                    warningText.style.display = 'none';
                }
            });
        },

        getAvailableToStates(fromStateId) {
            const fromState = this.states.find(s => s.id == fromStateId);
            if (!fromState) return [];

            // Get already existing transitions from this state
            const existingTransitions = (this.transitions || [])
                .filter(t => t.from_state_id == fromStateId)
                .map(t => t.to_state_id);

            // Get state_type from database
            const fromStateType = fromState.state_type || fromState.type;

            // Filter out invalid transitions based on business logic
            return this.states.filter(state => {
                const toStateType = state.state_type || state.type;

                // Can't transition to itself
                if (state.id == fromStateId) return false;

                // Can't create duplicate transitions
                if (existingTransitions.includes(state.id)) return false;

                // State type specific rules based on state_type from database
                
                // Initial state can transition to intermediate states only
                if (fromStateType === 'initial') {
                    return toStateType === 'intermediate';
                }

                // Intermediate states (processing, pending) can transition to:
                // - Other intermediate states
                // - Final states (solved, closed)
                if (fromStateType === 'intermediate') {
                    return toStateType === 'intermediate' || toStateType === 'final';
                }

                // Final states (solved, closed)
                if (fromStateType === 'final') {
                    // Check if current state is 'solved' by checking state name or code
                    const fromStateName = fromState.name.toLowerCase();
                    const fromStateCode = (fromState.code || '').toLowerCase();
                    
                    // If this is 'solved' state, allow transition to 'closed'
                    if (fromStateName.includes('solved') || fromStateCode === 'solved') {
                        const toStateName = state.name.toLowerCase();
                        const toStateCode = (state.code || '').toLowerCase();
                        
                        // Only allow transition to 'closed' state
                        return toStateName.includes('closed') || toStateCode === 'closed';
                    }
                    
                    // Other final states (like 'closed') cannot transition anywhere
                    return false;
                }

                return false;
            });
        },

        updateStateDropdowns() {
            const fromSelect = document.getElementById('transFromState');

            const options = this.states.map(state =>
                `<option value="${state.id}">${state.name} (${state.code})</option>`
            ).join('');

            fromSelect.innerHTML = '<option value="">Select from state</option>' + options;

            // To state will be populated dynamically based on from state selection
            const toSelect = document.getElementById('transToState');
            toSelect.disabled = true;
            toSelect.innerHTML = '<option value="">Select from state first</option>';
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
                        <button class="btn btn-sm btn-primary-it-ticket" onclick="TransitionManager.editTransition(${trans.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger-it-ticket" onclick="TransitionManager.deleteTransition(${trans.id})">
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
                TicketNotifier.showValidationError('Please select a workflow first');
                return;
            }

            if (this.states.length < 2) {
                TicketNotifier.showValidationError('Please create at least 2 states before adding transitions');
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

            this.validator.reset(['transName', 'transFromState', 'transToState', 'transRequiredRole', 'transConditions', 'transDesc']);
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
                TicketNotifier.showError('Error loading transition');
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

            const fieldMap = {
                name: 'transName',
                from_state_id: 'transFromState',
                to_state_id: 'transToState',
                required_role: 'transRequiredRole',
                conditions: 'transConditions',
                description: 'transDesc'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            // Validate JSON if conditions provided
            if (formData.conditions) {
                try {
                    JSON.parse(formData.conditions);
                } catch (e) {
                    TicketNotifier.showValidationError('Invalid JSON format in conditions');
                    return;
                }
            }

            try {
                const url = id ? `${this.baseUrl}workflow-transitions/update/${id}` : `${this.baseUrl}workflow-transitions/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Transition updated successfully' : 'Transition created successfully');
                    this.closeModal();
                    this.loadTransitions(workflowId);
                    refreshWorkflowsSmartUX();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving transition');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving transition');
            }
        },

        async deleteTransition(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this transition?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}workflow-transitions/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Transition deleted successfully');
                    this.loadTransitions(this.currentWorkflowId);
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting transition');
            }
        },

        closeModal() {
            this.validator.reset(['transName', 'transFromState', 'transToState', 'transRequiredRole', 'transConditions', 'transDesc']);
            document.getElementById('modalTransition').classList.remove('active');
        }
    };

    // ==================== WORKFLOW MAPPING MANAGER ====================
    const WorkflowMappingManager = {
        baseUrl: '<?= base_url() ?>',
        filterItService: '',

        async loadMappings() {
            try {
                const params = new URLSearchParams({
                    it_service_id: this.filterItService
                });

                const response = await fetch(`${this.baseUrl}it-service-workflows?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.renderMappings(data.data);
                }
            } catch (error) {
                console.error('Error loading workflow mappings:', error);
            }
        },

        renderMappings(mappings) {
            const tbody = document.getElementById('workflowMappingTableBody');

            if (mappings.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No workflow mappings found</p>
                    </td>
                </tr>
            `;
                return;
            }

            tbody.innerHTML = mappings.map((mapping, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${mapping.it_service_name}</strong></td>
                <td>${mapping.workflow_name}</td>
                <td><span class="badge badge-success">${mapping.states_count || 0} states</span></td>
                <td><span class="badge badge-${mapping.is_active ? 'success' : 'danger'}">${mapping.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary-it-ticket" onclick="WorkflowMappingManager.editMapping(${mapping.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger-it-ticket" onclick="WorkflowMappingManager.deleteMapping(${mapping.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        },

        openAddMapping() {
            document.getElementById('modalWorkflowMappingTitle').textContent = 'Add Workflow Mapping';
            document.getElementById('wfMappingId').value = '';
            document.getElementById('wfMappingItService').value = '';
            document.getElementById('wfMappingWorkflow').value = '';
            document.getElementById('wfMappingStatus').value = '1';
            document.getElementById('modalWorkflowMapping').classList.add('active');
        },

        async editMapping(id) {
            try {
                const response = await fetch(`${this.baseUrl}it-service-workflows/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const mapping = data.data;
                    document.getElementById('modalWorkflowMappingTitle').textContent = 'Edit Workflow Mapping';
                    document.getElementById('wfMappingId').value = mapping.id;
                    document.getElementById('wfMappingItService').value = mapping.it_service_id;
                    document.getElementById('wfMappingWorkflow').value = mapping.workflow_id;
                    document.getElementById('wfMappingStatus').value = mapping.is_active ? '1' : '0';
                    document.getElementById('modalWorkflowMapping').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading workflow mapping');
            }
        },

        async saveMapping() {
            const id = document.getElementById('wfMappingId').value;
            const formData = {
                it_service_id: document.getElementById('wfMappingItService').value,
                workflow_id: document.getElementById('wfMappingWorkflow').value,
                is_active: document.getElementById('wfMappingStatus').value === '1' ? 1 : 0
            };

            if (!formData.it_service_id || !formData.workflow_id) {
                TicketNotifier.showValidationError('Please select both IT Service and Workflow');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}it-service-workflows/update/${id}` : `${this.baseUrl}it-service-workflows/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Workflow Mapping updated successfully' : 'Workflow Mapping created successfully');
                    this.closeModal();
                    this.loadMappings();
                    refreshWorkflowsSmartUX();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving workflow mapping');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving workflow mapping');
            }
        },

        async deleteMapping(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this workflow mapping?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}it-service-workflows/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Workflow Mapping deleted successfully', () => {
                        this.loadMappings();
                        refreshWorkflowsSmartUX();
                    });
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting workflow mapping');
            }
        },

        filterMappings() {
            this.filterItService = document.getElementById('filterMappingWorkflowItService').value;
            this.loadMappings();
        },

        async loadItServicesDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}it-services/all-group-type`);
                const data = await response.json();

                if (data.success) {
                    let options = '';
                    
                    data.data.forEach((group, index) => {
                        // Service Group header
                        options += `<option disabled class="service-group-header">
                            ━━━ ${group.name.toUpperCase()} ━━━
                        </option>`;
                        
                        group.services.forEach(service => {
                            const hasChildren = service.children && service.children.length > 0;
                            
                            if (hasChildren) {
                                // Parent with children - not selectable
                                options += `<option disabled class="service-parent">
                                    • ${service.name}
                                </option>`;
                                
                                // Children - selectable
                                service.children.forEach(child => {
                                    options += `<option value="${child.id}" class="service-child">
                                        &nbsp;&nbsp;&nbsp;└─ ${child.name}
                                    </option>`;
                                });
                            } else {
                                // Leaf service - selectable
                                options += `<option value="${service.id}" class="service-leaf">
                                    • ${service.name}
                                </option>`;
                            }
                        });
                        
                        // Separator between groups (except last one)
                        if (index < data.data.length - 1) {
                            options += `<option disabled style="padding: 0; height: 2px; background: #e5e7eb;"></option>`;
                        }
                    });

                    document.getElementById('filterMappingWorkflowItService').innerHTML =
                        '<option value="">All IT Service</option>' + options;
                    document.getElementById('wfMappingItService').innerHTML =
                        '<option value="">Select IT Service</option>' + options;
                }
            } catch (error) {
                console.error('Error loading it service:', error);
            }
        },

        async loadWorkflowsDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}workflows/all`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(wf =>
                        `<option value="${wf.id}">${wf.name} (${wf.code})</option>`
                    ).join('');

                    document.getElementById('wfMappingWorkflow').innerHTML =
                        '<option value="">Select Workflow</option>' + options;
                }
            } catch (error) {
                console.error('Error loading workflows:', error);
            }
        },

        closeModal() {
            document.getElementById('modalWorkflowMapping').classList.remove('active');
        }
    };

    // ==================== WORKFLOWS SMART UX MANAGER (FIXED) ====================
    const WorkflowsSmartUX = {
        baseUrl: '<?= base_url() ?>',

        async checkPrerequisites() {
            try {
                // Check Workflows count
                const wfResponse = await fetch(`${this.baseUrl}workflows`);
                const wfData = await wfResponse.json();
                const wfCount = wfData.data ? wfData.data.length : 0;

                // For states and transitions count, we cannot call the API without workflow_id
                // Instead, we'll get the count from the workflow stats endpoint or aggregate from workflows
                let stCount = 0;
                let trCount = 0;

                // Calculate states and transitions count from workflows data
                if (wfData.data && wfData.data.length > 0) {
                    // Sum up states_count from all workflows
                    stCount = wfData.data.reduce((sum, wf) => sum + (parseInt(wf.states_count) || 0), 0);

                    // For transitions, we need to call the API for each workflow
                    // But for performance, we'll just estimate or skip this
                    // Alternatively, add a stats endpoint in backend
                    trCount = 0; // Will be calculated below if needed
                }

                // Check Mappings count
                let mapCount = 0;
                try {
                    const mapResponse = await fetch(`${this.baseUrl}it-service-workflows`);
                    const mapData = await mapResponse.json();
                    mapCount = mapData.data ? mapData.data.length : 0;
                } catch (e) {
                    console.log('Mappings API not available');
                }

                // Update progress indicator
                this.updateProgress(wfCount, stCount, trCount, mapCount);

                // Update button states
                this.updateButtonStates(wfCount, stCount);

                console.log(`Workflows Smart UX: WF=${wfCount}, States=${stCount}, Trans=${trCount}, Maps=${mapCount}`);
            } catch (error) {
                console.error('Workflows Smart UX Error:', error);
            }
        },

        updateProgress(wfCount, stCount, trCount, mapCount) {
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

            // Transitions step
            const trStep = document.getElementById('wf-step-transitions');
            if (trStep && stCount > 0) {
                trStep.querySelector('.step-count').textContent = trCount > 0 ? trCount : '-';
                trStep.style.opacity = '1';
                trStep.querySelector('.step-status').textContent = 'Optional';
                trStep.style.background = 'rgba(59, 130, 246, 0.2)';
            }

            // Mappings step
            const mapStep = document.getElementById('wf-step-mappings');
            if (mapStep && wfCount > 0) {
                mapStep.querySelector('.step-count').textContent = mapCount;
                mapStep.style.opacity = '1';
                if (mapCount > 0) {
                    mapStep.querySelector('.step-status').textContent = '✓ Completed';
                    mapStep.style.background = 'rgba(16, 185, 129, 0.3)';
                } else {
                    mapStep.querySelector('.step-status').textContent = '→ Create now';
                    mapStep.style.background = 'rgba(59, 130, 246, 0.3)';
                }
            }
        },

        updateButtonStates(wfCount, stCount) {
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

            // Add Workflow Mapping button
            const btnMapping = document.getElementById('btnAddWorkflowMapping');
            const hintMapping = document.getElementById('hintWorkflowMapping');

            if (btnMapping && hintMapping) {
                if (wfCount > 0) {
                    btnMapping.disabled = false;
                    btnMapping.style.opacity = '1';
                    btnMapping.style.cursor = 'pointer';
                    hintMapping.style.display = 'none';
                } else {
                    btnMapping.disabled = true;
                    btnMapping.style.opacity = '0.5';
                    btnMapping.style.cursor = 'not-allowed';
                    hintMapping.style.display = 'block';
                }
            }
        }
    };

    // Refresh Workflows Smart UX after any CRUD operation
    function refreshWorkflowsSmartUX() {
        WorkflowsSmartUX.checkPrerequisites();
    }

    // ==================== TAB INITIALIZATION ====================
    async function initWorkflowsTab() {
        console.log('Initializing Workflows Tab...');
        showTabLoading('tab-workflows');

        try {
            await Promise.all([
                WorkflowsSmartUX.checkPrerequisites(),
                WorkflowManager.init(),
                StateManager.init(),
                TransitionManager.init(),
                TransitionManager.loadTicketRolesDropdown(),
                WorkflowManager.loadWorkflows(),
                WorkflowMappingManager.loadMappings(),
                WorkflowMappingManager.loadItServicesDropdown(),
                WorkflowMappingManager.loadWorkflowsDropdown()
            ]);
            console.log('Workflows Tab fully loaded');
        } catch (error) {
            console.error('Error initializing Workflows Tab:', error);
        } finally {
            hideTabLoading('tab-workflows');
        }
    }

    function cleanupWorkflowsTab() {
        console.log('Cleaning up Workflows Tab...');
        document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
        document.getElementById('statesSection').style.display = 'none';
        document.getElementById('transitionsSection').style.display = 'none';
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