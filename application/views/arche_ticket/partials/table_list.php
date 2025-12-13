<!-- Filter Section -->
<div class="row mb-3">
    <!-- <div class="col-md-3">
        <label class="form-label fw-semibold">Category:</label>
        <select class="form-select form-select-sm filter-select" id="filter-category" data-filter="category">
            <option value="">--Category--</option>
        </select>
    </div> -->
    <!-- <div class="col-md-3">
        <label class="form-label fw-semibold">Type:</label>
        <select class="form-select form-select-sm filter-select" id="filter-type" data-filter="priority">
            <option value="">--Type--</option>
        </select>
    </div> -->
    <!-- <div class="col-md-3">
        <label class="form-label fw-semibold">Status:</label>
        <select class="form-select form-select-sm filter-select" id="filter-status" data-filter="status">
            <option value="">--Status--</option>
            <option value="Processing">Processing</option>
            <option value="New">New</option>
            <option value="Assigned">Assigned</option>
            <option value="Pending">Pending</option>
            <option value="Solved">Solved</option>
            <option value="Closed">Closed</option>
        </select>
    </div> -->
    <!-- <div class="col-md-3">
        <label class="form-label fw-semibold">Assigned to:</label>
        <select class="form-select form-select-sm filter-select" id="filter-assigned" data-filter="entity">
            <option value="">--Assigned to--</option>
        </select>
    </div> -->
</div>

<!-- Search & Actions -->
<div class="row mb-3">
    <div class="col-md-6">
        <!-- <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="search-input" placeholder="Search tickets...">
            <button class="btn btn-outline-secondary" type="button" id="btn-clear-search">
                <i class="fas fa-times"></i>
            </button>
        </div> -->
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-secondary me-2" id="btn-refresh">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <button type="button" class="btn btn-info text-white" id="btn-create-ticket">
            <i class="fas fa-plus"></i> Create New
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div id="table-loading" class="text-center py-5" style="display: none;">
    <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 text-muted">Loading tickets...</p>
</div>

<!-- Empty State -->
<div id="table-empty" class="text-center py-5" style="display: none;">
    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
    <p class="text-muted">No tickets found</p>
</div>

<!-- Tickets Table -->
<div class="table-responsive" id="table-container">
    <table class="table table-hover align-middle modern-table" id="tickets-table">
        <thead>
            <tr>
                <th style="width: 60px;" class="text-center">
                    <i class="fas fa-hashtag me-1"></i>ID
                </th>
                <th style="width: 200px;" class="sortable" data-sort="title">
                    <i class="fas fa-heading me-1"></i>Title
                    <i class="fas fa-sort sort-icon ms-1"></i>
                </th>
                <th style="width: 250px;">
                    <i class="fas fa-align-left me-1"></i>Description
                </th>
                <th style="width: 150px;" class="sortable" data-sort="supporter">
                    <i class="fas fa-user-tie me-1"></i>Assigned To
                    <i class="fas fa-sort sort-icon ms-1"></i>
                </th>
                <th style="width: 140px;" class="sortable" data-sort="category">
                    <i class="fas fa-folder me-1"></i>Category
                    <i class="fas fa-sort sort-icon ms-1"></i>
                </th>
                <th style="width: 120px;" class="sortable" data-sort="status">
                    <i class="fas fa-info-circle me-1"></i>Status
                    <i class="fas fa-sort sort-icon ms-1"></i>
                </th>
                <th style="width: 140px;" class="sortable" data-sort="created_date">
                    <i class="fas fa-calendar-plus me-1"></i>Date Created
                    <i class="fas fa-sort sort-icon ms-1"></i>
                </th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- Data will be loaded here -->
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted" id="table-info">
        Showing 0 to 0 of 0 entries
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Entries per page -->
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Show</label>
            <select class="form-select form-select-sm" id="entries-per-page" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>entries</span>
        </div>

        <!-- Pagination buttons -->
        <nav aria-label="Table pagination">
            <ul class="pagination pagination-sm mb-0" id="pagination-controls">
                <li class="page-item disabled" id="page-prev">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <!-- Page numbers will be inserted here -->
                <li class="page-item disabled" id="page-next">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

