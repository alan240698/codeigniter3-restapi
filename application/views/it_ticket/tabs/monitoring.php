    <style>
        /* Selection Panel */
        .selection-panel {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .selection-panel h3 {
            margin: 0 0 20px 0;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .selection-controls {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group-custom {
            margin: 0;
        }

        .form-group-custom label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            opacity: 0.9;
        }

        .form-group-custom select {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            background: white;
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-load {
            padding: 12px 24px;
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-load:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-load:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Service Overview Card */
        .service-overview {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 5px solid #3b82f6;
        }

        .service-overview-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .service-info {
            flex: 1;
        }

        .service-name {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .service-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #6b7280;
        }

        .service-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid #3b82f6;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Detail Sections */
        .detail-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .detail-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .detail-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-section-title i {
            font-size: 22px;
        }

        .detail-section-title.routing { color: #3b82f6; }
        .detail-section-title.routing i { color: #3b82f6; }
        .detail-section-title.approval { color: #f59e0b; }
        .detail-section-title.approval i { color: #f59e0b; }
        .detail-section-title.level { color: #10b981; }
        .detail-section-title.level i { color: #10b981; }
        .detail-section-title.fields { color: #8b5cf6; }
        .detail-section-title.fields i { color: #8b5cf6; }
        .detail-section-title.sla { color: #ef4444; }
        .detail-section-title.sla i { color: #ef4444; }
        .detail-section-title.workflow { color: #06b6d4; }
        .detail-section-title.workflow i { color: #06b6d4; }

        .count-badge {
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #6b7280;
        }

        /* Detail Items - Enhanced */
        .detail-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }

        .detail-item {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #d1d5db;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .detail-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .detail-item:hover {
            background: white;
            border-left-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .detail-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .detail-item-title {
            font-weight: 700;
            color: #1f2937;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .detail-item-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .detail-item-content {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.8;
        }

        .detail-item-row {
            display: flex;
            align-items: start;
            gap: 10px;
            margin: 8px 0;
            padding: 8px;
            background: white;
            border-radius: 6px;
        }

        .detail-item:hover .detail-item-row {
            background: #f9fafb;
        }

        .detail-item-row strong {
            color: #374151;
            min-width: 140px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .detail-item-row span {
            flex: 1;
        }

        /* Condition Display */
        .condition-box {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
        }

        .condition-box-title {
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .condition-item {
            background: #f9fafb;
            padding: 8px 10px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 12px;
            border-left: 3px solid #3b82f6;
        }

        .condition-field {
            font-weight: 600;
            color: #3b82f6;
        }

        .condition-operator {
            color: #6b7280;
            padding: 0 5px;
        }

        .condition-value {
            color: #1f2937;
            font-weight: 600;
            background: white;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Member List */
        .member-list {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }

        .member-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid #10b981;
            transition: all 0.2s;
        }

        .member-item:hover {
            background: #f0fdf4;
            transform: translateX(5px);
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .member-info {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .member-role {
            font-size: 12px;
            color: #6b7280;
        }

        .member-contact {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Info Tags */
        .info-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .info-tag {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .info-tag.blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .info-tag.green {
            background: #d1fae5;
            color: #065f46;
        }

        .info-tag.yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .info-tag.purple {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .info-tag.red {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Priority Badge */
        .priority-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            display: inline-block;
        }

        .priority-badge.critical {
            background: #991b1b;
            color: white;
        }

        .priority-badge.high {
            background: #dc2626;
            color: white;
        }

        .priority-badge.medium {
            background: #f59e0b;
            color: white;
        }

        .priority-badge.low {
            background: #10b981;
            color: white;
        }

        /* Level Badge */
        .level-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            display: inline-block;
        }

        .level-badge.l1 { background: #dbeafe; color: #1e40af; }
        .level-badge.l2 { background: #d1fae5; color: #065f46; }
        .level-badge.l3 { background: #fef3c7; color: #92400e; }
        .level-badge.l4 { background: #e9d5ff; color: #6b21a8; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.5;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-state-text {
            font-size: 14px;
        }

        /* Loading State */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: #3b82f6;
            font-weight: 600;
        }

        .error-state {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #991b1b;
        }

        .error-state i {
            margin-right: 8px;
        }

        /* Expandable Section */
        .expandable-section {
            margin-top: 12px;
        }

        .expand-toggle {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: #3b82f6;
            transition: all 0.2s;
        }

        .expand-toggle:hover {
            background: #f0f9ff;
            border-color: #3b82f6;
        }

        .expand-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .expand-content.open {
            max-height: 1000px;
        }
    </style>

<div id="tab-monitoring" class="tab-content active">
    

    <!-- Selection Panel -->
    <div class="selection-panel">
        <h3>
            <i class="fas fa-filter"></i>
            Select IT Service to Monitor
        </h3>
        <div class="selection-controls">
            <div class="form-group-custom">
                <label>📁 Service Group</label>
                <select id="selectServiceGroup" onchange="MonitoringManager.onServiceGroupChange()">
                    <option value="">-- Select Service Group --</option>
                </select>
            </div>
            <div class="form-group-custom">
                <label>🔧 IT Service</label>
                <select id="selectItService" disabled>
                    <option value="">-- Select IT Service --</option>
                </select>
            </div>
            <button class="btn-load" id="btnLoadDetails" onclick="MonitoringManager.loadServiceDetails()" disabled>
                <i class="fas fa-search"></i> Load Details
            </button>
        </div>
    </div>

    <!-- Content Container -->
    <div id="contentContainer">
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            <div class="empty-state-title">Select an IT Service</div>
            <div class="empty-state-text">Choose a service group and IT service above to view detailed configuration</div>
        </div>
    </div>
</div>

<script>
const MonitoringManager = {
    baseUrl: '<?= base_url() ?>',
    allData: {
        serviceGroups: [],
        services: [],
        routingRules: [],
        approvalRules: [],
        levelRules: [],
        customFields: [],
        slaMapping: [],
        workflowMapping: [],
        teams: [],
        teamMembers: [],
        templates: [],
        workflows: []
    },
    selectedServiceId: null,
    selectedService: null,

    async init() {
        console.log('Initializing Monitoring Tab...');
        await this.loadInitialData();
    },

    async loadInitialData() {
        try {
            const [servicesResp, teamsResp] = await Promise.all([
                this.fetchData('it-services/all-group-type'),
                this.fetchData('support-teams')
            ]);

            if (servicesResp && servicesResp.success !== false) {
                this.allData.serviceGroups = Array.isArray(servicesResp) ? servicesResp : (servicesResp.data || []);
            }

            if (teamsResp && teamsResp.success !== false) {
                this.allData.teams = Array.isArray(teamsResp) ? teamsResp : (teamsResp.data || []);
            }

            this.renderServiceGroups();
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showError('Failed to load service groups. Please refresh the page.');
        }
    },

    async fetchData(endpoint) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error(`Error fetching ${endpoint}:`, error);
            return { success: false, data: [], message: error.message };
        }
    },

    renderServiceGroups() {
        const select = document.getElementById('selectServiceGroup');
        if (!this.allData.serviceGroups || this.allData.serviceGroups.length === 0) {
            select.innerHTML = '<option value="">No service groups available</option>';
            return;
        }

        const options = this.allData.serviceGroups.map(group => 
            `<option value="${group.id}">${group.icon || '📁'} ${group.name}</option>`
        ).join('');
        select.innerHTML = '<option value="">-- Select Service Group --</option>' + options;
    },

    onServiceGroupChange() {
        const groupId = document.getElementById('selectServiceGroup').value;
        const select = document.getElementById('selectItService');
        const btnLoad = document.getElementById('btnLoadDetails');
        
        if (!groupId) {
            select.disabled = true;
            select.innerHTML = '<option value="">-- Select IT Service --</option>';
            btnLoad.disabled = true;
            return;
        }

        const group = this.allData.serviceGroups.find(g => g.id == groupId);
        if (!group || !group.services) {
            select.innerHTML = '<option value="">No services available</option>';
            select.disabled = true;
            btnLoad.disabled = true;
            return;
        }

        let options = '';
        group.services.forEach(service => {
            if (service.children && service.children.length > 0) {
                options += `<option disabled style="font-weight: 700; background: #f3f4f6;">📂 ${service.name}</option>`;
                service.children.forEach(child => {
                    options += `<option value="${child.id}">&nbsp;&nbsp;&nbsp;└─ ${child.name}</option>`;
                });
            } else {
                options += `<option value="${service.id}">🔧 ${service.name}</option>`;
            }
        });

        select.innerHTML = '<option value="">-- Select IT Service --</option>' + options;
        select.disabled = false;
        
        select.onchange = () => {
            btnLoad.disabled = !select.value;
        };
    },

    async loadServiceDetails() {
        const serviceId = document.getElementById('selectItService').value;
        
        if (!serviceId) {
            alert('Please select an IT Service first');
            return;
        }

        this.selectedServiceId = serviceId;
        
        // Find selected service
        const groupId = document.getElementById('selectServiceGroup').value;
        const group = this.allData.serviceGroups.find(g => g.id == groupId);
        if (group) {
            group.services.forEach(service => {
                if (service.id == serviceId) {
                    this.selectedService = service;
                } else if (service.children) {
                    const child = service.children.find(c => c.id == serviceId);
                    if (child) this.selectedService = child;
                }
            });
        }

        this.showLoading();

        try {
            const [routingRules, approvalRules, levelRules, customFields, slaMapping, workflowMapping] = await Promise.all([
                this.fetchData(`routing-rules?it_service_id=${serviceId}`),
                this.fetchData(`approval-rules?it_service_id=${serviceId}`),
                this.fetchData(`level-rules?it_service_id=${serviceId}`),
                this.fetchData(`custom-fields?it_service_id=${serviceId}`),
                this.fetchData(`it-service-sla?it_service_id=${serviceId}`),
                this.fetchData(`it-service-workflows?it_service_id=${serviceId}`)
            ]);

            this.allData.routingRules = this.extractData(routingRules);
            this.allData.approvalRules = this.extractData(approvalRules);
            this.allData.levelRules = this.extractData(levelRules);
            this.allData.customFields = this.extractData(customFields);
            this.allData.slaMapping = this.extractData(slaMapping);
            this.allData.workflowMapping = this.extractData(workflowMapping);

            await this.loadTeamMembersForService(serviceId);

            this.renderServiceDetails();
        } catch (error) {
            console.error('Error loading service details:', error);
            this.showError('Failed to load service details. Please try again.');
        } finally {
            this.hideLoading();
        }
    },

    extractData(response) {
        if (!response) return [];
        if (response.success === false) return [];
        if (Array.isArray(response)) return response;
        if (response.data && Array.isArray(response.data)) return response.data;
        return [];
    },

    async loadTeamMembersForService(serviceId) {
        const routingRules = this.allData.routingRules.filter(r => r.it_service_id == serviceId);
        const levelRules = this.allData.levelRules.filter(r => r.it_service_id == serviceId);
        
        const teamIds = new Set();
        routingRules.forEach(rule => {
            if (rule.target_team_id) teamIds.add(rule.target_team_id);
        });
        levelRules.forEach(rule => {
            if (rule.support_team_id) teamIds.add(rule.support_team_id);
        });

        this.allData.teamMembers = [];
        for (const teamId of teamIds) {
            try {
                const response = await this.fetchData(`team-members?team_id=${teamId}`);
                const members = this.extractData(response);
                if (members.length > 0) {
                    this.allData.teamMembers.push(...members);
                }
            } catch (error) {
                console.error(`Error loading members for team ${teamId}:`, error);
            }
        }
    },

    renderServiceDetails() {
        const serviceId = this.selectedServiceId;
        const routingRules = this.allData.routingRules;
        const approvalRules = this.allData.approvalRules;
        const levelRules = this.allData.levelRules;
        const customFields = this.allData.customFields;
        const sla = this.allData.slaMapping.length > 0 ? this.allData.slaMapping[0] : null;
        const workflow = this.allData.workflowMapping.length > 0 ? this.allData.workflowMapping[0] : null;

        let html = '';

        // Service Overview
        html += this.renderServiceOverview();

        // Statistics
        html += `
            <div class="stats-grid">
                <div class="stat-card" style="border-top-color: #3b82f6;">
                    <div class="stat-value">${routingRules.length}</div>
                    <div class="stat-label"><i class="fas fa-route"></i> Routing Rules</div>
                </div>
                <div class="stat-card" style="border-top-color: #f59e0b;">
                    <div class="stat-value">${approvalRules.length}</div>
                    <div class="stat-label"><i class="fas fa-stamp"></i> Approval Rules</div>
                </div>
                <div class="stat-card" style="border-top-color: #10b981;">
                    <div class="stat-value">${levelRules.length}</div>
                    <div class="stat-label"><i class="fas fa-layer-group"></i> Level Rules</div>
                </div>
                <div class="stat-card" style="border-top-color: #8b5cf6;">
                    <div class="stat-value">${customFields.length}</div>
                    <div class="stat-label"><i class="fas fa-sliders"></i> Custom Fields</div>
                </div>
            </div>
        `;

        html += this.renderRoutingRules(routingRules);
        html += this.renderApprovalRules(approvalRules);
        html += this.renderLevelRules(levelRules);
        html += this.renderCustomFields(customFields);
        html += this.renderSLA(sla);
        html += this.renderWorkflow(workflow);

        if (routingRules.length === 0 && approvalRules.length === 0 && 
            levelRules.length === 0 && customFields.length === 0 && !sla && !workflow) {
            html += `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <div class="empty-state-title">No Configuration Found</div>
                    <div class="empty-state-text">This IT Service has no rules or configurations set up yet.</div>
                </div>
            `;
        }

        document.getElementById('contentContainer').innerHTML = html;
    },

    renderServiceOverview() {
        if (!this.selectedService) return '';

        return `
            <div class="service-overview">
                <div class="service-overview-header">
                    <div class="service-icon">
                        ${this.selectedService.icon || '🔧'}
                    </div>
                    <div class="service-info">
                        <div class="service-name">${this.selectedService.name}</div>
                        <div class="service-meta">
                            <div class="service-meta-item">
                                <i class="fas fa-tag"></i>
                                <span>ID: ${this.selectedService.id}</span>
                            </div>
                            <div class="service-meta-item">
                                <i class="fas fa-code"></i>
                                <span>Code: ${this.selectedService.code || 'N/A'}</span>
                            </div>
                            ${this.selectedService.status ? `
                            <div class="service-meta-item">
                                <i class="fas fa-circle"></i>
                                <span>Status: ${this.selectedService.status}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ${this.selectedService.description ? `
                    <div style="padding: 15px; background: #f9fafb; border-radius: 8px; color: #6b7280; font-size: 14px;">
                        <i class="fas fa-info-circle" style="color: #3b82f6;"></i> ${this.selectedService.description}
                    </div>
                ` : ''}
            </div>
        `;
    },

    renderRoutingRules(rules) {
        if (rules.length === 0) return '';

        let html = `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title routing">
                        <i class="fas fa-route"></i> Routing Rules
                    </div>
                    <span class="count-badge">${rules.length} rule${rules.length > 1 ? 's' : ''}</span>
                </div>
                <div class="detail-items-grid">
        `;

        rules.forEach((rule, index) => {
            const team = this.allData.teams.find(t => t.id == rule.target_team_id);
            const teamMembers = team ? this.allData.teamMembers.filter(m => m.team_id == team.id) : [];

            // Parse conditions if exists
            let conditions = [];
            try {
                if (rule.conditions) {
                    conditions = typeof rule.conditions === 'string' ? JSON.parse(rule.conditions) : rule.conditions;
                }
            } catch (e) {
                console.error('Error parsing conditions:', e);
            }

            html += `
                <div class="detail-item" style="border-left-color: #3b82f6;">
                    <div class="detail-item-header">
                        <div class="detail-item-title">
                            <i class="fas fa-arrow-right" style="color: #3b82f6;"></i>
                            ${rule.rule_name}
                        </div>
                        <span class="detail-item-badge badge-${rule.status === 'active' ? 'active' : 'inactive'}">
                            ${rule.status}
                        </span>
                    </div>
                    <div class="detail-item-content">
                        <div class="detail-item-row">
                            <strong><i class="fas fa-layer-group"></i> Assignment Type:</strong>
                            <span><span class="info-tag blue">${rule.assignment_type}</span></span>
                        </div>
                        ${team ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-users"></i> Target Team:</strong>
                                <span>${team.name}</span>
                            </div>
                            <div class="detail-item-row">
                                <strong><i class="fas fa-signal"></i> Team Level:</strong>
                                <span><span class="level-badge l${team.support_level.toLowerCase().replace('l', '')}">${team.support_level}</span></span>
                            </div>
                        ` : ''}
                        <div class="detail-item-row">
                            <strong><i class="fas fa-sort-numeric-up"></i> Priority:</strong>
                            <span><span class="priority-badge ${rule.priority ? rule.priority.toLowerCase() : 'medium'}">${rule.priority || 'Medium'}</span></span>
                        </div>
                        ${rule.created_at ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-calendar-plus"></i> Created:</strong>
                                <span>${new Date(rule.created_at).toLocaleDateString('vi-VN')}</span>
                            </div>
                        ` : ''}
                        ${rule.updated_at ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-calendar-check"></i> Last Updated:</strong>
                                <span>${new Date(rule.updated_at).toLocaleDateString('vi-VN')}</span>
                            </div>
                        ` : ''}
                        ${conditions.length > 0 ? `
                            <div class="condition-box">
                                <div class="condition-box-title">
                                    <i class="fas fa-filter"></i> Routing Conditions
                                </div>
                                ${conditions.map(cond => `
                                    <div class="condition-item">
                                        <span class="condition-field">${cond.field || 'Field'}</span>
                                        <span class="condition-operator">${cond.operator || '='}</span>
                                        <span class="condition-value">${cond.value || 'Value'}</span>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                        ${teamMembers.length > 0 ? `
                            <div class="expandable-section">
                                <div class="expand-toggle" onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('i').classList.toggle('fa-chevron-down'); this.querySelector('i').classList.toggle('fa-chevron-up');">
                                    <span><i class="fas fa-users"></i> Team Members (${teamMembers.length})</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="expand-content">
                                    <div class="member-list">
                                        ${teamMembers.map(member => `
                                            <div class="member-item">
                                                <div class="member-avatar">${this.getInitials(member.employee_name)}</div>
                                                <div class="member-info">
                                                    <div class="member-name">${member.employee_name}</div>
                                                    <div class="member-role">${member.role || 'Team Member'}</div>
                                                    ${member.email ? `<div class="member-contact"><i class="fas fa-envelope"></i> ${member.email}</div>` : ''}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        return html;
    },

    renderApprovalRules(rules) {
        if (rules.length === 0) return '';

        let html = `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title approval">
                        <i class="fas fa-stamp"></i> Approval Rules
                    </div>
                    <span class="count-badge">${rules.length} rule${rules.length > 1 ? 's' : ''}</span>
                </div>
                <div class="detail-items-grid">
        `;

        rules.forEach(rule => {
            // Parse conditions
            let conditions = [];
            try {
                if (rule.conditions) {
                    conditions = typeof rule.conditions === 'string' ? JSON.parse(rule.conditions) : rule.conditions;
                }
            } catch (e) {
                console.error('Error parsing conditions:', e);
            }

            html += `
                <div class="detail-item" style="border-left-color: #f59e0b;">
                    <div class="detail-item-header">
                        <div class="detail-item-title">
                            <i class="fas fa-check-circle" style="color: #f59e0b;"></i>
                            ${rule.rule_name}
                        </div>
                        <span class="detail-item-badge badge-${rule.status === 'active' ? 'active' : 'inactive'}">
                            ${rule.status}
                        </span>
                    </div>
                    <div class="detail-item-content">
                        <div class="detail-item-row">
                            <strong><i class="fas fa-certificate"></i> Approval Type:</strong>
                            <span><span class="info-tag yellow">${rule.approval_type}</span></span>
                        </div>
                        <div class="detail-item-row">
                            <strong><i class="fas fa-shield-check"></i> Required:</strong>
                            <span>${rule.requires_approval ? '<span class="info-tag green"><i class="fas fa-check"></i> Yes</span>' : '<span class="info-tag red"><i class="fas fa-times"></i> No</span>'}</span>
                        </div>
                        <div class="detail-item-row">
                            <strong><i class="fas fa-robot"></i> Auto Assign:</strong>
                            <span>${rule.auto_assign_after_approval ? '<span class="info-tag green"><i class="fas fa-check"></i> Yes</span>' : '<span class="info-tag red"><i class="fas fa-times"></i> No</span>'}</span>
                        </div>
                        ${rule.approver_role ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-user-shield"></i> Approver Role:</strong>
                                <span><span class="info-tag purple">${rule.approver_role}</span></span>
                            </div>
                        ` : ''}
                        ${rule.created_at ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-calendar-plus"></i> Created:</strong>
                                <span>${new Date(rule.created_at).toLocaleDateString('vi-VN')}</span>
                            </div>
                        ` : ''}
                        ${conditions.length > 0 ? `
                            <div class="condition-box">
                                <div class="condition-box-title">
                                    <i class="fas fa-filter"></i> Approval Conditions
                                </div>
                                ${conditions.map(cond => `
                                    <div class="condition-item">
                                        <span class="condition-field">${cond.field || 'Field'}</span>
                                        <span class="condition-operator">${cond.operator || '='}</span>
                                        <span class="condition-value">${cond.value || 'Value'}</span>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        return html;
    },

    renderLevelRules(rules) {
        if (rules.length === 0) return '';

        let html = `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title level">
                        <i class="fas fa-layer-group"></i> Level Rules
                    </div>
                    <span class="count-badge">${rules.length} level${rules.length > 1 ? 's' : ''}</span>
                </div>
                <div class="detail-items-grid">
        `;

        rules.forEach(rule => {
            const team = this.allData.teams.find(t => t.id == rule.support_team_id);
            const teamMembers = team ? this.allData.teamMembers.filter(m => m.team_id == team.id) : [];

            html += `
                <div class="detail-item" style="border-left-color: #10b981;">
                    <div class="detail-item-header">
                        <div class="detail-item-title">
                            <span class="level-badge l${rule.level_number}">${rule.level_name}</span>
                        </div>
                        <span class="detail-item-badge badge-${rule.status === 'active' ? 'active' : 'inactive'}">
                            ${rule.status}
                        </span>
                    </div>
                    <div class="detail-item-content">
                        <div class="detail-item-row">
                            <strong><i class="fas fa-sort-numeric-up"></i> Level Number:</strong>
                            <span><span class="info-tag blue">Level ${rule.level_number}</span></span>
                        </div>
                        ${team ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-users"></i> Support Team:</strong>
                                <span>${team.name}</span>
                            </div>
                            <div class="detail-item-row">
                                <strong><i class="fas fa-signal"></i> Team Level:</strong>
                                <span><span class="level-badge l${team.support_level.toLowerCase().replace('l', '')}">${team.support_level}</span></span>
                            </div>
                        ` : ''}
                        <div class="detail-item-row">
                            <strong><i class="fas fa-clock"></i> Auto Escalate:</strong>
                            <span>${rule.auto_escalate_hours ? `<span class="info-tag yellow"><i class="fas fa-hourglass-half"></i> ${rule.auto_escalate_hours} hours</span>` : '<span class="info-tag red"><i class="fas fa-times"></i> No</span>'}</span>
                        </div>
                        ${rule.escalation_to_level ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-level-up-alt"></i> Escalate To:</strong>
                                <span><span class="info-tag purple">Level ${rule.escalation_to_level}</span></span>
                            </div>
                        ` : ''}
                        ${rule.created_at ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-calendar-plus"></i> Created:</strong>
                                <span>${new Date(rule.created_at).toLocaleDateString('vi-VN')}</span>
                            </div>
                        ` : ''}
                        ${teamMembers.length > 0 ? `
                            <div class="expandable-section">
                                <div class="expand-toggle" onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('i').classList.toggle('fa-chevron-down'); this.querySelector('i').classList.toggle('fa-chevron-up');">
                                    <span><i class="fas fa-users"></i> Team Members (${teamMembers.length})</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="expand-content">
                                    <div class="member-list">
                                        ${teamMembers.map(member => `
                                            <div class="member-item">
                                                <div class="member-avatar">${this.getInitials(member.employee_name)}</div>
                                                <div class="member-info">
                                                    <div class="member-name">${member.employee_name}</div>
                                                    <div class="member-role">${member.role || 'Team Member'}</div>
                                                    ${member.email ? `<div class="member-contact"><i class="fas fa-envelope"></i> ${member.email}</div>` : ''}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        return html;
    },

    renderCustomFields(fields) {
        if (fields.length === 0) return '';

        let html = `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title fields">
                        <i class="fas fa-sliders"></i> Custom Fields
                    </div>
                    <span class="count-badge">${fields.length} field${fields.length > 1 ? 's' : ''}</span>
                </div>
                <div class="detail-items-grid">
        `;

        fields.forEach(field => {
            let options = [];
            try {
                if (field.field_options) {
                    options = typeof field.field_options === 'string' ? field.field_options.split(',') : field.field_options;
                }
            } catch (e) {
                console.error('Error parsing field options:', e);
            }

            html += `
                <div class="detail-item" style="border-left-color: #8b5cf6;">
                    <div class="detail-item-header">
                        <div class="detail-item-title">
                            <i class="fas fa-input-text" style="color: #8b5cf6;"></i>
                            ${field.field_label}
                        </div>
                        <span class="detail-item-badge badge-${field.status === 'active' ? 'active' : 'inactive'}">
                            ${field.status}
                        </span>
                    </div>
                    <div class="detail-item-content">
                        <div class="detail-item-row">
                            <strong><i class="fas fa-code"></i> Field Name:</strong>
                            <code style="background: white; padding: 4px 8px; border-radius: 4px; color: #8b5cf6; font-weight: 600;">${field.field_name}</code>
                        </div>
                        <div class="detail-item-row">
                            <strong><i class="fas fa-cube"></i> Field Type:</strong>
                            <span><span class="info-tag purple">${field.field_type}</span></span>
                        </div>
                        <div class="detail-item-row">
                            <strong><i class="fas fa-shield-check"></i> Required:</strong>
                            <span>${field.is_required ? '<span class="info-tag green"><i class="fas fa-check"></i> Yes</span>' : '<span class="info-tag red"><i class="fas fa-times"></i> No</span>'}</span>
                        </div>
                        ${field.default_value ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-star"></i> Default Value:</strong>
                                <span><code style="background: white; padding: 2px 6px; border-radius: 4px;">${field.default_value}</code></span>
                            </div>
                        ` : ''}
                        ${field.placeholder ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-i-cursor"></i> Placeholder:</strong>
                                <span>${field.placeholder}</span>
                            </div>
                        ` : ''}
                        ${options.length > 0 ? `
                            <div class="condition-box">
                                <div class="condition-box-title">
                                    <i class="fas fa-list"></i> Available Options
                                </div>
                                <div class="info-tags">
                                    ${options.map(opt => `<span class="info-tag purple">${opt.trim()}</span>`).join('')}
                                </div>
                            </div>
                        ` : ''}
                        ${field.created_at ? `
                            <div class="detail-item-row">
                                <strong><i class="fas fa-calendar-plus"></i> Created:</strong>
                                <span>${new Date(field.created_at).toLocaleDateString('vi-VN')}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        return html;
    },

    renderSLA(sla) {
        if (!sla) return '';

        return `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title sla">
                        <i class="fas fa-clock"></i> SLA Policy
                    </div>
                    <span class="count-badge">1 policy</span>
                </div>
                <div class="detail-items-grid">
                    <div class="detail-item" style="border-left-color: #ef4444;">
                        <div class="detail-item-header">
                            <div class="detail-item-title">
                                <i class="fas fa-stopwatch" style="color: #ef4444;"></i>
                                ${sla.sla_name || 'SLA Policy'}
                            </div>
                            <span class="detail-item-badge badge-${sla.is_active ? 'active' : 'inactive'}">
                                ${sla.is_active ? 'active' : 'inactive'}
                            </span>
                        </div>
                        <div class="detail-item-content">
                            ${sla.priority ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Priority:</strong>
                                    <span><span class="priority-badge ${sla.priority.toLowerCase()}">${sla.priority}</span></span>
                                </div>
                            ` : ''}
                            ${sla.first_response_hours ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-hourglass-start"></i> First Response:</strong>
                                    <span><span class="info-tag yellow"><i class="fas fa-clock"></i> ${sla.first_response_hours} hours</span></span>
                                </div>
                            ` : ''}
                            ${sla.resolution_hours ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-hourglass-end"></i> Resolution Time:</strong>
                                    <span><span class="info-tag red"><i class="fas fa-clock"></i> ${sla.resolution_hours} hours</span></span>
                                </div>
                            ` : ''}
                            <div class="detail-item-row">
                                <strong><i class="fas fa-business-time"></i> Business Hours:</strong>
                                <span>${sla.business_hours_only ? '<span class="info-tag blue"><i class="fas fa-check"></i> Business Hours Only</span>' : '<span class="info-tag green"><i class="fas fa-infinity"></i> 24/7</span>'}</span>
                            </div>
                            ${sla.description ? `
                                <div style="margin-top: 12px; padding: 10px; background: white; border-radius: 6px; border-left: 3px solid #ef4444;">
                                    <strong style="color: #374151; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 5px;">
                                        <i class="fas fa-info-circle"></i> Description
                                    </strong>
                                    <p style="margin: 0; color: #6b7280; font-size: 13px;">${sla.description}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    renderWorkflow(workflow) {
        if (!workflow) return '';

        return `
            <div class="detail-section">
                <div class="detail-section-header">
                    <div class="detail-section-title workflow">
                        <i class="fas fa-project-diagram"></i> Workflow
                    </div>
                    <span class="count-badge">1 workflow</span>
                </div>
                <div class="detail-items-grid">
                    <div class="detail-item" style="border-left-color: #06b6d4;">
                        <div class="detail-item-header">
                            <div class="detail-item-title">
                                <i class="fas fa-sitemap" style="color: #06b6d4;"></i>
                                ${workflow.workflow_name || 'Workflow'}
                            </div>
                            <span class="detail-item-badge badge-${workflow.is_active ? 'active' : 'inactive'}">
                                ${workflow.is_active ? 'active' : 'inactive'}
                            </span>
                        </div>
                        <div class="detail-item-content">
                            ${workflow.workflow_code ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-barcode"></i> Workflow Code:</strong>
                                    <code style="background: white; padding: 4px 8px; border-radius: 4px; color: #06b6d4; font-weight: 600;">${workflow.workflow_code}</code>
                                </div>
                            ` : ''}
                            ${workflow.states_count ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-circle-nodes"></i> Total States:</strong>
                                    <span><span class="info-tag blue"><i class="fas fa-project-diagram"></i> ${workflow.states_count} states</span></span>
                                </div>
                            ` : ''}
                            ${workflow.initial_state ? `
                                <div class="detail-item-row">
                                    <strong><i class="fas fa-play-circle"></i> Initial State:</strong>
                                    <span><span class="info-tag green">${workflow.initial_state}</span></span>
                                </div>
                            ` : ''}
                            ${workflow.description ? `
                                <div style="margin-top: 12px; padding: 10px; background: white; border-radius: 6px; border-left: 3px solid #06b6d4;">
                                    <strong style="color: #374151; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 5px;">
                                        <i class="fas fa-info-circle"></i> Description
                                    </strong>
                                    <p style="margin: 0; color: #6b7280; font-size: 13px;">${workflow.description}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    getInitials(name) {
        if (!name) return '?';
        const parts = name.split(' ');
        if (parts.length >= 2) {
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    },

    showLoading() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <div class="loading-text">Loading service details...</div>
            </div>
        `;
        document.body.appendChild(overlay);
    },

    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.remove();
    },

    showError(message) {
        document.getElementById('contentContainer').innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> ${message}
            </div>
        `;
    }
};

// TAB INITIALIZATION
function initMonitoringTab() {
    console.log('Initializing Monitoring Tab...');
    MonitoringManager.init();
}

function cleanupMonitoringTab() {
    console.log('Cleaning up Monitoring Tab...');
}
</script>