const fileDataMap = new Map();
const validationRules = new Map();

// ==================== ERROR DISPLAY UTILITIES ====================

const showFieldError = (field, message) => {
    clearFieldError(field);
    field.classList.add('error');

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error-message';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

    const formGroup = field.closest('.form-group');
    if (formGroup) {
        formGroup.appendChild(errorDiv);
    } else {
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
};

const clearFieldError = (field) => {
    field.classList.remove('error');
    const formGroup = field.closest('.form-group');
    const errorMsg = formGroup ?
        formGroup.querySelector('.field-error-message') :
        field.parentNode.querySelector('.field-error-message');
    if (errorMsg) errorMsg.remove();
};

const clearAllFormErrors = (form) => {
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    form.querySelectorAll('.field-error-message').forEach(el => el.remove());
};

// Utility functions
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const getFileIcon = (fileName) => {
    const ext = fileName.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'txt': 'fa-file-alt',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image'
    };
    return iconMap[ext] || 'fa-file';
};

const getFileExtension = (fileName) => {
    return fileName.split('.').pop().toLowerCase();
};

const isValidFileType = (fileName) => {
    const ext = getFileExtension(fileName);
    return CONFIG.ALLOWED_EXTENSIONS.includes(ext);
};

// Toast notification with better styling
// const showToast = (message, type = 'success') => {
//     const toastContainer = document.getElementById('toastContainer');
//     if (!toastContainer) {
//         console.warn('Toast container not found');
//         return;
//     }

//     const toast = document.createElement('div');
//     toast.className = `toast ${type}`;

//     const icons = {
//         success: 'fa-check-circle',
//         error: 'fa-exclamation-circle',
//         warning: 'fa-exclamation-triangle',
//         info: 'fa-info-circle'
//     };

//     // Handle multiline messages
//     const formattedMessage = message.replace(/\n/g, '<br>');

//     toast.innerHTML = `
//         <i class="fas ${icons[type]}"></i>
//         <div>${formattedMessage}</div>
//     `;

//     toastContainer.appendChild(toast);

//     setTimeout(() => {
//         toast.style.animation = 'slideOut 0.3s ease forwards';
//         setTimeout(() => toast.remove(), 300);
//     }, CONFIG.TOAST_DURATION);
// };

const showToast = (message, type = 'success') => {
    let toastContainer = document.getElementById('toastContainer');

    // Tạo container nếu chưa có
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const formattedMessage = message.replace(/\n/g, '<br>');

    toast.innerHTML = `
        <i class="fas ${icons[type]}"></i>
        <div class="toast-message">${formattedMessage}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    toastContainer.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);

    const duration = CONFIG?.TOAST_DURATION || 5000;
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

// Timestamp update
const updateTimestamps = () => {
    const now = new Date();
    const timeString = now.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    document.querySelectorAll('.timestamp').forEach(el => {
        el.textContent = `Thời gian: ${timeString}`;
    });
};

// Card expansion
const dashboardContainer = document.querySelector('.dashboard-container');
const categoryCards = document.querySelectorAll('.category-card');
const navPills = document.querySelectorAll('.nav-pill');

const expandCard = (card) => {
    categoryCards.forEach(c => {
        if (c !== card) c.classList.remove('expanded');
    });

    card.classList.add('expanded');
    dashboardContainer?.classList.add('has-active');

    const category = card.dataset.category;
    navPills.forEach(pill => {
        pill.classList.toggle('active', pill.dataset.target === category);
    });
};

const collapseAll = () => {
    categoryCards.forEach(card => card.classList.remove('expanded'));
    dashboardContainer?.classList.remove('has-active');
    navPills.forEach(pill => pill.classList.remove('active'));
};

// ==================== FIELD VALIDATION ====================

const validateFormField = (field) => {
    const name = field.name;
    const value = field.value?.trim() || '';
    const label = field.closest('.form-group')?.querySelector('label')?.textContent?.replace(/[*:]/g, '').trim() || name;

    clearFieldError(field);

    // Check required
    if (field.hasAttribute('required')) {
        if (field.type === 'checkbox' || field.type === 'radio') {
            const form = field.closest('form');
            const isChecked = form.querySelector(`[name="${name}"]:checked`);
            if (!isChecked) {
                showFieldError(field, `${label} là bắt buộc`);
                return false;
            }
        } else if (!value) {
            showFieldError(field, `${label} là bắt buộc`);
            return false;
        }
    }

    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Email không hợp lệ');
            return false;
        }
    }

    // Number validation
    if (field.type === 'number' && value) {
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        const numValue = parseFloat(value);

        if (min !== null && numValue < parseFloat(min)) {
            showFieldError(field, `Giá trị tối thiểu là ${min}`);
            return false;
        }
        if (max !== null && numValue > parseFloat(max)) {
            showFieldError(field, `Giá trị tối đa là ${max}`);
            return false;
        }
    }

    return true;
};

const enableRealtimeValidation = (form) => {
    const fields = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');

    fields.forEach(field => {
        field.addEventListener('blur', () => {
            if (field.value.trim() || field.hasAttribute('required')) {
                validateFormField(field);
            }
        });

        field.addEventListener('input', () => {
            if (field.classList.contains('error')) {
                clearFieldError(field);
            }
        });

        if (field.tagName === 'SELECT') {
            field.addEventListener('change', () => {
                validateFormField(field);
            });
        }
    });
};

// File validation
const validateFile = (file) => {
    const errors = [];

    // Check file size
    if (file.size > CONFIG.MAX_FILE_SIZE) {
        errors.push(`File "${file.name}" vượt quá ${formatFileSize(CONFIG.MAX_FILE_SIZE)}`);
    }

    // Check file type
    if (!isValidFileType(file.name)) {
        const ext = getFileExtension(file.name);
        errors.push(`File format .${ext} not supported`);
    }

    // Check file name length
    if (file.name.length > 255) {
        errors.push(`File name "${file.name}" too long (maximum 255 characters)`);
    }

    return errors;
};

// File handling with improved validation
const addFileToList = (file, wrapper, formId) => {
    let fileList = wrapper.nextElementSibling;

    // Verify it's the correct element
    if (!fileList || !fileList.classList.contains('file-list')) {
        // Try to find it in parent
        fileList = wrapper.parentElement?.querySelector('.file-list');
    }

    if (!fileList) {
        console.error('File list container not found');
        return false;
    }

    const currentFiles = fileDataMap.get(formId) || [];

    // Validate max files
    if (currentFiles.length >= CONFIG.MAX_FILES) {
        showToast(`Only maximum uploads are allowed ${CONFIG.MAX_FILES} files`, 'warning');
        return false;
    }

    // Validate file
    const validationErrors = validateFile(file);
    if (validationErrors.length > 0) {
        showToast(validationErrors.join('\n'), 'error');
        return false;
    }

    // Check for duplicate file names
    const isDuplicate = currentFiles.some(f => f.name === file.name);
    if (isDuplicate) {
        showToast(`File "${file.name}" has been added`, 'warning');
        return false;
    }

    currentFiles.push(file);
    fileDataMap.set(formId, currentFiles);

    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    fileItem.setAttribute('data-file-name', file.name);
    fileItem.innerHTML = `
        <div class="file-info">
            <i class="fas ${getFileIcon(file.name)} file-icon"></i>
            <div class="file-details">
                <div class="file-name" title="${file.name}">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
        </div>
        <button type="button" class="file-remove" aria-label="Remove file">
            <i class="fas fa-times"></i>
        </button>
    `;

    fileItem.querySelector('.file-remove').addEventListener('click', () => {
        const files = fileDataMap.get(formId);
        const index = files.indexOf(file);
        if (index > -1) {
            files.splice(index, 1);
            fileDataMap.set(formId, files);
        }
        fileItem.remove();
        showToast(`File deleted "${file.name}"`, 'info');
    });

    fileList.appendChild(fileItem);

    // Add animation
    setTimeout(() => {
        fileItem.style.opacity = '1';
        fileItem.style.transform = 'translateY(0)';
    }, 10);

    return true;
};


const collectFormData = (form, category) => {
    const formData = new FormData();

    const csrfInput = form.querySelector('input[type="hidden"][name^="csrf_"]');
    if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
    } else {
        console.warn('⚠ CSRF token not found!');
    }

    formData.append('category', category);

    const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
    let fieldCount = 0;

    inputs.forEach(input => {
        const name = input.name;
        const value = input.value;

        if (!name || name === 'category') return;
        if (input.type === 'hidden' && (name.startsWith('csrf_') || name.startsWith('hidden_'))) return;

        // Checkboxes
        if (input.type === 'checkbox') {
            if (input.checked) {
                formData.append(name, value || 'on');
                fieldCount++;
                console.log('✓ Checkbox:', name, '=', value || 'on');
            }
        }
        // Radio buttons
        else if (input.type === 'radio') {
            if (input.checked) {
                formData.append(name, value);
                fieldCount++;
                console.log('✓ Radio:', name, '=', value);
            }
        }
        // Other inputs
        else {
            const trimmedValue = value.trim();
            if (trimmedValue !== '') {
                formData.append(name, trimmedValue);
                fieldCount++;
                const displayValue = trimmedValue.length > 50
                    ? trimmedValue.substring(0, 50) + '...'
                    : trimmedValue;
                console.log(`✓ ${input.tagName.toLowerCase()}:`, name, '=', displayValue);
            }
        }
    });

    console.log(`Total fields: ${fieldCount}`);

    const files = fileDataMap.get(category) || [];

    console.group('📎 File Handling');
    console.log('Files in fileDataMap:', files.length);

    if (files.length > 0) {
        // IMPORTANT: Append each file with the correct array notation
        files.forEach((file, index) => {
            // Use 'attachments[]' to match PHP's expected format
            formData.append('attachments[]', file, file.name);
            console.log(`✓ Appending file ${index + 1}:`, {
                name: file.name,
                size: formatFileSize(file.size),
                type: file.type
            });
        });
    } else {
        console.log('No files to upload');
    }
    console.groupEnd();

    let fileCount = 0;
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            fileCount++;
            console.log(`  ${pair[0]}: [File] ${pair[1].name} (${formatFileSize(pair[1].size)})`);
        }
    }

    // 6. Full Summary
    console.groupEnd();
    console.group('📦 Complete FormData Summary');
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log(`  ${pair[0]}: [File] ${pair[1].name}`);
        } else {
            const displayValue = typeof pair[1] === 'string' && pair[1].length > 100
                ? pair[1].substring(0, 100) + '...'
                : pair[1];
            console.log(`  ${pair[0]}:`, displayValue);
        }
    }
    console.groupEnd();

    return formData;
};

// Form validation
// const validateForm = (form, category) => {
//     const errors = [];
//     const formElements = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
//     const formValues = {};

//     formElements.forEach(el => {
//         const name = el.name;
//         if (!name || name.startsWith('hidden_')) return;

//         if (el.type === 'checkbox' || el.type === 'radio') {
//             if (el.checked) {
//                 formValues[name] = el.value;
//             }
//         } else if (el.value && el.value.trim() !== '') {
//             formValues[name] = el.value.trim();
//         }
//     });

//     // Check if form has any data
//     if (Object.keys(formValues).length === 0) {
//         errors.push('Vui lòng điền ít nhất một thông tin');
//         return { isValid: false, errors, formValues };
//     }

//     // Required fields validation
//     const requiredInputs = form.querySelectorAll('[required]');
//     requiredInputs.forEach(input => {
//         const name = input.name;
//         if (!name) return;

//         if (input.type === 'checkbox' || input.type === 'radio') {
//             const isChecked = form.querySelector(`[name="${name}"]:checked`);
//             if (!isChecked) {
//                 const label = input.closest('.form-group')?.querySelector('label')?.textContent || name;
//                 errors.push(`${label} là bắt buộc`);
//             }
//         } else if (!formValues[name] || formValues[name].trim() === '') {
//             const label = input.closest('.form-group')?.querySelector('label')?.textContent || name;
//             errors.push(`${label} là bắt buộc`);
//         }
//     });

//     // Check subcategory OR description
//     if (!formValues.subcategory && !formValues.description) {
//         errors.push('Vui lòng điền Subcategory hoặc Description');
//     }

//     return {
//         isValid: errors.length === 0,
//         errors,
//         formValues
//     };
// };


const validateForm = (form, category) => {
    const errors = [];
    clearAllFormErrors(form);

    const formElements = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
    const formValues = {};
    let hasAnyValue = false;

    // Validate từng field
    formElements.forEach(el => {
        const name = el.name;
        if (!name || name.startsWith('hidden_') || name === 'category') return;

        if (!validateFormField(el)) {
            const label = el.closest('.form-group')?.querySelector('label')?.textContent?.replace(/[*:]/g, '').trim() || name;
            errors.push(`${label} có lỗi`);
        }

        if (el.type === 'checkbox' || el.type === 'radio') {
            if (el.checked) {
                formValues[name] = el.value;
                hasAnyValue = true;
            }
        } else if (el.value && el.value.trim() !== '') {
            formValues[name] = el.value.trim();
            hasAnyValue = true;
        }
    });

    if (!hasAnyValue) {
        errors.push('Vui lòng điền ít nhất một thông tin');
        showToast('Vui lòng điền ít nhất một thông tin', 'warning');
        return { isValid: false, errors, formValues };
    }

    // Check subcategory OR description
    const subcategoryField = form.querySelector('[name="subcategory"]');
    const descriptionField = form.querySelector('[name="description"]');

    if (subcategoryField && descriptionField) {
        if (!formValues.subcategory && !formValues.description) {
            const error = 'Vui lòng điền Subcategory hoặc Description';
            errors.push(error);
            if (subcategoryField.offsetParent !== null) showFieldError(subcategoryField, error);
            if (descriptionField.offsetParent !== null) showFieldError(descriptionField, error);
        }
    }

    return {
        isValid: errors.length === 0,
        errors,
        formValues
    };
};

// Form submit handler
// const handleFormSubmit = async (e, form) => {
//     e.preventDefault();

//     const btn = form.querySelector('.submit-btn');
//     const btnText = btn.querySelector('span');
//     const originalText = btnText.textContent;
//     const category = form.dataset.category;


//     const validation = validateForm(form, category);
//     if (!validation.isValid) {
//         showToast(validation.errors.join('\n'), 'warning');
//         return;
//     }

//     btn.disabled = true;
//     btnText.textContent = 'Sending...';
//     btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

//     const formData = collectFormData(form, category);

//     try {
//         const response = await fetch(CONFIG.ENDPOINTS.CREATE, {
//             method: 'POST',
//             body: formData
//         });

//         const responseText = await response.text();

//         // Check if response is HTML error
//         if (responseText.trim().startsWith('<')) {
//             console.error('HTML Error Response:', responseText.substring(0, 500));
//             throw new Error('Server returned HTML error. Check PHP error logs.');
//         }

//         // Parse JSON
//         let result;
//         try {
//             result = JSON.parse(responseText);
//         } catch (parseError) {
//             console.error('JSON parse error:', parseError);
//             console.error('Raw response:', responseText.substring(0, 500));
//             throw new Error('Server returned invalid JSON format');
//         }

//         if (result.success) {
//             const ticketId = result.ticket_id || 'N/A';
//             showToast(`Ticket #${ticketId} created successfully!`, 'success');

//             // Reset form
//             form.reset();
//             const fileList = form.querySelector('.file-list');
//             if (fileList) fileList.innerHTML = '';
//             fileDataMap.set(category, []);

//             setTimeout(() => collapseAll(), 1500);
//         } else {
//             // Handle validation errors
//             let errorMessage = result.message || 'An error occurred while creating the ticket';

//             if (result.errors && typeof result.errors === 'object') {
//                 const errorList = Object.entries(result.errors).map(([field, msgs]) => {
//                     const messages = Array.isArray(msgs) ? msgs : [msgs];
//                     return messages.join(', ');
//                 });
//                 errorMessage = errorList.join('\n');
//             }

//             showToast(errorMessage, 'error');
//         }

//     } catch (error) {
//         console.error('Submit error:', error);
//         showToast(error.message || 'An error occurred. Please try again!', 'error');
//     } finally {
//         btn.disabled = false;
//         btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
//     }
// };

const handleFormSubmit = async (e, form) => {
    e.preventDefault();

    const btn = form.querySelector('.submit-btn');
    const btnText = btn.querySelector('span');
    const originalText = btnText.textContent;
    const category = form.dataset.category;

    // ✅ THÊM: Validate với field-level errors
    const validation = validateForm(form, category);
    if (!validation.isValid) {
        if (validation.errors.length > 0) {
            showToast(validation.errors[0], 'error');
        }

        // ✅ THÊM: Scroll đến lỗi đầu tiên
        const firstError = form.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Đang gửi...';
    btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

    const formData = collectFormData(form, category);

    try {
        const response = await fetch(CONFIG.ENDPOINTS.CREATE, {
            method: 'POST',
            body: formData
        });

        const responseText = await response.text();

        if (responseText.trim().startsWith('<')) {
            throw new Error('Server trả về lỗi HTML');
        }

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            throw new Error('Server trả về định dạng JSON không hợp lệ');
        }

        if (result.success) {
            const ticketId = result.ticket_id || 'N/A';
            showToast(`✅ Ticket #${ticketId} đã được tạo thành công!`, 'success');

            form.reset();
            clearAllFormErrors(form); // ✅ THÊM: Clear errors khi reset

            const fileList = form.querySelector('.file-list');
            if (fileList) fileList.innerHTML = '';
            fileDataMap.set(category, []);

            setTimeout(() => collapseAll(), 1500);
        } else {
            let errorMessage = result.message || 'Có lỗi xảy ra';

            // ✅ THÊM: Hiển thị lỗi từng field từ server
            if (result.errors && typeof result.errors === 'object') {
                Object.entries(result.errors).forEach(([field, msgs]) => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    const messages = Array.isArray(msgs) ? msgs : [msgs];
                    if (fieldElement) {
                        showFieldError(fieldElement, messages.join(', '));
                    }
                });

                const errorList = Object.values(result.errors).flat();
                errorMessage = errorList.join('\n');
            }

            showToast(errorMessage, 'error');
        }

    } catch (error) {
        console.error('❌ Submit error:', error);
        showToast(error.message || 'Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
    }
};

// Event listeners initialization
const initEventListeners = () => {
    // Card expansion
    categoryCards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.close-btn') || e.target.closest('.card-body')) return;
            expandCard(card);
        });
    });

    // Close buttons
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            collapseAll();
        });
    });

    // Navigation pills
    navPills.forEach(pill => {
        pill.addEventListener('click', () => {
            const target = pill.dataset.target;
            const targetCard = document.querySelector(`.category-card[data-category="${target}"]`);
            if (targetCard) expandCard(targetCard);
        });
    });

    // File upload
    document.querySelectorAll('.file-upload-wrapper').forEach(wrapper => {
        const input = wrapper.querySelector('input[type="file"]');
        const form = wrapper.closest('form');
        if (!form) {
            console.warn('File upload wrapper found but no parent form');
            return;
        }

        const formId = form.dataset.category;

        // File input change
        input?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);

            let successCount = 0;
            files.forEach(file => {
                if (addFileToList(file, wrapper, formId)) {
                    successCount++;
                }
            });

            if (successCount > 0) {
                showToast(`Đã thêm ${successCount} file`, 'success');
            }

            e.target.value = ''; // Reset input
        });

        // Drag and drop
        wrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            wrapper.classList.add('dragover');
        });

        wrapper.addEventListener('dragleave', (e) => {
            if (e.target === wrapper) {
                wrapper.classList.remove('dragover');
            }
        });

        wrapper.addEventListener('drop', (e) => {
            e.preventDefault();
            wrapper.classList.remove('dragover');

            const files = Array.from(e.dataTransfer.files);

            let successCount = 0;
            files.forEach(file => {
                if (addFileToList(file, wrapper, formId)) {
                    successCount++;
                }
            });

            if (successCount > 0) {
                showToast(`Đã thêm ${successCount} file`, 'success');
            }
        });

        // Form submissions
        document.querySelectorAll('.ticket-form').forEach(form => {
            form.addEventListener('submit', (e) => handleFormSubmit(e, form));

            // ✅ THÊM: Enable real-time validation
            enableRealtimeValidation(form);
        });
    });

    // Form submissions
    document.querySelectorAll('.ticket-form').forEach(form => {
        form.addEventListener('submit', (e) => handleFormSubmit(e, form));
    });
};

const injectValidationStyles = () => {
    if (document.getElementById('validation-styles')) return;

    const style = document.createElement('style');
    style.id = 'validation-styles';
    style.textContent = `
        /* Field Error Styles */
        .form-control.error,
        .form-select.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2;
        }
        
        .field-error-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            border-radius: 4px;
            color: #dc2626;
            font-size: 0.875rem;
            animation: slideDown 0.3s ease;
        }
        
        .field-error-message i {
            color: #ef4444;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Toast Container */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }
        
        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-success {
            border-left: 4px solid #10b981;
        }
        
        .toast-error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-warning {
            border-left: 4px solid #f59e0b;
        }
        
        .toast-info {
            border-left: 4px solid #3b82f6;
        }
        
        .toast i:first-child {
            font-size: 1.5rem;
        }
        
        .toast-success i:first-child {
            color: #10b981;
        }
        
        .toast-error i:first-child {
            color: #ef4444;
        }
        
        .toast-warning i:first-child {
            color: #f59e0b;
        }
        
        .toast-info i:first-child {
            color: #3b82f6;
        }
        
        .toast-message {
            flex: 1;
            color: #1f2937;
            line-height: 1.5;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
        
        .toast-close:hover {
            color: #1f2937;
        }
        
        /* File Upload Error */
        .file-upload-wrapper.error {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
};

// Initialize on DOM ready
const init = () => {
    injectValidationStyles();
    updateTimestamps();
    setInterval(updateTimestamps, CONFIG.TIMESTAMP_INTERVAL);
    initEventListeners();

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}