<script setup lang="ts">
import { InputValue } from '@/types/input'

defineProps<{ value?: InputValue }>()
</script>
<template>
  <div v-html="value" />
</template>
