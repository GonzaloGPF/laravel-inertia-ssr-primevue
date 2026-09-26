<script setup lang="ts">
import useInput from '@/composables/useInput'
import IText from '@/Components/Inputs/iText.vue'
import { computed, ref, toRefs, watch } from 'vue'
import { omit } from 'lodash-es'
import { TextInputProps } from '@/types/input'

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
const props = defineProps<TextInputProps>()
const boundProps = computed(() => omit(props, 'modelValue'))

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
    v-bind="boundProps"
    :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
    :type="show ? 'text' : 'password'"
    @append="show = !show"
  />
</template>
