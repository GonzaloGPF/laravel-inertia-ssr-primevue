<script setup lang="ts">
import { ChipProps } from 'primevue'

defineEmits(['close'])
defineProps<ChipProps>()
</script>
<template>
  <Chip v-bind="$props" @remove="$emit('close')" />
</template>
