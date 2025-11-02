<?php
    $valid_categories = array_filter($cards, function ($cat) {
        return strtolower($cat['name']) !== 'root entity';
    });
?>

<?php if (empty($valid_categories)): ?>
    <?php $this->load->view('arche_ticket/partials/empty_state'); ?>
<?php else: ?>
    <!-- Category Navigation -->
    <div class="category-nav">
        <?php foreach ($valid_categories as $category): ?>
            <?php
                $slug = strtolower(str_replace(' ', '-', $category['name']));
                $icon = 'fa-folder';
            ?>
            <div class="nav-pill" data-target="<?= $slug ?>">
                <i class="fas <?= $icon ?>"></i>
                <span><?= htmlspecialchars($category['name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Dynamic Forms Grid -->
    <div class="cards-grid">
        <?php foreach ($formData as $slug => $data): ?>
            <?php if (isset($data[$slug]) && !empty($data[$slug])): ?>
                <?php
                    $form_config = [
                        'slug'        => $slug,
                        'title'       => ucwords(str_replace('-', ' ', $slug)),
                        'description' => $data['description'] ?? '',
                        'icon'        => $data['icon'] ?? 'fa-folder-open',
                        'fields'      => $data[$slug] ?? []
                    ];
                    $this->load->view('arche_ticket/partials/dynamic_main_form', $form_config);
                ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>