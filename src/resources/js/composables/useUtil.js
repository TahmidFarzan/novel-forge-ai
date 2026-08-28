export const replaceAllOccurrences = (text, search, replace) => {
    if (!text || !search) return text;
    const escapedSearch = search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(escapedSearch, 'g');

    return text.replace(regex, replace);
};

export const extractModelName = (fullClassName) => {
    if (!fullClassName) return '';

    return fullClassName.split(/\\+/).pop();
};
