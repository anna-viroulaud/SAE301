


/**
 * Renders a template string by replacing placeholders with corresponding data values.
 *
 * @param {string} template - The template string containing placeholders in the format {{key}}.
 * @param {Object} data - An object containing key-value pairs where the key corresponds to the placeholder in the template.
 * @returns {string} - The rendered HTML string with placeholders replaced by data values.
 */
let genericRenderer = function(template, data){
    let html = template;
    for(let key in data){
        html = html.replaceAll(new RegExp("{{"+key+"}}", "g"), data[key]);
    }
    return html;
}

/**
 * Converts an HTML string into a DocumentFragment.
 *
 * @param {string} htmlString - The HTML string to convert.
 * @returns {DocumentFragment} - A DocumentFragment containing the parsed HTML elements.
 */
function htmlToFragment(htmlString) {
    const template = document.createElement('template');
    template.innerHTML = htmlString.trim(); // trim supprime les espaces inutiles
    return template.content;
}

/**
 * Converts a string to a URL-friendly slug.
 *
 * @param {string} text - The text to convert to a slug.
 * @returns {string} - A URL-friendly slug (lowercase, hyphens, no special characters).
 */
function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .normalize('NFD')                   // Normalize accents
        .replace(/[\u0300-\u036f]/g, '')    // Remove accents
        .replace(/[^\w\s-]/g, '')           // Remove special characters
        .trim()
        .replace(/\s+/g, '-')               // Replace spaces with hyphens
        .replace(/--+/g, '-');              // Replace multiple hyphens with single hyphen
}

export { genericRenderer, htmlToFragment, slugify };