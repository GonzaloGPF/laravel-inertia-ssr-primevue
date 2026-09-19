<script setup lang="ts">
import { Size } from '@/types/design'

type Props = {
  icon?: string
  size?: Size
  color?: string
}

defineProps<Props>()
</script>
<template>
  <span class="material-symbols-outlined">{{ icon }}</span>
</template>
