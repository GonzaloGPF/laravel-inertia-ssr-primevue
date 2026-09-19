<script setup lang="ts">
import { DropdownProps } from 'primevue'

defineProps<DropdownProps>()

const value = defineModel<string>()
</script>
<template>
  <Dropdown
    v-model="value"
    v-bind="$props"
  />
</template>