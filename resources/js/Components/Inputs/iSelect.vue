<script setup lang="ts" generic="T extends OptionItem">
import useInput from '@/composables/useInput'
import { ref, toRefs, watch } from 'vue'
import useSelect from '@/composables/useSelect'
import { Utils } from '@/objects/Utils'
import Select from 'primevue/select'
import InputLayout from '@/Layouts/InputLayout.vue'
import {
  OptionItem,
  SelectInputProps,
} from '@/types/input'

const props = defineProps<SelectInputProps<T>>()
const search = ref('')
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
  if (Utils.isEquals(newValue, oldValue)) return

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
      :filter="!!filter"
      @click:clear="reset"
    />
  </InputLayout>
</template>
