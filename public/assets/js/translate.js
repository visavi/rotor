// Перевод для JS: данные в window.translations подключает директива @translation

export const __ = (key) => {
    const value = key.split('.').reduce((obj, k) => obj?.[k], window.translations)
    if (value !== undefined) return value

    console.warn(`Translation not found: ${key}`)
    return key
}
