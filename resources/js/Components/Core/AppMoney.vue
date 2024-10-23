<script setup>
import Formatter from '@/objects/Formatter'

defineProps({
  modelValue: {
    type: [Number, String],
    default: 0,
  },
  clean: {
    type: Boolean,
    default: false,
  },
})
</script>
<template>
  <span
    :class="clean ? null : ['border', 'rounded bg-gray-500']"
    class="whitespace-no-wrap px-1 text-white"
    v-text="Formatter.money(modelValue)"
  />
</template>
