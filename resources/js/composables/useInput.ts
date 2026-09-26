import { computed, ref, watch, type ComputedRef, type Ref } from 'vue'
import { Translator } from '@/objects/Translator'
import { Formatter } from '@/objects/Formatter'
import { isEqual } from 'lodash-es'
import type { InputValue } from '@/types/input'

type MaybeRef<T> = Ref<T> | ComputedRef<T>

type UseInputPropRefs = {
  name: MaybeRef<string | undefined>
  modelValue: MaybeRef<InputValue>
  label?: MaybeRef<string | null | undefined | (() => string)>
  multiple?: MaybeRef<boolean | undefined>
}

export default function useInput<T extends InputValue>(
  propRefs: UseInputPropRefs,
  valueParser?: (value: InputValue) => InputValue
) {
  const name = propRefs.name
  const modelValue = propRefs.modelValue
  const label = propRefs.label
  const multiple = propRefs.multiple
  let initialized = false

  const iValue = ref<T>()

  const iName = computed(() => name.value)

  const iLabel = computed<string>(() => {
    const labelValue = label?.value

    if (labelValue === null) {
      return ''
    }

    if (labelValue) {
      return typeof labelValue === 'function' ? labelValue() : labelValue
    }

    if (Translator.te(`validation.attributes.${name.value}`)) {
      return Translator.ta(name.value)
    }

    return Formatter.ucFirst(name.value)
  })

  const setValue = (value: InputValue, force = false): void => {
    let newValue: InputValue

    if (valueParser) {
      value = valueParser(value)
    } else if (!value) {
      value = multiple?.value ? [] : undefined
    }

    if (multiple?.value) {
      const items = Array.isArray(value) ? value : []
      newValue = items.map((v) => getValue(v)) as InputValue[]
    } else {
      newValue = getValue(value) as InputValue
    }

    if (!force && isEqual(newValue, modelValue.value)) {
      return
    }

    iValue.value = newValue as T
  }

  const reset = (): void => {
    iValue.value = undefined
  }

  watch(
    modelValue,
    (newValue) => {
      setValue(newValue, !initialized)
      initialized = true
    },
    { immediate: true, deep: true }
  )

  return {
    iLabel,
    iName,
    iValue,
    setValue,
    reset,
  }
}

function getValue(value: InputValue): InputValue {
  if (typeof value === 'string' && !isNaN(parseInt(value))) {
    return value
  }

  return value
}
