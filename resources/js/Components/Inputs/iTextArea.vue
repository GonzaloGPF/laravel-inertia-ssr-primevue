<script setup>
import useInput from '@/composables/useInput'
import { textProps } from '@/objects/Props'
import { ref, toRefs, watch } from 'vue'
import Textarea from 'primevue/textarea'
import InputLayout from '@/Layouts/InputLayout.vue'

const props = defineProps({
  ...textProps,
})

const inputElement = ref(null)
const { iValue, iName, reset } = useInput(toRefs(props))

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

const onClear = () => {
  emits('clear')
  reset()
}

const focus = () => {
  inputElement.value?.$el.focus()
}

watch(iValue, (value) => emits('update:modelValue', value))

defineExpose({
  reset,
  focus,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <Textarea
      ref="inputElement"
      v-model="iValue"
      :name="iName"
      :loading="loading"
      :filled="filled"
      :disabled="disabled"
      :placeholder="placeholder"
      :invalid="!!error"
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
      @click:append="
        clearable ? $emit('clear', $event) : $emit('append', $event)
      "
      @click:prepend="$emit('prepend', $event)"
      @click:clear="onClear"
    />
  </InputLayout>
</template>
