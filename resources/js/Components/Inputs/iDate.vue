<script setup>
import useInput from '@/composables/useInput'
import { computed, toRefs, watch } from 'vue'
import { baseProps } from '@/objects/Props'
import DatePicker from 'primevue/datepicker'
import locales from '@/config/locales'
import InputLayout from '@/Layouts/InputLayout.vue'

const emits = defineEmits(['update:model-value'])
const props = defineProps({
  ...baseProps,
  range: {
    type: Boolean,
    default: false,
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  min: {
    type: String,
    default: null,
  },
  max: {
    type: String,
    default: null,
  },
  type: {
    type: String,
    default: 'date',
    validator: (value) => ['date', 'month', 'year'].includes(value),
  },
})
const { iLabel, iName, iValue } = useInput(toRefs(props))

const selectionMode = computed(() => {
  if (props.range) {
    return 'range'
  }
  if (props.multiple) {
    return 'multiple'
  }
  return 'single'
})

watch(iValue, (event) => {
  emits('update:model-value', event)
})

const reset = () => {
  if (props.multiple) {
    iValue.value = []
  } else {
    iValue.value = null
  }
}

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <DatePicker
      v-model="iValue"
      :name="iName"
      :title="iLabel"
      :date-format="locales.dateFormat"
      :selection-mode="selectionMode"
      :min-date="min"
      :max-date="max"
      :manual-input="false"
      :view="type"
      :invalid="!!error"
      :fluid="fluid"
      show-button-bar
    />
  </InputLayout>
</template>
