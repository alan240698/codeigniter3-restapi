<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Build category mapping with full paths
 * 
 * @param array $options
 * @param array $parent_path
 * @return array Map of value => full_path
 */
function build_category_path_map($options, $parent_path = [])
{
    $map = [];

    foreach ($options as $key => $option) {
        $label = (string)($option['label'] ?? $key);
        $value = (string)($option['value'] ?? $key);
        
        // Build full path
        $current_path = array_merge($parent_path, [$label]);
        $full_path = implode(' > ', $current_path);
        
        // Store mapping: value => full_path
        $map[$value] = $full_path;

        // Recursively process children
        if (!empty($option['children']) && is_array($option['children'])) {
            $children_map = build_category_path_map($option['children'], $current_path);
            $map = array_merge($map, $children_map);
        }
    }

    return $map;
}

/**
 * Render category options (normal display in dropdown)
 *
 * @param array $options
 * @param int $depth
 * @return string
 */
function render_category_options_recursive($options, $depth = 0)
{
    $html = '';
    $indent = str_repeat('&nbsp;&nbsp;', $depth);

    foreach ($options as $key => $option) {
        $label = htmlspecialchars((string)($option['label'] ?? $key));
        $value = htmlspecialchars((string)($option['value'] ?? $key));

        if (!empty($option['children']) && is_array($option['children'])) {
            $html .= '<optgroup label="' . $indent . $label . '">';
            $html .= render_category_options_recursive($option['children'], $depth + 1);
            $html .= '</optgroup>';
        } else {
            $html .= '<option value="' . $value . '">' . $indent . $label . '</option>';
        }
    }

    return $html;
}

/**
 * Generate form field HTML based on configuration
 * 
 * @param string $field_name
 * @param array $field_config
 * @param string $category
 * @return string
 */
function render_form_field($field_name, $field_config, $category = '')
{
    $html = '';
    $required = $field_config['required'] ? 'required' : '';
    $label = $field_config['label'];

    switch ($field_config['type']) {
        case 'text':
            $html .= '<div class="form-group">';
            $html .= '<label><i class="fas fa-heading"></i> ' . $label . '</label>';
            $html .= '<input type="text" class="form-control" name="' . $field_name . '" ';
            $html .= 'placeholder="' . ($field_config['placeholder'] ?? '') . '" ' . $required . '>';
            $html .= '</div>';
            break;

        case 'textarea':
            $html .= '<div class="form-group full-width">';
            $html .= '<label><i class="fas fa-align-left"></i> ' . $label . '</label>';
            $html .= '<textarea class="form-control" name="' . $field_name . '" ';
            $html .= 'placeholder="' . ($field_config['placeholder'] ?? '') . '" ' . $required . '></textarea>';
            $html .= '</div>';
            break;

        case 'select':
            // Build path mapping for JavaScript
            $path_map = build_category_path_map($field_config['options']);
            $path_map_json = json_encode($path_map);
            
            $select_id = 'select_' . uniqid();
            
            $html .= '<div class="form-group full-width">';
            $html .= '<label><i class="fas fa-list"></i> ' . $label . '</label>';
            
            // Hidden input to store full path
            $html .= '<input type="hidden" name="' . $field_name . '" id="hidden_' . $select_id . '">';
            
            // Visible select with custom display
            $html .= '<div class="category-select-wrapper">';
            $html .= '<select class="form-select category-select" id="' . $select_id . '" ' . $required . '>';
            $html .= '<option value="">Chọn ' . strtolower($label) . '...</option>';
            $html .= render_category_options_recursive($field_config['options']);
            $html .= '</select>';
            $html .= '<div class="selected-path" id="path_' . $select_id . '" style="display:none;"></div>';
            $html .= '</div>';
            
            // Add JavaScript for handling selection
            $html .= '<script>';
            $html .= 'const pathMap_' . $select_id . ' = ' . $path_map_json . ';';
            $html .= '
                (function() {
                    const select = document.getElementById("' . $select_id . '");
                    const hiddenInput = document.getElementById("hidden_' . $select_id . '");
                    const pathDisplay = document.getElementById("path_' . $select_id . '");
                    
                    select.addEventListener("change", function() {
                        const value = this.value;
                        
                        if (value) {
                            const fullPath = pathMap_' . $select_id . '[value];
                            hiddenInput.value = fullPath;
                            
                            // Truncate if too long
                            const maxLength = 50;
                            let displayText = fullPath;
                            if (fullPath.length > maxLength) {
                                displayText = fullPath.substring(0, maxLength) + "...";
                            }
                            
                            pathDisplay.innerHTML = "<i class=\"fas fa-check-circle\"></i> " + displayText;
                            pathDisplay.title = fullPath; // Full text on hover
                            pathDisplay.style.display = "block";
                            select.style.display = "none";
                        } else {
                            hiddenInput.value = "";
                            pathDisplay.style.display = "none";
                            select.style.display = "block";
                        }
                    });
                    
                    // Click on path to change selection
                    pathDisplay.addEventListener("click", function() {
                        pathDisplay.style.display = "none";
                        select.style.display = "block";
                        select.focus();
                    });
                })();
            ';
            $html .= '</script>';
            
            // Add inline styles
            $html .= '<style>
                .category-select-wrapper {
                    position: relative;
                }
                .selected-path {
                    padding: 0.75rem 1rem;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-size: 0.9rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
                }
                .selected-path:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
                }
                .selected-path i {
                    color: #4ade80;
                }
            </style>';
            
            $html .= '</div>';
            break;

        case 'file':
            $max_files = $field_config['max_files'] ?? 5;
            $max_size_mb = ($field_config['max_size'] ?? 10485760) / 1048576;

            $html .= '<div class="form-group full-width">';
            $html .= '<label><i class="fas fa-paperclip"></i> ' . $label . ' (tùy chọn)</label>';
            $html .= '<div class="file-upload-wrapper">';
            $html .= '<input type="file" name="attachments[]" multiple ';
            $html .= 'accept="' . str_replace('|', ',', $field_config['allowed_types']) . '">';
            $html .= '<div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>';
            $html .= '<div class="upload-text">';
            $html .= '<strong>Click để chọn file</strong> hoặc kéo thả vào đây';
            $html .= '</div>';
            $html .= '<div class="file-limit-text">';
            $html .= 'Tối đa ' . $max_files . ' files, mỗi file không quá ' . $max_size_mb . 'MB';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="file-list"></div>';
            $html .= '</div>';
            break;
    }

    return $html;
}