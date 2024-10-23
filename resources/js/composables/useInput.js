import { computed, ref, watch } from 'vue'
import Translator from '@/objects/Translator'
import Formatter from '@/objects/Formatter'
import lodash from 'lodash'

export default (propRefs, valueParser) => {
  const name = propRefs.name
  const modelValue = propRefs.modelValue
  const label = propRefs.label
  const multiple = propRefs.multiple
  let initialized = false

  const iValue = ref()

  const iName = computed(
    () => name.value + '_' + Math.random().toString(36).substring(2)
  )

  const iLabel = computed(() => {
    const labelValue = label?.value

    if (labelValue === null) {
      return null
    }

    if (labelValue) {
      return typeof labelValue === 'function' ? labelValue() : labelValue
    }

    if (Translator.te(`validation.attributes.${name.value}`)) {
      return Translator.ta(name.value)
    }

    return Formatter.ucFirst(name.value)
  })

  const setValue = (value, force) => {
    let newValue

    if (valueParser) {
      value = valueParser(value)
    } else if (!value) {
      value = multiple?.value ? [] : null
    }

    if (multiple?.value) {
      newValue = value.map((v) => getValue(v))
    } else {
      newValue = getValue(value)
    }

    if (!force && lodash.isEqual(newValue, modelValue.value)) {
      return
    }

    iValue.value = newValue
  }

  const reset = () => {
    iValue.value = null
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

function getValue(value) {
  if (!isNaN(parseInt(value))) {
    return value
  } else {
    return value
  }
}
