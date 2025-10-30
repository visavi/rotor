const locales = import.meta.glob('/resources/lang/*/main.json', { eager: true });

const translations = {};
for (const path in locales) {
    const lang = path.match(/\/([^/]+)\/main\.json$/)?.[1];
    if (lang) translations[lang] = locales[path].default;
}

let currentLang = document.documentElement.lang || 'ru';
let active = translations[currentLang] || translations.ru || {};

window.addTranslations = (lang, data) => {
    translations[lang] = data;
    if (currentLang === lang) {
        active = data;
    }
};

async function loadExtraLang(lang) {
    if (translations[lang]) return;

    try {
        const res = await fetch(`/lang/${lang}.json`);
        if (res.ok) {
            const data = await res.json();
            window.addTranslations(lang, data);
        } else {
            console.warn(`Language file not found: /lang/${lang}.json`);
            if (lang !== 'ru') await loadExtraLang('ru'); // fallback
        }
    } catch (err) {
        console.error(`Failed to load language ${lang}:`, err);
    }
}

if (!translations[currentLang]) {
    void loadExtraLang(currentLang);
}

export function trans(key) {
    const value = key.split('.').reduce((obj, k) => obj?.[k], active);
    if (value === undefined) {
        console.warn(`Translation key not found: ${key}`);
        return key;
    }
    return value;
}

window.__ = trans;
