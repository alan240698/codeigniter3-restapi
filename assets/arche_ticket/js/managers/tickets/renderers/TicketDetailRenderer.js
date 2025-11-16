import { CONFIG, PRIORITY_GLPI }            from '../../../config/constants.js';
import { formatDate }                       from '../../../utils/format.js';
import { escapeHtml }                       from '../../../utils/dom.js';
import Logger                               from '../../../utils/logger.js';
import TemplateRenderer                     from './TemplateRenderer.js';

class TicketDetailRenderer {
    /**
     * Render ticket detail
     */
    static render(ticket) {
        if (!ticket || typeof ticket !== 'object') {
            Logger.error('Invalid ticket data');
            return TemplateRenderer.getErrorHtml('Invalid ticket data');
        }

        const ticketId = parseInt(ticket.id);
        const status = (ticket.status || 'unknown').toLowerCase();
        const statusConfig = CONFIG.STATUS[status];
        const statusIcon = statusConfig?.icon || 'fa-circle';
        const statusLabel = statusConfig?.label || ticket.status || 'Unknown';

        const {category, content} = this.getCategoryName(escapeHtml(ticket.name || 'Untitled'));
        const title = content;
        const priority = ticket.priority ? this.getStringPriority(escapeHtml(ticket.priority)) : 'Normal';
        const description = escapeHtml(ticket.content || 'No description provided');
        const createdDate = ticket.date_creation ? formatDate(ticket.date_creation) : '';
        const updatedDate = ticket.date_mod ? formatDate(ticket.date_mod) : '';

        const showReopenBtn = ['closed', 'resolved', 'cancelled'].includes(status);

        let supporterHtml = '<span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>';

    if (ticket.supporter) {
        if (Array.isArray(ticket.supporter) && ticket.supporter.length > 0) {
            supporterHtml = ticket.supporter.map(supporter => {
                const fullName = `${supporter.firstname || ''} ${supporter.realname || ''}`.trim();
                const displayName = fullName || supporter.name || 'Unknown';
                const email = supporter.email || '';
                const phone = supporter.phone || supporter.mobile || '';

                return `
                    <div class="supporter-info">
                        <i class="fas fa-user-circle"></i>
                        <strong>${escapeHtml(displayName)}</strong>
                        ${email ? `<br><small><i class="fas fa-envelope"></i> ${escapeHtml(email)}</small>` : ''}
                        ${phone ? `<br><small><i class="fas fa-phone"></i> ${escapeHtml(phone)}</small>` : ''}
                    </div>
                `;
            }).join('');
        } else if (typeof ticket.supporter === 'object') {
            const fullName = `${ticket.supporter.firstname || ''} ${ticket.supporter.realname || ''}`.trim();
            const displayName = fullName || ticket.supporter.name || 'Support will be available soon';
            const email = ticket.supporter.email || '';
            const phone = ticket.supporter.phone || ticket.supporter.mobile || '';

            supporterHtml = `
                <div class="supporter-info">
                    <i class="fas fa-user-circle"></i>
                    <strong>${escapeHtml(displayName)}</strong>
                    ${email ? `<br><small><i class="fas fa-envelope"></i> ${escapeHtml(email)}</small>` : ''}
                    ${phone ? `<br><small><i class="fas fa-phone"></i> ${escapeHtml(phone)}</small>` : ''}
                </div>
            `;
        }
    }

    let html = `
        <div class="ticket-detail">
            <div class="ticket-detail-header">
                <div class="ticket-detail-title-section">
                    <span class="ticket-id-badge">#${ticketId}</span>
                    <h2 class="ticket-detail-title">${title}</h2>
                </div>
                <div class="ticket-detail-actions">
                    ${showReopenBtn ? `
                        <button 
                            class="btn btn-success btn-reopen-detail" 
                            onclick="TicketModalManager.reopenTicket(${ticketId}, event)"
                        >
                            <i class="fas fa-redo"></i> Reopen
                        </button>
                    ` : ''}
                </div>
            </div>

            <div class="ticket-detail-meta">
                <div class="ticket-detail-meta-item">
                    <label>Status:</label>
                    <span class="status-badge ${status}">
                        <i class="fas ${statusIcon}"></i> ${escapeHtml(statusLabel)}
                    </span>
                </div>
                <div class="ticket-detail-meta-item">
                    <label>Category:</label>
                    <span><i class="fas fa-folder"></i> ${category}</span>
                </div>
                <div class="ticket-detail-meta-item">
                    <label>Priority:</label>
                    <span><i class="fas fa-exclamation-circle"></i> ${priority}</span>
                </div>
                <div class="ticket-detail-meta-item">
                    <label>Assigned To:</label>
                    <span>${supporterHtml}</span>
                </div>
                <div class="ticket-detail-meta-item">
                    <label>Created:</label>
                    <span><i class="fas fa-calendar-plus"></i> ${createdDate}</span>
                </div>
                <div class="ticket-detail-meta-item">
                    <label>Updated:</label>
                    <span><i class="fas fa-calendar-check"></i> ${updatedDate}</span>
                </div>
            </div>

            <div class="ticket-detail-section">
                <h3><i class="fas fa-align-left"></i> Description</h3>
                <div class="ticket-detail-content">
                    ${description.replace(/\n/g, '<br>')}
                </div>
            </div>
    `;

    if (ticket.responses && Array.isArray(ticket.responses) && ticket.responses.length > 0) {
        html += `
            <div class="ticket-detail-section">
                <h3><i class="fas fa-comments"></i> Responses (${ticket.responses.length})</h3>
                <div class="ticket-responses">
                    ${ticket.responses.map(response => this.renderResponse(response)).join('')}
                </div>
            </div>
        `;
    }

    if (ticket.attachments && Array.isArray(ticket.attachments) && ticket.attachments.length > 0) {
        html += `
            <div class="ticket-detail-section">
                <h3><i class="fas fa-paperclip"></i> Attachments (${ticket.attachments.length})</h3>
                <div class="ticket-attachments">
                    ${ticket.attachments.map(att => this.renderAttachment(att, ticketId)).join('')}
                </div>
            </div>
        `;
    }

    html += `</div>`;

    return html;
}
    /**
     * Render single response
     */
    static renderResponse(response) {
        if (!response || typeof response !== 'object') {
            return '';
        }

        const author = escapeHtml(response.author || response.user || 'Unknown');
        const message = escapeHtml(response.message || response.text || '');
        const date = formatDate(response.created_at || response.date);
        const isStaff = response.is_staff || response.type === 'staff';

        return `
            <div class="ticket-response ${isStaff ? 'staff-response' : 'user-response'}">
                <div class="response-header">
                    <div class="response-author">
                        <i class="fas ${isStaff ? 'fa-user-shield' : 'fa-user'}"></i>
                        <strong>${author}</strong>
                        ${isStaff ? '<span class="staff-badge">Staff</span>' : ''}
                    </div>
                    <div class="response-date">
                        <i class="fas fa-clock"></i> ${date}
                    </div>
                </div>
                <div class="response-content">
                    ${message.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    }

    /**
     * Render single attachment
     */
    static renderAttachment(attachment, ticketId) {
        if (!attachment || typeof attachment !== 'object') {
            return '';
        }

        const name = escapeHtml(attachment.name || attachment.filename || 'Unknown file');
        const size = this.formatFileSize(attachment.size || 0);
        const mime = attachment.mime || 'application/octet-stream';
        const icon = this.getFileIcon(mime);
        const docId = attachment.id;

        return `
            <div class="attachment-item">
                <div class="attachment-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="attachment-info">
                    <div class="attachment-name" title="${name}">
                        ${name}
                    </div>
                    <div class="attachment-meta">
                        <span class="attachment-size">${size}</span>
                        <span class="attachment-type">${this.getFileType(mime)}</span>
                    </div>
                </div>
                <div class="attachment-actions">
                    <button 
                        class="btn btn-sm btn-primary btn-download"
                        data-docid="${docId}"
                        data-ticketid="${ticketId}"
                        title="Download"
                    >
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Format file size
     */
    static formatFileSize(bytes) {
        if (bytes === 0) return '0 B';

        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    static getCategoryName(message) {

        const category = message.split('&gt;')[0].trim().toUpperCase();
        const content = message.includes('&gt;') 
            ? message.split('&gt;')[1].trim().toUpperCase()
            : message;

        return {category, content}
    }

    static getStringPriority(number)
    {
        return PRIORITY_GLPI[number]
    }

    /**
     * Get file icon based on mime type
     */
    static getFileIcon(mime) {
        if (!mime) return 'fa-file';

        const iconMap = {
            'image/': 'fa-file-image',
            'video/': 'fa-file-video',
            'audio/': 'fa-file-audio',
            'application/pdf': 'fa-file-pdf',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml': 'fa-file-excel',
            'application/vnd.ms-powerpoint': 'fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml': 'fa-file-powerpoint',
            'application/zip': 'fa-file-archive',
            'application/x-rar': 'fa-file-archive',
            'text/': 'fa-file-alt'
        };

        for (let key in iconMap) {
            if (mime.startsWith(key)) {
                return iconMap[key];
            }
        }

        return 'fa-file';
    }

    /**
     * Get file type label
     */
    static getFileType(mime) {
        if (!mime) return 'File';

        const parts = mime.split('/');
        if (parts.length > 1) {
            return parts[1].toUpperCase();
        }

        return 'File';
    }
}

export default TicketDetailRenderer;