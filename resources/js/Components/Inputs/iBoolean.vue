<script setup>
import useInput from '@/composables/useInput'
import { computed, toRefs, watch } from 'vue'
import Translator from '@/objects/Translator'
import Select from 'primevue/select'
import { baseProps } from '@/objects/Props'
import InputLayout from '@/Layouts/InputLayout.vue'

const props = defineProps({
  ...baseProps,
  checkmark: {
    type: Boolean,
    default: false,
  },
  clearable: {
    type: Boolean,
    default: false,
  },
})
const { iLabel, iName, iValue, reset } = useInput(toRefs(props), (value) => {
  return value === null ? null : value ? 1 : 0
})
const items = computed(() => [
  {
    id: 0,
    label: Translator.tl('no'),
  },
  {
    id: 1,
    label: Translator.tl('yes'),
  },
])

const emits = defineEmits([
  'update:modelValue',
  'input',
  'focus',
  'blur',
  'mousedown',
  'mouseup',
  'clear',
  'append',
])

watch(iValue, (value) => {
  emits('update:modelValue', value)
})

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <Select
      v-model="iValue"
      :title="iLabel"
      :options="items"
      :option-label="iLabel"
      :placeholder="placeholder"
      :checkmark="checkmark"
      :required="required"
      :class="{ required }"
      :show-clear="clearable"
      :name="iName"
      :disabled="disabled"
      :fluid="fluid"
      :option-value="(item) => item.id"
      @click:clear="reset"
    />
  </InputLayout>
</template>
