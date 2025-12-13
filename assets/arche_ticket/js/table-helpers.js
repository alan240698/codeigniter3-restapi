/**
 * Helper functions for enhanced table rendering
 */

/**
 * Render category - strip country code and uppercase
 */
function renderCategoryWithCountry(category) {
    if (!category) return 'N/A';

    // Extract category name, removing country code prefix if present (e.g., "[VN] - user-computer" -> "USER-COMPUTER")
    const countryMatch = category.match(/^\[(\w+)\]\s*-?\s*(.+)$/);

    let categoryName;
    if (countryMatch) {
        // Has country code - extract just the category name
        categoryName = countryMatch[2].trim();
    } else {
        // No country code - use as is
        categoryName = category;
    }

    // Convert to uppercase and display
    return `<span class="category-badge"><i class="fas fa-tag me-1"></i>${categoryName.toUpperCase()}</span>`;
}

/**
 * Render assigned to as plain text (no badge background)
 */
function renderAssignedBadge(detail, isLoading) {
    if (isLoading) {
        return '<i class="fas fa-spinner fa-spin"></i>';
    }

    if (detail && detail.supporter && detail.supporter.length > 0) {
        const supporterName = detail.supporter[0]['realname'];
        return supporterName; // Just plain text, no badge
    }

    // Default text without badge
    return 'GroupIS/GroupIT';
}
