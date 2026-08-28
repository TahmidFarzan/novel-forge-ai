export const titleFormat = (text) => {
    if (!text) return "";

    return String(text)
        .replace(/([a-z])([A-Z])/g, "$1 $2")
        .replace(/[_\-]+/g, " ")
        .trim()
        .toLowerCase()
        .replace(/^\w/, (c) => c.toUpperCase());
};

export const capitalize = (str) => {
    if (!str) return 'N/A';

    return str.charAt(0).toUpperCase() + str.slice(1);
};

export const loweriseText = (value) => {
    return String(value ?? '')
        .trim()
        .toLowerCase()
}
