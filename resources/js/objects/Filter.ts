import type { FilterField, FilterFormValues, FilterValue } from '@/types/filter'
import type { QueryParams } from '@/types/input'
import type { SearchValues } from '@/objects/QueryString'
import { omit } from 'lodash-es'
import { Utils } from '@/objects/Utils'

/** A visible filter field, ready to be rendered. */
export type FilterInput = Omit<FilterField, 'visible' | 'value' | 'params'> & {
  value?: FilterValue
  params?: QueryParams
}

export const Filter = {
  /**
   * Returns the inputs to render of the given fields: only the visible ones.
   */
  processFields(fields: FilterField[] = []): FilterInput[] {
    return fields.filter(Filter.isVisible).map(Filter.toInput)
  },

  isVisible(field: FilterField): boolean {
    return typeof field.visible === 'function' ? field.visible() : true
  },

  toInput(field: FilterField): FilterInput {
    return {
      ...omit(field, ['visible', 'value', 'params']),
      value: field.value,
      params:
        typeof field.params === 'function' ? field.params() : field.params,
    }
  },

  /**
   * Names of the url params an input is sent in. A range input has a min and a max.
   */
  keys(input: FilterInput): string[] {
    return input.range
      ? [`min_${input.name}`, `max_${input.name}`]
      : [input.name]
  },

  /**
   * Builds the form values from the url search. The preset values of the inputs
   * are used for the params the url doesn't have.
   */
  toForm(
    inputs: FilterInput[],
    search: SearchValues,
    usePresets = true
  ): FilterFormValues {
    const form: FilterFormValues = {}

    inputs.forEach((input) => {
      Filter.keys(input).forEach((key) => {
        const parsed = parseValue(input, search[key])

        form[key] =
          parsed === undefined && usePresets && !input.range
            ? toPreset(input)
            : parsed
      })
    })

    return form
  },

  /**
   * The value to give to the component of an input. A range input receives [min, max].
   */
  toModelValue(input: FilterInput, form: FilterFormValues): FilterValue {
    if (input.range) {
      const [min, max] = Filter.keys(input).map((key) => form[key])

      // The inputs of a range don't accept an empty pair
      return Utils.isEmptyValue(min as string) &&
        Utils.isEmptyValue(max as string)
        ? undefined
        : ([min, max] as FilterValue)
    }

    return form[input.name]
  },

  /**
   * Returns the form values with the new value of an input.
   */
  setValue(
    input: FilterInput,
    form: FilterFormValues,
    value: FilterValue
  ): FilterFormValues {
    if (input.range) {
      const [min, max] = Filter.keys(input)
      const [minValue, maxValue] = Array.isArray(value) ? value : []

      return { ...form, [min]: minValue, [max]: maxValue }
    }

    if (input.input === 'check' && !value) {
      return { ...form, [input.name]: undefined }
    }

    return { ...form, [input.name]: value }
  },

  /**
   * Values of the form as they are written in the url: the empty ones are left out.
   */
  serialize(inputs: FilterInput[], form: FilterFormValues): SearchValues {
    const search: SearchValues = {}

    inputs.flatMap(Filter.keys).forEach((key) => {
      const value = serializeValue(form[key])

      if (value !== undefined) {
        search[key] = value
      }
    })

    return search
  },
}

function toPreset(input: FilterInput): FilterValue {
  if (Utils.isEmptyValue(input.value as string)) {
    return undefined
  }

  return input.multiple ? [input.value as string | number] : input.value
}

function parseValue(input: FilterInput, raw?: string | string[]): FilterValue {
  if (raw === undefined) {
    return undefined
  }

  if (input.multiple || Array.isArray(raw)) {
    return [raw].flat()
  }

  switch (input.input) {
    case 'boolean':
      return Number(raw) ? 1 : 0
    case 'check':
      return raw === '1'
    case 'number':
    case 'number_range':
      return Number(raw)
    case 'date':
      return parseDate(raw)
    default:
      return raw
  }
}

function serializeValue(value: FilterValue): string | string[] | undefined {
  if (Utils.isEmptyValue(value as string)) {
    return undefined
  }

  if (Array.isArray(value)) {
    return value.map(String)
  }

  if (value instanceof Date) {
    return formatDate(value)
  }

  if (typeof value === 'boolean') {
    return value ? '1' : '0'
  }

  return String(value)
}

/** Dates travel as `YYYY-MM-DD`, in the local time zone of the user. */
function formatDate(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${date.getFullYear()}-${month}-${day}`
}

function parseDate(value: string): Date | undefined {
  const [year, month, day] = value.split('-').map(Number)
  const date = new Date(year, month - 1, day)

  return Number.isNaN(date.getTime()) ? undefined : date
}
