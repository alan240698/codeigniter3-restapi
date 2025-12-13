/**
 * Truncate text and add tooltip
 */
function truncateText(text, maxLength = 100) {
    if (!text) return '';

    const cleanText = text.replace(/<[^>]*>/g, '').trim(); // Remove HTML tags

    if (cleanText.length <= maxLength) {
        return cleanText;
    }

    const truncated = cleanText.substring(0, maxLength) + '...';
    // Add title attribute for tooltip on hover
    return `<span class="truncated-text" title="${cleanText.replace(/"/g, '&quot;')}">${truncated}</span>`;
}
