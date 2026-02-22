<div id="tab-templates" class="tab-content active">
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-envelope"></i> Email Templates</h3>
            <button class="btn btn-primary" onclick="TemplateManager.openAddTemplate()">
                <i class="fas fa-plus"></i> Add Template
            </button>
        </div>

        <div class="filter-section" style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <input type="text" id="searchTemplate" placeholder="Search templates..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="TemplateManager.searchTemplates(this.value)">
            </div>
            <div>
                <select id="filterTemplateStatus" onchange="TemplateManager.filterTemplates()"
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
                        <th>Template Name</th>
                        <th>Template Code</th>
                        <th>Subject</th>
                        <th>Variables</th>
                        <th>Status</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="templatesTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                            <p style="margin-top: 10px; color: #6b7280;">Loading templates...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- MODAL: Add/Edit Template -->
<div id="modalTemplate" class="modal">
    <div class="modal-content" style="max-width: 1400px;">
        <div class="modal-header">
            <h3 id="modalTemplateTitle">Add Email Template</h3>
            <button class="modal-close" onclick="TemplateManager.closeModal()">×</button>
        </div>
        <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- LEFT SIDE: Form -->
            <div class="template-form-section">
                <input type="hidden" id="templateId">
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Template Name <span class="required">*</span></label>
                        <input type="text" id="templateName" placeholder="e.g., Ticket Created Notification" oninput="TemplateManager.autoPreview()">
                    </div>
                    
                    <div class="form-group">
                        <label>Template Code <span class="required">*</span></label>
                        <input type="text" id="templateCode" placeholder="e.g., TICKET_CREATED" oninput="TemplateManager.autoPreview()">
                        <small style="color: #6b7280;">Uppercase, underscores only</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Subject <span class="required">*</span></label>
                    <input type="text" id="templateSubject" placeholder="e.g., Ticket #{ticket_number} Created" oninput="TemplateManager.autoPreview()">
                    <small style="color: #6b7280;">Use variables like {ticket_number}, {requester_name}, etc.</small>
                </div>

                <div class="form-group">
                    <label>Email Body <span class="required">*</span></label>
                    <textarea id="templateBody" rows="12" placeholder="Enter email body..." 
                        style="font-family: monospace; font-size: 13px;" oninput="TemplateManager.autoPreview()"></textarea>
                    <small style="color: #6b7280;">Use HTML tags and variables</small>
                </div>

                <div class="form-group">
                    <label>Available Variables (JSON)</label>
                    <textarea id="templateVariables" rows="4" placeholder='["ticket_number", "requester_name", "subject", "priority"]'
                        style="font-family: monospace; font-size: 13px;" oninput="TemplateManager.autoPreview()"></textarea>
                    <small style="color: #6b7280;">List of variables available for this template</small>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="templateStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- RIGHT SIDE: Live Preview -->
            <div class="template-preview-section" style="position: sticky; top: 20px; height: fit-content;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 20px; color: white; margin-bottom: 15px;">
                    <h4 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-eye"></i> 
                        <span>Live Preview</span>
                        <span style="margin-left: auto; font-size: 12px; opacity: 0.8;">Auto-updates</span>
                    </h4>
                </div>
                
                <div style="background: white; border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px; max-height: 600px; overflow-y: auto;">
                    <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                        <strong style="color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Subject</strong>
                        <div id="previewSubject" style="font-size: 16px; font-weight: 600; margin-top: 8px; color: #1f2937;">
                            Enter subject to see preview...
                        </div>
                    </div>
                    <div>
                        <strong style="color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Body</strong>
                        <div id="previewBody" style="font-size: 14px; line-height: 1.8; color: #374151; margin-top: 8px;">
                            Enter body to see preview...
                        </div>
                    </div>
                </div>

                <!-- Variable Helper -->
                <div style="background: #f9fafb; border-radius: 8px; padding: 15px; margin-top: 15px; border: 1px solid #e5e7eb;">
                    <strong style="color: #374151; font-size: 12px; display: block; margin-bottom: 8px;">
                        <i class="fas fa-info-circle"></i> Available Variables
                    </strong>
                    <div id="variablesList" style="display: flex; flex-wrap: wrap; gap: 5px; font-size: 11px;">
                        <span class="variable-tag">{ticket_number}</span>
                        <span class="variable-tag">{requester_name}</span>
                        <span class="variable-tag">{subject}</span>
                        <span class="variable-tag">{priority}</span>
                        <span class="variable-tag">{status}</span>
                        <span class="variable-tag">{assigned_to}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="TemplateManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="TemplateManager.saveTemplate()">
                <i class="fas fa-save"></i> Save Template
            </button>
        </div>
    </div>
</div>

<style>
.form-row .form-group {
    margin-bottom: 20px;
}

.template-code-badge {
    font-family: monospace;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #374151;
}

.variables-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.variable-tag {
    background: #dbeafe;
    color: #1e40af;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-family: monospace;
}
</style>

<script>
const TemplateManager = {
    baseUrl: '<?= base_url() ?>',
    searchQuery: '',
    filterStatus: '',

    async loadTemplates() {
        try {
            const params = new URLSearchParams({
                search: this.searchQuery,
                status: this.filterStatus
            });

            const response = await fetch(`${this.baseUrl}email-templates?${params}`);
            const data = await response.json();

            if (data.success) {
                this.renderTemplates(data.data);
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            this.showError();
        }
    },

    renderTemplates(templates) {
        const tbody = document.getElementById('templatesTableBody');

        if (templates.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No templates found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = templates.map((template, index) => {
            let variables = [];
            try {
                variables = template.variables ? JSON.parse(template.variables) : [];
            } catch (e) {}

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${template.template_name}</strong></td>
                    <td><span class="template-code-badge">${template.template_code}</span></td>
                    <td>${template.subject}</td>
                    <td>
                        <div class="variables-list">
                            ${variables.slice(0, 3).map(v => `<span class="variable-tag">{${v}}</span>`).join('')}
                            ${variables.length > 3 ? `<span class="variable-tag">+${variables.length - 3} more</span>` : ''}
                        </div>
                    </td>
                    <td><span class="badge badge-${template.status === 'active' ? 'success' : 'danger'}">${template.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-success" onclick="TemplateManager.previewTemplateModal(${template.id})">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="TemplateManager.editTemplate(${template.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="TemplateManager.deleteTemplate(${template.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    openAddTemplate() {
        document.getElementById('modalTemplateTitle').textContent = 'Add Email Template';
        document.getElementById('templateId').value = '';
        document.getElementById('templateName').value = '';
        document.getElementById('templateCode').value = '';
        document.getElementById('templateSubject').value = '';
        document.getElementById('templateBody').value = '';
        document.getElementById('templateVariables').value = '';
        document.getElementById('templateStatus').value = 'active';
        document.getElementById('previewSubject').textContent = 'Preview subject here...';
        document.getElementById('previewBody').textContent = 'Preview body here...';
        document.getElementById('modalTemplate').classList.add('active');
    },

    async editTemplate(id) {
        try {
            const response = await fetch(`${this.baseUrl}email-templates/show/${id}`);
            const data = await response.json();

            if (data.success) {
                const template = data.data;
                document.getElementById('modalTemplateTitle').textContent = 'Edit Email Template';
                document.getElementById('templateId').value = template.id;
                document.getElementById('templateName').value = template.template_name;
                document.getElementById('templateCode').value = template.template_code;
                document.getElementById('templateSubject').value = template.subject;
                document.getElementById('templateBody').value = template.body;
                document.getElementById('templateVariables').value = template.variables || '';
                document.getElementById('templateStatus').value = template.status;
                
                // Update preview
                this.previewTemplate();
                
                document.getElementById('modalTemplate').classList.add('active');
            }
        } catch (error) {
            TicketNotifier.showError('Error loading template');
        }
    },

    async saveTemplate() {
        const id = document.getElementById('templateId').value;
        const formData = {
            template_name: document.getElementById('templateName').value,
            template_code: document.getElementById('templateCode').value,
            subject: document.getElementById('templateSubject').value,
            body: document.getElementById('templateBody').value,
            variables: document.getElementById('templateVariables').value || null,
            status: document.getElementById('templateStatus').value
        };

        if (!formData.template_name || !formData.template_code || !formData.subject || !formData.body) {
            TicketNotifier.showValidationError('Please fill in required fields');
            return;
        }

        // Validate JSON if variables provided
        if (formData.variables) {
            try {
                JSON.parse(formData.variables);
            } catch (e) {
                TicketNotifier.showValidationError('Invalid JSON format in variables');
                return;
            }
        }

        try {
            const url = id ? `${this.baseUrl}email-templates/update/${id}` : `${this.baseUrl}email-templates/store`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                TicketNotifier.showSuccess(id ? 'Template updated successfully' : 'Template created successfully');
                this.closeModal();
                this.loadTemplates();
            } else {
                TicketNotifier.showError(data.message || 'Error saving template');
            }
        } catch (error) {
            TicketNotifier.showError('Error saving template');
        }
    },

    async deleteTemplate(id) {
        const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this template?', 'Yes, proceed'); if (!confirmed) return;

        try {
            const response = await fetch(`${this.baseUrl}email-templates/delete/${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                TicketNotifier.showSuccess('Template deleted successfully', () => this.loadTemplates());
            }
        } catch (error) {
            TicketNotifier.showError('Error deleting template');
        }
    },

    // Auto preview with debouncing
    autoPreview() {
        // Clear existing timeout
        if (this.previewTimeout) {
            clearTimeout(this.previewTimeout);
        }

        // Set new timeout for debouncing (300ms delay)
        this.previewTimeout = setTimeout(() => {
            this.previewTemplate();
        }, 300);
    },

    previewTemplate() {
        const subject = document.getElementById('templateSubject').value;
        const body = document.getElementById('templateBody').value;

        // Replace variables with sample data for preview
        const sampleData = {
            ticket_number: 'TK-12345',
            requester_name: 'John Doe',
            subject: 'Network connectivity issue',
            priority: 'High',
            assigned_to: 'IT Support Team',
            due_date: '2025-12-15 10:00:00',
            status: 'In Progress'
        };

        let previewSubject = subject || 'Enter subject to see preview...';
        let previewBody = body || 'Enter body to see preview...';

        Object.keys(sampleData).forEach(key => {
            const regex = new RegExp(`{${key}}`, 'g');
            previewSubject = previewSubject.replace(regex, sampleData[key]);
            previewBody = previewBody.replace(regex, sampleData[key]);
        });

        document.getElementById('previewSubject').textContent = previewSubject;
        document.getElementById('previewBody').innerHTML = previewBody;
    },

    searchTemplates(query) {
        this.searchQuery = query;
        this.loadTemplates();
    },

    filterTemplates() {
        this.filterStatus = document.getElementById('filterTemplateStatus').value;
        this.loadTemplates();
    },

    closeModal() {
        document.getElementById('modalTemplate').classList.remove('active');
    },

    showError() {
        document.getElementById('templatesTableBody').innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                    <p style="margin-top: 10px;">Error loading templates</p>
                </td>
            </tr>
        `;
    }
};

// TAB INITIALIZATION
function initTemplatesTab() {
    console.log('Initializing Templates Tab...');
    TemplateManager.loadTemplates();
}

function cleanupTemplatesTab() {
    console.log('Cleaning up Templates Tab...');
    document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
}
</script>