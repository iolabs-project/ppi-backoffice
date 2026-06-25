export function parseMaskIntoNumeric(value) {
    if (!value) return 0;

    return (
        Number(
            String(value)
                .replace(/\./g, "") // remove thousand separator
                .replace(",", "."), // decimal separator
        ) || 0
    );
}

export function formatNumericIntoMask(value) {
    if (!value) return "0";

    const parts = String(value).split(".");
    const integerPart = parts[0];
    const decimalPart = parts[1] || "";

    const formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    return decimalPart ? `${formattedIntegerPart},${decimalPart}` : formattedIntegerPart;
}