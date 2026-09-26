<script setup lang="ts">
import { DropdownProps } from 'primevue'

defineProps<Omit<DropdownProps, 'modelValue'>>()

const value = defineModel<string>()
</script>
<template>
  <Dropdown
    v-model="value"
    v-bind="$props"
  />
</template>