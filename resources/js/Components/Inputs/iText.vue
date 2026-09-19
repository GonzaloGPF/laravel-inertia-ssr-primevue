<script setup lang="ts">
import useInput from '@/composables/useInput'
import { toRefs, useTemplateRef, watch } from 'vue'
import InputText from 'primevue/inputtext'
import InputLayout from '@/Layouts/InputLayout.vue'
import { TextInputProps } from '@/types/input'

const props = defineProps<TextInputProps>()

const inputElement = useTemplateRef<HTMLInputElement>('inputElement')
const { iValue, iLabel, iName, reset } = useInput<string>(toRefs(props))

const emits = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'mousedown',
  'mouseup',
  'mouseover',
  'mouseenter',
  'mouseleave',
  'clear',
  'append',
  'prepend',
  'keyup',
])

// const onClear = () => {
//   emits('clear')
//   reset()
// }

const focus = () => {
  inputElement.value?.focus()
}

watch(iValue, (value) => emits('update:modelValue', value))

defineExpose({
  reset,
  focus,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <InputText
      :id="id"
      ref="inputElement"
      v-model="iValue"
      :label="iLabel"
      :name="iName"
      :type="type"
      :disabled="disabled || loading"
      :placeholder="iLabel"
      :filled="filled"
      :invalid="!!error"
      :fluid="fluid"
      :size="size"
      :autocomplete="autocomplete ? 'new-password' : undefined"
      :required="required"
      :class="{ required }"
      :readonly="readonly"
      :autofocus="autofocus"
      @keyup.enter="$emit('keyup', $event)"
      @focus="$emit('focus', $event)"
      @blur="$emit('blur', $event)"
      @mouseover.stop="$emit('mouseover', $event)"
      @mouseenter.stop="$emit('mouseenter', $event)"
      @mouseleave.stop="$emit('mouseleave', $event)"
      @mousedown="$emit('mousedown', $event)"
      @mouseup="$emit('mouseup', $event)"
    />
  </InputLayout>
</template>
