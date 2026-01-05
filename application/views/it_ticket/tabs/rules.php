<div id="tab-rules" class="tab-content active">

    <style>
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

    <!-- Smart UX: Setup Progress Indicator -->
    <div class="setup-progress-container" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #1c191c 0%, #0a0103 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-clipboard-check"></i> Prerequisites Check
            <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Complete setup in Service Structure tab first</span>
        </h4>
        <div class="progress-steps" style="display: flex; gap: 15px;">
            <div id="rules-step-it-services" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">IT Service</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 Required</div>
            </div>

            <div id="rules-step-teams" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Support Teams</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 For Level Rules</div>
            </div>

            <div id="rules-step-ready" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Ready</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">-</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Create Rules</div>
            </div>
        </div>
    </div>

    <!-- Sub-Tab Navigation for Rules -->
    <div class="rules-sub-tabs" style="background: white; border-radius: 12px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="display: flex; gap: 10px; overflow-x: auto;">
            <button class="sub-tab-btn active" data-section="routing" onclick="RulesTabManager.switchSection('routing', this)">
                <i class="fas fa-route"></i> Routing Rules
            </button>
            <button class="sub-tab-btn" data-section="approval" onclick="RulesTabManager.switchSection('approval', this)">
                <i class="fas fa-stamp"></i> Approval Rules
            </button>
            <button class="sub-tab-btn" data-section="level" onclick="RulesTabManager.switchSection('level', this)">
                <i class="fas fa-layer-group"></i> Level Rules
            </button>
            <button class="sub-tab-btn" data-section="custom-fields" onclick="RulesTabManager.switchSection('custom-fields', this)">
                <i class="fas fa-sliders"></i> Custom Fields
            </button>
        </div>
    </div>

    <!-- ROUTING RULES SECTION -->
    <div id="section-routing" class="rules-section active">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-route"></i> Routing Rules</h3>
                <button class="btn btn-primary-it-ticket" onclick="RoutingRulesManager.openAddRule()">
                    <i class="fas fa-plus"></i> Add Routing Rule
                </button>
            </div>

            <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
                <div style="flex: 1;">
                    <select id="filterRoutingItService" onchange="RoutingRulesManager.filterRules()"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="">All IT Service</option>
                    </select>
                </div>
                <div>
                    <select id="filterRoutingStatus" onchange="RoutingRulesManager.filterRules()"
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
                            <th>Rule Name</th>
                            <th>IT Service</th>
                            <th>Priority</th>
                            <th>Assignment Type</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="routingRulesTableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- APPROVAL RULES SECTION -->
    <div id="section-approval" class="rules-section" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-stamp"></i> Approval Rules</h3>
                <div>
                    <button id="btnAddApprovalRule" class="btn btn-primary-it-ticket" disabled
                        onclick="ApprovalRulesManager.openAddRule()"
                        style="opacity: 0.5; cursor: not-allowed;">
                        <i class="fas fa-plus"></i> Add Approval Rule
                    </button>
                    <small id="hintApprovalRule" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> Create IT Service first (Service Structure tab)
                    </small>
                </div>
            </div>

            <div class="filter-section">
                <select id="filterApprovalItService" onchange="ApprovalRulesManager.filterRules()"
                    style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All IT Service</option>
                </select>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Rule Name</th>
                        <th>IT Service</th>
                        <th>Approval Type</th>
                        <th>Approver</th>
                        <th>Auto Assign</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="approvalRulesTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LEVEL RULES SECTION -->
    <div id="section-level" class="rules-section" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-layer-group"></i> Multi-Level Support Rules</h3>
                <div>
                    <button id="btnAddLevelRule" class="btn btn-primary-it-ticket" disabled
                        onclick="LevelRulesManager.openAddRule()"
                        style="opacity: 0.5; cursor: not-allowed;">
                        <i class="fas fa-plus"></i> Add Level Rule
                    </button>
                    <small id="hintLevelRule" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> Create IT Service + Support Teams first
                    </small>
                </div>
            </div>

            <div class="filter-section">
                <select id="filterLevelItService" onchange="LevelRulesManager.filterRules()"
                    style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All IT Service</option>
                </select>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>IT Service</th>
                        <th>Level</th>
                        <th>Level Name</th>
                        <th>Support Team</th>
                        <th>Auto Escalate</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="levelRulesTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CUSTOM FIELDS SECTION -->
    <div id="section-custom-fields" class="rules-section" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-sliders"></i> Custom Fields</h3>
                <div>
                    <button id="btnAddCustomField" class="btn btn-primary-it-ticket" disabled
                        onclick="CustomFieldsManager.openAddField()"
                        style="opacity: 0.5; cursor: not-allowed;">
                        <i class="fas fa-plus"></i> Add Custom Field
                    </button>
                    <small id="hintCustomField" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> Create IT Service first (Service Structure tab)
                    </small>
                </div>
            </div>

            <div class="filter-section">
                <select id="filterFieldItService" onchange="CustomFieldsManager.filterFields()"
                    style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All IT Service</option>
                </select>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Field Name</th>
                        <th>IT Service</th>
                        <th>Field Type</th>
                        <th>Required</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="customFieldsTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- Modal: Routing Rule -->
<div id="modalRoutingRule" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalRoutingTitle">Add Routing Rule</h3>
            <button class="modal-close" onclick="RoutingRulesManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="routingId">
            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="routingItService"></select>
            </div>
            <div class="form-group">
                <label>Rule Name <span class="required">*</span></label>
                <input type="text" id="routingRuleName" placeholder="e.g., VPN to L1 VN">
            </div>
            <div class="form-group">
                <label>Priority</label>
                <input type="number" id="routingPriority" value="100" min="0">
                <small>Lower number = higher priority</small>
            </div>
            <div class="form-group">
                <label>Assignment Type <span class="required">*</span></label>
                <select id="routingAssignmentType" onchange="RoutingRulesManager.toggleTargetFields()">
                    <option value="team">Team</option>
                    <option value="user">Specific User</option>
                </select>
            </div>
            <div class="form-group" id="targetTeamGroup">
                <label>Target Team</label>
                <select id="routingTargetTeam"></select>
            </div>
            <div class="form-group" id="targetUserGroup" style="display: none;">
                <label>Target User</label>
                <select id="routingTargetUser"></select>
            </div>
            <div class="form-group">
                <label>Conditions (JSON)</label>
                <textarea id="routingConditions" placeholder='{"country": "VN", "priority": "high"}'></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="routingStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="RoutingRulesManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="RoutingRulesManager.saveRule()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<!-- Modal: Approval Rule -->
<div id="modalApprovalRule" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalApprovalTitle">Add Approval Rule</h3>
            <button class="modal-close" onclick="ApprovalRulesManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="approvalId">
            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="approvalItService"></select>
            </div>
            <div class="form-group">
                <label>Rule Name <span class="required">*</span></label>
                <input type="text" id="approvalRuleName" placeholder="e.g., VPN Manager Approval">
            </div>
            <div class="form-group">
                <label>Requires Approval</label>
                <select id="approvalRequired">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label>Approval Type <span class="required">*</span></label>
                <select id="approvalType" onchange="ApprovalRulesManager.toggleApproverFields()">
                    <option value="manager">Manager</option>
                    <option value="specific_user">Specific User</option>
                </select>
            </div>
            <div class="form-group" id="approverManagerGroup">
                <label>Approver Manager</label>
                <select id="approvalManagerId"></select>
            </div>
            <div class="form-group" id="approverUserGroup" style="display: none;">
                <label>Approver User</label>
                <select id="approvalUserId"></select>
            </div>
            <div class="form-group">
                <label>Auto Assign After Approval</label>
                <select id="approvalAutoAssign">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label>Reminder Hours</label>
                <input type="number" id="approvalReminderHours" value="24">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="approvalStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="ApprovalRulesManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="ApprovalRulesManager.saveRule()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<!-- Modal: Level Rule -->
<div id="modalLevelRule" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalLevelTitle">Add Level Rule</h3>
            <button class="modal-close" onclick="LevelRulesManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="levelId">
            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="levelItService"></select>
            </div>
            <div class="form-group">
                <label>Level Number <span class="required">*</span></label>
                <input type="number" id="levelNumber" min="1" max="10" placeholder="1, 2, 3...">
            </div>
            <div class="form-group">
                <label>Level Name <span class="required">*</span></label>
                <input type="text" id="levelName" placeholder="e.g., L1 Support, L2 Support">
            </div>
            <div class="form-group">
                <label>Support Team</label>
                <select id="levelSupportTeam"></select>
            </div>
            <div class="form-group">
                <label>Auto Escalate (Hours)</label>
                <input type="number" id="levelAutoEscalateHours" placeholder="e.g., 4, 8, 24">
            </div>
            <div class="form-group">
                <label>Assignment Type</label>
                <select id="levelAssignmentType">
                    <option value="team">Team</option>
                </select>
            </div>
            <div class="form-group">
                <label>Requires Escalation Reason</label>
                <select id="levelRequiresReason">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="levelStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="LevelRulesManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="LevelRulesManager.saveRule()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<!-- Modal: Custom Field -->
<div id="modalCustomField" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalFieldTitle">Add Custom Field</h3>
            <button class="modal-close" onclick="CustomFieldsManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fieldId">
            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="fieldItService"></select>
            </div>
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Field Name <span class="required">*</span></label>
                    <input type="text" id="fieldName" placeholder="e.g., manager_approval">
                </div>
                <div class="form-group">
                    <label>Field Label <span class="required">*</span></label>
                    <input type="text" id="fieldLabel" placeholder="e.g., Select Manager">
                </div>
            </div>
            <div class="form-group">
                <label>Field Type <span class="required">*</span></label>
                <select id="fieldType">
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="select">Select (Dropdown)</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="file">File Upload</option>
                    <option value="user_select">User Select</option>
                </select>
            </div>
            <div class="form-group">
                <label>Field Options (JSON - for select/radio/checkbox)</label>
                <textarea id="fieldOptions" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
            </div>
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Required</label>
                    <select id="fieldRequired">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" id="fieldSortOrder" value="0">
                </div>
            </div>
            <div class="form-group">
                <label>Default Value</label>
                <input type="text" id="fieldDefaultValue" placeholder="Optional">
            </div>
            <div class="form-group">
                <label>Placeholder</label>
                <input type="text" id="fieldPlaceholder" placeholder="Placeholder text">
            </div>
            <div class="form-group">
                <label>Help Text</label>
                <textarea id="fieldHelpText" placeholder="Help text for users" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="fieldStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="CustomFieldsManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="CustomFieldsManager.saveField()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<style>
    .sub-tab-btn {
        padding: 12px 20px;
        border: none;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .sub-tab-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .sub-tab-btn.active {
        background: #3b82f6;
        color: white;
    }

    .rules-section {
        display: none;
        animation: fadeIn 0.3s;
    }

    .rules-section.active {
        display: block !important;
    }

    .form-row .form-group {
        margin-bottom: 20px;
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

<script>
    window.RulesTabManager = window.RulesTabManager || {
        currentSection: 'routing',
        baseUrl: '<?= base_url() ?>',
        async loadItServicesDropdowns() {
            try {
                const response = await fetch(`${this.baseUrl}it-services/all-group-type`);
                const data = await response.json();

                if (data.success) {
                    let options = '';

                    data.data.forEach((group, index) => {
                        // Group header
                        options += `
                    <option disabled class="service-group-header">
                        ━━━ ${group.name.toUpperCase()} ━━━
                    </option>
                `;

                        group.services.forEach(service => {
                            const hasChildren = service.children && service.children.length > 0;

                            if (hasChildren) {
                                // Parent (not selectable)
                                options += `
                            <option disabled class="service-parent">
                                • ${service.name}
                            </option>
                        `;

                                // Children (selectable)
                                service.children.forEach(child => {
                                    options += `
                                <option value="${child.id}" class="service-child">
                                    &nbsp;&nbsp;&nbsp;└─ ${child.name}
                                </option>
                            `;
                                });
                            } else {
                                // Leaf service (selectable)
                                options += `
                            <option value="${service.id}" class="service-leaf">
                                • ${service.name}
                            </option>
                        `;
                            }
                        });

                        // Separator giữa các group
                        if (index < data.data.length - 1) {
                            options += `
                        <option disabled style="padding:0;height:2px;background:#e5e7eb;"></option>
                    `;
                        }
                    });

                    // ===== Filter dropdowns =====
                    const filterDropdowns = [
                        'filterRoutingItService',
                        'filterApprovalItService',
                        'filterLevelItService',
                        'filterFieldItService'
                    ];

                    filterDropdowns.forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) {
                            elem.innerHTML = '<option value="">All IT Service</option>' + options;
                        }
                    });

                    // ===== Modal dropdowns =====
                    const modalDropdowns = [
                        'routingItService',
                        'approvalItService',
                        'levelItService',
                        'fieldItService'
                    ];

                    modalDropdowns.forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) {
                            elem.innerHTML = '<option value="">Select IT Service</option>' + options;
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading IT Service:', error);
            }
        },


        async loadTeamsDropdowns() {
            try {
                const response = await fetch(`${this.baseUrl}support-teams`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(team =>
                        `<option value="${team.id}">${team.name}</option>`
                    ).join('');

                    // Update team dropdowns
                    const teamDropdowns = ['routingTargetTeam', 'levelSupportTeam'];
                    teamDropdowns.forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) elem.innerHTML = '<option value="">Select Team</option>' + options;
                    });
                }
            } catch (error) {
                console.error('Error loading teams:', error);
            }
        },

        async loadUsersDropdowns() {
            try {
                const response = await fetch(`${this.baseUrl}employees/active`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(user =>
                        `<option value="${user.employee_id}">${user.fullname} (${user.department_code || 'N/A'})</option>`
                    ).join('');

                    // Update user dropdowns
                    const userDropdowns = ['routingTargetUser', 'approvalUserId'];
                    userDropdowns.forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) elem.innerHTML = '<option value="">Select User</option>' + options;
                    });
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },

        async loadManagersDropdowns() {
            try {
                const response = await fetch(`${this.baseUrl}employees/managers`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(manager =>
                        `<option value="${manager.employee_id}">${manager.fullname} (${manager.department_code || 'N/A'})</option>`
                    ).join('');

                    // Update manager dropdowns
                    const managerDropdowns = ['approvalManagerId'];
                    managerDropdowns.forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) elem.innerHTML = '<option value="">Select Manager</option>' + options;
                    });
                }
            } catch (error) {
                console.error('Error loading managers:', error);
            }
        },


        switchSection(section, element) {
            // Update buttons
            document.querySelectorAll('.sub-tab-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');

            // Update sections
            document.querySelectorAll('.rules-section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(`section-${section}`).classList.add('active');

            this.currentSection = section;

            // Load data for section
            switch (section) {
                case 'routing':
                    RoutingRulesManager.loadRules();
                    break;
                case 'approval':
                    ApprovalRulesManager.loadRules();
                    break;
                case 'level':
                    LevelRulesManager.loadRules();
                    break;
                case 'custom-fields':
                    CustomFieldsManager.loadFields();
                    break;
            }
        }
    };

    // ROUTING RULES MANAGER
    window.RoutingRulesManager = window.RoutingRulesManager || {
        baseUrl: '<?= base_url() ?>',
        init() {
            this.validator = new FormValidationManager(ValidationRulesRules.routingRule);
            this.validator.setupFormValidation([{
                    fieldId: 'routingItService',
                    fieldName: 'it_service_id'
                },
                {
                    fieldId: 'routingRuleName',
                    fieldName: 'rule_name',
                },
                {
                    fieldId: 'routingPriority',
                    fieldName: 'priority'
                },
                {
                    fieldId: 'routingAssignmentType',
                    fieldName: 'assignment_type'
                },
                {
                    fieldId: 'routingTargetTeam',
                    fieldName: 'target_team_id'
                },
            ]);
        },

        async loadRules() {
            try {
                const itServiceId = document.getElementById('filterRoutingItService')?.value || '';
                const status = document.getElementById('filterRoutingStatus')?.value || '';

                const response = await fetch(`${this.baseUrl}routing-rules?it_service_id=${itServiceId}&status=${status}`);
                const data = await response.json();

                if (data.success) {
                    this.renderRules(data.data);
                }
            } catch (error) {
                console.error('Error loading routing rules:', error);
            }
        },

        renderRules(rules) {
            const tbody = document.getElementById('routingRulesTableBody');
            if (rules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px;">No routing rules found</td></tr>';
                return;
            }

            tbody.innerHTML = rules.map((rule, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${rule.rule_name}</strong></td>
                <td>${rule.it_service_name}</td>
                <td><span class="badge badge-info">${rule.priority}</span></td>
                <td><span class="badge badge-warning">${rule.assignment_type}</span></td>
                <td>${rule.target_team_name || rule.target_user_name || '-'}</td>
                <td><span class="badge badge-${rule.status === 'active' ? 'success' : 'danger'}">${rule.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary-it-ticket" onclick="RoutingRulesManager.editRule(${rule.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="RoutingRulesManager.deleteRule(${rule.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
        },

        toggleTargetFields() {
            const assignmentType = document.getElementById('routingAssignmentType').value;
            const targetTeamGroup = document.getElementById('targetTeamGroup');
            const targetUserGroup = document.getElementById('targetUserGroup');

            if (assignmentType === 'team') {
                targetTeamGroup.style.display = 'block';
                targetUserGroup.style.display = 'none';
            } else if (assignmentType === 'user') {
                targetTeamGroup.style.display = 'none';
                targetUserGroup.style.display = 'block';
            }
        },

        openAddRule() {
            document.getElementById('modalRoutingTitle').textContent = 'Add Routing Rule';
            document.getElementById('routingId').value = '';
            document.getElementById('routingRuleName').value = '';
            document.getElementById('routingItService').value = '';
            document.getElementById('routingPriority').value = '100';
            document.getElementById('routingAssignmentType').value = 'team';
            document.getElementById('routingTargetTeam').value = '';
            document.getElementById('routingTargetUser').value = '';
            document.getElementById('routingConditions').value = '';
            document.getElementById('routingStatus').value = 'active';
            
            // Load dropdowns
            RulesTabManager.loadTeamsDropdowns();
            RulesTabManager.loadUsersDropdowns();
            
            // Show correct target field
            this.toggleTargetFields();
            
            this.validator.reset([
                'routingItService',
                'routingRuleName',
                'routingPriority',
                'routingAssignmentType',
                'routingTargetTeam',
            ]);
            document.getElementById('modalRoutingRule').classList.add('active');
        },

        async editRule(id) {
            try {
                // Load dropdowns first
                await Promise.all([
                    RulesTabManager.loadTeamsDropdowns(),
                    RulesTabManager.loadUsersDropdowns()
                ]);
                
                const response = await fetch(`${this.baseUrl}routing-rules/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const rule = data.data;
                    document.getElementById('modalRoutingTitle').textContent = 'Edit Routing Rule';
                    document.getElementById('routingId').value = rule.id;
                    document.getElementById('routingRuleName').value = rule.rule_name;
                    document.getElementById('routingItService').value = rule.it_service_id;
                    document.getElementById('routingPriority').value = rule.priority;
                    document.getElementById('routingAssignmentType').value = rule.assignment_type;
                    document.getElementById('routingTargetTeam').value = rule.target_team_id || '';
                    document.getElementById('routingTargetUser').value = rule.target_user_id || '';
                    document.getElementById('routingConditions').value = rule.conditions || '';
                    document.getElementById('routingStatus').value = rule.status;
                    
                    // Show correct target field
                    this.toggleTargetFields();
                    
                    document.getElementById('modalRoutingRule').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading routing rule');
            }
        },

        async saveRule() {
            const id = document.getElementById('routingId').value;
            const assignmentType = document.getElementById('routingAssignmentType').value;
            
            const formData = {
                it_service_id: document.getElementById('routingItService').value,
                rule_name: document.getElementById('routingRuleName').value,
                priority: document.getElementById('routingPriority').value,
                assignment_type: assignmentType,
                target_team_id: assignmentType === 'team' ? document.getElementById('routingTargetTeam').value || null : null,
                target_user_id: assignmentType === 'user' ? document.getElementById('routingTargetUser').value || null : null,
                conditions: document.getElementById('routingConditions').value || null,
                status: document.getElementById('routingStatus').value
            };

            
            const fieldMap = {
                it_service_id: 'routingItService',
                rule_name: 'routingRuleName',
                priority: 'routingPriority',
                assignment_type: 'routingAssignmentType',
                target_team_id: 'routingTargetTeam',
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            const url = id ? `${this.baseUrl}routing-rules/update/${id}` : `${this.baseUrl}routing-rules/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            if (data.success) {
                TicketNotifier.showSuccess('Routing rule saved');
                this.closeModal();
                this.loadRules();
            }
        },

        async deleteRule(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Delete this routing rule?', 'Yes, proceed');
            if (!confirmed) return;
            await fetch(`${this.baseUrl}routing-rules/delete/${id}`, {
                method: 'POST'
            });
            this.loadRules();
        },

        filterRules() {
            this.loadRules();
        },
        closeModal() {
                    this.validator.reset([
            'routingItService',
            'routingRuleName',
            'routingPriority',
            'routingAssignmentType',
            'routingTargetTeam',
        ]);
            document.getElementById('modalRoutingRule').classList.remove('active');
        }
    };

    // APPROVAL RULES MANAGER
    window.ApprovalRulesManager = window.ApprovalRulesManager || {
        baseUrl: '<?= base_url() ?>',

        init() {
            this.validator = new FormValidationManager(ValidationRulesRules.approvalRule);
            this.validator.setupFormValidation([{
                    fieldId: 'approvalItService',
                    fieldName: 'it_service_id'
                },
                {
                    fieldId: 'approvalRuleName',
                    fieldName: 'rule_name',
                },
                {
                    fieldId: 'approvalType',
                    fieldName: 'approval_type'
                },
            ]);
        },

        async loadRules() {
            const itServiceId = document.getElementById('filterApprovalItService')?.value || '';
            const response = await fetch(`${this.baseUrl}approval-rules?it_service_id=${itServiceId}`);
            const data = await response.json();
            if (data.success) this.renderRules(data.data);
        },

        renderRules(rules) {
            const tbody = document.getElementById('approvalRulesTableBody');
            if (rules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px;">No approval rules found</td></tr>';
                return;
            }

            tbody.innerHTML = rules.map((rule, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${rule.rule_name}</strong></td>
                <td>${rule.it_service_name}</td>
                <td><span class="badge badge-warning">${rule.approval_type}</span></td>
                <td>${rule.approver_user_id || rule.approver_role || '-'}</td>
                <td>${rule.auto_assign_after_approval ? 'Yes' : 'No'}</td>
                <td><span class="badge badge-${rule.status === 'active' ? 'success' : 'danger'}">${rule.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary-it-ticket" onclick="ApprovalRulesManager.editRule(${rule.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="ApprovalRulesManager.deleteRule(${rule.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
        },

        toggleApproverFields() {
            const approvalType = document.getElementById('approvalType').value;
            const approverManagerGroup = document.getElementById('approverManagerGroup');
            const approverUserGroup = document.getElementById('approverUserGroup');

            if (approvalType === 'manager') {
                approverManagerGroup.style.display = 'block';
                approverUserGroup.style.display = 'none';
            } else if (approvalType === 'specific_user') {
                approverManagerGroup.style.display = 'none';
                approverUserGroup.style.display = 'block';
            }
        },

        openAddRule() {
            document.getElementById('modalApprovalTitle').textContent = 'Add Approval Rule';
            document.getElementById('approvalId').value = '';
            document.getElementById('approvalItService').value = '';
            document.getElementById('approvalRuleName').value = '';
            document.getElementById('approvalRequired').value = '1';
            document.getElementById('approvalType').value = 'manager';
            document.getElementById('approvalManagerId').value = '';
            document.getElementById('approvalUserId').value = '';
            document.getElementById('approvalAutoAssign').value = '1';
            document.getElementById('approvalReminderHours').value = '24';
            document.getElementById('approvalStatus').value = 'active';
            
            // Load dropdowns
            RulesTabManager.loadManagersDropdowns();
            RulesTabManager.loadUsersDropdowns();
            
            // Reset validation
            this.validator.reset([
                'approvalItService',
                'approvalRuleName',
                'approvalType',
            ]);
            
            // Show correct approver field
            this.toggleApproverFields();
            
            document.getElementById('modalApprovalRule').classList.add('active');
        },

        async editRule(id) {
            try {
                // Load dropdowns first
                await Promise.all([
                    RulesTabManager.loadManagersDropdowns(),
                    RulesTabManager.loadUsersDropdowns()
                ]);
                
                const response = await fetch(`${this.baseUrl}approval-rules/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const rule = data.data;
                    document.getElementById('modalApprovalTitle').textContent = 'Edit Approval Rule';
                    document.getElementById('approvalId').value = rule.id;
                    document.getElementById('approvalItService').value = rule.it_service_id;
                    document.getElementById('approvalRuleName').value = rule.rule_name;
                    document.getElementById('approvalRequired').value = rule.requires_approval;
                    document.getElementById('approvalType').value = rule.approval_type;
                    
                    // Set approver based on type
                    if (rule.approval_type === 'manager') {
                        document.getElementById('approvalManagerId').value = rule.approver_user_id || '';
                    } else {
                        document.getElementById('approvalUserId').value = rule.approver_user_id || '';
                    }
                    
                    document.getElementById('approvalAutoAssign').value = rule.auto_assign_after_approval;
                    document.getElementById('approvalReminderHours').value = rule.reminder_hours;
                    document.getElementById('approvalStatus').value = rule.status;
                    
                    // Show correct approver field
                    this.toggleApproverFields();
                    
                    document.getElementById('modalApprovalRule').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading approval rule');
            }
        },

        async saveRule() {
            const id = document.getElementById('approvalId').value;
            const approvalType = document.getElementById('approvalType').value;
            
            // Get approver_user_id based on approval type
            let approverUserId = null;
            if (approvalType === 'manager') {
                approverUserId = document.getElementById('approvalManagerId').value || null;
            } else if (approvalType === 'specific_user') {
                approverUserId = document.getElementById('approvalUserId').value || null;
            }
            
            const formData = {
                it_service_id: document.getElementById('approvalItService').value,
                rule_name: document.getElementById('approvalRuleName').value,
                requires_approval: document.getElementById('approvalRequired').value,
                approval_type: approvalType,
                approver_user_id: approverUserId,
                auto_assign_after_approval: document.getElementById('approvalAutoAssign').value,
                reminder_hours: document.getElementById('approvalReminderHours').value,
                status: document.getElementById('approvalStatus').value
            };
            
            const fieldMap = {
                it_service_id: 'approvalItService',
                rule_name: 'approvalRuleName',
                approval_type: 'approvalType',
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                return;
            }

            const url = id ? `${this.baseUrl}approval-rules/update/${id}` : `${this.baseUrl}approval-rules/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if ((await response.json()).success) {
                TicketNotifier.showSuccess('Approval rule saved');
                this.closeModal();
                this.loadRules();
            }
        },

        async deleteRule(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Delete this approval rule?', 'Yes, proceed');
            if (!confirmed) return;
            await fetch(`${this.baseUrl}approval-rules/delete/${id}`, {
                method: 'POST'
            });
            this.loadRules();
        },

        filterRules() {
            this.loadRules();
        },
        closeModal() {
            this.validator.reset([
                'approvalItService',
                'approvalRuleName',
                'approvalType',
            ]);
            document.getElementById('modalApprovalRule').classList.remove('active');
        }
    };

    // LEVEL RULES MANAGER  
    const LevelRulesManager = {
        baseUrl: '<?= base_url() ?>',

        init() {
            this.validator = new FormValidationManager(ValidationRulesRules.levelRule);
            this.validator.setupFormValidation([{
                    fieldId: 'levelItService',
                    fieldName: 'it_service_id'
                },
                {
                    fieldId: 'levelNumber',
                    fieldName: 'level_number'
                },
                {
                    fieldId: 'levelName',
                    fieldName: 'level_name'
                },
                {
                    fieldId: 'levelSupportTeam',
                    fieldName: 'support_team_id'
                },
                {
                    fieldId: 'levelAutoEscalateHours',
                    fieldName: 'auto_escalate_hours'
                },
                {
                    fieldId: 'levelStatus',
                    fieldName: 'status'
                }
            ]);
        },

        async loadRules() {
            const itServiceId = document.getElementById('filterLevelItService')?.value || '';
            const response = await fetch(`${this.baseUrl}level-rules?it_service_id=${itServiceId}`);
            const data = await response.json();
            if (data.success) this.renderRules(data.data);
        },

        renderRules(rules) {
            const tbody = document.getElementById('levelRulesTableBody');
            if (rules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px;">No level rules found</td></tr>';
                return;
            }

            tbody.innerHTML = rules.map((rule, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${rule.it_service_name}</td>
                <td><span class="level-badge l${rule.level_number}">L${rule.level_number}</span></td>
                <td><strong>${rule.level_name}</strong></td>
                <td>${rule.support_team_name || '-'}</td>
                <td>${rule.auto_escalate_hours ? rule.auto_escalate_hours + 'h' : '-'}</td>
                <td><span class="badge badge-${rule.status === 'active' ? 'success' : 'danger'}">${rule.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary-it-ticket" onclick="LevelRulesManager.editRule(${rule.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="LevelRulesManager.deleteRule(${rule.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
        },

        openAddRule() {
            document.getElementById('modalLevelTitle').textContent = 'Add Level Rule';
            document.getElementById('levelId').value = '';
            document.getElementById('levelItService').value = '';
            document.getElementById('levelNumber').value = '';
            document.getElementById('levelName').value = '';
            document.getElementById('levelSupportTeam').value = '';
            document.getElementById('levelAutoEscalateHours').value = '';
            document.getElementById('levelAssignmentType').value = 'team';
            document.getElementById('levelRequiresReason').value = '1';
            document.getElementById('levelStatus').value = 'active';
            
            this.validator.reset([
                'levelItService',
                'levelNumber',
                'levelName',
                'levelSupportTeam',
                'levelAutoEscalateHours',
                'levelStatus',
            ]);
            
            document.getElementById('modalLevelRule').classList.add('active');
        },

        async editRule(id) {
            try {
                const response = await fetch(`${this.baseUrl}level-rules/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const rule = data.data;
                    document.getElementById('modalLevelTitle').textContent = 'Edit Level Rule';
                    document.getElementById('levelId').value = rule.id;
                    document.getElementById('levelItService').value = rule.it_service_id;
                    document.getElementById('levelNumber').value = rule.level_number;
                    document.getElementById('levelName').value = rule.level_name;
                    document.getElementById('levelSupportTeam').value = rule.support_team_id || '';
                    document.getElementById('levelAutoEscalateHours').value = rule.auto_escalate_hours || '';
                    document.getElementById('levelAssignmentType').value = rule.assignment_type;
                    document.getElementById('levelRequiresReason').value = rule.requires_escalation_reason;
                    document.getElementById('levelStatus').value = rule.status;
                    document.getElementById('modalLevelRule').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading level rule');
            }
        },

        async saveRule() {
            const id = document.getElementById('levelId').value;
            const formData = {
                it_service_id: document.getElementById('levelItService').value,
                level_number: document.getElementById('levelNumber').value,
                level_name: document.getElementById('levelName').value,
                support_team_id: document.getElementById('levelSupportTeam').value || null,
                auto_escalate_hours: document.getElementById('levelAutoEscalateHours').value || null,
                assignment_type: document.getElementById('levelAssignmentType').value,
                requires_escalation_reason: document.getElementById('levelRequiresReason').value,
                status: document.getElementById('levelStatus').value
            };
            
            const fieldMap = {
                it_service_id: 'levelItService',
                level_number: 'levelNumber',
                level_name: 'levelName',
                support_team_id: 'levelSupportTeam',
                auto_escalate_hours: 'levelAutoEscalateHours',
                status: 'levelStatus'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                return;
            }

            const url = id ? `${this.baseUrl}level-rules/update/${id}` : `${this.baseUrl}level-rules/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if ((await response.json()).success) {
                TicketNotifier.showSuccess('Level rule saved');
                this.closeModal();
                this.loadRules();
            }
        },

        async deleteRule(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Delete this level rule?', 'Yes, proceed');
            if (!confirmed) return;
            await fetch(`${this.baseUrl}level-rules/delete/${id}`, {
                method: 'POST'
            });
            this.loadRules();
        },

        filterRules() {
            this.loadRules();
        },
        closeModal() {
            this.validator.reset([
                'levelItService',
                'levelNumber',
                'levelName',
                'levelSupportTeam',
                'levelAutoEscalateHours',
                'levelStatus',
            ]);
            document.getElementById('modalLevelRule').classList.remove('active');
        }
    };

    // CUSTOM FIELDS MANAGER
    window.CustomFieldsManager = window.CustomFieldsManager || {
        baseUrl: '<?= base_url() ?>',

        init() {
            this.validator = new FormValidationManager(ValidationRulesRules.customField);
            this.validator.setupFormValidation([{
                    fieldId: 'fieldItService',
                    fieldName: 'it_service_id'
                },
                {
                    fieldId: 'fieldName',
                    fieldName: 'field_name'
                },
                {
                    fieldId: 'fieldLabel',
                    fieldName: 'field_label'
                },
                {
                    fieldId: 'fieldType',
                    fieldName: 'field_type'
                },
                {
                    fieldId: 'fieldOptions',
                    fieldName: 'field_options'
                },
                {
                    fieldId: 'fieldStatus',
                    fieldName: 'status'
                }
            ]);
        },

        async loadFields() {
            const itServiceId = document.getElementById('filterFieldItService')?.value || '';
            const response = await fetch(`${this.baseUrl}custom-fields?it_service_id=${itServiceId}`);
            const data = await response.json();
            if (data.success) this.renderFields(data.data);
        },

        renderFields(fields) {
            const tbody = document.getElementById('customFieldsTableBody');
            if (fields.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px;">No custom fields found</td></tr>';
                return;
            }

            tbody.innerHTML = fields.map((field, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${field.field_label}</strong><br><small style="color: #6b7280;">${field.field_name}</small></td>
                <td>${field.it_service_name}</td>
                <td><span class="badge badge-info">${field.field_type}</span></td>
                <td>${field.is_required ? '<i class="fas fa-check text-success"></i>' : '-'}</td>
                <td>${field.sort_order}</td>
                <td><span class="badge badge-${field.status === 'active' ? 'success' : 'danger'}">${field.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary-it-ticket" onclick="CustomFieldsManager.editField(${field.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="CustomFieldsManager.deleteField(${field.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
        },

        openAddField() {
            document.getElementById('modalFieldTitle').textContent = 'Add Custom Field';
            document.getElementById('fieldId').value = '';
            document.getElementById('fieldItService').value = '';
            document.getElementById('fieldName').value = '';
            document.getElementById('fieldLabel').value = '';
            document.getElementById('fieldType').value = 'text';
            document.getElementById('fieldOptions').value = '';
            document.getElementById('fieldRequired').value = '0';
            document.getElementById('fieldSortOrder').value = '0';
            document.getElementById('fieldDefaultValue').value = '';
            document.getElementById('fieldPlaceholder').value = '';
            document.getElementById('fieldHelpText').value = '';
            document.getElementById('fieldStatus').value = 'active';
            
            this.validator.reset([
                'fieldItService',
                'fieldName',
                'fieldLabel',
                'fieldType',
                'fieldOptions',
                'fieldStatus',
            ]);
            
            document.getElementById('modalCustomField').classList.add('active');
        },

        async editField(id) {
            try {
                const response = await fetch(`${this.baseUrl}custom-fields/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const field = data.data;
                    document.getElementById('modalFieldTitle').textContent = 'Edit Custom Field';
                    document.getElementById('fieldId').value = field.id;
                    document.getElementById('fieldItService').value = field.it_service_id;
                    document.getElementById('fieldName').value = field.field_name;
                    document.getElementById('fieldLabel').value = field.field_label;
                    document.getElementById('fieldType').value = field.field_type;
                    document.getElementById('fieldOptions').value = field.field_options || '';
                    document.getElementById('fieldRequired').value = field.is_required;
                    document.getElementById('fieldSortOrder').value = field.sort_order;
                    document.getElementById('fieldDefaultValue').value = field.default_value || '';
                    document.getElementById('fieldPlaceholder').value = field.placeholder || '';
                    document.getElementById('fieldHelpText').value = field.help_text || '';
                    document.getElementById('fieldStatus').value = field.status;
                    document.getElementById('modalCustomField').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading custom field');
            }
        },

        async saveField() {
            const id = document.getElementById('fieldId').value;
            const formData = {
                it_service_id: document.getElementById('fieldItService').value,
                field_name: document.getElementById('fieldName').value,
                field_label: document.getElementById('fieldLabel').value,
                field_type: document.getElementById('fieldType').value,
                field_options: document.getElementById('fieldOptions').value || null,
                is_required: document.getElementById('fieldRequired').value,
                default_value: document.getElementById('fieldDefaultValue').value || null,
                placeholder: document.getElementById('fieldPlaceholder').value || null,
                help_text: document.getElementById('fieldHelpText').value || null,
                sort_order: document.getElementById('fieldSortOrder').value,
                status: document.getElementById('fieldStatus').value
            };
            
            const fieldMap = {
                it_service_id: 'fieldItService',
                field_name: 'fieldName',
                field_label: 'fieldLabel',
                field_type: 'fieldType',
                field_options: 'fieldOptions',
                status: 'fieldStatus'
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                return;
            }

            const url = id ? `${this.baseUrl}custom-fields/update/${id}` : `${this.baseUrl}custom-fields/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if ((await response.json()).success) {
                TicketNotifier.showSuccess('Custom field saved');
                this.closeModal();
                this.loadFields();
            }
        },

        async deleteField(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Delete this custom field?', 'Yes, proceed');
            if (!confirmed) return;
            await fetch(`${this.baseUrl}custom-fields/delete/${id}`, {
                method: 'POST'
            });
            this.loadFields();
        },

        filterFields() {
            this.loadFields();
        },
        closeModal() {
            this.validator.reset([
                'fieldItService',
                'fieldName',
                'fieldLabel',
                'fieldType',
                'fieldOptions',
                'fieldStatus',
            ]);
            document.getElementById('modalCustomField').classList.remove('active');
        }
    };

    // ==================== RULES SMART UX MANAGER ====================
    const RulesSmartUX = {
        baseUrl: '<?= base_url("") ?>',

        async checkPrerequisites() {
            try {
                // Check IT Service
                const itResponse = await fetch(`${this.baseUrl}it-services/all-group-type`);
                const itData = await itResponse.json();
                const itCount = itData.data ? itData.data.length : 0;

                // Check Support Teams
                const teamResponse = await fetch(`${this.baseUrl}support-teams`);
                const teamData = await teamResponse.json();
                const teamCount = teamData.data ? teamData.data.length : 0;

                // Update progress indicator
                this.updateProgress(itCount, teamCount);

                // Update button states
                this.updateButtonStates(itCount, teamCount);

                console.log(`Rules Smart UX: IT=${itCount}, Teams=${teamCount}`);
            } catch (error) {
                console.error('Rules Smart UX Error:', error);
            }
        },

        updateProgress(itCount, teamCount) {
            // IT Service step
            const itStep = document.getElementById('rules-step-it-services');
            if (itStep) {
                itStep.querySelector('.step-count').textContent = itCount;
                if (itCount > 0) {
                    itStep.querySelector('.step-status').textContent = '✓ Ready';
                    itStep.style.opacity = '1';
                    itStep.style.background = 'rgba(16, 185, 129, 0.3)';
                }
            }

            // Support Teams step
            const teamStep = document.getElementById('rules-step-teams');
            if (teamStep) {
                teamStep.querySelector('.step-count').textContent = teamCount;
                if (teamCount > 0) {
                    teamStep.querySelector('.step-status').textContent = '✓ Ready';
                    teamStep.style.opacity = '1';
                    teamStep.style.background = 'rgba(16, 185, 129, 0.3)';
                }
            }

            // Ready step
            const readyStep = document.getElementById('rules-step-ready');
            if (readyStep && itCount > 0) {
                readyStep.style.opacity = '1';
                readyStep.style.background = 'rgba(59, 130, 246, 0.3)';
                readyStep.querySelector('.step-status').textContent = '→ Create Rules';
            }
        },

        updateButtonStates(itCount, teamCount) {
            // Approval Rules button (needs IT Service)
            const btnApproval = document.getElementById('btnAddApprovalRule');
            const hintApproval = document.getElementById('hintApprovalRule');
            if (btnApproval && hintApproval) {
                if (itCount > 0) {
                    btnApproval.disabled = false;
                    btnApproval.style.opacity = '1';
                    btnApproval.style.cursor = 'pointer';
                    hintApproval.style.display = 'none';
                } else {
                    btnApproval.disabled = true;
                    btnApproval.style.opacity = '0.5';
                    btnApproval.style.cursor = 'not-allowed';
                    hintApproval.style.display = 'block';
                }
            }

            // Level Rules button (needs IT Service + Support Teams)
            const btnLevel = document.getElementById('btnAddLevelRule');
            const hintLevel = document.getElementById('hintLevelRule');
            if (btnLevel && hintLevel) {
                if (itCount > 0 && teamCount > 0) {
                    btnLevel.disabled = false;
                    btnLevel.style.opacity = '1';
                    btnLevel.style.cursor = 'pointer';
                    hintLevel.style.display = 'none';
                } else {
                    btnLevel.disabled = true;
                    btnLevel.style.opacity = '0.5';
                    btnLevel.style.cursor = 'not-allowed';
                    hintLevel.style.display = 'block';
                }
            }

            // Custom Fields button (needs IT Service)
            const btnCustom = document.getElementById('btnAddCustomField');
            const hintCustom = document.getElementById('hintCustomField');
            if (btnCustom && hintCustom) {
                if (itCount > 0) {
                    btnCustom.disabled = false;
                    btnCustom.style.opacity = '1';
                    btnCustom.style.cursor = 'pointer';
                    hintCustom.style.display = 'none';
                } else {
                    btnCustom.disabled = true;
                    btnCustom.style.opacity = '0.5';
                    btnCustom.style.cursor = 'not-allowed';
                    hintCustom.style.display = 'block';
                }
            }
        }
    };

    // Refresh Rules Smart UX after any CRUD operation
    function refreshRulesSmartUX() {
        RulesSmartUX.checkPrerequisites();
    }

    // TAB INITIALIZATION
    async function initRulesTab() {
        console.log('Initializing Rules Tab...');

        showTabLoading('tab-rules');

        try {
            // Wait for all data to load
            await Promise.all([
                RulesSmartUX.checkPrerequisites(),
                RulesTabManager.loadItServicesDropdowns(),
                RulesTabManager.loadTeamsDropdowns(),
                RoutingRulesManager.init(),
                ApprovalRulesManager.init(),
                LevelRulesManager.init(),
                CustomFieldsManager.init(),
            ]);

            // Activate first section
            const firstSection = document.getElementById('section-routing');
            const firstButton = document.querySelector('.sub-tab-btn[data-section="routing"]');

            if (firstSection && firstButton) {
                document.querySelectorAll('.rules-section').forEach(sec => sec.classList.remove('active'));
                firstSection.classList.add('active');
                document.querySelectorAll('.sub-tab-btn').forEach(btn => btn.classList.remove('active'));
                firstButton.classList.add('active');
            }

            await RoutingRulesManager.loadRules();
            console.log('✅ Rules Tab fully loaded');
        } catch (error) {
            console.error('Error initializing Rules Tab:', error);
        } finally {
            hideTabLoading('tab-rules');
        }
    }

    function cleanupRulesTab() {
        console.log('Cleaning up Rules Tab...');
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