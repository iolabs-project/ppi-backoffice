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

    const negative = num < 0;

    const [integerPart, decimalPart] = Math.abs(num)
        .toFixed(decimalPlaces)
        .split(".");

    const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    return negative
        ? `-${formattedInteger}${decimalPlaces > 0 ? `.${decimalPart}` : ""}`
        : `${formattedInteger}${decimalPlaces > 0 ? `.${decimalPart}` : ""}`;
}