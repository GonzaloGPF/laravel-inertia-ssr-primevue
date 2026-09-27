<script setup lang="ts" generic="T extends OptionItem">
import useAutoComplete from '@/composables/useAutoComplete'
import AutoComplete from 'primevue/autocomplete'
import useInput from '@/composables/useInput'
import { ref, toRefs, watch } from 'vue'
import InputLayout from '@/Layouts/InputLayout.vue'
import { AutoCompleteInputProps, OptionItem } from '@/types/input'

const props = defineProps<AutoCompleteInputProps<T>>()
const emits = defineEmits(['update:modelValue', 'update:selected', 'clear'])
const search = ref('')
const { model } = toRefs(props)
const { iLabel, iName, iValue, reset } = useInput(toRefs(props))
const options = ref({
  multiple: props.multiple,
  params: props.params,
  urlAttribute: props.urlAttribute,
})
const { loading, items, selectedItems } = useAutoComplete(
  iValue,
  model,
  search,
  options
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
      :option-label="
        (item) => item[String(urlAttribute)] || item.label || item.name
      "
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
