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

const toYmd = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

// Report period shortcuts; "this year" runs to the end of the current month, like "this month" does
const PERIODS = {
    this_month: (y, m) => [new Date(y, m, 1), new Date(y, m + 1, 0)],
    this_year: (y, m) => [new Date(y, 0, 1), new Date(y, m + 1, 0)],
    last_year: (y) => [new Date(y - 1, 0, 1), new Date(y - 1, 11, 31)],
};

export function periodRange(key, today = new Date()) {
    const [start, end] = PERIODS[key](today.getFullYear(), today.getMonth());
    return { start_date: toYmd(start), end_date: toYmd(end) };
}

// Which shortcut the dates were set by, '' when picked by hand
export function matchPeriod(startDate, endDate, today = new Date()) {
    return Object.keys(PERIODS).find((key) => {
        const range = periodRange(key, today);
        return range.start_date === startDate && range.end_date === endDate;
    }) ?? '';
}
