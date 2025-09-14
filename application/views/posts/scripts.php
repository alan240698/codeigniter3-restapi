<script>
    const API_BASE_URL = '/api/posts';
    const CSRF_COOKIE_NAME = '<?= $this->config->item('csrf_cookie_name'); ?>';

    let currentPage = 1;
    let totalPages = 1;

    // Get cookie
    function getCookie(name) {
        const v = document?.cookie.split('; ').find(row => row.startsWith(name + '='));
        return v ? decodeURIComponent(v.split('=')[1]) : '';
    }

    // Escape HTML
    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ---- VALIDATION RULES ----
    const TITLE_MIN = 2,
        TITLE_MAX = 255;
    const DESC_MIN = 10,
        DESC_MAX = 65000;

    // Show field error
    function showFieldError($el, msg) {
        $el.addClass('is-invalid');
        $el.siblings('.invalid-feedback').text(msg);
    }

    // Clear field error
    function clearFieldError($el) {
        $el.removeClass('is-invalid');
        $el.siblings('.invalid-feedback').empty();
    }

    // Validation post from
    function validatePostForm(data) {
        let isCheck = true;

        const title = (data?.title || '').trim();
        const desc  = (data?.description || '').trim();

        // Clear first
        clearFieldError($('#title'));
        clearFieldError($('#description'));

        // Title
        if (!title) {
            showFieldError($('#title'), 'Title is required.');
            isCheck = false;
        } else if (title.length < TITLE_MIN) {
            showFieldError($('#title'), `Title must be at least ${TITLE_MIN} characters.`);
            isCheck = false;
        } else if (title.length > TITLE_MAX) {
            showFieldError($('#title'), `Title must be ≤ ${TITLE_MAX} characters.`);
            isCheck = false;
        }

        // Description
        if (!desc) {
            showFieldError($('#description'), 'Description is required.');
            isCheck = false;
        } else if (desc.length < DESC_MIN) {
            showFieldError($('#description'), `Description must be at least ${DESC_MIN} characters.`);
            isCheck = false;
        } else if (desc.length > DESC_MAX) {
            showFieldError($('#description'), `Description must be ≤ ${DESC_MAX} characters.`);
            isCheck = false;
        }

        return isCheck;
    }

    $.ajaxSetup({
        xhrFields: {
            withCredentials: true
        },
        beforeSend: function(xhr, settings) {
            const method = (settings?.type || '').toUpperCase();
            if (method && method !== 'GET' && method !== 'OPTIONS') {
                const csrf = getCookie(CSRF_COOKIE_NAME);
                if (csrf) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
                }
            }
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        }
    });

    // Initialize
    $(document).ready(function() {
        loadPosts();
        initializeEventHandlers();

        $('#title').attr('maxlength', TITLE_MAX);
        $('#description').attr('maxlength', DESC_MAX);

        $('#title').attr('minlength', TITLE_MIN);
        $('#description').attr('minlength', DESC_MIN);
    });

    // Event handlers
    function initializeEventHandlers() {
        // New post button
        $('#btnNew').click(function() {
            openPostModal();
        });

        // Post form submission
        $('#postForm').submit(function(e) {
            e.preventDefault();
            savePost();
        });

        // Clear form validation on input
        $('.form-control').on('input', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').empty();
        });
    }

    // Load posts from API
    function loadPosts(page = 1) {
        currentPage = page;
        showLoading(true);
        hideEmptyState();

        $.ajax({
            url: API_BASE_URL,
            method: 'GET',
            data: {
                page: page,
                per_page: 10
            },
            success: function(response) {
                if (response.success) {
                    displayPosts(response.data);
                    updatePagination(response?.meta?.pagination);
                } else {
                    showError('Failed to load posts');
                    showEmptyState();
                }
            },
            error: function(xhr, status, error) {
                showError('Failed to load posts: ' + error);
                showEmptyState();
            },
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Display posts in table
    function displayPosts(posts) {
        const tbody = $('#postsTableBody');
        tbody.empty();

        if (!posts || posts.length === 0) {
            showEmptyState();
            return;
        }

        posts.forEach(function(post, index) {
            const rawTitle = post.title || '';
            const rawDesc = post.description || '';

            const titleShort = rawTitle.length > 30 ? rawTitle.substring(0, 30) + '...' : rawTitle;
            const descShort = rawDesc.length > 50 ? rawDesc.substring(0, 50) + '...' : rawDesc;

            const row = `
                <tr class="fade-in">
                    <td>${escapeHtml(post.id)}</td>
                    <td><span class="title-preview" title="${escapeHtml(rawTitle)}">${escapeHtml(titleShort)}</span></td>
                    <td><span class="description-preview" title="${escapeHtml(rawDesc)}">${escapeHtml(descShort)}</span></td>
                    <td class="action-buttons text-center">
                        <div class="btn-group-actions">
                            <button class="btn btn-info btn-sm" onclick="viewPost(${Number(post.id)})" title="View">
                                <i class="fas fa-eye"></i> Show
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="editPost(${Number(post.id)})" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deletePost(${Number(post.id)})" title="Delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Update pagination
    function updatePagination(pagination) {
        if (!pagination) return;
        totalPages = pagination.total_pages;
        const paginationEl = $('#pagination');
        paginationEl.empty();

        if (totalPages <= 1) return;

        // Previous button
        const prevClass = pagination.has_prev ? '' : 'disabled';
        paginationEl.append(`
                <li class="page-item ${prevClass}">
                    <a class="page-link" href="#" onclick="loadPosts(${currentPage - 1})">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            paginationEl.append(`
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" onclick="loadPosts(${i})">${i}</a>
                    </li>
                `);
        }

        // Next button
        const nextClass = pagination.has_next ? '' : 'disabled';
        paginationEl.append(`
                <li class="page-item ${nextClass}">
                    <a class="page-link" href="#" onclick="loadPosts(${currentPage + 1})">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `);
    }

    // Open post modal for create/edit
    function openPostModal(post = null) {
        const modal = $('#postModal');
        const form = $('#postForm');

        // Reset form
        form[0].reset();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').empty();

        if (post) {
            // Edit mode
            $('#postModalLabel').html('<i class="fas fa-edit me-2"></i>Edit Post');
            $('#saveText').text('Update Post');
            $('#postId').val(post.id);
            $('#title').val(post.title);
            $('#description').val(post.description);
        } else {
            // Create mode
            $('#postModalLabel').html('<i class="fas fa-plus me-2"></i>Add New Post');
            $('#saveText').text('Save Post');
            $('#postId').val('');
        }

        modal.modal('show');
    }

    // Save post (create or update)
    function savePost() {
        const form = $('#postForm');
        const postId = $('#postId').val();
        const isEdit = postId !== '';



        // Prepare data
        const formData = {
            title: $('#title').val().trim(),
            description: $('#description').val().trim()
        };

        // Validate form
        if (!validatePostForm(formData)) {
            return;
        }

        // Show loading
        $('#saveSpinner').removeClass('d-none');
        $('#btnSave').prop('disabled', true);

        const ajaxConfig = {
            url: isEdit ? `${API_BASE_URL}/${postId}` : API_BASE_URL,
            method: isEdit ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    $('#postModal').modal('hide');
                    showSuccess(response.message || (isEdit ? 'Post updated successfully' : 'Post created successfully'));
                    loadPosts(currentPage);
                } else {
                    showError(response.message || 'Failed to save post');
                }
            },
            error: function(xhr, status, error) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    handleValidationErrors(xhr.responseJSON.errors);
                } else {
                    const message = xhr.responseJSON ? xhr.responseJSON.message : error;
                    showError('Failed to save post: ' + message);
                }
            },
            complete: function() {
                $('#saveSpinner').addClass('d-none');
                $('#btnSave').prop('disabled', false);
            }
        };

        $.ajax(ajaxConfig);
    }

    // Edit post
    function editPost(id) {
        $.ajax({
            url: `${API_BASE_URL}/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    openPostModal(response.data);
                } else {
                    showError('Failed to load post data');
                }
            },
            error: function(xhr, status, error) {
                showError('Failed to load post data: ' + error);
            }
        });
    }

    // View post details
    function viewPost(id) {
        $.ajax({
            url: `${API_BASE_URL}/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const post = response.data;
                    $('#viewPostId').text(post.id);
                    $('#viewPostTitle').text(post.title || 'N/A');
                    $('#viewPostDescription').text(post.description || 'N/A');
                    $('#viewPostCreated').text(post.created_at ? new Date(post.created_at).toLocaleDateString('vi-VN') : 'N/A');
                    $('#viewPostUpdated').text(post.updated_at ? new Date(post.updated_at).toLocaleDateString('vi-VN') : 'N/A');
                    $('#viewPostModal').modal('show');
                } else {
                    showError('Failed to load post data');
                }
            },
            error: function(xhr, status, error) {
                showError('Failed to load post data: ' + error);
            }
        });
    }

    // Delete post with confirmation
    function deletePost(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_BASE_URL}/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                'Post has been deleted.',
                                'success'
                            );
                            loadPosts(currentPage);
                        } else {
                            showError('Failed to delete post');
                        }
                    },
                    error: function(xhr, status, error) {
                        showError('Failed to delete post: ' + error);
                    }
                });
            }
        });
    }

    // Handle validation errors
    function handleValidationErrors(errors) {
        Object.keys(errors).forEach(function(field) {
            let inputField;

            // Map API field names to form field names
            switch (field) {
                case 'title':
                    inputField = $('#title');
                    break;
                case 'description':
                    inputField = $('#description');
                    break;
                default:
                    return;
            }

            if (inputField.length) {
                inputField.addClass('is-invalid');
                inputField.siblings('.invalid-feedback').text(errors[field]);
            }
        });
    }

    // Utility functions
    function showLoading(show) {
        if (show) {
            $('#loading').show();
            $('#postsTable').hide();
        } else {
            $('#loading').hide();
            $('#postsTable').show();
        }
    }

    function showEmptyState() {
        $('#postsTable').hide();
        $('#emptyState').show();
    }

    function hideEmptyState() {
        $('#emptyState').hide();
        $('#postsTable').show();
    }

    function showSuccess(message) {
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }

    function showError(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
</script>