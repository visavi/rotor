const locales = import.meta.glob('resources/lang/*/main.js', { eager: true });

const translations = {};
for (const path in locales) {
    const lang = path.match(/\/([^/]+)\/main\.js$/)?.[1];
    if (lang) translations[lang] = locales[path].default;
}

const currentLang = document.documentElement.lang || 'ru';
const active = translations[currentLang] || translations.ru || {};

export function trans(key) {
    const value = key.split('.').reduce((obj, k) => obj?.[k], active);
    if (value === undefined) {
        console.warn(`Translation key not found: ${key}`);
        return key;
    }
    return value;
}

window.__ = trans;
