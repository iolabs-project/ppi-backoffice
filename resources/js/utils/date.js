// Lists show dates the way detail pages do: "2026-10-25" → "25/10/2026"
export function formatDate(value) {
    if (!value) return '-';

    const date = /^\d{4}-\d{2}-\d{2}$/.test(value) || / \d{2}:\d{2}/.test(value)
        // A plain date (or local datetime) is already in the company's day; don't shift it through UTC
        ? new Date(value.slice(0, 10) + 'T00:00:00')
        : new Date(value);
    if (isNaN(date)) return value;

    return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
