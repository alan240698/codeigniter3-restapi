// File upload handling
const MAX_FILES = 5;
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const fileDataMap = new Map();

function formatFileSize(bytes) {
if (bytes === 0) return '0 Bytes';
const k = 1024;
const sizes = ['Bytes', 'KB', 'MB', 'GB'];
const i = Math.floor(Math.log(bytes) / Math.log(k));
return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function getFileIcon(fileName) {
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
}

// Timestamp update
function updateTimestamps() {
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
}

updateTimestamps();
setInterval(updateTimestamps, 1000);

// Toast notification
function showToast(message, type = 'success') {
const toast = document.createElement('div');
toast.className = `toast ${type}`;

const icons = {
success: 'fa-check-circle',
error: 'fa-exclamation-circle',
warning: 'fa-exclamation-triangle'
};

toast.innerHTML = `
<i class="fas ${icons[type]}"></i>
<div>${message}</div>
`;

document.getElementById('toastContainer').appendChild(toast);

setTimeout(() => {
toast.style.animation = 'slideIn 0.3s ease reverse';
setTimeout(() => toast.remove(), 300);
}, 3000);
}

// Card expansion handling
const dashboardContainer = document.querySelector('.dashboard-container');
const categoryCards = document.querySelectorAll('.category-card');
const navPills = document.querySelectorAll('.nav-pill');

function expandCard(card) {
categoryCards.forEach(c => {
if (c !== card) {
c.classList.remove('expanded');
}
});

card.classList.add('expanded');
dashboardContainer.classList.add('has-active');

const category = card.dataset.category;
navPills.forEach(pill => {
if (pill.dataset.target === category) {
pill.classList.add('active');
} else {
pill.classList.remove('active');
}
});
}

function collapseAll() {
categoryCards.forEach(card => {
card.classList.remove('expanded');
});
dashboardContainer.classList.remove('has-active');
navPills.forEach(pill => {
pill.classList.remove('active');
});
}

// Event listeners
categoryCards.forEach(card => {
card.addEventListener('click', function(e) {
if (e.target.closest('.close-btn') || e.target.closest('.card-body')) {
return;
}
expandCard(card);
});
});

document.querySelectorAll('.close-btn').forEach(btn => {
btn.addEventListener('click', function(e) {
e.stopPropagation();
collapseAll();
});
});

navPills.forEach(pill => {
pill.addEventListener('click', function() {
const target = this.dataset.target;
const targetCard = document.querySelector(`.category-card[data-category="${target}"]`);
if (targetCard) {
expandCard(targetCard);
}
});
});

// File upload handling
function addFileToList(file, wrapper, formId) {
const fileList = wrapper.querySelector('.file-list');
const currentFiles = fileDataMap.get(formId) || [];

if (currentFiles.length >= MAX_FILES) {
showToast(`Chỉ được phép tải lên tối đa ${MAX_FILES} files`, 'warning');
return false;
}

if (file.size > MAX_FILE_SIZE) {
showToast(`File "${file.name}" vượt quá 10MB`, 'error');
return false;
}

currentFiles.push(file);
fileDataMap.set(formId, currentFiles);

const fileItem = document.createElement('div');
fileItem.className = 'file-item';
fileItem.innerHTML = `
<div class="file-info">
    <i class="fas ${getFileIcon(file.name)} file-icon"></i>
    <div class="file-details">
        <div class="file-name">${file.name}</div>
        <div class="file-size">${formatFileSize(file.size)}</div>
    </div>
</div>
<button type="button" class="file-remove">
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
});

fileList.appendChild(fileItem);
return true;
}

document.querySelectorAll('.file-upload-wrapper').forEach(wrapper => {
const input = wrapper.querySelector('input[type="file"]');
const form = wrapper.closest('form');
const formId = form.dataset.category;

input.addEventListener('change', (e) => {
const files = Array.from(e.target.files);
files.forEach(file => {
addFileToList(file, wrapper, formId);
});
e.target.value = '';
});

wrapper.addEventListener('dragover', (e) => {
e.preventDefault();
wrapper.classList.add('dragover');
});

wrapper.addEventListener('dragleave', () => {
wrapper.classList.remove('dragover');
});

wrapper.addEventListener('drop', (e) => {
e.preventDefault();
wrapper.classList.remove('dragover');

const files = Array.from(e.dataTransfer.files);
files.forEach(file => {
addFileToList(file, wrapper, formId);
});
});
});

// Form submit handler
document.querySelectorAll('.ticket-form').forEach(form => {
form.addEventListener('submit', async (e) => {
e.preventDefault();

const btn = form.querySelector('.submit-btn');
const btnText = btn.querySelector('span');
const originalText = btnText.textContent;

btn.disabled = true;
btnText.textContent = 'Đang gửi...';
btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

const formData = new FormData(form);
const category = form.dataset.category;

// Thêm files từ fileDataMap
const files = fileDataMap.get(category) || [];
files.forEach((file, index) => {
formData.append(`attachments[]`, file);
});

try {
const response = await fetch(TICKET_CREATE_URL, {
method: 'POST',
body: formData
});

const result = await response.json();

if (result.success) {
showToast(`Ticket #${result.ticket_id} đã được tạo thành công!`, 'success');

// Reset form
form.reset();
form.querySelector('.file-list').innerHTML = '';
fileDataMap.set(category, []);

setTimeout(() => {
collapseAll();
}, 1500);
} else {
showToast(result.message || 'Có lỗi xảy ra khi tạo ticket', 'error');
}

} catch (error) {
showToast('Có lỗi xảy ra khi tạo ticket. Vui lòng thử lại!', 'error');
} finally {
btn.disabled = false;
btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
}
});
});