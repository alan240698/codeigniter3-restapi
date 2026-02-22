/**
 * User Ticket System - JavaScript Utilities
 * Shared functions for all ticket pages
 */

// ============================================
// LOADING OVERLAY
// ============================================

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('hidden');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('hidden');
    }
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas fa-${icons[type] || 'info-circle'}" style="font-size: 1.2rem;"></i>
        <span style="flex: 1;">${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: var(--gray);">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
}

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// FORM VALIDATION
// ============================================

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\d\s\-\+\(\)]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

function validateRequired(value) {
    return value !== null && value !== undefined && value.toString().trim() !== '';
}

function validateMinLength(value, minLength) {
    return value && value.toString().length >= minLength;
}

function validateMaxLength(value, maxLength) {
    return !value || value.toString().length <= maxLength;
}

// ============================================
// FILE HANDLING
// ============================================

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    
    const icons = {
        pdf: 'file-pdf',
        doc: 'file-word',
        docx: 'file-word',
        xls: 'file-excel',
        xlsx: 'file-excel',
        ppt: 'file-powerpoint',
        pptx: 'file-powerpoint',
        jpg: 'file-image',
        jpeg: 'file-image',
        png: 'file-image',
        gif: 'file-image',
        zip: 'file-archive',
        rar: 'file-archive',
        txt: 'file-alt',
        csv: 'file-csv'
    };
    
    return icons[ext] || 'file';
}

function validateFileType(filename, allowedTypes) {
    const ext = filename.split('.').pop().toLowerCase();
    return allowedTypes.includes(ext);
}

function validateFileSize(fileSize, maxSize) {
    return fileSize <= maxSize;
}

// ============================================
// DATE & TIME FORMATTING
// ============================================

function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatRelativeTime(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
    } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        return `${months} month${months > 1 ? 's' : ''} ago`;
    } else {
        const years = Math.floor(diffDays / 365);
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
}

// ============================================
// STRING UTILITIES
// ============================================

function escapeHtml(text) {
    if (!text) return '';
    
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function capitalizeWords(str) {
    if (!str) return '';
    return str.replace(/\b\w/g, l => l.toUpperCase());
}

// ============================================
// DEBOUNCE & THROTTLE
// ============================================

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ============================================
// API HELPERS
// ============================================

async function apiGet(url) {
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        return await response.json();
    } catch (error) {
        console.error('API GET Error:', error);
        throw error;
    }
}

async function apiPost(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        return await response.json();
    } catch (error) {
        console.error('API POST Error:', error);
        throw error;
    }
}

async function apiPostFormData(url, formData) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    } catch (error) {
        console.error('API POST FormData Error:', error);
        throw error;
    }
}

async function apiPut(url, data) {
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        return await response.json();
    } catch (error) {
        console.error('API PUT Error:', error);
        throw error;
    }
}

async function apiDelete(url) {
    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        return await response.json();
    } catch (error) {
        console.error('API DELETE Error:', error);
        throw error;
    }
}

// ============================================
// DOM UTILITIES
// ============================================

function createElement(tag, className, innerHTML) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (innerHTML) element.innerHTML = innerHTML;
    return element;
}

function removeElement(element) {
    if (element && element.parentNode) {
        element.parentNode.removeChild(element);
    }
}

function toggleClass(element, className) {
    if (element) {
        element.classList.toggle(className);
    }
}

function addClass(element, className) {
    if (element) {
        element.classList.add(className);
    }
}

function removeClass(element, className) {
    if (element) {
        element.classList.remove(className);
    }
}

// ============================================
// LOCAL STORAGE HELPERS
// ============================================

function setLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('LocalStorage Set Error:', error);
        return false;
    }
}

function getLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('LocalStorage Get Error:', error);
        return defaultValue;
    }
}

function removeLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('LocalStorage Remove Error:', error);
        return false;
    }
}

function clearLocalStorage() {
    try {
        localStorage.clear();
        return true;
    } catch (error) {
        console.error('LocalStorage Clear Error:', error);
        return false;
    }
}

// ============================================
// MODAL HELPERS
// ============================================

function createModal(title, content, buttons = []) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    `;
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.style.cssText = `
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    `;
    
    modalContent.innerHTML = `
        <h2 style="margin-bottom: 1rem; color: var(--dark);">${title}</h2>
        <div style="margin-bottom: 1.5rem;">${content}</div>
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            ${buttons.map(btn => `
                <button class="btn ${btn.className || 'btn-secondary'}" 
                        onclick="${btn.onclick || 'this.closest(\'.modal-overlay\').remove()'}">
                    ${btn.text}
                </button>
            `).join('')}
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    return modal;
}

function closeModal(modal) {
    if (modal && modal.parentNode) {
        modal.remove();
    }
}

// ============================================
// CONFIRMATION DIALOG
// ============================================

function confirmDialog(message, onConfirm, onCancel) {
    const modal = createModal(
        'Confirmation',
        `<p>${message}</p>`,
        [
            {
                text: 'Cancel',
                className: 'btn-secondary',
                onclick: `closeModal(this.closest('.modal-overlay')); ${onCancel ? onCancel + '()' : ''}`
            },
            {
                text: 'Confirm',
                className: 'btn-primary',
                onclick: `closeModal(this.closest('.modal-overlay')); ${onConfirm}()`
            }
        ]
    );
    
    return modal;
}

// ============================================
// COPY TO CLIPBOARD
// ============================================

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showToast('Copied to clipboard', 'success');
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                showToast('Failed to copy', 'error');
            });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showToast('Copied to clipboard', 'success');
        } catch (err) {
            console.error('Failed to copy:', err);
            showToast('Failed to copy', 'error');
        }
        
        document.body.removeChild(textarea);
    }
}

// ============================================
// PRINT HELPERS
// ============================================

function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link rel="stylesheet" href="' + document.querySelector('link[rel="stylesheet"]').href + '">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// ============================================
// EXPORT HELPERS
// ============================================

function downloadJSON(data, filename) {
    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || 'data.json';
    link.click();
    URL.revokeObjectURL(url);
}

function downloadCSV(data, filename) {
    if (!data || !data.length) return;
    
    const headers = Object.keys(data[0]);
    const csv = [
        headers.join(','),
        ...data.map(row => headers.map(header => JSON.stringify(row[header] || '')).join(','))
    ].join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || 'data.csv';
    link.click();
    URL.revokeObjectURL(url);
}

// ============================================
// INITIALIZATION
// ============================================

console.log('User Ticket System JS loaded successfully');
