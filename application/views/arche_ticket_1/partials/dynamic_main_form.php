<?php

/**
 * Dynamic Ticket Category Card View
 *
 * @var string $slug        - slug dạng 'network', 'user-computer', ...
 * @var string $title       - tên hiển thị dạng 'Network', 'User Computer', ...
 * @var string $description - mô tả ngắn cho category (nếu có)
 * @var array  $fields      - danh sách field để render form
 */
?>

<div class="category-card <?= htmlspecialchars($slug) ?>" data-category="<?= htmlspecialchars($slug) ?>">
    <div class="card-header">
        <div class="card-header-content">
            <!-- Tự động đổi icon theo category -->
            <i class="fas <?= isset($icon) ? htmlspecialchars($icon) : 'fa-folder-open' ?>"></i>
            <h3><?= htmlspecialchars($title) ?></h3>
        </div>
        <button class="close-btn" type="button">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <?php if (!empty($description)): ?>
        <p class="card-description"><?= htmlspecialchars($description) ?></p>
    <?php endif; ?>

    <div class="card-body">
        <form class="ticket-form" data-category="<?= htmlspecialchars($slug) ?>">
            <!-- Hidden category -->
            <input type="hidden" name="category" value="<?= htmlspecialchars($slug) ?>">

            <!-- CSRF Token -->
            <input type="hidden"
                name="<?= $this->security->get_csrf_token_name(); ?>"
                value="<?= $this->security->get_csrf_hash(); ?>" />

            <!-- Dynamic Form Fields -->
            <div class="form-row">
                <?php
                require_once(APPPATH . 'views/arche_ticket/partials/form_field_helper.php');

                if (!empty($fields) && is_array($fields)) {
                    foreach ($fields as $field_name => $field_config) {
                        echo render_form_field($field_name, $field_config, $slug);
                    }
                } else {
                    echo '<p class="no-fields">No fields configured for this category.</p>';
                }
                ?>
            </div>

            <div class="timestamp" id="timestamp-<?= htmlspecialchars($slug) ?>"></div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                <span>Tạo Ticket</span>
            </button>
        </form>
    </div>
</div>
