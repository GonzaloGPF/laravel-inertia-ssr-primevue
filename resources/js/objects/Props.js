import design from '@/config/design.js'

export const baseProps = {
  id: {
    type: String,
    default: null,
  },
  name: {
    type: String,
    required: true,
    default: null,
  },
  modelValue: {
    type: [Number, String, Boolean, Array, File, Object],
    default: null,
  },
  label: {
    type: [String, Function],
    default: undefined,
  },
  required: {
    type: Boolean,
    default: null,
  },
  hint: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  color: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: null,
  },
  tooltip: {
    type: String,
    default: null,
  },
  prependIcon: {
    type: String,
    default: null,
  },
  appendIcon: {
    type: String,
    default: null,
  },
  rules: {
    type: Array,
    default: () => [],
  },
  autocomplete: {
    type: Boolean,
    default: false,
  },
  hideLabel: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  hideDetails: {
    type: Boolean,
    default: null,
  },
  error: {
    type: String,
    default: null,
  },
  title: {
    type: String,
    default: null,
  },
  fluid: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: null,
    validator: design.validSize,
  },
}

export const textProps = {
  ...baseProps,
  type: {
    type: String,
    default: null,
  },
  filled: {
    type: Boolean,
    default: null,
  },
  loading: {
    type: Boolean,
    default: null,
  },
}

export const numberProps = {
  ...textProps,
  min: {
    type: Number,
    default: null,
  },
  max: {
    type: Number,
    default: null,
  },
}
