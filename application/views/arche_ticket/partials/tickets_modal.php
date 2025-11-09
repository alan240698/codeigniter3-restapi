<div class="tickets-modal" id="ticketsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-ticket-alt"></i> My Tickets</h2>
            <button class="close-modal" onclick="TicketModal.close()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="ticketsContainer">
        </div>
        <div id="ticketsPagination" class="pagination-wrapper"></div>
    </div>
</div>

<div id="ticketDetailModal" class="modal modal-detail">
    <div class="modal-content modal-content-large">
        <!-- <button class="modal-close">
            <i class="fas fa-times"></i>
        </button> -->
                <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-ticket-alt"></i> Detail</h2>
            <button class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="ticketDetailContainer" class="ticket-detail-container"></div>
    </div>
</div>