<!-- Modal: Create/Edit Ticket -->
<div class="modal fade" id="ticketFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-3">
                <h5 class="modal-title fw-bold">Create New Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="ticket-form" enctype="multipart/form-data">
                <div class="modal-body pt-0">
                    <!-- Steps Indicator -->
                    <div class="steps-container mb-4">
                        <div class="step-item">
                            <div class="step-circle">01</div>
                            <div class="step-text">
                                <p class="mb-0">Please choose the <strong>category</strong> and</p>
                                <p class="mb-0"><strong>issue type</strong> of your request.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">02</div>
                            <div class="step-text">
                                <p class="mb-0">Enter a <strong>brief description</strong> of your issue</p>
                                <p class="mb-0">and <strong>attach files</strong> if needed.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="row g-4">
                        <!-- Left Section -->
                        <div class="col-lg-6">
                            <!-- Category & Issue Type Row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Category<span class="text-danger">*</span></label>
                                    <select class="form-select" name="category" id="selected-category">
                                        <option value="">--Category--</option>
                                        <?php if (!empty($cards)): ?>
                                            <?php foreach ($cards as $card): ?>
                                                <option value="<?= $card['key'] ?>" data-entity-id="<?= $card['id'] ?>">
                                                    <?= $card['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Issue Type<span class="text-danger">*</span></label>
                                    <select class="form-select" name="subcategory" id="subcategory" disabled>
                                        <option value="">--Select Subcategory--</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Sub Issue Type -->
                            <div class="mb-3" id="sub-issue-section" style="display: none;">
                                <label class="form-label fw-semibold">Sub Issue Type<span class="text-danger">*</span></label>
                                <select class="form-select" name="sub_issue_type" id="sub-issue-type">
                                    <option value="">--Select--</option>
                                </select>
                            </div>

                            <!-- Instructions -->
                            <div class="instruction-card">
                                <h6 class="instruction-title">Instruction:</h6>
                                <div class="instruction-item">
                                    <strong>Network:</strong> For problems with connectivity (Wi-Fi, LAN, Internet, VPN, firewall or other network issues).
                                </div>
                                <div class="instruction-item">
                                    <strong>User Computer:</strong> When you need support to your computer hardware (computer, monitor, mouse..) or software (install/remove...).
                                </div>
                                <div class="instruction-item">
                                    <strong>Group Application:</strong> For issues using company systems such as Group websites, Intranet, Microsoft 365, Navision, Power BI or other internal apps.
                                </div>
                                <div class="instruction-item">
                                    <strong>Cyber Security:</strong> When you see scam emails, viruses or anything that looks like a security risk.
                                </div>
                            </div>
                        </div>

                        <!-- Right Section -->
                        <div class="col-lg-6">
                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Description<span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" id="description"
                                    rows="8" placeholder="Enter a short description here" style="resize: none;"></textarea>
                            </div>

                            <!-- Attachments -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Attachment <span class="text-muted small">(Maximum 100MB, 3 files only)</span></label>
                                <div class="file-upload-box">
                                    <label for="attachments" class="file-upload-label">
                                        <i class="far fa-folder-open fa-lg text-muted me-2"></i>
                                        <span class="text-dark">File Upload</span>
                                    </label>
                                    <input type="file" class="d-none" name="attachments[]"
                                        id="attachments" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                </div>
                                <div id="file-list" class="mt-2"></div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-outline-info btn-action" id="btn-review-ticket">
                                    Review <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Review Section (Hidden by default) -->
                    <div id="review-section" style="display: none;">
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Review Your Ticket</h6>
                        <div class="review-card">
                            <div class="review-row">
                                <div class="review-label">Category:</div>
                                <div class="review-value" id="review-category">-</div>
                            </div>
                            <div class="review-row">
                                <div class="review-label">Issue Type:</div>
                                <div class="review-value" id="review-issue-type">-</div>
                            </div>
                            <div class="review-row" id="review-sub-issue-row" style="display: none;">
                                <div class="review-label">Sub Issue Type:</div>
                                <div class="review-value" id="review-sub-issue">-</div>
                            </div>
                            <div class="review-row">
                                <div class="review-label">Description:</div>
                                <div class="review-value" id="review-description" style="white-space: pre-wrap;">-</div>
                            </div>
                            <div class="review-row">
                                <div class="review-label">Attachments:</div>
                                <div class="review-value" id="review-attachments">No files attached</div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-action me-2" id="btn-back-edit">
                                <i class="fas fa-arrow-left me-2"></i> Back to Edit
                            </button>
                            <button type="submit" class="btn btn-info btn-action text-white" id="btn-submit-final">
                                Submit Ticket <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="country_glpi" id="country-glpi" value="<?= isset($country) ? $country : 'Vietnam' ?>">

                    <!-- Form Data as JSON -->
                    <script>
                        window.formDataConfig = <?= json_encode($formData ?? []) ?>;
                    </script>

                    <!-- Error Alert -->
                    <div id="form-error-alert" class="alert alert-danger d-none mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="form-error-message"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('selected-category');
        const subcategorySelect = document.getElementById('subcategory');
        const subIssueTypeSelect = document.getElementById('sub-issue-type');
        const subIssueSection = document.getElementById('sub-issue-section');
        const descriptionField = document.getElementById('description');
        const attachmentsField = document.getElementById('attachments');
        const fileList = document.getElementById('file-list');
        const btnReview = document.getElementById('btn-review-ticket');
        const btnSubmitFinal = document.getElementById('btn-submit-final');
        const btnBackEdit = document.getElementById('btn-back-edit');
        const reviewSection = document.getElementById('review-section');

        // Category change enables subcategory
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                if (this.value) {
                    subcategorySelect.disabled = false;
                    loadSubcategories(this.value);
                } else {
                    subcategorySelect.disabled = true;
                    subcategorySelect.innerHTML = '<option value="">--Select Subcategory--</option>';
                    subIssueSection.style.display = 'none';
                }
            });
        }

        // File attachments preview
        if (attachmentsField) {
            attachmentsField.addEventListener('change', function() {
                displayFileList(this.files);
            });
        }

        function displayFileList(files) {
            fileList.innerHTML = '';
            if (files.length > 0) {
                const ul = document.createElement('ul');
                ul.className = 'list-group';
                Array.from(files).forEach((file, index) => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    li.innerHTML = `
                        <span><i class="fas fa-file me-2 text-muted"></i>${file.name}</span>
                        <span class="badge bg-info rounded-pill">${formatFileSize(file.size)}</span>
                    `;
                    ul.appendChild(li);
                });
                fileList.appendChild(ul);
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function loadSubcategories(categoryKey) {
            subcategorySelect.innerHTML = '<option value="">--Select Subcategory--</option>';

            if (window.formDataConfig && window.formDataConfig[categoryKey]) {
                const categoryData = window.formDataConfig[categoryKey][categoryKey];

                if (categoryData && categoryData.subcategory && categoryData.subcategory.options) {
                    const options = categoryData.subcategory.options;

                    Object.keys(options).forEach(key => {
                        const option = document.createElement('option');
                        option.value = options[key].value;
                        option.textContent = options[key].label;

                        if (options[key].children && Object.keys(options[key].children).length > 0) {
                            option.setAttribute('data-has-children', 'true');
                            option.setAttribute('data-children', JSON.stringify(options[key].children));
                        }

                        subcategorySelect.appendChild(option);
                    });

                    subcategorySelect.disabled = false;
                } else {
                    subcategorySelect.innerHTML = '<option value="">No issue types available</option>';
                    subcategorySelect.disabled = true;
                }
            } else {
                subcategorySelect.innerHTML = '<option value="">No issue types available</option>';
                subcategorySelect.disabled = true;
            }
        }

        // Handle subcategory change
        if (subcategorySelect) {
            subcategorySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const hasChildren = selectedOption.getAttribute('data-has-children');

                subIssueSection.style.display = 'none';
                subIssueTypeSelect.innerHTML = '<option value="">--Select--</option>';

                if (hasChildren === 'true' && this.value) {
                    const children = JSON.parse(selectedOption.getAttribute('data-children') || '{}');

                    if (Object.keys(children).length > 0) {
                        Object.keys(children).forEach(key => {
                            const option = document.createElement('option');
                            option.value = children[key].value;
                            option.textContent = children[key].label;
                            subIssueTypeSelect.appendChild(option);
                        });

                        subIssueSection.style.display = 'block';
                    }
                }
            });
        }

        // Form validation
        function validateForm() {
            const errorAlert = document.getElementById('form-error-alert');
            const errorMessage = document.getElementById('form-error-message');

            errorAlert.classList.add('d-none');

            if (!categorySelect.value) {
                showError('Please select a category');
                return false;
            }

            if (!subcategorySelect.value) {
                showError('Please select an issue type');
                return false;
            }

            if (subIssueSection.style.display !== 'none' && !subIssueTypeSelect.value) {
                showError('Please select a sub issue type');
                return false;
            }

            if (!descriptionField.value.trim() || descriptionField.value.trim().length < 10) {
                showError('Please enter a description (minimum 10 characters)');
                return false;
            }

            return true;
        }

        function showError(message) {
            const errorAlert = document.getElementById('form-error-alert');
            const errorMessage = document.getElementById('form-error-message');
            errorMessage.textContent = message;
            errorAlert.classList.remove('d-none');
            errorAlert.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Review button click
        if (btnReview) {
            btnReview.addEventListener('click', function() {
                if (validateForm()) {
                    showReview();
                }
            });
        }

        // Back to edit button
        if (btnBackEdit) {
            btnBackEdit.addEventListener('click', function() {
                hideReview();
            });
        }

        function showReview() {
            document.getElementById('review-category').textContent =
                categorySelect.options[categorySelect.selectedIndex].text;
            document.getElementById('review-issue-type').textContent =
                subcategorySelect.options[subcategorySelect.selectedIndex].text;

            const reviewSubIssueRow = document.getElementById('review-sub-issue-row');
            if (subIssueSection.style.display !== 'none' && subIssueTypeSelect.value) {
                document.getElementById('review-sub-issue').textContent =
                    subIssueTypeSelect.options[subIssueTypeSelect.selectedIndex].text;
                reviewSubIssueRow.style.display = 'flex';
            } else {
                reviewSubIssueRow.style.display = 'none';
            }

            document.getElementById('review-description').textContent =
                descriptionField.value || '-';

            const files = attachmentsField.files;
            if (files.length > 0) {
                const fileNames = Array.from(files).map(f => f.name).join(', ');
                document.getElementById('review-attachments').textContent = fileNames;
            } else {
                document.getElementById('review-attachments').textContent = 'No files attached';
            }

            document.querySelector('.row.g-4').style.display = 'none';
            reviewSection.style.display = 'block';
            document.querySelector('.modal-body').scrollTop = 0;
        }

        function hideReview() {
            document.querySelector('.row.g-4').style.display = 'flex';
            reviewSection.style.display = 'none';
            document.querySelector('.modal-body').scrollTop = 0;
        }

        // Form submission
        function handleSubmit(e) {
            e.preventDefault();

            if (reviewSection.style.display === 'none') {
                if (validateForm()) {
                    showReview();
                }
                return;
            }

            const formData = new FormData(document.getElementById('ticket-form'));

            btnSubmitFinal.disabled = true;
            btnSubmitFinal.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

            fetch('<?= base_url("tickets/create") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Ticket created successfully!');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('ticketFormModal'));
                        modal.hide();
                        location.reload();
                    } else {
                        showError(data.message || 'Failed to create ticket. Please try again.');
                        btnSubmitFinal.disabled = false;
                        btnSubmitFinal.innerHTML = 'Submit Ticket <i class="fas fa-paper-plane ms-2"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error submitting ticket:', error);
                    showError('An error occurred while submitting your ticket. Please try again.');
                    btnSubmitFinal.disabled = false;
                    btnSubmitFinal.innerHTML = 'Submit Ticket <i class="fas fa-paper-plane ms-2"></i>';
                });
        }

        document.getElementById('ticket-form').addEventListener('submit', handleSubmit);
        if (btnSubmitFinal) {
            btnSubmitFinal.addEventListener('click', handleSubmit);
        }
    });
</script>