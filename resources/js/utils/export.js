// Excel (.xlsx) export shared by list pages and reports.
//
//   exportXlsx({
//       filename: 'Pemesanan Pembelian',          // date and .xlsx are appended
//       title: 'Pemesanan Pembelian',             // optional first row above the header
//       subtitle: 'Status: Semua',                // optional second row (filters, period, ...)
//       columns: [
//           { header: 'Nomor PO', value: (row) => row.number },
//           { header: 'Total', value: (row) => row.total_amount, type: 'number' },
//       ],
//       rows: [...],
//   });
//
// Numbers stay real numbers in the sheet (summable, locale-proof); SheetJS is loaded only when used.

const NUMBER_FORMAT = '#,##0.00';

function toNumber(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }
    const n = typeof value === 'number' ? value : Number(String(value).trim().replace(/,/g, ''));
    // Round to cents: amounts computed in floating point (e.g. 365651.3064133017) would skew sums in Excel
    return Number.isFinite(n) ? Math.round(n * 100) / 100 : null;
}

function today() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

export async function exportXlsx({ filename, title = null, subtitle = null, columns, rows, sheetName = 'Data' }) {
    const XLSX = await import('xlsx');

    const aoa = [];
    if (title) aoa.push([title]);
    if (subtitle) aoa.push([subtitle]);
    if (title || subtitle) aoa.push([]);
    const headerRow = aoa.length;
    aoa.push(columns.map(c => c.header));

    for (const row of rows) {
        aoa.push(columns.map(c => {
            const value = c.value(row);
            if (c.type === 'number') return toNumber(value);
            // 'auto': plain numbers (also "1,755,200.00") become numbers, anything else ("28.1 %") stays text
            if (c.type === 'auto') return toNumber(value) ?? (value === null || value === undefined ? '' : String(value));
            return value === null || value === undefined ? '' : String(value);
        }));
    }

    const sheet = XLSX.utils.aoa_to_sheet(aoa);

    // Thousand separators + 2 decimals on number columns
    columns.forEach((c, ci) => {
        if (c.type !== 'number' && c.type !== 'auto') return;
        for (let r = headerRow + 1; r < aoa.length; r++) {
            const cell = sheet[XLSX.utils.encode_cell({ r, c: ci })];
            if (cell && cell.t === 'n') cell.z = c.format || NUMBER_FORMAT;
        }
    });

    // Column widths from the longest value in each column
    sheet['!cols'] = columns.map((c, ci) => {
        const longest = aoa.slice(headerRow).reduce((max, r) => Math.max(max, String(r[ci] ?? '').length), 0);
        return { wch: Math.min(Math.max(longest + 2, 10), 60) };
    });

    const book = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(book, sheet, sheetName.slice(0, 31));
    XLSX.writeFile(book, `${filename} ${today()}.xlsx`);
}

// Loads every row of a paginated datatable endpoint (same filters as the screen) for export.
export async function fetchAllRows(url, params = {}) {
    const response = await window.axios.get(url, { params: { ...params, page: 1, per_page: 100000 } });
    return response.data?.data ?? response.data ?? [];
}

// Wraps an export with a loading dialog and a readable error
export async function runExport(task) {
    window.Swal.fire({ title: 'Menyiapkan file Excel...', allowOutsideClick: false, didOpen: () => window.Swal.showLoading() });
    try {
        await task();
        window.Swal.close();
    } catch (error) {
        console.error(error);
        window.Swal.close();
        window.Toast.fire({ icon: 'error', title: 'Gagal mengekspor data. Silakan coba lagi.' });
    }
}

// Second line of the sheet, e.g. "Status: Draft · Periode: 2026-09-01 s/d 2026-09-30 · Pencarian: "abc""
export function describeFilters({ status = null, from = null, to = null, search = null } = {}) {
    const parts = [];
    if (status) parts.push(`Status: ${status}`);
    if (from || to) parts.push(`Periode: ${from || '…'} s/d ${to || '…'}`);
    if (search) parts.push(`Pencarian: "${search}"`);
    return parts.length ? parts.join(' · ') : 'Semua data';
}
