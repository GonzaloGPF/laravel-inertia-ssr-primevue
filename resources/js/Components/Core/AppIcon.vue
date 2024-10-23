<script setup lang="ts">
import design from '@/config/design'

defineProps({
  icon: {
    type: String,
    default: null,
  },
  size: {
    type: String,
    default: undefined,
    validator: design.validSize,
  },
  color: {
    type: String,
    default: null,
  },
})
</script>
<template>
  <span class="material-symbols-outlined">{{ icon }}</span>
</template>
