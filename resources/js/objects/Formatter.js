import pluralize from 'pluralize'
import locales from '@/config/locales'
import lodash from 'lodash'
import Time from '@/objects/Time'
import Translator from '@/objects/Translator.js'

const Formatter = {
  /**
   *
   * @param {string} string
   * @returns {string}
   */
  ucFirst: (string) => {
    if (!string) return ''

    string = string.toString()

    return string.charAt(0).toUpperCase() + string.slice(1)
  },

  plural: (value) => pluralize(value, 2),

  singular: (value) => pluralize(value, 1),

  snakeCase: (value) => lodash.snakeCase(value),

  camelCase: (value) => lodash.camelCase(value),

  studly: (value) => lodash.upperFirst(lodash.camelCase(value)),

  title: (value) => value.split(' ').map(lodash.capitalize).join(' '), // lodash.startCase

  model(value) {
    if (!value) {
      return ''
    }

    if (!value.startsWith('App\\Models\\')) {
      return `App\\Models\\${Formatter.studly(value)}`
    }

    return value
  },

  capitalize: (string) => {
    if (typeof string !== 'string') return ''

    return string.charAt(0).toUpperCase() + string.slice(1)
  },

  /**
   * Formats a value to show 'Yes' or 'No'
   *
   * @param value
   * @returns {string}
   */
  boolean: (value) => {
    if (Array.isArray(value)) {
      value = value.length !== 0
    }

    return value ? '&#10004;' : '&#10008;'
  },

  /**
   * @param {string} text
   * @param {number|null} charsLimit
   * @returns {string|*}
   */
  text: (text, charsLimit = null) => {
    if (!charsLimit || typeof charsLimit === 'object') {
      charsLimit = 60
    }

    if (!text) return ''

    if (text.length <= charsLimit) return text

    return text.substring(0, charsLimit) + '...'
  },

  /**
   * @param {Date|string|number|null} value
   * @param {boolean} humanize
   * @param {string|null} format
   * @returns {string}
   */
  date: (value = null, humanize = false, format = null) => {
    format = format || locales.dateFormat

    if (typeof humanize === 'object') {
      humanize = false
    }

    return Time.format(value, humanize, format)
  },

  formatDiff: (initialDate, endDate) => {
    const formatted = Formatter.duration(initialDate, endDate, {
      format: ['years', 'months'],
      locale: Translator.getDateLocale(),
    })
    initialDate = Formatter.date(initialDate)
    endDate = Formatter.date(endDate)

    return `${initialDate} - ${endDate} ( ${formatted} )`
  },

  /**
   * Returns a String date
   *
   * @param {string} value
   * @returns {string}
   */
  dateTimeFormat: (value) =>
    Formatter.date(value, false, locales.dateTimeFormat),

  /**
   * Returns a String date
   *
   * @param {string} value
   * @returns {string}
   */
  timeFormat: (value) => Formatter.date(value, false, locales.timeFormat),

  /**
   * Returns a String date
   *
   * @returns {string}
   * @param seconds
   */
  passedTime: (seconds) => {
    if (!seconds) {
      seconds = 0
    }

    const now = Time.now(0)

    const targetDate = Time.addSeconds(now, seconds)

    const diff = targetDate.getTime() - now.getTime()

    return Formatter.date(new Date(diff), false, locales.durationFormat)
    // const duration = Time.getDuration(seconds);
    // return `${duration.minutes}:${duration.seconds}`;
  },

  duration: (initialDate, endDate, options) => {
    const duration = Time.getDuration(initialDate, endDate)

    return Time.formatDuration(duration, options)
  },

  /**
   * Only two decimals without rounding. Using .toFixed(2) is not allowed
   * @param value
   * @returns {string}
   */
  twoDecimals: (value) => {
    if (isNaN(value) || !value) return '0'

    // parseFloat(value).toFixed(2) + '€';

    return value.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0] // truncate number with two decimals without rounding
  },

  percent: (value) => {
    value = value || 0

    return `${value}%`
  },

  money: (value) => {
    if (isNaN(value) || !value) {
      value = 0
    }

    value = parseFloat(Formatter.twoDecimals(value))

    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR',
      trailingZeroDisplay: 'stripIfInteger',
    }).format(value)

    // return Translator.n(value, 'currency', 'en-US'); // Formatter.languageToLocale()
  },

  /**
   *
   * @param value
   * @returns {string}
   */
  items: (value) => {
    if (!value) {
      value = []
    }

    if (!Array.isArray(value)) {
      value = [value]
    }

    return value.join(', ')
  },
}

export default Formatter
