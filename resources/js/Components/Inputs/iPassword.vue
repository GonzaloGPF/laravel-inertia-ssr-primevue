<script setup>
import useInput from '@/composables/useInput'
import { textProps } from '@/objects/Props'
import IText from '@/Components/Inputs/iText.vue'
import { ref, toRefs, watch } from 'vue'

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
const props = defineProps({
  ...textProps,
})

const show = ref(false)

const { iValue, reset } = useInput(toRefs(props))

watch(iValue, (value) => emits('update:modelValue', value))

defineExpose({
  reset,
})
</script>
<template>
  <i-text
    v-model="iValue"
    v-bind="$props"
    :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
    :type="show ? 'text' : 'password'"
    @append="show = !show"
  />
</template>
