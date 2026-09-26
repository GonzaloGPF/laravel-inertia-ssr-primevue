<script setup lang="ts">
import AppButtons from '@/Components/Core/AppButtons.vue'
import { ActionName } from '@/types/actions'

type Props = {
  title?: string
  color?: string
  elevation?: string
  actions?: ActionName[]
  prependActions?: ActionName[]
  loading?: boolean
  disabled?: boolean
}

defineEmits(['click'])
defineProps<Props>()
</script>
<template>
  <div density="compact" :color="color" :elevation="elevation" flat>
    <AppButtons
      :actions="prependActions"
      :loading="loading"
      :disabled="disabled"
      @click="$emit('click', $event)"
    />
    <div v-text="title" />
    <slot />
    <AppButtons
      :actions="actions"
      :loading="loading"
      :disabled="disabled"
      @click="$emit('click', $event)"
    />
  </div>
</template>
