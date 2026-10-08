<script setup lang="ts">
import Chip, { type ChipProps } from 'primevue/chip'

defineEmits(['close'])
defineProps<ChipProps>()
</script>
<template>
  <Chip
    v-if="label || $slots.default"
    v-bind="$props"
    @remove="$emit('close')"
  />
</template>
