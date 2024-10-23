import {
  addMonths,
  addSeconds,
  differenceInMonths,
  differenceInYears,
  format,
  formatDistanceToNow,
  formatDuration,
  intervalToDuration,
  isAfter,
  isValid,
  parse,
} from 'date-fns'
import { enGB, es, fr, pt, ru } from 'date-fns/locale'
import locales from '@/config/locales'
import { toZonedTime } from 'date-fns-tz'

const dateLocales = {
  es,
  enGB,
  fr,
  pt,
  ru,
}

const Time = {
  isAfter(dateA, dateB) {
    dateA = Time.parse(dateA)
    dateB = Time.parse(dateB)

    return isAfter(dateA, dateB)
  },

  addSeconds(value, amount) {
    return addSeconds(value, amount)
  },

  addMonths(value, amount) {
    return addMonths(value, amount)
  },

  differenceInMonths(dateA, dateB) {
    dateA = Time.parse(dateA)
    dateB = Time.parse(dateB)

    if (!dateA || !dateB) {
      return 0
    }

    return differenceInMonths(dateA, dateB)
  },

  differenceInYears(dateA, dateB) {
    dateA = Time.parse(dateA)
    dateB = Time.parse(dateB)

    if (!dateA || !dateB) {
      return 0
    }

    return differenceInYears(dateA, dateB)
  },

  /**
   * @param start
   * @param end
   * @returns {Duration}
   */
  getDuration(start, end) {
    return intervalToDuration({
      start: Time.parse(start),
      end: Time.parse(end),
    })
  },

  /**
   *
   * @param {Duration} duration
   * @param options
   * @returns {string}
   */
  formatDuration(duration, options) {
    return formatDuration(duration, options)
  },

  /**
   * Tries to create a Date object from given value
   *
   * @param {Date|string} value
   * @returns {Date|null}
   */
  parse(value) {
    if (!value) {
      return null
    }

    let parsedDate = new Date(value)

    if (!isValid(parsedDate)) {
      // it assumes given date is in UTC
      parsedDate = locales.dateFormats
        .map((format) => parse(value, format, Time.now()))
        .filter((date) => isValid(date))
        .shift()
    }

    if (!isValid(parsedDate)) {
      return null
    }

    return toZonedTime(new Date(value), locales.getTimezone())
  },

  /**
   * @returns {Date}
   */
  now(args = null) {
    if (args) {
      return new Date(args)
    } else {
      return new Date()
    }
  },

  /**
   * Returns a string with formatted date
   *
   * @param {Date|string} value
   * @param humanize
   * @param customFormat
   * @returns {string|null}
   */
  format(value, humanize = false, customFormat = null) {
    const parsedDate = Time.parse(value)

    if (!parsedDate) {
      return null
    }

    const options = {
      locale: dateLocales[locales.getLocale()],
      includeSeconds: true,
    }

    return humanize
      ? formatDistanceToNow(parsedDate, options)
      : format(parsedDate, customFormat || locales.dateFormat, options)
  },
}

export default Time
