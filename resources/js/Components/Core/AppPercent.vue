<script setup>
import { computed } from 'vue'
import Formatter from '@/objects/Formatter'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    required: true,
  },
  ceil: {
    type: Boolean,
    default: false,
  },
})
const value = computed(() => {
  if (!props.modelValue) {
    return 0
  }

  if (props.ceil) {
    return Math.ceil(props.modelValue)
  }

  return Formatter.twoDecimals(props.modelValue) + '%'
})
</script>
<template>
  <span v-text="value" />
</template>
