<script setup lang="ts">
import useInput from '@/composables/useInput'
import iText from '@/Components/Inputs/iText.vue'
import { computed, toRefs, watch } from 'vue'
import { omit } from 'lodash-es'
import { NumberInputProps } from '@/types/input'

const props = defineProps<NumberInputProps>()
const boundProps = computed(() => omit(props, 'modelValue'))
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
  <iText
    ref="v-input"
    v-model="iValue"
    v-bind="boundProps"
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
