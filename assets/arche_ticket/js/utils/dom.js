export const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

export const createElement = (html) => {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstChild;
};

export const removeElement = (element) => {
    if (element && element.parentNode) {
        element.parentNode.removeChild(element);
    }
};

export const addClass = (element, ...classes) => {
    if (element) element.classList.add(...classes);
};

export const removeClass = (element, ...classes) => {
    if (element) element.classList.remove(...classes);
};

export const toggleClass = (element, className, force) => {
    if (element) return element.classList.toggle(className, force);
};

export const $ = (selector, parent = document) => {
    return parent.querySelector(selector);
};

export const $$ = (selector, parent = document) => {
    return Array.from(parent.querySelectorAll(selector));
};
