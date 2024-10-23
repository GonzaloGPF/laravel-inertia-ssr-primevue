<script setup>
import useInput from '@/composables/useInput'
import { ref, toRefs, watch } from 'vue'
import useSelect from '@/composables/useSelect'
import { baseProps } from '@/objects/Props'
import Utils from '@/objects/Utils'
import Select from 'primevue/select'
import InputLayout from '@/Layouts/InputLayout.vue'

const props = defineProps({
  ...baseProps,
  src: {
    type: String,
    default: null,
  },
  filter: {
    type: Function,
    default: null,
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  checkmark: {
    type: Boolean,
    default: false,
  },
  clearable: {
    type: Boolean,
    default: true,
  },
  options: {
    type: Array,
    default: null,
  },
  variant: {
    type: String,
    default: null,
    validator: (value) => ['solo', 'underlined'].includes(value),
  },
})
const search = ref(null)
const { iName, iValue, reset } = useInput(toRefs(props))
const { items, selectedItems, getItemTitle } = useSelect(
  iValue,
  props.src,
  props.multiple,
  search,
  props.filter,
  props.options
)

const emits = defineEmits(['update:modelValue', 'update:selected', 'clear'])

watch(iValue, (newValue, oldValue) => {
  if (Utils.areEquals(newValue, oldValue)) return

  emits('update:modelValue', iValue.value)
  emits('update:selected', selectedItems.value)
})

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <Select
      v-model="iValue"
      :options="items || []"
      :option-label="getItemTitle"
      :placeholder="placeholder"
      :checkmark="checkmark"
      :required="required"
      :class="{ required }"
      :show-clear="clearable"
      :name="iName"
      :disabled="disabled"
      :fluid="fluid"
      :option-value="(item) => item.id"
      :multiple="!!multiple"
      :filter="filter"
      @click:clear="reset"
    />
  </InputLayout>
</template>
