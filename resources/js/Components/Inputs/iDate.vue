<script setup lang="ts">
import useInput from '@/composables/useInput'
import { computed, toRefs, watch } from 'vue'
import DatePicker from 'primevue/datepicker'
import { locales } from '@/config/locales'
import InputLayout from '@/Layouts/InputLayout.vue'
import { DateInputProps } from '@/types/input'
import { Time } from '@/objects/Time'

const emits = defineEmits(['update:model-value'])
const props = withDefaults(defineProps<DateInputProps>(), {
  type: 'date',
})
const { iLabel, iName, iValue } = useInput<Date | Date[]>(toRefs(props))

const selectionMode = computed(() => {
  if (props.range) {
    return 'range'
  }
  if (props.multiple) {
    return 'multiple'
  }
  return 'single'
})

const reset = () => {
  if (props.multiple) {
    iValue.value = []
  } else {
    iValue.value = undefined
  }
}

watch(iValue, (event) => {
  emits('update:model-value', event)
})

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
      :min-date="Time.parse(min)"
      :max-date="Time.parse(max)"
      :manual-input="false"
      :view="type"
      :invalid="!!error"
      :fluid="fluid"
      show-button-bar
    />
  </InputLayout>
</template>
