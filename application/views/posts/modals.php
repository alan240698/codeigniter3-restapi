<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Add New Post
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="postForm">
                <div class="modal-body">
                    <input type="hidden" id="postId" name="id">

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner"></span>
                        <i class="fas fa-save me-1"></i>
                        <span id="saveText">Save Post</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Post Modal -->
<div class="modal fade" id="viewPostModal" tabindex="-1" aria-labelledby="viewPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPostModalLabel">
                    <i class="fas fa-eye me-2"></i>Post Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-3">
                        <strong>ID:</strong>
                    </div>
                    <div class="col-sm-9" id="viewPostId"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Title:</strong>
                    </div>
                    <div class="col-sm-9" id="viewPostTitle"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Description:</strong>
                    </div>
                    <div class="col-sm-9" id="viewPostDescription"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-sm-9" id="viewPostCreated"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Updated:</strong>
                    </div>
                    <div class="col-sm-9" id="viewPostUpdated"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>