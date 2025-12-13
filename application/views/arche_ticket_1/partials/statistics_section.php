<?php
    $status_items = [
        'new'        => ['icon' => 'fa-certificate',    'label' => 'New'],
        'pending'    => ['icon' => 'fa-clock',          'label' => 'Pending'],
        'processing' => ['icon' => 'fa-spinner',        'label' => 'Processing'],
        'solved'     => ['icon' => 'fa-check-circle',   'label' => 'Solved'],
        'closed'     => ['icon' => 'fa-archive',        'label' => 'Closed']
    ];
?>

<div class="stats-section">
    <div class="section-header">
        <h2>
            <i class="fas fa-chart-line"></i>
            Ticket Tracking
        </h2>
        <p>Monitor your requests</p>
    </div>

    <div class="stats-content">
        <?php foreach ($status_items as $status => $config): ?>
            <div class="stat-item" onclick="TicketModal.showByStatus('<?= $status ?>')">
                <div class="stat-item-left">
                    <div class="stat-icon <?= $status ?>">
                        <i class="fas <?= $config['icon'] ?>"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $config['label'] ?></h4>
                    </div>
                </div>
                <div class="stat-count" id="<?= $status ?>Count">0</div>
            </div>
        <?php endforeach; ?>

        <button class="view-all-btn" onclick="TicketModal.showAll()">
            <i class="fas fa-list"></i>
            View All Tickets
        </button>
    </div>
</div>