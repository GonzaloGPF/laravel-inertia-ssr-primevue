import { OptionItem, QueryParams } from '@/types/input'

export type FilterFieldInput =
  | 'text'
  | 'select'
  | 'auto'
  | 'boolean'
  | 'check'
  | 'number'
  | 'number_range'
  | 'date'

/** A value of the filters form. Lists (multiple filters) are arrays. */
export type FilterValue =
  string | number | boolean | Date | Array<string | number> | null | undefined

/** Values of the filters form, by the name of the url param they are sent in. */
export type FilterFormValues = Record<string, FilterValue>

export type FilterField<T = OptionItem> = {
  /** Name of the filter, as the backend filter expects it. */
  name: string
  /** Preset value of the filter. */
  value?: string | number
  label?: string | (() => string)
  /** Input component to render. By default, `text`. */
  input?: FilterFieldInput
  options?: T[]
  src?: string
  model?: string
  urlAttribute?: keyof T
  type?: string
  multiple?: boolean
  min?: string | number
  max?: string | number
  /** Renders a range, filtering by the `min_{name}` and `max_{name}` params. */
  range?: boolean
  filter?: string
  params?: QueryParams | (() => QueryParams)
  /** Shows or hides the input. */
  visible?: () => boolean
}

type CustomFilter = (item: OptionItem, search?: string) => boolean
