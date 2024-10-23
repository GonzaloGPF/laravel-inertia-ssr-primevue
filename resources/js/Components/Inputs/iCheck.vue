<script setup>
import Checkbox from 'primevue/checkbox'
import useInput from '@/composables/useInput'
import { baseProps } from '@/objects/Props'
import { toRefs } from 'vue'
import InputLayout from '@/Layouts/InputLayout.vue'

const props = defineProps({
  ...baseProps,
  indeterminate: {
    type: Boolean,
    default: false,
  },
  binary: {
    type: Boolean,
    default: true,
  },
})
const { iLabel, iName, iValue, reset } = useInput(toRefs(props))
defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props" is-checkbox>
    <Checkbox
      v-model="iValue"
      :name="iName"
      :title="iLabel"
      :disabled="disabled"
      :invalid="!!error"
      :required="required"
      :binary="binary"
      :class="{ required }"
      :indeterminate="indeterminate"
    />
  </InputLayout>
</template>
