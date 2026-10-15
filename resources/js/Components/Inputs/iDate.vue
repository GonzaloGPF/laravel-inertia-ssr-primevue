<script setup lang="ts">
import useInput from '@/composables/useInput'
import { computed, toRefs, watch } from 'vue'
import DatePicker from 'primevue/datepicker'
import InputLayout from '@/Layouts/InputLayout.vue'
import { DateInputProps } from '@/types/input'
import { Time } from '@/objects/Time'

// The date picker has its own date format syntax, which is not the one of date-fns
const DATE_PICKER_FORMAT = 'yy-mm-dd'

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
      :date-format="DATE_PICKER_FORMAT"
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
