import {
  addMonths,
  addSeconds,
  differenceInMonths,
  differenceInYears,
  format,
  formatDistanceToNow,
  FormatDistanceToNowOptions,
  formatDuration,
  intervalToDuration,
  isAfter,
  isValid,
  parse,
} from 'date-fns'
import type { Duration, Locale } from 'date-fns'
import { enGB, es, fr, pt, ru } from 'date-fns/locale'
import { toZonedTime } from 'date-fns-tz'
import { locales } from '@/config/locales'
import { InputValue } from '@/types/input'

type FormatDurationOptions = Parameters<typeof formatDuration>[1]
type FormatOptions = Parameters<typeof format>[2]

const dateLocales: Record<string, Locale> = {
  es,
  enGB,
  fr,
  pt,
  ru,
}

const dateLocale = (): Locale => dateLocales[locales.getLocale()] ?? enGB

export const Time = {
  isAfter(dateA: InputValue, dateB: InputValue): boolean {
    const parsedDateA = Time.parse(dateA)
    const parsedDateB = Time.parse(dateB)

    if (!parsedDateA || !parsedDateB) {
      return false
    }

    return isAfter(parsedDateA, parsedDateB)
  },

  addSeconds(value: InputValue, amount: number): Date {
    return addSeconds(Time.parse(value) ?? Time.now(), amount)
  },

  addMonths(value: InputValue, amount: number): Date {
    return addMonths(Time.parse(value) ?? Time.now(), amount)
  },

  differenceInMonths(dateA: InputValue, dateB: InputValue): number {
    const parsedDateA = Time.parse(dateA)
    const parsedDateB = Time.parse(dateB)

    if (!parsedDateA || !parsedDateB) {
      return 0
    }

    return differenceInMonths(parsedDateA, parsedDateB)
  },

  differenceInYears(dateA: InputValue, dateB: InputValue): number {
    const parsedDateA = Time.parse(dateA)
    const parsedDateB = Time.parse(dateB)

    if (!parsedDateA || !parsedDateB) {
      return 0
    }

    return differenceInYears(parsedDateA, parsedDateB)
  },

  getDuration(start: InputValue, end: InputValue): Duration {
    return intervalToDuration({
      start: Time.parse(start) ?? Time.now(),
      end: Time.parse(end) ?? Time.now(),
    })
  },

  formatDuration(duration: Duration, options?: FormatDurationOptions): string {
    return formatDuration(duration, options)
  },

  /**
   * Tries to create a Date object from given value
   */
  parse(value: InputValue): Date | undefined {
    if (!value) {
      return undefined
    }

    let parsedDate = new Date(String(value))

    if (!isValid(parsedDate)) {
      // it assumes given date is in UTC
      parsedDate =
        locales.dateFormats
          .map((dateFormat: string) =>
            parse(String(value), dateFormat, Time.now())
          )
          .find((date: Date) => isValid(date)) ?? parsedDate
    }

    if (!isValid(parsedDate)) {
      return undefined
    }

    return toZonedTime(parsedDate, locales.getTimezone())
  },

  now(args = null) {
    if (args) {
      return new Date(args)
    }

    return new Date()
  },

  format(
    value: InputValue,
    humanize = false,
    customFormat?: string
  ): string | undefined {
    const parsedDate = Time.parse(value)

    if (!parsedDate) {
      return undefined
    }

    const formatOptions: FormatOptions = {
      locale: dateLocale(),
    }

    const distanceOptions: FormatDistanceToNowOptions = {
      locale: dateLocale(),
      includeSeconds: true,
    }

    return humanize
      ? formatDistanceToNow(parsedDate, distanceOptions)
      : format(parsedDate, customFormat || locales.dateFormat, formatOptions)
  },

  /**
   * Formats a month given as year and month (2026-11). It is a month of the calendar, not an instant, so
   * it is not moved to the time zone of the user, as parse() does: it would be the previous month for
   * the time zones behind UTC.
   */
  formatMonth(month: InputValue, customFormat?: string): string | undefined {
    const parsedMonth = parse(String(month), 'yyyy-MM', Time.now())

    if (!isValid(parsedMonth)) {
      return undefined
    }

    return format(parsedMonth, customFormat || locales.monthFormat, {
      locale: dateLocale(),
    })
  },
}
