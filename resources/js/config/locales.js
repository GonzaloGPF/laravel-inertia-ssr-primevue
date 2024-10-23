const numberCurrency = {
    style: 'currency',
    currencyDisplay: 'symbol',
    minimumFractionDigits: 2,
    notation: 'compact',
}
const date = {
    hour: {
        hour: 'numeric',
        minute: 'numeric',
    },
    short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    },
    long: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short',
        hour: 'numeric',
        minute: 'numeric',
        // hour12: true,
    }
}
const locales = {
    /**
     * The app locale is given by Laravel (it's placed in config/app.php as 'locale')
     *
     * @returns {string}
     */
    getLocale () {
        return document.documentElement.getAttribute('lang')
    },

    getTimezone () {
        return Intl.DateTimeFormat()?.resolvedOptions()?.timeZone || 'Europe/Madrid'
    },

    /**
     * Maps Laravel locales to i18n locales
     *
     * @type {{es: string, en: string}}
     */
    locales: {
        en: 'en-GB',
        es: 'es-ES',
        fr: 'fr-FR',
        de: 'de-DE',
        ru: 'ru',
        ch: 'ch-CH',
        ja: 'ja-JP'
    },

    numbers: {
        'en-EN': {
            currency: { currency: 'GBP', ...numberCurrency }
        },
        'en-US': {
            currency: { currency: 'USD', ...numberCurrency }
        },
        'es-ES': {
            currency: { currency: 'EUR', ...numberCurrency }
        },
        'fr-FR': {
            currency: { currency: 'EUR', ...numberCurrency }
        },
        'de-DE': {
            currency: { currency: 'EUR', ...numberCurrency }
        },
        ru: {
            currency: { currency: 'RUB', ...numberCurrency }
        },
        'ch-CH': {
            currency: { currency: 'CNY', ...numberCurrency }
        },
        'ja-JP': {
            currency: { currency: 'JPY', ...numberCurrency }
        }
    },

    dates: {
        'en-EN': date,
        'en-US': date,
        'es-ES': date,
        'fr-FR': date,
        'de-DE': date,
        ru: date,
        'ch-CH': date,
        'ja-JP': date
    },

    dateFormats: [
        'yyyy-MM-dd HH:mm:ss',
        'yyyy-MM-dd',
        'HH:mm:ss'
    ],

    dateFormat: 'yyyy-MM-dd',

    timeFormat: 'hh:mm:ss',

    dateTimeFormat: 'yyyy-MM-dd HH:mm:ss',

    durationFormat: 'mm:ss',

    getBrowserLocale (options = {}) {
        const defaultOptions = { countryCodeOnly: false }
        const opt = { ...defaultOptions, ...options }
        const navigatorLocale =
            navigator.languages !== undefined
                ? navigator.languages[0]
                : navigator.language
        if (! navigatorLocale) {
            return undefined
        }
        return opt.countryCodeOnly
            ? navigatorLocale.trim().split(/[-_]/)[0]
            : navigatorLocale.trim()
    }
}

export default locales
