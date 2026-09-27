import { CustomFilter } from '@/types/filter'

export type QueryParamValue =
  string | number | boolean | Array<string | number | boolean>
export type QueryParams = Record<string, QueryParamValue>
export type InputValueType = undefined | string | number | boolean | object
export type InputValue = InputValueType | Array<InputValueType>

export type FieldType =
  | null
  | 'chip'
  | 'text'
  | 'html'
  | 'boolean'
  | 'date'
  | 'time'
  | 'datetime'
  | 'money'
  | 'percent'
  | 'link'
  | 'email'

export type DatePickerType = 'date' | 'month' | 'year'

export type FileType =
  | {
      id: string
    }
  | number

export type OptionItem = {
  id?: string
  label?: string | ((item?: OptionItem) => string)
  name?: string
  value?: string | number
}

export type InputProps = {
  id?: string
  name?: string
  modelValue?: InputValue
  label?: string
  required?: boolean
  hint?: string
  disabled?: boolean
  autofocus?: boolean
  color?: string
  placeholder?: string
  tooltip?: string
  prependIcon?: string
  appendIcon?: string
  rules?: string
  autocomplete?: boolean | string
  hideLabel?: boolean
  readonly?: boolean
  hideDetails?: boolean
  error?: string
  title?: string
  fluid?: boolean
  clearable?: boolean
  loading?: boolean
  size?: string
}

export interface TextInputProps extends InputProps {
  type?: string
  filled?: boolean
}

export interface SelectInputProps<T extends OptionItem> extends InputProps {
  src: string
  filter?: CustomFilter
  multiple?: boolean
  checkmark?: boolean
  clearable?: boolean
  options?: T[]
  variant?: 'solo' | 'underlined'
}

export interface AutoCompleteInputProps<
  T extends OptionItem,
> extends SelectInputProps<T> {
  model?: string
  urlAttribute?: string
  params?: QueryParams
}

export interface BooleanInputProps extends InputProps {
  checkmark?: boolean
}

export interface CheckInputProps extends InputProps {
  indeterminate?: boolean
  binary?: boolean
}

export interface DateInputProps extends InputProps {
  range?: boolean
  multiple?: boolean
  min?: string
  max?: string
  type?: DatePickerType
}

export interface NumberInputProps extends InputProps {
  min?: number
  max?: number
}
