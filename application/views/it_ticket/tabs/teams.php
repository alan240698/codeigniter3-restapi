<div id="tab-teams" class="tab-content active">
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
    </style>

    <!-- Support Teams Section -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-users-gear"></i> Support Teams</h3>
            <button class="btn btn-primary" onclick="TeamManager.openAddTeam()">
                <i class="fas fa-plus"></i> Add Team
            </button>
        </div>

        <!-- Smart UX: Setup Progress Indicator -->
        <div class="setup-progress-container"
            style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h4 style="margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users-gear"></i> Team Organization
                <span style="font-size: 12px; opacity: 0.8; font-weight: normal; margin-left: auto;">Optional: Create
                    Service Groups for better organization</span>
            </h4>
            <div class="progress-steps" style="display: flex; gap: 15px;">
                <div id="teams-step-service-groups" class="progress-step"
                    style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.7; transition: all 0.3s ease;">
                    <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Service
                        Groups</div>
                    <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                    <div class="step-status" style="font-size: 12px; opacity: 0.8;">Optional</div>
                </div>

                <div id="teams-step-teams" class="progress-step"
                    style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; transition: all 0.3s ease;">
                    <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Support
                        Teams</div>
                    <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">0</div>
                    <div class="step-status" style="font-size: 12px; opacity: 0.8;">Create teams</div>
                </div>

                <div id="teams-step-members" class="progress-step"
                    style="flex: 1; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; opacity: 0.5; transition: all 0.3s ease;">
                    <div class="step-icon" style="font-size: 24px; margin-bottom: 8px;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="step-title" style="font-weight: bold; margin-bottom: 5px; font-size: 14px;">Team Members
                    </div>
                    <div class="step-count" style="font-size: 20px; font-weight: bold; margin: 5px 0;">-</div>
                    <div class="step-status" style="font-size: 12px; opacity: 0.8;">Assign users</div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="filter-section" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" id="searchTeam" placeholder="Search teams..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                    oninput="TeamManager.searchTeams(this.value)">
            </div>
            <div>
                <select id="filterTeamLevel" onchange="TeamManager.filterTeams()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 150px;">
                    <option value="">All Levels</option>
                    <option value="L1">L1 Support</option>
                    <option value="L2">L2 Support</option>
                    <option value="L3">L3 Support</option>
                    <option value="L4">L4 Support</option>
                </select>
            </div>
            <div>
                <select id="filterTeamCountry" onchange="TeamManager.filterTeams()"
                    style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 150px;">
                    <option value="">All Countries</option>
                    <option value="VN">Vietnam</option>
                    <option value="US">United States</option>
                    <option value="SG">Singapore</option>
                    <option value="JP">Japan</option>
                </select>
            </div>
            <div>
                <select id="filterTeamStatus" onchange="TeamManager.filterTeams()"
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
                    <th>Team Name</th>
                    <th>Code</th>
                    <th>Support Level</th>
                    <th>Department</th>
                    <th>Country</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody id="teamsTableBody">
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #3b82f6;"></i>
                        <p style="margin-top: 10px; color: #6b7280;">Loading teams...</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div id="teamsPagination" class="pagination-container"
            style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
            <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
                Showing <strong id="teamShowingStart">0</strong> to <strong id="teamShowingEnd">0</strong> of <strong
                    id="teamTotal">0</strong> entries
            </div>
            <div class="pagination-buttons" id="teamPaginationButtons"></div>
        </div>
    </div>

    <!-- Team Members Section (Shows when team selected) -->
    <div class="card" id="membersSection" style="display: none;">
        <div class="card-header">
            <h3>
                <i class="fas fa-user-group"></i>
                <span id="membersTeamTitle">Team Members</span>
            </h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-secondary btn-sm" onclick="TeamManager.closeMembersSection()">
                    <i class="fas fa-arrow-left"></i> Back to Teams
                </button>
                <button class="btn btn-primary" onclick="MemberManager.openAddMember()">
                    <i class="fas fa-user-plus"></i> Add Member
                </button>
            </div>
        </div>

        <!-- Team Info Summary -->
        <div id="teamInfoSummary"
            style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; margin-bottom: 20px; color: white;">
            <!-- Team info will be rendered here -->
        </div>

        <!-- Members Stats -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div style="padding: 15px; background: #dbeafe; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 700; color: #1e40af;" id="totalMembers">0</div>
                <div style="font-size: 13px; color: #1e40af;">Total Members</div>
            </div>
            <div style="padding: 15px; background: #d1fae5; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 700; color: #065f46;" id="activeMembers">0</div>
                <div style="font-size: 13px; color: #065f46;">Active</div>
            </div>
            <div style="padding: 15px; background: #fef3c7; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 700; color: #92400e;" id="leadMembers">0</div>
                <div style="font-size: 13px; color: #92400e;">Team Leads</div>
            </div>
            <div style="padding: 15px; background: #e9d5ff; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 700; color: #6b21a8;" id="managerMembers">0</div>
                <div style="font-size: 13px; color: #6b21a8;">Managers</div>
            </div>
        </div>

        <!-- Members Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="membersTableBody">
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #6b7280;">
                        Select a team to view members
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- MODAL: Add/Edit Team -->
<div id="modalTeam" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="modalTeamTitle">Add Support Team</h3>
            <button class="modal-close" onclick="TeamManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="teamId">

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Team Name <span class="required">*</span></label>
                    <input type="text" id="teamName" placeholder="e.g., IT Support L1 Vietnam">
                </div>

                <div class="form-group">
                    <label>Code <span class="required">*</span></label>
                    <input type="text" id="teamCode" placeholder="e.g., IT_L1_VN">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Support Level <span class="required">*</span></label>
                    <select id="teamLevel">
                        <option value="">Select Level</option>
                        <option value="L1">L1 - First Line Support</option>
                        <option value="L2">L2 - Advanced Support</option>
                        <option value="L3">L3 - Expert Support</option>
                        <option value="L4">L4 - Specialist Support</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="teamStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Department Code</label>
                    <input type="text" id="teamDepartment" placeholder="e.g., IT, HR, FACILITIES">
                </div>

                <div class="form-group">
                    <label>Office</label>
                    <select id="teamOfficeId">
                        <option value="">Select Office</option>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Country</label>
                    <select id="teamCountry">
                        <option value="">Select Country</option>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Manager</label>
                    <select id="teamManagerId">
                        <option value="">Select Manager</option>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Team Email</label>
                <input type="email" id="teamEmail" placeholder="e.g., it-support-l1@company.com">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="teamDesc" placeholder="Enter team description" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="TeamManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="TeamManager.saveTeam()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Add/Edit Member -->
<div id="modalMember" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalMemberTitle">Add Team Member</h3>
            <button class="modal-close" onclick="MemberManager.closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="memberId">
            <input type="hidden" id="memberTeamId">

            <div class="form-group">
                <label>Select Employee <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="text" id="memberEmployeeSearch" placeholder="Search employee by name or email..."
                        style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;"
                        autocomplete="off">
                    <input type="hidden" id="memberEmployeeId">
                    <div id="employeeDropdown" class="employee-dropdown" style="display: none;">
                        <!-- Employee options will be populated here -->
                    </div>
                </div>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Type to search active employees from HR system
                </small>
            </div>

            <!-- Employee Details Preview -->
            <div id="employeeDetailsPreview"
                style="display: none; padding: 15px; background: #f0f9ff; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #3b82f6;">
                <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af;">
                    <i class="fas fa-user-check"></i> Selected Employee
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                    <div>
                        <strong>Name:</strong> <span id="previewName">-</span>
                    </div>
                    <div>
                        <strong>Email:</strong> <span id="previewEmail">-</span>
                    </div>
                    <div>
                        <strong>Department:</strong> <span id="previewDepartment">-</span>
                    </div>
                    <div>
                        <strong>Office:</strong> <span id="previewOffice">-</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Member Role <span class="required">*</span></label>
                <select id="memberRole">
                    <option value="member">Member - Regular team member</option>
                    <option value="lead">Lead - Team lead/supervisor</option>
                    <option value="manager">Manager - Team manager</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="memberStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label>Joined Date</label>
                <input type="date" id="memberJoinedDate">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="MemberManager.closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="MemberManager.saveMember()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<style>
    /* Support Level Badges */
    .level-badge {
        padding: 5px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .level-badge.l1 {
        background: #dbeafe;
        color: #1e40af;
    }

    .level-badge.l2 {
        background: #d1fae5;
        color: #065f46;
    }

    .level-badge.l3 {
        background: #fef3c7;
        color: #92400e;
    }

    .level-badge.l4 {
        background: #e9d5ff;
        color: #6b21a8;
    }

    /* Role Badges */
    .role-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }

    .role-badge.manager {
        background: #fecaca;
        color: #991b1b;
    }

    .role-badge.lead {
        background: #fef3c7;
        color: #92400e;
    }

    .role-badge.member {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Team Info Summary */
    .team-info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .team-info-row:last-child {
        border-bottom: none;
    }

    .team-info-label {
        font-size: 13px;
        opacity: 0.9;
    }

    .team-info-value {
        font-size: 14px;
        font-weight: 600;
    }

    /* Form Row */
    .form-row {
        margin-bottom: 0;
    }

    .form-row .form-group {
        margin-bottom: 20px;
    }

    /* Country Flag Icons (Optional Enhancement) */
    .country-flag {
        width: 20px;
        height: 15px;
        margin-right: 5px;
        border-radius: 2px;
        object-fit: cover;
    }

    /* Employee Autocomplete Dropdown */
    .employee-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin-top: 4px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .employee-option {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s;
    }

    .employee-option:last-child {
        border-bottom: none;
    }

    .employee-option:hover {
        background-color: #f0f9ff;
    }

    .employee-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .employee-dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .employee-dropdown::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .employee-dropdown::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<script>
    // ==================== TEAM MANAGER ====================
    const TeamManager = {
        baseUrl: '<?= base_url() ?>',
        currentPage: 1,
        perPage: 10,
        totalItems: 0,
        searchQuery: '',
        filterLevel: '',
        filterCountry: '',
        filterStatus: '',
        selectedTeamId: null,

        init() {
            this.validator = new FormValidationManager(ValidationRulesTeams.team);
            this.validator.setupFormValidation([{
                    fieldId: 'teamName',
                    fieldName: 'name'
                },
                {
                    fieldId: 'teamCode',
                    fieldName: 'code',
                    autoUppercase: true
                },
                {
                    fieldId: 'teamLevel',
                    fieldName: 'support_level'
                },
                {
                    fieldId: 'teamDepartment',
                    fieldName: 'department_code',
                    autoUppercase: true
                },
                {
                    fieldId: 'teamOfficeId',
                    fieldName: 'office_id'
                },
                {
                    fieldId: 'teamCountry',
                    fieldName: 'country'
                },
                {
                    fieldId: 'teamManagerId',
                    fieldName: 'manager_id'
                },
                {
                    fieldId: 'teamEmail',
                    fieldName: 'email'
                },
                {
                    fieldId: 'teamDesc',
                    fieldName: 'description'
                },
            ]);
            this.loadTeams();
        },

        async loadTeams() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.searchQuery,
                    support_level: this.filterLevel,
                    country: this.filterCountry,
                    status: this.filterStatus
                });

                const response = await fetch(`${this.baseUrl}support-teams?${params}`);
                const data = await response.json();

                if (data.success) {
                    this.totalItems = data.total;
                    this.renderTeams(data.data);
                    this.renderPagination();
                }
            } catch (error) {
                console.error('Error loading teams:', error);
                this.showError('teamsTableBody', 9);
            }
        },

        renderTeams(teams) {
            const tbody = document.getElementById('teamsTableBody');

            if (teams.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No teams found</p>
                    </td>
                </tr>
            `;
                return;
            }

            const startIndex = (this.currentPage - 1) * this.perPage;
            tbody.innerHTML = teams.map((team, index) => `
            <tr>
                <td>${startIndex + index + 1}</td>
                <td><strong>${team.name}</strong></td>
                <td><span class="badge badge-info">${team.code}</span></td>
                <td><span class="level-badge ${team.support_level.toLowerCase()}">${team.support_level}</span></td>
                <td>${team.department_code || '-'}</td>
                <td>${team.country ? `<i class="fas fa-flag"></i> ${team.country}` : '-'}</td>
                <td><span class="badge badge-success">${team.members_count || 0} members</span></td>
                <td><span class="badge badge-${team.status === 'active' ? 'success' : 'danger'}">${team.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-success" onclick="TeamManager.viewMembers(${team.id}, '${team.name}')">
                            <i class="fas fa-users"></i> Members
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="TeamManager.editTeam(${team.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="TeamManager.deleteTeam(${team.id})">
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

            document.getElementById('teamShowingStart').textContent = start;
            document.getElementById('teamShowingEnd').textContent = end;
            document.getElementById('teamTotal').textContent = this.totalItems;

            const buttonsContainer = document.getElementById('teamPaginationButtons');
            let buttons = '';

            buttons += `<button class="pagination-btn" ${this.currentPage === 1 ? 'disabled' : ''} 
            onclick="TeamManager.goToPage(${this.currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                    buttons += `<button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                    onclick="TeamManager.goToPage(${i})">${i}</button>`;
                } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                    buttons += `<span style="padding: 8px;">...</span>`;
                }
            }

            buttons += `<button class="pagination-btn" ${this.currentPage === totalPages ? 'disabled' : ''} 
            onclick="TeamManager.goToPage(${this.currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>`;

            buttonsContainer.innerHTML = buttons;
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadTeams();
        },

        searchTeams(query) {
            this.searchQuery = query;
            this.currentPage = 1;
            this.loadTeams();
        },

        filterTeams() {
            this.filterLevel = document.getElementById('filterTeamLevel').value;
            this.filterCountry = document.getElementById('filterTeamCountry').value;
            this.filterStatus = document.getElementById('filterTeamStatus').value;
            this.currentPage = 1;
            this.loadTeams();
        },

        openAddTeam() {
            document.getElementById('modalTeamTitle').textContent = 'Add Support Team';
            document.getElementById('teamId').value = '';
            document.getElementById('teamName').value = '';
            document.getElementById('teamCode').value = '';
            document.getElementById('teamLevel').value = '';
            document.getElementById('teamDepartment').value = '';
            document.getElementById('teamOfficeId').value = '';
            document.getElementById('teamCountry').value = '';
            document.getElementById('teamManagerId').value = '';
            document.getElementById('teamEmail').value = '';
            document.getElementById('teamDesc').value = '';
            document.getElementById('teamStatus').value = 'active';

            // Load dropdown data
            this.loadOffices();
            this.loadCountries();
            this.loadManagers();

            document.getElementById('modalTeam').classList.add('active');
        },

        async loadOffices() {
            try {
                const response = await fetch(`${this.baseUrl}employees/offices`);
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('teamOfficeId');
                    select.innerHTML = '<option value="">Select Office</option>';
                    data.data.forEach(office => {
                        const option = document.createElement('option');
                        option.value = office.id;
                        option.textContent = office.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading offices:', error);
            }
        },

        async loadCountries() {
            try {
                const response = await fetch(`${this.baseUrl}employees/countries`);
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('teamCountry');
                    select.innerHTML = '<option value="">Select Country</option>';
                    data.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.country;
                        option.textContent = item.country;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading countries:', error);
            }
        },

        async loadManagers() {
            try {
                const response = await fetch(`${this.baseUrl}employees/managers`);
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('teamManagerId');
                    select.innerHTML = '<option value="">Select Manager</option>';
                    data.data.forEach(manager => {
                        const option = document.createElement('option');
                        option.value = manager.employee_id;
                        option.textContent = `${manager.fullname} (${manager.department_code || 'N/A'})`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading managers:', error);
            }
        },
        async editTeam(id) {
        try {
            // Load dropdowns first
            await Promise.all([
                this.loadOffices(),
                this.loadCountries(),
                this.loadManagers()
            ]);
            
            const response = await fetch(`${this.baseUrl}support-teams/show/${id}`);
            const data = await response.json();

            if (data.success) {
                const team = data.data;
                document.getElementById('modalTeamTitle').textContent = 'Edit Support Team';
                document.getElementById('teamId').value = team.id;
                document.getElementById('teamName').value = team.name;
                document.getElementById('teamCode').value = team.code;
                document.getElementById('teamLevel').value = team.support_level;
                document.getElementById('teamDepartment').value = team.department_code || '';
                document.getElementById('teamOfficeId').value = team.office_id || '';
                document.getElementById('teamCountry').value = team.country || '';
                document.getElementById('teamManagerId').value = team.manager_id || '';
                document.getElementById('teamEmail').value = team.email || '';
                document.getElementById('teamDesc').value = team.description || '';
                document.getElementById('teamStatus').value = team.status;
                document.getElementById('modalTeam').classList.add('active');
            }
        } catch (error) {
            TicketNotifier.showError('Error loading team');
        }
    },

        async saveTeam() {
            const id = document.getElementById('teamId').value;
            const formData = {
                name: document.getElementById('teamName').value,
                code: document.getElementById('teamCode').value,
                support_level: document.getElementById('teamLevel').value,
                department_code: document.getElementById('teamDepartment').value,
                office_id: document.getElementById('teamOfficeId').value || null,
                country: document.getElementById('teamCountry').value || null,
                manager_id: document.getElementById('teamManagerId').value || null,
                email: document.getElementById('teamEmail').value || null,
                description: document.getElementById('teamDesc').value,
                status: document.getElementById('teamStatus').value
            };

            const teamFieldMap = {
                name: 'teamName',
                code: 'teamCode',
                support_level: 'teamLevel',
                department_code: 'teamDepartment',
                office_id: 'teamOfficeId',
                country: 'teamCountry',
                manager_id: 'teamManagerId',
                email: 'teamEmail',
                description: 'teamDesc',
            };

            if (!this.validator.validateAndShowErrors(formData, teamFieldMap)) {
                // TicketNotifier.showValidationError('Please fix all validation errors');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}support-teams/update/${id}` : `${this.baseUrl}support-teams/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Team updated successfully' : 'Team created successfully');
                    this.closeModal();
                    this.loadTeams();
                } else {
                    TicketNotifier.showError(data.message || 'Error saving team');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving team');
            }
        },

        async deleteTeam(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to delete this team?', 'Yes, proceed'); if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}support-teams/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Team deleted successfully', () => this.loadTeams());
                }
            } catch (error) {
                TicketNotifier.showError('Error deleting team');
            }
        },

        async viewMembers(teamId, teamName) {
            this.selectedTeamId = teamId;

            // Update section title
            document.getElementById('membersTeamTitle').innerHTML =
                `<i class="fas fa-user-group"></i> Members of: <span style="color: #3b82f6;">${teamName}</span>`;

            // Show members section
            document.getElementById('membersSection').style.display = 'block';

            // Load team info and members
            await this.loadTeamInfo(teamId);
            MemberManager.loadMembers(teamId);
        },

        async loadTeamInfo(teamId) {
            try {
                const response = await fetch(`${this.baseUrl}support-teams/show/${teamId}`);
                const data = await response.json();

                if (data.success) {
                    const team = data.data;
                    document.getElementById('teamInfoSummary').innerHTML = `
                    <h4 style="margin-bottom: 15px; font-size: 18px;">${team.name}</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div class="team-info-row">
                            <span class="team-info-label">Support Level:</span>
                            <span class="team-info-value">${team.support_level}</span>
                        </div>
                        <div class="team-info-row">
                            <span class="team-info-label">Department:</span>
                            <span class="team-info-value">${team.department_code || '-'}</span>
                        </div>
                        <div class="team-info-row">
                            <span class="team-info-label">Country:</span>
                            <span class="team-info-value">${team.country || '-'}</span>
                        </div>
                        <div class="team-info-row">
                            <span class="team-info-label">Status:</span>
                            <span class="team-info-value">${team.status}</span>
                        </div>
                    </div>
                `;
                }
            } catch (error) {
                console.error('Error loading team info:', error);
            }
        },

        closeMembersSection() {
            document.getElementById('membersSection').style.display = 'none';
            this.selectedTeamId = null;
        },

        closeModal() {
            document.getElementById('modalTeam').classList.remove('active');
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

    // ==================== MEMBER MANAGER ====================
    const MemberManager = {
        baseUrl: '<?= base_url() ?>',
        currentTeamId: null,

        async loadMembers(teamId) {
            this.currentTeamId = teamId;

            try {
                const response = await fetch(`${this.baseUrl}team-members?team_id=${teamId}`);
                const data = await response.json();

                if (data.success) {
                    this.renderMembers(data.data);
                    this.updateMemberStats(data.data);
                }
            } catch (error) {
                console.error('Error loading members:', error);
            }
        },

        renderMembers(members) {
            const tbody = document.getElementById('membersTableBody');

            if (members.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 36px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px;">No members found. Add your first member!</p>
                    </td>
                </tr>
            `;
                return;
            }

            tbody.innerHTML = members.map((member, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${member.employee_id}</strong></td>
                <td>
                    <div style="line-height: 1.4;">
                        <div style="font-weight: 600;">${member.employee_name || 'Unknown'}</div>
                        ${member.employee_email ? `<div style="font-size: 12px; color: #6b7280;">${member.employee_email}</div>` : ''}
                        ${member.employee_department ? `<div style="font-size: 11px; color: #9ca3af;"><i class="fas fa-building"></i> ${member.employee_department}</div>` : ''}
                    </div>
                </td>
                <td><span class="role-badge ${member.role}">${member.role}</span></td>
                <td>${member.joined_at ? new Date(member.joined_at).toLocaleDateString() : '-'}</td>
                <td><span class="badge badge-${member.is_active ? 'success' : 'danger'}">${member.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="MemberManager.editMember(${member.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="MemberManager.removeMember(${member.id})">
                            <i class="fas fa-user-minus"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        },

        updateMemberStats(members) {
            const total = members.length;
            const active = members.filter(m => m.is_active).length;
            const leads = members.filter(m => m.role === 'lead').length;
            const managers = members.filter(m => m.role === 'manager').length;

            document.getElementById('totalMembers').textContent = total;
            document.getElementById('activeMembers').textContent = active;
            document.getElementById('leadMembers').textContent = leads;
            document.getElementById('managerMembers').textContent = managers;
        },

        openAddMember() {
            if (!this.currentTeamId) {
                TicketNotifier.showValidationError('Please select a team first');
                return;
            }

            document.getElementById('modalMemberTitle').textContent = 'Add Team Member';
            document.getElementById('memberId').value = '';
            document.getElementById('memberTeamId').value = this.currentTeamId;
            document.getElementById('memberEmployeeId').value = '';
            document.getElementById('memberEmployeeSearch').value = '';
            document.getElementById('memberRole').value = 'member';
            document.getElementById('memberStatus').value = '1';
            document.getElementById('memberJoinedDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('employeeDetailsPreview').style.display = 'none';
            document.getElementById('modalMember').classList.add('active');
        },

        async editMember(id) {
            try {
                const response = await fetch(`${this.baseUrl}team-members/show/${id}`);
                const data = await response.json();

                if (data.success) {
                    const member = data.data;
                    document.getElementById('modalMemberTitle').textContent = 'Edit Team Member';
                    document.getElementById('memberId').value = member.id;
                    document.getElementById('memberTeamId').value = member.team_id;
                    document.getElementById('memberEmployeeId').value = member.employee_id;
                    document.getElementById('memberEmployeeSearch').value = member.employee_name || '';
                    document.getElementById('memberRole').value = member.role;
                    document.getElementById('memberStatus').value = member.is_active ? '1' : '0';
                    document.getElementById('memberJoinedDate').value = member.joined_at ? member.joined_at.split(' ')[0] : '';

                    // Show employee details if available
                    if (member.employee_name) {
                        document.getElementById('previewName').textContent = member.employee_name;
                        document.getElementById('previewEmail').textContent = member.employee_email || '-';
                        document.getElementById('previewDepartment').textContent = member.employee_department || '-';
                        document.getElementById('previewOffice').textContent = member.employee_office || '-';
                        document.getElementById('employeeDetailsPreview').style.display = 'block';
                    }

                    document.getElementById('modalMember').classList.add('active');
                }
            } catch (error) {
                TicketNotifier.showError('Error loading member');
            }
        },

        async saveMember() {
            const id = document.getElementById('memberId').value;
            const teamId = document.getElementById('memberTeamId').value;

            const formData = {
                team_id: teamId,
                employee_id: document.getElementById('memberEmployeeId').value,
                role: document.getElementById('memberRole').value,
                is_active: document.getElementById('memberStatus').value === '1' ? 1 : 0,
                joined_at: document.getElementById('memberJoinedDate').value
            };

            if (!formData.employee_id || !formData.role) {
                TicketNotifier.showValidationError('Please fill in required fields');
                return;
            }

            try {
                const url = id ? `${this.baseUrl}team-members/update/${id}` : `${this.baseUrl}team-members/store`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess(id ? 'Member updated successfully' : 'Member added successfully');
                    this.closeModal();
                    this.loadMembers(teamId);
                } else {
                    TicketNotifier.showError(data.message || 'Error saving member');
                }
            } catch (error) {
                TicketNotifier.showError('Error saving member');
            }
        },

        async removeMember(id) {
            const confirmed = await TicketNotifier.confirm('Confirm Action', 'Are you sure you want to remove this member from the team?', 'Yes, proceed'); if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}team-members/delete/${id}`, {
                    method: 'POST'
                });

                const data = await response.json();

                if (data.success) {
                    TicketNotifier.showSuccess('Member removed successfully');
                    this.loadMembers(this.currentTeamId);
                }
            } catch (error) {
                TicketNotifier.showError('Error removing member');
            }
        },

        closeModal() {
            document.getElementById('modalMember').classList.remove('active');
        }
    };

    // ==================== EMPLOYEE AUTOCOMPLETE ====================
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSearchInput = document.getElementById('memberEmployeeSearch');
        const employeeDropdown = document.getElementById('employeeDropdown');
        const employeeIdInput = document.getElementById('memberEmployeeId');
        let searchTimeout;
        let allEmployees = [];

        // Load all active employees on page load
        async function loadActiveEmployees() {
            try {
                const response = await fetch(`${MemberManager.baseUrl}employees/active`);
                const data = await response.json();
                if (data.success) {
                    allEmployees = data.data;
                }
            } catch (error) {
                console.error('Error loading employees:', error);
            }
        }

        // Search and filter employees
        function searchEmployees(query) {
            if (!query || query.length < 2) {
                employeeDropdown.style.display = 'none';
                return;
            }

            const filtered = allEmployees.filter(emp => {
                const searchStr = query.toLowerCase();
                return emp.fullname.toLowerCase().includes(searchStr) ||
                    emp.email.toLowerCase().includes(searchStr) ||
                    emp.firstname.toLowerCase().includes(searchStr) ||
                    emp.lastname.toLowerCase().includes(searchStr);
            }).slice(0, 10); // Limit to 10 results

            if (filtered.length === 0) {
                employeeDropdown.innerHTML = '<div style="padding: 10px; color: #6b7280;">No employees found</div>';
                employeeDropdown.style.display = 'block';
                return;
            }

            employeeDropdown.innerHTML = filtered.map(emp => `
            <div class="employee-option" data-employee-id="${emp.employee_id}" 
                data-fullname="${emp.fullname}"
                data-email="${emp.email}"
                data-department="${emp.department_code || '-'}"
                data-office="${emp.office || '-'}">
                <div style="font-weight: 600; color: #1f2937;">${emp.fullname}</div>
                <div style="font-size: 12px; color: #6b7280;">
                    ${emp.email} • ${emp.department_code || 'N/A'}
                </div>
            </div>
        `).join('');

            employeeDropdown.style.display = 'block';

            // Add click handlers to options
            document.querySelectorAll('.employee-option').forEach(option => {
                option.addEventListener('click', function () {
                    selectEmployee(this);
                });
            });
        }

        // Select an employee
        function selectEmployee(optionElement) {
            const employeeId = optionElement.dataset.employeeId;
            const fullname = optionElement.dataset.fullname;
            const email = optionElement.dataset.email;
            const department = optionElement.dataset.department;
            const office = optionElement.dataset.office;

            employeeIdInput.value = employeeId;
            employeeSearchInput.value = fullname;
            employeeDropdown.style.display = 'none';

            // Show employee details preview
            document.getElementById('previewName').textContent = fullname;
            document.getElementById('previewEmail').textContent = email;
            document.getElementById('previewDepartment').textContent = department;
            document.getElementById('previewOffice').textContent = office;
            document.getElementById('employeeDetailsPreview').style.display = 'block';
        }

        if (employeeSearchInput) {
            // Load employees when page loads
            loadActiveEmployees();

            // Search on input
            employeeSearchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchEmployees(this.value);
                }, 300);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!employeeSearchInput.contains(e.target) && !employeeDropdown.contains(e.target)) {
                    employeeDropdown.style.display = 'none';
                }
            });

            // Show dropdown on focus if there's text
            employeeSearchInput.addEventListener('focus', function () {
                if (this.value.length >= 2) {
                    searchEmployees(this.value);
                }
            });
        }
    });


    // ==================== TAB INITIALIZATION ====================
    async function initTeamsTab() {
        console.log('Initializing Teams Tab...');
        showTabLoading('tab-teams');

        try {
            // TeamManager.init() calls both checkPrerequisites and loadTeams
            await TeamManager.init();
            console.log('✅ Teams Tab fully loaded');
        } catch (error) {
            console.error('Error initializing Teams Tab:', error);
        } finally {
            hideTabLoading('tab-teams');
        }
    }

    function cleanupTeamsTab() {
        console.log('Cleaning up Teams Tab...');
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.getElementById('membersSection').style.display = 'none';
    }

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
                <p>Loading data...</p>
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
        if (overlay) overlay.style.display = 'none';
    }
</script>