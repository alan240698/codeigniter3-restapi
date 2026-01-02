<style>
    #review-button-container {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        margin: 0 -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
        z-index: 10;
        margin-top: auto;
    }
</style>

<div class="modal fade" id="ticketFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-3">
                <h5 class="modal-title fw-bold">Create New Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="ticket-form" enctype="multipart/form-data">
                <div class="modal-body pt-0">
                    <div class="steps-container mb-4" id="steps-indicator">
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

                    <div class="row g-4" id="form-fields">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Service Group<span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="service_group_id" id="service-group">
                                    <option value="">--Service Group--</option>
                                    <?php if (!empty($service_groups)): ?>
                                        <?php foreach ($service_groups as $group): ?>
                                            <option value="<?= $group['id'] ?>">
                                                <?= $group['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div id="service-group-error" class="inline-error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    IT Service<span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="it_service_id" id="it-service" disabled>
                                    <option value="">--IT Service--</option>
                                </select>
                                <div id="it-service-error" class="inline-error"></div>
                            </div>

                            <div class="mb-3" id="sub-service-section" style="display: none;">
                                <label class="form-label fw-semibold">
                                    Sub Service<span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="sub_service_id" id="sub-service">
                                    <option value="">--Select Sub Service--</option>
                                </select>
                                <div id="sub-service-error" class="inline-error"></div>
                            </div>

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

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">
                                    Description<span class="text-danger">*</span>
                                    <span class="text-muted small">(Minimum 10 characters)</span>
                                </label>
                                <textarea class="form-control"
                                    name="description"
                                    id="description"
                                    rows="8"
                                    placeholder="Enter a short description here"
                                    style="resize: none;"></textarea>
                                <div id="description-error" class="inline-error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Attachment
                                    <span class="text-muted small">(Maximum 30MB, 3 files only)</span>
                                </label>
                                
                                <!-- Drag & Drop Zone -->
                                <div class="file-upload-zone" id="file-upload-zone">
                                    <div class="upload-zone-content">
                                        <i class="far fa-images fa-3x text-muted mb-3"></i>
                                        <p class="mb-2"><strong>Drag & Drop files here</strong></p>
                                        <p class="text-muted small mb-2">or</p>
                                        <label for="attachments" class="btn btn-outline-info btn-sm mb-2">
                                            <i class="far fa-folder-open me-1"></i> Browse Files
                                        </label>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-paste me-1"></i> You can also <strong>Ctrl+V</strong> (Cmd+V on Mac) to paste images
                                        </p>
                                        <p class="text-muted" style="font-size: 0.75rem; margin-top: 8px;">
                                            Supported: JPG, PNG, GIF, PDF, DOC, XLS, TXT
                                        </p>
                                    </div>
                                    <input type="file" class="d-none" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                </div>
                                
                                <div id="attachments-error" class="inline-error"></div>
                                <div id="file-list" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="review-button-container">
                        <div class="col-12">
                            <div class="text-center mt-4">
                                <button type="button"
                                    class="btn btn-outline-info btn-action"
                                    id="btn-review-ticket">
                                    Review <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="review-section" style="display: none;">
                        <hr class="">
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
                            <button type="button"
                                class="btn btn-outline-secondary btn-action me-2"
                                id="btn-back-edit">
                                <i class="fas fa-arrow-left me-2"></i> Back to Edit
                            </button>
                            <button type="submit"
                                class="btn btn-info btn-action text-white"
                                id="btn-submit-final">
                                Submit Ticket <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="country_glpi" id="country-glpi" value="<?= isset($country) ? $country : 'Vietnam' ?>">

                    <script>
                        // Debug: Check if data is loaded
                        console.log('Service Groups:', <?= json_encode($service_groups ?? []) ?>);
                        console.log('Ticket Types:', <?= json_encode($ticket_types ?? []) ?>);
                        
                        window.formDataConfig = <?= json_encode($formData ?? []) ?>;
                    </script>

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
    const serviceGroupSelect = document.getElementById('service-group');
    const itServiceSelect = document.getElementById('it-service');
    const subServiceSelect = document.getElementById('sub-service');
    const subServiceSection = document.getElementById('sub-service-section');
    const descriptionField = document.getElementById('description');
    const btnReview = document.getElementById('btn-review-ticket');
    const btnBackEdit = document.getElementById('btn-back-edit');
    const reviewSection = document.getElementById('review-section');
    const reviewButtonContainer = document.getElementById('review-button-container');
    const stepsIndicator = document.getElementById('steps-indicator');

    // Store all IT services data
    let allItServices = [];

    // Service group change - load IT services
    if (serviceGroupSelect) {
        serviceGroupSelect.addEventListener('change', function() {
            const groupId = this.value;

            if (groupId) {
                loadItServicesByGroup(groupId);
            } else {
                itServiceSelect.disabled = true;
                itServiceSelect.innerHTML = '<option value="">--IT Service--</option>';
                hideSubServiceSection();
            }
        });
    }

    // IT Service change - check if has children
    if (itServiceSelect) {
        itServiceSelect.addEventListener('change', function() {
            const serviceId = this.value;
            
            if (serviceId) {
                // Check if this service has children
                const children = allItServices.filter(s => s.parent_id == serviceId);
                
                if (children.length > 0) {
                    // Show sub-service dropdown
                    showSubServiceSection(children);
                } else {
                    hideSubServiceSection();
                }
            } else {
                hideSubServiceSection();
            }
        });
    }

    // Load IT services by service group
    function loadItServicesByGroup(groupId) {
        itServiceSelect.innerHTML = '<option value="">Loading...</option>';
        itServiceSelect.disabled = true;
        hideSubServiceSection();

        $.ajax({
            url: BASE_URL + 'my-tickets/services-by-group/' + groupId,
            method: 'GET',
            success: function(response) {
                itServiceSelect.innerHTML = '<option value="">--IT Service--</option>';
                
                if (response.success && response.data) {
                    allItServices = response.data;
                    
                    // Only show parent services (no parent_id or parent_id is null)
                    const parentServices = response.data.filter(s => !s.parent_id);
                    
                    parentServices.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = service.name;
                        itServiceSelect.appendChild(option);
                    });
                    
                    itServiceSelect.disabled = false;
                } else {
                    itServiceSelect.innerHTML = '<option value="">No services available</option>';
                }
            },
            error: function() {
                itServiceSelect.innerHTML = '<option value="">Error loading services</option>';
            }
        });
    }

    function showSubServiceSection(children) {
        subServiceSelect.innerHTML = '<option value="">--Select Sub Service--</option>';
        
        children.forEach(child => {
            const option = document.createElement('option');
            option.value = child.id;
            option.textContent = child.name;
            subServiceSelect.appendChild(option);
        });
        
        subServiceSection.style.display = 'block';
    }

    function hideSubServiceSection() {
        subServiceSection.style.display = 'none';
        subServiceSelect.innerHTML = '<option value="">--Select Sub Service--</option>';
    }

    if (btnBackEdit) {
        btnBackEdit.addEventListener('click', function() {
            hideReview();
        });
    }

    function showReview() {
        // Service Group
        document.getElementById('review-category').textContent =
            serviceGroupSelect.options[serviceGroupSelect.selectedIndex].text;
        
        // IT Service
        document.getElementById('review-issue-type').textContent =
            itServiceSelect.options[itServiceSelect.selectedIndex].text;

        // Sub Service (if visible)
        const reviewSubIssueRow = document.getElementById('review-sub-issue-row');
        if (subServiceSection.style.display !== 'none' && subServiceSelect.value) {
            document.getElementById('review-sub-issue').textContent =
                subServiceSelect.options[subServiceSelect.selectedIndex].text;
            reviewSubIssueRow.style.display = 'flex';
        } else {
            reviewSubIssueRow.style.display = 'none';
        }

        // Description
        document.getElementById('review-description').textContent =
            descriptionField.value || '-';

        // Attachments
        const files = document.getElementById('attachments').files;
        if (files.length > 0) {
            const fileNames = Array.from(files).map(f => f.name).join(', ');
            document.getElementById('review-attachments').textContent = fileNames;
        } else {
            document.getElementById('review-attachments').textContent = 'No files attached';
        }

        document.getElementById('form-fields').style.display = 'none';
        reviewButtonContainer.style.display = 'none';
        stepsIndicator.style.display = 'none';
        reviewSection.style.display = 'block';
        document.querySelector('.modal-body').scrollTop = 0;
    }

    function hideReview() {
        document.getElementById('form-fields').style.display = 'flex';
        reviewButtonContainer.style.display = 'block';
        stepsIndicator.style.display = 'flex';
        reviewSection.style.display = 'none';
        document.querySelector('.modal-body').scrollTop = 0;
    }

    window.showReview = showReview;
    window.hideReview = hideReview;
});
</script>
