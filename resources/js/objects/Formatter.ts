import pluralize from 'pluralize'
import { camelCase, capitalize as lodashCapitalize, snakeCase, upperFirst } from 'lodash-es'
import { locales } from '@/config/locales'
import { Time } from '@/objects/Time'
import { Translator } from '@/objects/Translator'
import type { InputValue } from '@/types/input'
import type { FormatDurationOptions } from 'date-fns'

export const Formatter = {
  ucFirst: (value?: InputValue): string => {
    if (value == null || value === '') return ''

    const text = String(value)

    return text.charAt(0).toUpperCase() + text.slice(1)
  },

  plural: (value?: string): string => pluralize(value ?? '', 2),

  singular: (value?: string): string => pluralize(value ?? '', 1),

  snakeCase: (value?: string): string => snakeCase(value ?? ''),

  camelCase: (value?: string): string => camelCase(value ?? ''),

  studly: (value?: string): string =>
    upperFirst(camelCase(value ?? '')),

  title: (value?: string): string =>
    (value ?? '')
      .split(' ')
      .map((word) => lodashCapitalize(word))
      .join(' '), // lodash.startCase

  model(value?: string): string {
    if (!value) {
      return ''
    }

    if (!value.startsWith('App\\Models\\')) {
      return `App\\Models\\${Formatter.studly(value)}`
    }

    return value
  },

  capitalize: (value?: InputValue): string => {
    if (value == null) return ''

    const text = String(value)

    if (!text) return ''

    return text.charAt(0).toUpperCase() + text.slice(1)
  },

  boolean: (value?: string | boolean | unknown[]): string => {
    if (Array.isArray(value)) {
      value = value.length !== 0
    }

    return value ? '&#10004;' : '&#10008;'
  },

  text: (text?: string, charsLimit = 0): string => {
    if (!charsLimit || typeof charsLimit === 'object') {
      charsLimit = 60
    }

    if (!text) return ''

    if (text.length <= charsLimit) return text

    return text.substring(0, charsLimit) + '...'
  },

  date: (value?: InputValue, humanize = false, format?: string): string => {
    if (!value) {
      return '-'
    }

    const dateFormat = format || locales.dateFormat

    if (typeof humanize === 'object') {
      humanize = false
    }

    return Time.format(value, humanize, dateFormat) ?? '-'
  },

  formatDiff: (initialDate: InputValue, endDate: InputValue): string => {
    const formatted = Formatter.duration(initialDate, endDate, {
      format: ['years', 'months'],
      locale: Translator.getDateLocale(),
    } satisfies FormatDurationOptions)

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
  dateTimeFormat: (value?: InputValue): string =>
    Formatter.date(value, false, locales.dateTimeFormat),

  /**
   * Returns a String date
   *
   * @param {string} value
   * @returns {string}
   */
  timeFormat: (value?: InputValue): string =>
    Formatter.date(value, false, locales.timeFormat),

  /**
   * Returns a String date
   *
   * @returns {string}
   * @param seconds
   */
  passedTime: (seconds?: InputValue): string => {
    const now = Time.now()
    const targetDate = Time.addSeconds(now, toNumber(seconds))
    const diff = targetDate.getTime() - now.getTime()

    return Formatter.date(new Date(diff), false, locales.durationFormat)
    // const duration = Time.getDuration(seconds);
    // return `${duration.minutes}:${duration.seconds}`;
  },

  duration: (
    initialDate: InputValue,
    endDate: InputValue,
    options?: FormatDurationOptions
  ): string => {
    const duration = Time.getDuration(initialDate, endDate)

    return Time.formatDuration(duration, options)
  },

  twoDecimals: (value: InputValue): string => {
    const number = toNumber(value).toString()
    const match = number.match(/^-?\d+(?:\.\d{0,2})?/)

    return match ? match[0] : '0' // truncate number with two decimals without rounding
  },

  percent: (value?: InputValue): string => {
    value = value || 0

    return `${value}%`
  },

  money: (value?: InputValue): string => {
    value = toNumber(value)

    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR',
      trailingZeroDisplay: 'stripIfInteger',
    }).format(value)

    // return Translator.n(value, 'currency', 'en-US'); // Formatter.languageToLocale()
  },

  items: (value: string | string[] | null | undefined): string => {
    if (!value) {
      value = []
    }

    if (!Array.isArray(value)) {
      value = [value]
    }

    return value.join(', ')
  },
}

function toNumber(value?: InputValue): number {
  if (!value) return 0

  if (Array.isArray(value)) {
    value = value[0] as InputValue
    if (value == null) return 0
  }

  if (typeof value === 'number') {
    return isNaN(value) ? 0 : value
  }

  if (typeof value === 'string') {
    const parsed = parseFloat(value)
    return isNaN(parsed) ? 0 : parsed
  }

  return 0
}
