export function parseMaskIntoNumeric(value) {
    if (value === null || value === undefined || value === "") return 0;

    return (
        Number(
            String(value)
                .replace(/,/g, ""),  // remove , thousand separators
        ) || 0
    );
}

export function formatNumericIntoMask(value, decimalPlaces = 2) {
    if (value === null || value === undefined || value === '') {
        return (0).toFixed(decimalPlaces);
    }

    const num = Number(value);
    if (isNaN(num)) {
        return (0).toFixed(decimalPlaces);
    }

    // toFixed() switches to exponent notation from 1e21, Intl never does
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: decimalPlaces,
        maximumFractionDigits: decimalPlaces,
    }).format(num);
}