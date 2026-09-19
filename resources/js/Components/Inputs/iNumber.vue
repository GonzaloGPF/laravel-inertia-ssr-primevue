<script setup lang="ts">
import useInput from '@/composables/useInput'
import IText from '@/Components/Inputs/iText.vue'
import { toRefs, watch } from 'vue'
import { NumberInputProps } from '@/types/input'

const props = defineProps<NumberInputProps>()
const { iValue, reset } = useInput(toRefs(props))

const emits = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'mousedown',
  'mouseup',
  'clear',
  'append',
  'prepend',
])

watch(iValue, (value) => emits('update:modelValue', value))

defineExpose({
  reset,
})
</script>
<template>
  <i-text
    ref="v-input"
    v-model="iValue"
    v-bind="$props"
    :min="min"
    :max="max"
    type="number"
    @append="$emit('append')"
    @prepend="$emit('prepend')"
    @clear="$emit('clear')"
    @focus="$emit('focus')"
    @blur="$emit('blur')"
    @mousedown="$emit('mousedown')"
    @mouseup="$emit('mouseup')"
  />
</template>
