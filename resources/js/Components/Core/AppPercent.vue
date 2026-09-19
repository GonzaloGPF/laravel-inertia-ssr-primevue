<script setup lang="ts">
import { computed } from 'vue'
import { Formatter } from '@/objects/Formatter'
import { InputValue } from '@/types/input'

const props = defineProps<{ ceil?: boolean, value?: InputValue }>()

const parsedValue = computed(() => {
  if (!props.value) {
    return 0
  }

  const val = Number(props.value);

  if (isNaN(val)) {
    return 0
  }

  if (props.ceil) {
    return Math.ceil(val)
  }

  return Formatter.twoDecimals(val) + '%'
})
</script>
<template>
  <span v-text="parsedValue" />
</template>
