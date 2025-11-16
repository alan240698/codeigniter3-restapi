<?php
    $valid_categories = array_filter($cards, function ($cat) {
        return strtolower($cat['name']) !== 'root entity';
    });

    $placeholder_slug = '__placeholder__';

    if (!isset($formData[$placeholder_slug])) {
        $formData = array_merge(
            [$placeholder_slug => [
                $placeholder_slug => [],
                'description' => 'Please select a category above'
            ]],
            $formData
        );
    }
?>

<?php if (empty($valid_categories)): ?>
    <?php $this->load->view('arche_ticket/partials/empty_state'); ?>
<?php else: ?>
    <div class="category-nav">
        <?php foreach ($valid_categories as $category): ?>
            <?php
                $slug = strtolower(str_replace(' ', '-', $category['name']));
                $icon = 'fa-folder';

                switch($slug) {
                    case 'network': $icon = 'fa-network-wired'; break;
                    case 'user-computer': $icon = 'fa-desktop'; break;
                    case 'group-app': $icon = 'fa-th'; break;
                    case 'cyber-security': $icon = 'fa-shield-alt'; break;
                }
            ?>
            <div class="nav-pill" data-target="<?= $slug ?>">
                <i class="fas <?= $icon ?>"></i>
                <span><?= htmlspecialchars($category['name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cards-grid">
        <?php foreach ($formData as $slug => $data): ?>
            <?php if (isset($data[$slug]) || $slug === $placeholder_slug): ?>
                <?php
                    if ($slug === $placeholder_slug) {
                        $form_config = [
                            'slug'        => $placeholder_slug,
                            'title'       => 'Select Category',
                            'description' => 'Please select a category above to create your ticket',
                            'icon'        => 'fa-folder-open',
                            'fields'      => [],
                            'is_placeholder' => true
                        ];
                    } else {
                        $form_config = [
                            'slug'        => $slug,
                            'title'       => ucwords(str_replace('-', ' ', $slug)),
                            'description' => $data['description'] ?? '',
                            'icon'        => 'fa-folder-open',
                            'fields'      => $data[$slug] ?? [],
                            'is_placeholder' => false
                        ];
                    }
                    
                    $this->load->view('arche_ticket/partials/dynamic_main_form', $form_config);
                ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

