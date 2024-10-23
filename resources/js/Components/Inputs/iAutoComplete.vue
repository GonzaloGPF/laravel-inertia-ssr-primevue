<script setup>
import { baseProps } from '@/objects/Props'
import useAutoComplete from '@/composables/useAutoComplete'
import AutoComplete from 'primevue/autocomplete'
import useInput from '@/composables/useInput'
import { ref, toRefs, watch } from 'vue'
import InputLayout from '@/Layouts/InputLayout.vue'

const props = defineProps({
  ...baseProps,
  model: {
    type: String,
    required: true,
    default: null,
  },
  filter: {
    type: Function,
    default: () => true,
  },
  multiple: {
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
  urlAttribute: {
    type: String,
    default: 'name',
  },
  params: {
    type: Object,
    default: null,
  },
})
const emits = defineEmits(['update:modelValue', 'update:selected', 'clear'])
const search = ref(null)
const { iLabel, iName, iValue, reset } = useInput(toRefs(props))
const { loading, items, selectedItems } = useAutoComplete(
  iValue,
  props.model,
  props.multiple,
  search,
  props.params,
  props.urlAttribute
)

watch(iValue, (value) => {
  emits('update:modelValue', value)
  emits('update:selected', selectedItems.value)
})
defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <AutoComplete
      v-model="iValue"
      :suggestions="items"
      :option-label="(item) => item[urlAttribute] || item.label || item.name"
      :loading="loading"
      :title="iLabel"
      :placeholder="placeholder"
      :multiple="multiple"
      :disabled="disabled"
      :fluid="fluid"
      :invalid="!!error"
      :required="required"
      :class="{ required }"
      :name="iName"
      :show-clear="clearable"
    />
  </InputLayout>
</template>
