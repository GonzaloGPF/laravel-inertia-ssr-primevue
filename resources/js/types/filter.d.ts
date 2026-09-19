import { OptionItem, QueryParams } from '@/types/input'

export type FilterFieldInput = 'text'
  | 'number'
  | 'boolean'
  | 'select'
  | 'autocomplete'
  | 'date'
  | 'range'

export type FilterField<T = OptionItem> = {
  input?: FilterFieldInput
  name?: string
  label?: string
  options?: T[]
  src?: string
  model?: string
  urlAttribute?: keyof T
  type?: string
  multiple?: boolean
  min?: string | number
  max?: string | number
  range?: boolean
  filter?: string
  params?: QueryParams | (() => QueryParams)
}

type CustomFilter = (item: OptionItem, search?: string) => boolean