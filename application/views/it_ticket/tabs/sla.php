<div id="tab-sla" class="tab-content active">

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
    <div class="setup-progress-container" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-clock"></i> SLA Configuration Progress
            <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Create IT Service first in Service Structure tab</span>
        </h4>
        <div class="progress-steps" style="display: flex; gap: 15px;">
            <div id="sla-step-it_services" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">IT Service</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">🔒 Required</div>
            </div>

            <div id="sla-step-policies" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">SLA Policies</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Create policies</div>
            </div>

            <div id="sla-step-mappings" class="progress-step" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                    <i class="fas fa-link"></i>
                </div>
                <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Mappings</div>
                <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                <div class="step-status" style="font-size: 12px; opacity: 0.8;">Link to it services</div>
            </div>
        </div>
    </div>

    <!-- SLA Policies Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> SLA Policies</h3>
            <div>
                <button id="btnAddSLAPolicy" class="btn btn-primary-it-ticket" disabled
                    onclick="SLAManager.openAddPolicy()"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plus"></i> Add SLA Policy
                </button>
                <small id="hintSLAPolicy" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Create IT Service first (Service Structure tab)
                </small>
            </div>
        </div>

        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div>
                <select id="filterSLAPriority" onchange="SLAManager.filterPolicies()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 200px;">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <select id="filterSLAStatus" onchange="SLAManager.filterPolicies()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- SLA Cards Grid -->
        <div id="slaCardsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="text-align: center; padding: 40px; grid-column: 1/-1;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                <p style="margin-top: 10px; color: #6b7280;">Loading SLA policies...</p>
            </div>
        </div>
    </div>

    <!-- IT Service SLA Mapping Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-link"></i> IT Service SLA Mapping</h3>
            <div>
                <button id="btnAddSLAMapping" class="btn btn-primary-it-ticket" disabled
                    onclick="SLAMappingManager.openAddMapping()"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plus"></i> Add Mapping
                </button>
                <small id="hintSLAMapping" class="help-text" style="display: block; margin-top: 8px; color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Create SLA Policies first
                </small>
            </div>
        </div>

        <div class="filter-section">
            <select id="filterMappingItService" onchange="SLAMappingManager.filterMappings()"
                style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">
                <option value="">All IT Service</option>
            </select>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>IT Service</th>
                    <th>SLA Policy</th>
                    <th>Priority</th>
                    <th>Response Time</th>
                    <th>Resolution Time</th>
                    <th>Status</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="slaMappingTableBody">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODALS -->

<!-- Modal: SLA Policy -->
<div id="modalSLAPolicy" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalSLATitle">Add SLA Policy</h3>
            <button class="modal-close" onclick="SLAManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="slaId">

            <div class="form-group">
                <label>Policy Name <span class="required">*</span></label>
                <input type="text" id="slaName" placeholder="e.g., Critical SLA, High Priority SLA">
            </div>

            <div class="form-group">
                <label>Priority <span class="required">*</span></label>
                <select id="slaPriority">
                    <option value="">Select Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>First Response (Hours) <span class="required">*</span></label>
                    <input type="number" id="slaFirstResponse" placeholder="e.g., 1, 2, 4" min="0">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">
                        Time to first response to ticket
                    </small>
                </div>

                <div class="form-group">
                    <label>Resolution Time (Hours) <span class="required">*</span></label>
                    <input type="number" id="slaResolution" placeholder="e.g., 4, 8, 24" min="1">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">
                        Time to resolve ticket
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label>Business Hours Only <span class="required">*</span></label>
                <select id="slaBusinessHours">
                    <option value="0">No - 24/7 SLA</option>
                    <option value="1">Yes - Business hours only (8AM-6PM)</option>
                </select>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    If enabled, SLA countdown pauses outside business hours
                </small>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="slaDescription" placeholder="Enter policy description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="slaStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="SLAManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="SLAManager.savePolicy()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- Modal: SLA Mapping -->
<div id="modalSLAMapping" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalMappingTitle">Add SLA Mapping</h3>
            <button class="modal-close" onclick="SLAMappingManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mappingId">

            <div class="form-group">
                <label>IT Service <span class="required">*</span></label>
                <select id="mappingItService">
                    <option value="">Select IT Service</option>
                </select>
            </div>

            <div class="form-group">
                <label>SLA Policy <span class="required">*</span></label>
                <select id="mappingSLAPolicy">
                    <option value="">Select SLA Policy</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="mappingStatus">
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
                            Each IT Service can only have ONE active SLA policy. If you assign a new policy,
                            the previous one will be automatically deactivated.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="SLAMappingManager.closeModal()">Cancel</button>
            <button class="btn btn-primary-it-ticket" onclick="SLAMappingManager.saveMapping()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<style>
    /* SLA Cards */
    .sla-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        border-left: 4px solid;
        position: relative;
    }

    .sla-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .sla-card.priority-low {
        border-left-color: #10b981;
    }

    .sla-card.priority-medium {
        border-left-color: #3b82f6;
    }

    .sla-card.priority-high {
        border-left-color: #f59e0b;
    }

    .sla-card.priority-critical {
        border-left-color: #ef4444;
    }

    .sla-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .sla-card-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
    }

    .sla-card-priority {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .priority-low {
        background: #d1fae5;
        color: #065f46;
    }

    .priority-medium {
        background: #dbeafe;
        color: #1e40af;
    }

    .priority-high {
        background: #fef3c7;
        color: #92400e;
    }

    .priority-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    .sla-card-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin: 15px 0;
    }

    .sla-stat {
        text-align: center;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .sla-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
    }

    .sla-stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sla-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #f3f4f6;
    }

    .sla-card-actions {
        display: flex;
        gap: 5px;
    }

    .form-row .form-group {
        margin-bottom: 20px;
    }
</style>

<script>
    // SLA MANAGER
    const SLAManager = {
        baseUrl: '<?= base_url() ?>',
        filterPriority: '',
        filterStatus: '',

        init() {
            this.validator = new FormValidationManager(ValidationRulesSLA.slaPolicy);

            this.validator.setupFormValidation([{
                    fieldId: 'slaName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'slaPriority',
                    fieldName: 'priority',
                },
                {
                    fieldId: 'slaFirstResponse',
                    fieldName: 'first_response_hours'
                },
                {
                    fieldId: 'slaResolution',
                    fieldName: 'resolution_hours'
                },
                {
                    fieldId: 'slaBusinessHours',
                    fieldName: 'business_hours_only'
                },
                {
                    fieldId: 'slaDescription',
                    fieldName: 'description'
                },
            ]);
        },

        async loadPolicies() {
            try {
                const params = new URLSearchParams({
                    priority: this.filterPriority,
                    status: this.filterStatus
                });

                const response = await fetch(`${this.baseUrl}sla-policies?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.renderPoliciesCards(data.data);
                }
            } catch (error) {
                console.error('Error loading SLA policies:', error);
            }
        },

        renderPoliciesCards(policies) {
            const container = document.getElementById('slaCardsGrid');

            if (policies.length === 0) {
                container.innerHTML = `
                <div style="text-align: center; padding: 40px; grid-column: 1/-1; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                    <p style="margin-top: 10px;">No SLA policies found</p>
                </div>
            `;
                return;
            }

            container.innerHTML = policies.map(policy => `
            <div class="sla-card priority-${policy.priority}">
                <div class="sla-card-header">
                    <div>
                        <div class="sla-card-title">${policy.name}</div>
                        <span class="sla-card-priority priority-${policy.priority}">${policy.priority}</span>
                    </div>
                    <div>
                        <span class="badge badge-${policy.status === 'active' ? 'success' : 'danger'}">${policy.status}</span>
                    </div>
                </div>

                <div class="sla-card-stats">
                    <div class="sla-stat">
                        <div class="sla-stat-value">${policy.first_response_hours || '-'}</div>
                        <div class="sla-stat-label">First Response (h)</div>
                    </div>
                    <div class="sla-stat">
                        <div class="sla-stat-value">${policy.resolution_hours}</div>
                        <div class="sla-stat-label">Resolution (h)</div>
                    </div>
                </div>

                ${policy.description ? `<p style="font-size: 13px; color: #6b7280; margin: 10px 0;">${policy.description}</p>` : ''}

                <div style="display: flex; align-items: center; gap: 5px; margin: 10px 0; font-size: 12px; color: #6b7280;">
                    <i class="fas fa-${policy.business_hours_only ? 'briefcase' : 'clock'}"></i>
                    <span>${policy.business_hours_only ? 'Business hours only' : '24/7 SLA'}</span>
                </div>

                <div class="sla-card-footer">
                    <div style="font-size: 12px; color: #9ca3af;">
                        <i class="fas fa-ticket"></i> ${policy.it_services_count || 0} IT Service
                    </div>
                    <div class="sla-card-actions">
                        <button class="btn btn-sm btn-primary" onclick="SLAManager.editPolicy(${policy.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="SLAManager.deletePolicy(${policy.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        },

        openAddPolicy() {
            document.getElementById('modalSLATitle').textContent = 'Add SLA Policy';
            document.getElementById('slaId').value = '';
            document.getElementById('slaName').value = '';
            document.getElementById('slaPriority').value = '';
            document.getElementById('slaFirstResponse').value = '';
            document.getElementById('slaResolution').value = '';
            document.getElementById('slaBusinessHours').value = '0';
            document.getElementById('slaDescription').value = '';
            document.getElementById('slaStatus').value = 'active';

            this.validator.reset([
                'slaName',
                'slaPriority',
                'slaFirstResponse',
                'slaResolution',
                'slaBusinessHours',
                'slaDescription'
            ]);

            document.getElementById('modalSLAPolicy').classList.add('active');
        },

        async editPolicy(id) {
            try {
                const response = await fetch(`${this.baseUrl}sla-policies/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const policy = data.data;
                    document.getElementById('modalSLATitle').textContent = 'Edit SLA Policy';
                    document.getElementById('slaId').value = policy.id;
                    document.getElementById('slaName').value = policy.name;
                    document.getElementById('slaPriority').value = policy.priority;
                    document.getElementById('slaFirstResponse').value = policy.first_response_hours || '';
                    document.getElementById('slaResolution').value = policy.resolution_hours;
                    document.getElementById('slaBusinessHours').value = policy.business_hours_only;
                    document.getElementById('slaDescription').value = policy.description || '';
                    document.getElementById('slaStatus').value = policy.status;
                    document.getElementById('modalSLAPolicy').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading SLA policy');
            }
        },

        async savePolicy() {
            const id = document.getElementById('slaId').value;
            const formData = {
                name: document.getElementById('slaName').value,
                priority: document.getElementById('slaPriority').value,
                first_response_hours: document.getElementById('slaFirstResponse').value || null,
                resolution_hours: document.getElementById('slaResolution').value,
                business_hours_only: document.getElementById('slaBusinessHours').value,
                description: document.getElementById('slaDescription').value,
                status: document.getElementById('slaStatus').value
            };

            const fieldMap = {
                name: 'slaName',
                priority: 'slaPriority',
                first_response_hours: 'slaFirstResponse',
                resolution_hours: 'slaResolution',
                business_hours_only: 'slaBusinessHours',
                description: 'slaDescription',
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}sla-policies/update/${id}` : `${this.baseUrl}sla-policies/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'SLA Policy updated successfully' : 'SLA Policy created successfully');
                    this.closeModal();
                    this.loadPolicies();
                    SLAMappingManager.loadSLAPoliciesDropdown();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving SLA policy');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving SLA policy');
            }
        },

        async deletePolicy(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this SLA policy?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}sla-policies/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('SLA Policy deleted successfully', () => this.loadPolicies());
                } else {
                    TicketNotifier.showError(data.message || 'Error delete sla');
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting SLA policy');
            }
        },

        filterPolicies() {
            this.filterPriority = document.getElementById('filterSLAPriority').value;
            this.filterStatus = document.getElementById('filterSLAStatus').value;
            this.loadPolicies();
        },

        closeModal() {
            this.validator.reset([
                'slaName',
                'slaPriority',
                'slaFirstResponse',
                'slaResolution',
                'slaBusinessHours',
                'slaDescription'
            ]);

            document.getElementById('modalSLAPolicy').classList.remove('active');
        }
    };

    // SLA MAPPING MANAGER
    const SLAMappingManager = {
        baseUrl: '<?= base_url() ?>',
        filterItService: '',

        init() {
            this.validator = new FormValidationManager(ValidationRulesSLA.slaMapping);

            this.validator.setupFormValidation([{
                    fieldId: 'mappingItService',
                    fieldName: 'it_service_id'
                },
                {
                    fieldId: 'mappingSLAPolicy',
                    fieldName: 'sla_policy_id',
                },
            ]);
        },

        async loadMappings() {
            try {
                const params = new URLSearchParams({
                    it_service_id: this.filterItService
                });

                const response = await fetch(`${this.baseUrl}it-service-sla?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.renderMappings(data.data);
                }
            } catch (error) {
                console.error('Error loading SLA mappings:', error);
            }
        },

        renderMappings(mappings) {
            const tbody = document.getElementById('slaMappingTableBody');

            if (mappings.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No SLA mappings found</p>
                    </td>
                </tr>
            `;
                return;
            }

            tbody.innerHTML = mappings.map((mapping, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${mapping.it_service_name}</strong></td>
                <td>${mapping.sla_policy_name}</td>
                <td><span class="priority-${mapping.priority}">${mapping.priority.toUpperCase()}</span></td>
                <td>${mapping.first_response_hours ? mapping.first_response_hours + 'h' : '-'}</td>
                <td><strong>${mapping.resolution_hours}h</strong></td>
                <td><span class="badge badge-${mapping.is_active ? 'success' : 'danger'}">${mapping.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="SLAMappingManager.editMapping(${mapping.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="SLAMappingManager.deleteMapping(${mapping.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        },

        openAddMapping() {
            document.getElementById('modalMappingTitle').textContent = 'Add SLA Mapping';
            document.getElementById('mappingId').value = '';
            document.getElementById('mappingItService').value = '';
            document.getElementById('mappingSLAPolicy').value = '';
            document.getElementById('mappingStatus').value = '1';

            this.validator.reset([
                'mappingItService',
                'mappingSLAPolicy',
            ]);
            document.getElementById('modalSLAMapping').classList.add('active');
        },

        async editMapping(id) {
            try {
                const response = await fetch(`${this.baseUrl}it-service-sla/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const mapping = data.data;
                    document.getElementById('modalMappingTitle').textContent = 'Edit SLA Mapping';
                    document.getElementById('mappingId').value = mapping.id;
                    document.getElementById('mappingItService').value = mapping.it_service_id;
                    document.getElementById('mappingSLAPolicy').value = mapping.sla_policy_id;
                    document.getElementById('mappingStatus').value = mapping.is_active ? '1' : '0';
                    document.getElementById('modalSLAMapping').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading SLA mapping');
            }
        },

        async saveMapping() {
            const id = document.getElementById('mappingId').value;
            const formData = {
                it_service_id: document.getElementById('mappingItService').value,
                sla_policy_id: document.getElementById('mappingSLAPolicy').value,
                is_active: document.getElementById('mappingStatus').value === '1' ? 1 : 0
            };

            const fieldMap = {
                it_service_id: 'mappingItService',
                sla_policy_id: 'mappingSLAPolicy',
            };

            if (!this.validator.validateAndShowErrors(formData, fieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}it-service-sla/update/${id}` : `${this.baseUrl}it-service-sla/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'SLA Mapping updated successfully' : 'SLA Mapping created successfully');
                    this.closeModal();
                    this.loadMappings();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving SLA mapping');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving SLA mapping');
            }
        },

        async deleteMapping(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this SLA mapping?', 'Yes, proceed');
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}it-service-sla/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('SLA Mapping deleted successfully', () => this.loadMappings());
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting SLA mapping');
            }
        },

        filterMappings() {
            this.filterItService = document.getElementById('filterMappingItService').value;
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

                    document.getElementById('filterMappingItService').innerHTML =
                        '<option value="">All IT Service</option>' + options;
                    document.getElementById('mappingItService').innerHTML =
                        '<option value="">Select IT Service</option>' + options;
                }
            } catch (error) {
                console.error('Error loading it service:', error);
            }
        },

        async loadSLAPoliciesDropdown() {
            try {
                const response = await fetch(`${this.baseUrl}sla-policies/all`);
                const data = await response.json();

                if (data.success) {
                    const options = data.data.map(policy =>
                        `<option value="${policy.id}">${policy.name} (${policy.priority})</option>`
                    ).join('');

                    document.getElementById('mappingSLAPolicy').innerHTML =
                        '<option value="">Select SLA Policy</option>' + options;
                }
            } catch (error) {
                console.error('Error loading SLA policies:', error);
            }
        },

        closeModal() {
            this.validator.reset([
                'mappingItService',
                'mappingSLAPolicy',
            ]);
            document.getElementById('modalSLAMapping').classList.remove('active');
        }
    };

    // ==================== SLA SMART UX MANAGER ====================
    const SLASmartUX = {
        baseUrl: '<?= base_url() ?>',

        async checkPrerequisites() {
            try {
                // Check IT Service
                const itResponse = await fetch(`${this.baseUrl}it-services/all-group-type`);
                const itData = await itResponse.json();
                const itCount = itData.data ? itData.data.length : 0;

                // Check SLA Policies
                const slaResponse = await fetch(`${this.baseUrl}sla-policies`);
                const slaData = await slaResponse.json();
                const slaCount = slaData.data ? slaData.data.length : 0;

                // Check Mappings
                const mapResponse = await fetch(`${this.baseUrl}it-service-sla`);
                const mapData = await mapResponse.json();
                const mapCount = mapData.data ? mapData.data.length : 0;

                // Update progress indicator
                this.updateProgress(itCount, slaCount, mapCount);

                // Update button states
                this.updateButtonStates(itCount, slaCount);

                console.log(`SLA Smart UX: IT=${itCount}, Policies=${slaCount}, Mappings=${mapCount}`);
            } catch (error) {
                console.error('SLA Smart UX Error:', error);
            }
        },

        updateProgress(itCount, slaCount, mapCount) {
            // IT Service step
            const itStep = document.getElementById('sla-step-it_services');
            if (itStep) {
                itStep.querySelector('.step-count').textContent = itCount;
                if (itCount > 0) {
                    itStep.querySelector('.step-status').textContent = '✓ Ready';
                    itStep.style.opacity = '1';
                    itStep.style.background = 'rgba(16, 185, 129, 0.3)';
                }
            }

            // SLA Policies step
            const slaStep = document.getElementById('sla-step-policies');
            if (slaStep && itCount > 0) {
                slaStep.querySelector('.step-count').textContent = slaCount;
                slaStep.style.opacity = '1';
                if (slaCount > 0) {
                    slaStep.querySelector('.step-status').textContent = '✓ Completed';
                    slaStep.style.background = 'rgba(16, 185, 129, 0.3)';
                } else {
                    slaStep.querySelector('.step-status').textContent = '→ Create now';
                    slaStep.style.background = 'rgba(59, 130, 246, 0.3)';
                }
            }

            // Mappings step
            const mapStep = document.getElementById('sla-step-mappings');
            if (mapStep && slaCount > 0) {
                mapStep.querySelector('.step-count').textContent = mapCount;
                mapStep.style.opacity = '1';
                if (mapCount > 0) {
                    mapStep.querySelector('.step-status').textContent = '✓ Completed';
                    mapStep.style.background = 'rgba(16, 185, 129, 0.3)';
                } else {
                    mapStep.querySelector('.step-status').textContent = '→ Link now';
                    mapStep.style.background = 'rgba(59, 130, 246, 0.3)';
                }
            }
        },

        updateButtonStates(itCount, slaCount) {
            // Add SLA Policy button
            const btnPolicy = document.getElementById('btnAddSLAPolicy');
            const hintPolicy = document.getElementById('hintSLAPolicy');

            if (btnPolicy && hintPolicy) {
                if (itCount > 0) {
                    btnPolicy.disabled = false;
                    btnPolicy.style.opacity = '1';
                    btnPolicy.style.cursor = 'pointer';
                    hintPolicy.style.display = 'none';
                } else {
                    btnPolicy.disabled = true;
                    btnPolicy.style.opacity = '0.5';
                    btnPolicy.style.cursor = 'not-allowed';
                    hintPolicy.style.display = 'block';
                }
            }

            // Add Mapping button
            const btnMapping = document.getElementById('btnAddSLAMapping');
            const hintMapping = document.getElementById('hintSLAMapping');

            if (btnMapping && hintMapping) {
                if (slaCount > 0) {
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

    // Refresh SLA Smart UX after any CRUD operation
    function refreshSLASmartUX() {
        SLASmartUX.checkPrerequisites();
    }


    async function initSlaTab() {
        console.log('Initializing SLA Tab...');
        showTabLoading('tab-sla');

        try {
            await Promise.all([
                SLASmartUX.checkPrerequisites(),
                SLAManager.init(),
                SLAManager.loadPolicies(),
                SLAMappingManager.init(),
                SLAMappingManager.loadMappings(),
                SLAMappingManager.loadItServicesDropdown(),
                SLAMappingManager.loadSLAPoliciesDropdown()
            ]);
            console.log('SLA Tab fully loaded');
        } catch (error) {
            console.error('Error initializing SLA Tab:', error);
        } finally {
            hideTabLoading('tab-sla');
        }
    }

    function cleanupSlaTab() {
        console.log('Cleaning up SLA Tab...');
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