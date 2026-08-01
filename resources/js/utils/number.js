export function parseMaskIntoNumeric(value) {
    if (value === null || value === undefined || value === "") return 0;

    return (
        Number(
            String(value)
                .replace(/,/g, ""),  // remove , thousand separators
        ) || 0
    );
}

export function formatNumericIntoMask(value) {
    if (value === null || value === undefined) return "0";

    const num = Number(value);
    if (isNaN(num)) return "0";

    const negative = num < 0;
    const parts = Math.abs(num).toString().split(".");
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    const decimalPart = parts[1] || "";

    const result = decimalPart ? `${integerPart}.${decimalPart}` : integerPart;
    return negative ? `-${result}` : result;
}