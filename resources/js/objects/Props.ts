import { InputValue } from '@/types/input'

export type PropOption<T> = {
  type?: unknown
  default?: T
  required?: boolean
  validator?: (value: unknown) => boolean
}

export type BaseProps = {
  id?: PropOption<string>
  name?: PropOption<string>
  modelValue?: PropOption<InputValue | File>
  label?: PropOption<string | ((...args: unknown[]) => unknown) | undefined>
  required?: PropOption<boolean>
  hint?: PropOption<string>
  disabled?: PropOption<boolean>
  autofocus?: PropOption<boolean>
  color?: PropOption<string>
  placeholder?: PropOption<string>
  tooltip?: PropOption<string>
  prependIcon?: PropOption<string>
  appendIcon?: PropOption<string>
  rules?: PropOption<unknown[]>
  autocomplete?: PropOption<boolean>
  hideLabel?: PropOption<boolean>
  readonly?: PropOption<boolean>
  hideDetails?: PropOption<boolean>
  error?: PropOption<string>
  title?: PropOption<string>
  fluid?: PropOption<boolean>
  size?: PropOption<string>
}

export type TextProps = BaseProps & {
  type?: PropOption<string>
  filled?: PropOption<boolean>
  loading?: PropOption<boolean>
}

export type NumberProps = TextProps & {
  min?: PropOption<number>
  max?: PropOption<number>
}
