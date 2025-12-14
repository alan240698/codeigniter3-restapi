<div id="tab-monitoring" class="tab-content active">
    
    <!-- Real-time Stats -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; background: #dbeafe; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-ticket"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 32px; font-weight: 700; color: #1f2937;" id="totalTickets">0</div>
            <div class="stat-label" style="font-size: 14px; color: #6b7280;">Total Tickets</div>
        </div>

        <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; background: #fef3c7; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 32px; font-weight: 700; color: #1f2937;" id="openTickets">0</div>
            <div class="stat-label" style="font-size: 14px; color: #6b7280;">Open Tickets</div>
        </div>

        <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; background: #d1fae5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 32px; font-weight: 700; color: #1f2937;" id="resolvedTickets">0</div>
            <div class="stat-label" style="font-size: 14px; color: #6b7280;">Resolved Today</div>
        </div>

        <div class="stat-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 32px; font-weight: 700; color: #1f2937;" id="overdueTickets">0</div>
            <div class="stat-label" style="font-size: 14px; color: #6b7280;">SLA Overdue</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Tickets by Status -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Tickets by Status</h3>
            </div>
            <canvas id="chartTicketsByStatus" style="max-height: 300px;"></canvas>
        </div>

        <!-- Tickets by Priority -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Tickets by Priority</h3>
            </div>
            <canvas id="chartTicketsByPriority" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Tickets Timeline -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Tickets Timeline (Last 7 Days)</h3>
            <select id="timelineRange" onchange="MonitoringManager.loadTimeline()" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
        </div>
        <canvas id="chartTicketsTimeline" style="max-height: 300px;"></canvas>
    </div>

    <!-- Team Performance -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-users-gear"></i> Team Performance</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team Name</th>
                    <th>Level</th>
                    <th>Active Tickets</th>
                    <th>Resolved (Today)</th>
                    <th>Avg Response Time</th>
                    <th>SLA Compliance</th>
                </tr>
            </thead>
            <tbody id="teamPerformanceTableBody">
                <tr><td colspan="7" style="text-align: center; padding: 30px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <button class="btn btn-sm btn-secondary" onclick="MonitoringManager.loadRecentActivity()">
                <i class="fas fa-refresh"></i> Refresh
            </button>
        </div>
        <div id="recentActivityList" style="max-height: 400px; overflow-y: auto;">
            <div style="text-align: center; padding: 30px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
        </div>
    </div>
</div>

<style>
.activity-item {
    padding: 15px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    gap: 15px;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f9fafb;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.activity-icon.created { background: #dbeafe; color: #3b82f6; }
.activity-icon.assigned { background: #e9d5ff; color: #a855f7; }
.activity-icon.resolved { background: #d1fae5; color: #10b981; }
.activity-icon.escalated { background: #fef3c7; color: #f59e0b; }
.activity-icon.closed { background: #e5e7eb; color: #6b7280; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 5px;
}

.activity-meta {
    font-size: 12px;
    color: #6b7280;
}

.activity-time {
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
const MonitoringManager = {
    baseUrl: '<?= base_url() ?>',
    charts: {},
    refreshInterval: null,

    init() {
        this.loadStats();
        this.loadCharts();
        this.loadTeamPerformance();
        this.loadRecentActivity();
        
        // Auto-refresh every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadStats();
            this.loadRecentActivity();
        }, 30000);
    },

    async loadStats() {
        try {
            const response = await fetch(`${this.baseUrl}monitoring/stats`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('totalTickets').textContent = data.data.total || 0;
                document.getElementById('openTickets').textContent = data.data.open || 0;
                document.getElementById('resolvedTickets').textContent = data.data.resolved_today || 0;
                document.getElementById('overdueTickets').textContent = data.data.overdue || 0;
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    },

    async loadCharts() {
        try {
            const response = await fetch(`${this.baseUrl}monitoring/charts`);
            const data = await response.json();

            if (data.success) {
                this.renderStatusChart(data.data.by_status);
                this.renderPriorityChart(data.data.by_priority);
                this.loadTimeline();
            }
        } catch (error) {
            console.error('Error loading charts:', error);
        }
    },

    renderStatusChart(data) {
        const ctx = document.getElementById('chartTicketsByStatus');
        
        if (this.charts.status) this.charts.status.destroy();
        
        this.charts.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#6b7280']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    },

    renderPriorityChart(data) {
        const ctx = document.getElementById('chartTicketsByPriority');
        
        if (this.charts.priority) this.charts.priority.destroy();
        
        this.charts.priority = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    label: 'Tickets',
                    data: data.map(d => d.count),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    },

    async loadTimeline() {
        try {
            const days = document.getElementById('timelineRange').value;
            const response = await fetch(`${this.baseUrl}monitoring/timeline?days=${days}`);
            const data = await response.json();

            if (data.success) {
                this.renderTimelineChart(data.data);
            }
        } catch (error) {
            console.error('Error loading timeline:', error);
        }
    },

    renderTimelineChart(data) {
        const ctx = document.getElementById('chartTicketsTimeline');
        
        if (this.charts.timeline) this.charts.timeline.destroy();
        
        this.charts.timeline = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [
                    {
                        label: 'Created',
                        data: data.map(d => d.created),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Resolved',
                        data: data.map(d => d.resolved),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    },

    async loadTeamPerformance() {
        try {
            const response = await fetch(`${this.baseUrl}monitoring/team-performance`);
            const data = await response.json();

            if (data.success) {
                this.renderTeamPerformance(data.data);
            }
        } catch (error) {
            console.error('Error loading team performance:', error);
        }
    },

    renderTeamPerformance(teams) {
        const tbody = document.getElementById('teamPerformanceTableBody');

        if (teams.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px;">No data available</td></tr>';
            return;
        }

        tbody.innerHTML = teams.map((team, i) => {
            const slaCompliance = team.sla_compliance || 0;
            const slaColor = slaCompliance >= 90 ? 'success' : slaCompliance >= 75 ? 'warning' : 'danger';

            return `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${team.name}</strong></td>
                    <td><span class="level-badge l${team.level.toLowerCase().replace('l', '')}">${team.level}</span></td>
                    <td><span class="badge badge-warning">${team.active_tickets || 0}</span></td>
                    <td><span class="badge badge-success">${team.resolved_today || 0}</span></td>
                    <td>${team.avg_response_time || '-'}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; background: ${slaColor === 'success' ? '#10b981' : slaColor === 'warning' ? '#f59e0b' : '#ef4444'}; width: ${slaCompliance}%;"></div>
                            </div>
                            <span style="font-weight: 600; font-size: 13px;">${slaCompliance}%</span>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    async loadRecentActivity() {
        try {
            const response = await fetch(`${this.baseUrl}monitoring/recent-activity`);
            const data = await response.json();

            if (data.success) {
                this.renderRecentActivity(data.data);
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    },

    renderRecentActivity(activities) {
        const container = document.getElementById('recentActivityList');

        if (activities.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 30px; color: #6b7280;">No recent activity</div>';
            return;
        }

        container.innerHTML = activities.map(activity => {
            const icon = this.getActivityIcon(activity.action_type);
            
            return `
                <div class="activity-item">
                    <div class="activity-icon ${activity.action_type}">
                        <i class="fas fa-${icon}"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">${activity.title}</div>
                        <div class="activity-meta">
                            ${activity.employee_name} • Ticket #${activity.ticket_number}
                        </div>
                    </div>
                    <div class="activity-time">${activity.time_ago}</div>
                </div>
            `;
        }).join('');
    },

    getActivityIcon(actionType) {
        const icons = {
            created: 'plus-circle',
            assigned: 'user-check',
            status_changed: 'sync',
            resolved: 'check-circle',
            escalated: 'arrow-up',
            closed: 'times-circle',
            commented: 'comment'
        };
        return icons[actionType] || 'circle';
    },

    cleanup() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
    }
};

// TAB INITIALIZATION
function initMonitoringTab() {
    console.log('Initializing Monitoring Tab...');
    MonitoringManager.init();
}

function cleanupMonitoringTab() {
    console.log('Cleaning up Monitoring Tab...');
    MonitoringManager.cleanup();
}
</script>