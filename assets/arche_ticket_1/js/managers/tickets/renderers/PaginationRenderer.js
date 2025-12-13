class PaginationRenderer {
    /**
     * Render pagination controls
     */
    static render(currentPage, totalPages, filteredCount, itemsPerPage) {
        if (totalPages <= 1) {
            return '';
        }

        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, filteredCount);

        let html = `
            <div class="pagination-info">
                Showing ${start}-${end} of ${filteredCount} tickets
            </div>
            <div class="pagination-controls">
        `;

        html += `
            <button 
                class="btn btn-sm btn-pagination ${currentPage === 1 ? 'disabled' : ''}"
                onclick="TicketModalManager.goToPage(${currentPage - 1})"
                ${currentPage === 1 ? 'disabled' : ''}
            >
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            html += `<button class="btn btn-sm btn-pagination" onclick="TicketModalManager.goToPage(1)">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <button 
                    class="btn btn-sm btn-pagination ${i === currentPage ? 'active' : ''}"
                    onclick="TicketModalManager.goToPage(${i})"
                >
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<button class="btn btn-sm btn-pagination" onclick="TicketModalManager.goToPage(${totalPages})">${totalPages}</button>`;
        }

        html += `
            <button 
                class="btn btn-sm btn-pagination ${currentPage === totalPages ? 'disabled' : ''}"
                onclick="TicketModalManager.goToPage(${currentPage + 1})"
                ${currentPage === totalPages ? 'disabled' : ''}
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        html += `</div>`;

        return html;
    }
}

export default PaginationRenderer;