<script setup lang="ts">
import { Formatter } from '@/objects/Formatter'
import { InputValue } from '@/types/input'

defineProps<{ clean: boolean; value: InputValue }>()
</script>
<template>
  <span
    :class="clean ? null : ['border', 'rounded bg-gray-500']"
    class="whitespace-no-wrap px-1 text-white"
    v-text="Formatter.money(value)"
  />
</template>
