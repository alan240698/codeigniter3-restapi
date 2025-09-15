<div class="container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Main Card -->
    <div class="card fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-file-alt me-2"></i>CRUD REST API</h4>
                <a href="https://github.com/alan240698/codeigniter3-restapi" target="_blank" class="text-white-50 text-decoration-none small">
                    <i class="fab fa-github me-1"></i>View on GitHub
                </a>
            </div>
            <button class="btn btn-light btn-sm" id="btnNew">
                <i class="fas fa-plus me-1"></i>New Post
            </button>
        </div>

        <div class="card-body p-0">
            <!-- Loading Spinner -->
            <div class="loading" id="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading posts...</div>
            </div>

            <!-- Posts Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="postsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="postsTableBody">
                        <!-- Table data -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No posts found</h5>
                <p class="text-muted">Start by adding your first post</p>
                <button class="btn btn-primary" id="btnNewBody">
                    <i class="fas fa-plus me-1"></i>Add Post
                </button>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-white">
            <nav aria-label="Posts pagination">
                <ul class="pagination mb-0" id="pagination">
                    <!-- Pagination data -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modals -->
<?php $this->load->view('posts/modals'); ?>