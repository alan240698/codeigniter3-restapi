window.PostManager = {
    // State
    currentPage: 1,
    totalPages: 1,
    originalData: {},
    currentRequest: null,
    isEditing: false,

    // Initialize
    init() {
        this.bindEvents();
        this.setupFormAttributes();
        this.loadPosts();
    },

    // Event binding
    bindEvents() {
        // New post
        $('#btnNew').off('click.post').on('click.post', (e) => {
            e.preventDefault();
            this.openModal();
        });

        // New post in body table
        $('#btnNewBody').off('click.post').on('click.post', (e) => {
            e.preventDefault();
            this.openModal();
        });

        // Form submit
        $('#postForm').off('submit.post').on('submit.post', (e) => {
            e.preventDefault();
            this.savePost();
        });

        // Clear validation on input
        $('.form-control').off('input.post').on('input.post', function () {
            $(this).removeClass('is-invalid').siblings('.invalid-feedback').empty();
        });

        // Modal close
        $('#postModal').off('hidden.bs.modal.post').on('hidden.bs.modal.post', () => {
            this.resetForm();
        });

        // Post actions (using delegation)
        $('#postsTableBody')
            .off('click.post')
            .on('click.post', '.btn-view', (e) => {
                const id = $(e.target).closest('tr').data('post-id');
                if (Utils.validateId(id)) this.viewPost(id);
            })
            .on('click.post', '.btn-edit', (e) => {
                const id = $(e.target).closest('tr').data('post-id');
                if (Utils.validateId(id)) this.editPost(id);
            })
            .on('click.post', '.btn-delete', (e) => {
                const id = $(e.target).closest('tr').data('post-id');
                if (Utils.validateId(id)) this.deletePost(id);
            });

        // Event onchange field
        $('#title').off('input').on('input', (e) => {
            this.validateField('title', e);
        });

        $('#description').off('input').on('input', (e) => {
            this.validateField('description', e);
        });
    },

    // Setup form attributes
    setupFormAttributes() {
        const config = window.AppConfig.VALIDATION;
        $('#title').attr({'minlength': config.TITLE.MIN });
        $('#description').attr({'minlength': config.DESCRIPTION.MIN });
    },

    // Validate Field
    validateField(field, e) {
        const fieldValue = e.currentTarget.value.trim();
        const formData   = {};
        formData[field]  = fieldValue;

        // Validate
        const validation = Utils.validateOnChangePostForm(formData);
        const isErrorsEmpty = Object.keys(validation?.errors).length === 0;

        // Reset error
        if (validation?.isValid && isErrorsEmpty) {
            console.log(field)
            this.hiddenValidationErrors(field);
            return;
        }

        if (!validation?.isValid) {
            // Show error field
            this.showOnChangeValidationErrors(field, validation?.errors[field]);
        } else {
            // Hidden error field
            this.hiddenValidationErrors(field);
        }
    },

    // API calls
    async loadPosts(page = 1) {
        try {
            page = Utils.validatePageNumber(page, this.totalPages);
            this.currentPage = page;

            this.showLoading(true);

            // Cancel previous request
            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            this.currentRequest = $.ajax({
                url: window.AppConfig.API.BASE_URL,
                method: 'GET',
                data: { page, per_page: window?.AppConfig?.API.PER_PAGE },
                timeout: window?.AppConfig?.API.TIMEOUT
            });

            const response = await this.currentRequest;

            if (!response?.success || !Array.isArray(response?.data)) {
                throw new Error(response?.message || 'Invalid response');
            }

            if (response?.success && Array.isArray(response?.data) && response?.data?.length === 0) {
                page = parseInt(page) - 1 > 0 ? parseInt(page) - 1 : 1;
                this.currentPage = page;

                this.currentRequest = $.ajax({
                    url: window.AppConfig.API.BASE_URL,
                    method: 'GET',
                    data: { page, per_page: window.AppConfig.API.PER_PAGE },
                    timeout: window.AppConfig.API.TIMEOUT
                });

                // Get new response and check new responsive
                const newResponse = await this.currentRequest;
                if (!newResponse?.success || !Array.isArray(newResponse?.data)) {
                    throw new Error(newResponse?.message || 'Invalid response');
                }

                this.displayPosts(newResponse?.data);
                this.updatePagination(newResponse?.meta?.pagination);
            } else {
                this.displayPosts(response?.data);
                this.updatePagination(response?.meta?.pagination);
            }

        } catch (error) {
            console.error('Load posts error:', error);
            if (error.statusText !== 'abort') {
                Utils.notify.error(this.formatErrorMessage(error, 'Failed to load posts'));
                this.showEmptyState();
            }
        } finally {
            this.showLoading(false);
            this.currentRequest = null;
        }
    },

    async savePost() {
        if ($('#btnSave').prop('disabled')) return;

        const formData = {
            title: ($('#title').val() || '').trim(),
            description: ($('#description').val() || '').trim()
        };

        // Validate
        const validation = Utils.validatePostForm(formData);
        if (!validation.isValid) {
            this.showValidationErrors(validation.errors);
            return;
        }

        // Check for changes
        if (this.isEditing && this.hasNoChanges(formData)) {
            Utils.notify.info('No changes detected');
            $('#postModal').modal('hide');
            return;
        }

        try {
            this.showSaveLoading(true);

            const postId = $('#postId').val();
            const url = this.isEditing ?
                `${window.AppConfig.API.BASE_URL}/${postId}` :
                window.AppConfig.API.BASE_URL;

            const response = await $.ajax({
                url: url,
                method: this.isEditing ? 'PUT' : 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                timeout: window.AppConfig.API.TIMEOUT
            });

            if (!response?.success) {
                throw new Error(response?.message || 'Save failed');
            }

            $('#postModal').modal('hide');
            Utils.notify.success(response.message || 'Post saved successfully');
            this.loadPosts(this.isEditing ? this.currentPage : 1);

        } catch (error) {
            console.error('Save error:', error);

            if (error.responseJSON?.errors) {
                this.showValidationErrors(error.responseJSON.errors);
            } else {
                Utils.notify.error(this.formatErrorMessage(error, 'Failed to save'));
            }
        } finally {
            this.showSaveLoading(false);
        }
    },

    async editPost(id) {
        try {
            const response = await $.ajax({
                url: `${window.AppConfig.API.BASE_URL}/${id}`,
                timeout: window.AppConfig.UI.LOADING_TIMEOUT
            });

            if (!response?.success) {
                throw new Error('Invalid response');
            }

            this.openModal(response.data);

        } catch (error) {
            Utils.notify.error(this.formatErrorMessage(error, 'Failed to load post'));
        }
    },

    async viewPost(id) {
        try {
            const response = await $.ajax({
                url: `${window.AppConfig.API.BASE_URL}/${id}`,
                timeout: window.AppConfig.UI.LOADING_TIMEOUT
            });

            if (!response?.success) {
                throw new Error('Invalid response');
            }

            const post = response.data;
            $('#viewPostId').text(post.id);
            $('#viewPostTitle').text(post.title || 'N/A');
            $('#viewPostDescription').text(post.description || 'N/A');
            $('#viewPostCreated').text(Utils.formatDate(post.created_at));
            $('#viewPostUpdated').text(Utils.formatDate(post.updated_at));
            $('#viewPostModal').modal('show');

        } catch (error) {
            Utils.notify.error(this.formatErrorMessage(error, 'Failed to load post'));
        }
    },

    async deletePost(id) {
        try {
            const confirmed = await Utils.notify.confirm('Delete this post?');
            if (!confirmed) return;

            const response = await $.ajax({
                url: `${window.AppConfig.API.BASE_URL}/${id}`,
                method: 'DELETE',
                timeout: window.AppConfig.UI.LOADING_TIMEOUT
            });

            if (!response?.success) {
                throw new Error(response?.message || 'Delete failed');
            }

            Utils.notify.success('Post deleted successfully');
            this.loadPosts(this.currentPage);

        } catch (error) {
            Utils.notify.error(this.formatErrorMessage(error, 'Failed to delete'));
        }
    },

    // UI Methods
    displayPosts(posts) {
        const tbody = $('#postsTableBody');
        tbody.empty();

        if (!posts?.length) {
            this.showEmptyState();
            return;
        }

        posts.forEach(post => {
            if (!Utils.validateId(post.id)) return;

            const row = this.createPostRow(post);
            tbody.append(row);
        });

        this.hideEmptyState();
    },

    createPostRow(post) {
        const config = window.AppConfig.UI;
        const titleShort = Utils.truncate(post.title, config.TRUNCATE_TITLE);
        const descShort = Utils.truncate(post.description, config.TRUNCATE_DESC);

        return $(`
            <tr class="fade-in" data-post-id="${Utils.escapeHtml(post.id)}">
                <td>${Utils.escapeHtml(post.id)}</td>
                <td>
                    <span class="title-preview" title="${Utils.escapeHtml(post.title)}">
                        ${Utils.escapeHtml(titleShort)}
                    </span>
                </td>
                <td>
                    <span class="description-preview" title="${Utils.escapeHtml(post.description)}">
                        ${Utils.escapeHtml(descShort)}
                    </span>
                </td>
                <td class="action-buttons text-center">
                    <div class="btn-group-actions">
                        <button class="btn btn-info btn-sm btn-view" title="View">
                            <i class="fas fa-eye"></i> Show
                        </button>
                        <button class="btn btn-warning btn-sm btn-edit" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete" title="Delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `);
    },

    updatePagination(pagination) {
        const container = $('#pagination');
        container.empty();

        if (!pagination || pagination.total_pages <= 1) return;

        this.totalPages = pagination.total_pages;
        this.renderPagination(container, pagination);
    },

    renderPagination(container, pagination) {
        container.empty();

        const maxVisiblePages = 5;
        const current = this.currentPage;
        const total = this.totalPages;

        // Previous button
        const prevDisabled = !pagination.has_prev ? 'disabled' : '';
        container.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${current - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `);

        // Calculate visible range
        let startPage = Math.max(1, current - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(total, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // First page and left gap
        if (startPage > 1) {
            container.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `);

            if (startPage > 2) {
                // Dropdown for gap pages
                const leftGapPages = [];
                for (let i = 2; i < startPage; i++) {
                    leftGapPages.push(i);
                }

                if (leftGapPages.length === 1) {
                    // If there is only 1 page, then display directly
                    container.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${leftGapPages[0]}">${leftGapPages[0]}</a>
                        </li>
                    `);
                } else {
                    // Dropdown for many pages
                    container.append(`
                        <li class="page-item dropdown">
                            <a class="page-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu">
                                ${leftGapPages.map(p => 
                                    `<a class="dropdown-item pagination-gap-item" href="#" data-page="${p}">Trang ${p}</a>`
                                ).join('')}
                            </div>
                        </li>
                    `);
                }
            }
        }

        // Main visible pages
        for (let i = startPage; i <= endPage; i++) {
            const active = i === current ? 'active' : '';
            container.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Right gap và last page
        if (endPage < total) {
            if (endPage < total - 1) {
                const rightGapPages = [];
                for (let i = endPage + 1; i < total; i++) {
                    rightGapPages.push(i);
                }

                if (rightGapPages.length === 1) {
                    container.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${rightGapPages[0]}">${rightGapPages[0]}</a>
                        </li>
                    `);
                } else {
                    container.append(`
                        <li class="page-item dropdown">
                            <a class="page-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu">
                                ${rightGapPages.map(p => 
                                    `<a class="dropdown-item pagination-gap-item" href="#" data-page="${p}">Trang ${p}</a>`
                                ).join('')}
                            </div>
                        </li>
                    `);
                }
            }

            container.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${total}">${total}</a>
                </li>
            `);
        }

        // Next button
        const nextDisabled = !pagination.has_next ? 'disabled' : '';
        container.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${current + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);

        // Bind events
        container.off('click.pagination').on('click.pagination', 'a:not(.dropdown-toggle)', (e) => {
            e.preventDefault();
            const page = parseInt($(e.target).closest('a').data('page'));
            if (page && page !== this.currentPage && !$(e.target).closest('li').hasClass('disabled')) {
                this.loadPosts(page);
            }
        });

        // Bind dropdown events
        container.off('click.gap').on('click.gap', '.pagination-gap-item', (e) => {
            e.preventDefault();
            const page = parseInt($(e.target).data('page'));
            if (page && page !== this.currentPage) {
                this.loadPosts(page);
            }
        });
    },

    openModal(post = null) {
        this.resetForm();

        if (post) {
            this.isEditing = true;
            this.originalData = {
                title: (post.title || '').trim(),
                description: (post.description || '').trim()
            };

            $('#postModalLabel').html('<i class="fas fa-edit me-2"></i>Edit Post');
            $('#saveText').text('Update Post');
            $('#postId').val(post.id);
            $('#title').val(post.title);
            $('#description').val(post.description);
        } else {
            this.isEditing = false;
            $('#postModalLabel').html('<i class="fas fa-plus me-2"></i>Add New Post');
            $('#saveText').text('Save Post');
        }

        $('#postModal').modal('show');
    },

    // Helper methods
    hasNoChanges(formData) {
        return this.originalData.title === formData.title &&
            this.originalData.description === formData.description;
    },

    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const $field = $(`#${field}`);

            if ($field.length) {
                const message = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                $field.addClass('is-invalid').siblings('.invalid-feedback').text(message);
            }
        });
    },

    showOnChangeValidationErrors(field, message) {
        const $field = $(`#${field}`);

        if ($field.length) {
            $field.addClass('is-invalid').siblings('.invalid-feedback').text(message);
        }
    },

    hiddenValidationErrors(field) {
        const $field = $(`#${field}`);

        if ($field.length) {
            $field.removeClass('is-invalid');
        }
    },

    formatErrorMessage(error, defaultMsg) {
        if (error.status === 0) return `${defaultMsg}: Network error`;
        if (error.statusText === 'timeout') return `${defaultMsg}: Request timed out`;
        if (error.responseJSON?.message) return `${defaultMsg}: ${error.responseJSON.message}`;
        return defaultMsg;
    },

    resetForm() {
        $('#postForm')[0]?.reset();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').empty();
        this.originalData = {};
        this.isEditing = false;
    },

    showLoading(show) {
        $('#loading').toggle(show);
        $('#postsTable').toggle(!show);
    },

    showEmptyState() {
        $('#postsTable').hide();
        $('#emptyState').show();
    },

    hideEmptyState() {
        $('#emptyState').hide();
        $('#postsTable').show();
    },

    showSaveLoading(show) {
        $('#saveSpinner').toggleClass('d-none', !show);
        $('#btnSave').prop('disabled', show);
    }
};