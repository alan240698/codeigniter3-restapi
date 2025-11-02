// Configuration
const CONFIG = {
    MAX_FILES: 3,
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
    TOAST_DURATION: 3000,
    TIMESTAMP_INTERVAL: 1000,
    ALLOWED_EXTENSIONS: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'gif'],
    MIN_DESCRIPTION_LENGTH: 10,
    IMAGE_EXTENSIONS: ['jpg', 'jpeg', 'png', 'gif']
};

// State management
const fileDataMap = new Map();
const validationRules = new Map();

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
const showToast = (message, type = 'success') => {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        console.warn('Toast container not found');
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    // Handle multiline messages
    const formattedMessage = message.replace(/\n/g, '<br>');

    toast.innerHTML = `
        <i class="fas ${icons[type]}"></i>
        <div>${formattedMessage}</div>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, CONFIG.TOAST_DURATION);
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
        errors.push(`Định dạng file .${ext} không được hỗ trợ`);
    }

    // Check file name length
    if (file.name.length > 255) {
        errors.push(`Tên file "${file.name}" quá dài (tối đa 255 ký tự)`);
    }

    return errors;
};

// File handling with improved validation
const addFileToList = (file, wrapper, formId) => {
    const fileList = wrapper.querySelector('.file-list');
    if (!fileList) {
        console.error('File list container not found');
        return false;
    }

    const currentFiles = fileDataMap.get(formId) || [];

    // Validate max files
    if (currentFiles.length >= CONFIG.MAX_FILES) {
        showToast(`Chỉ được phép tải lên tối đa ${CONFIG.MAX_FILES} files`, 'warning');
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
        showToast(`File "${file.name}" đã được thêm`, 'warning');
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
        showToast(`Đã xóa file "${file.name}"`, 'info');
    });

    fileList.appendChild(fileItem);
    
    // Add animation
    setTimeout(() => {
        fileItem.style.opacity = '1';
        fileItem.style.transform = 'translateY(0)';
    }, 10);

    return true;
};

// Form data collection with comprehensive logging
const collectFormData = (form, category) => {
    const formData = new FormData();
    
    console.group('📋 Form Data Collection');
    
    // 1. CSRF Token
    const csrfInput = form.querySelector('input[type="hidden"][name^="csrf_"]');
    if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
        console.log('✓ CSRF Token:', csrfInput.name);
    } else {
        console.warn('⚠ CSRF token not found!');
    }
    
    // 2. Category
    formData.append('category', category);
    console.log('✓ Category:', category);
    
    // 3. Form inputs
    const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
    let fieldCount = 0;
    
    inputs.forEach(input => {
        const name = input.name;
        const value = input.value;
        
        // Skip processed fields
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
    
    // 4. Files
    const files = fileDataMap.get(category) || [];
    if (files.length > 0) {
        files.forEach((file, index) => {
            formData.append('attachments[]', file);
            console.log(`✓ File ${index + 1}:`, file.name, '-', formatFileSize(file.size));
        });
    } else {
        console.log('ℹ No files attached');
    }
    
    // 5. Summary
    console.groupEnd();
    console.group('📦 FormData Summary');
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log(`  ${pair[0]}: [File] ${pair[1].name}`);
        } else {
            console.log(`  ${pair[0]}:`, pair[1]);
        }
    }
    console.groupEnd();
    
    return formData;
};

// Form validation
const validateForm = (form, category) => {
    const errors = [];
    const formElements = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
    const formValues = {};
    
    formElements.forEach(el => {
        const name = el.name;
        if (!name || name.startsWith('hidden_')) return;
        
        if (el.type === 'checkbox' || el.type === 'radio') {
            if (el.checked) {
                formValues[name] = el.value;
            }
        } else if (el.value && el.value.trim() !== '') {
            formValues[name] = el.value.trim();
        }
    });

    // Check if form has any data
    if (Object.keys(formValues).length === 0) {
        errors.push('Vui lòng điền ít nhất một thông tin');
        return { isValid: false, errors, formValues };
    }

    // Required fields validation
    const requiredInputs = form.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        const name = input.name;
        if (!name) return;
        
        if (input.type === 'checkbox' || input.type === 'radio') {
            const isChecked = form.querySelector(`[name="${name}"]:checked`);
            if (!isChecked) {
                const label = input.closest('.form-group')?.querySelector('label')?.textContent || name;
                errors.push(`${label} là bắt buộc`);
            }
        } else if (!formValues[name] || formValues[name].trim() === '') {
            const label = input.closest('.form-group')?.querySelector('label')?.textContent || name;
            errors.push(`${label} là bắt buộc`);
        }
    });

    // Specific field validations
    if (formValues.description && formValues.description.length < CONFIG.MIN_DESCRIPTION_LENGTH) {
        // errors.push(`Mô tả phải có ít nhất ${CONFIG.MIN_DESCRIPTION_LENGTH} ký tự`);
    }

    // Check subcategory OR description
    if (!formValues.subcategory && !formValues.description) {
        errors.push('Vui lòng điền Subcategory hoặc Description');
    }

    return {
        isValid: errors.length === 0,
        errors,
        formValues
    };
};

// Form submit handler
const handleFormSubmit = async (e, form) => {
    e.preventDefault();

    const btn = form.querySelector('.submit-btn');
    const btnText = btn.querySelector('span');
    const originalText = btnText.textContent;
    const category = form.dataset.category;

    console.group('🚀 Form Submission');
    console.log('Category:', category);

    // Validate form
    const validation = validateForm(form, category);
    console.log('Validation:', validation);

    if (!validation.isValid) {
        showToast(validation.errors.join('\n'), 'warning');
        console.groupEnd();
        return;
    }

    // Disable submit button
    btn.disabled = true;
    btnText.textContent = 'Đang gửi...';
    btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

    const formData = collectFormData(form, category);

    try {
        console.log('Sending request to:', TICKET_CREATE_URL);
        
        const response = await fetch(TICKET_CREATE_URL, {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status, response.statusText);

        // Get response text first
        const responseText = await response.text();
        console.log('Raw response length:', responseText.length);

        // Check if response is HTML error
        if (responseText.trim().startsWith('<')) {
            console.error('HTML Error Response:', responseText.substring(0, 500));
            throw new Error('Server trả về HTML error. Kiểm tra PHP error logs.');
        }

        // Parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed result:', result);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response:', responseText.substring(0, 500));
            throw new Error('Server trả về định dạng không hợp lệ');
        }

        if (result.success) {
            showToast(`Ticket #${result.ticket_id} đã được tạo thành công!`, 'success');
            console.log('✅ Ticket created:', result.ticket_id);

            // Reset form
            form.reset();
            const fileList = form.querySelector('.file-list');
            if (fileList) fileList.innerHTML = '';
            fileDataMap.set(category, []);

            setTimeout(() => collapseAll(), 1500);
        } else {
            // Handle validation errors
            let errorMessage = result.message || 'Có lỗi xảy ra khi tạo ticket';
            
            if (result.errors && typeof result.errors === 'object') {
                const errorList = Object.entries(result.errors).map(([field, msgs]) => {
                    const messages = Array.isArray(msgs) ? msgs : [msgs];
                    return messages.join(', ');
                });
                errorMessage = errorList.join('\n');
            }
            
            showToast(errorMessage, 'error');
            console.error('❌ Validation errors:', result.errors);
        }

    } catch (error) {
        console.error('❌ Submit error:', error);
        showToast(error.message || 'Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
        console.groupEnd();
    }
};

// Event listeners initialization
const initEventListeners = () => {
    console.log('🔧 Initializing event listeners...');

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
            console.log(`📎 ${files.length} file(s) selected`);
            
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
            console.log(`📎 ${files.length} file(s) dropped`);
            
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
    });

    // Form submissions
    document.querySelectorAll('.ticket-form').forEach(form => {
        form.addEventListener('submit', (e) => handleFormSubmit(e, form));
    });

    console.log('✅ Event listeners initialized');
};

// Initialize on DOM ready
const init = () => {
    console.log('🎬 Ticket System Initializing...');
    console.log('Config:', CONFIG);
    
    updateTimestamps();
    setInterval(updateTimestamps, CONFIG.TIMESTAMP_INTERVAL);
    initEventListeners();
    
    console.log('✅ Ticket System Ready!');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}