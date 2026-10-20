<script setup lang="ts">
import AppButtons from '@/Components/Core/AppButtons.vue'
import { ActionName } from '@/types/actions'

type Props = {
  title?: string
  color?: string
  elevation?: string
  actions?: ActionName[]
  prependActions?: ActionName[]
  /** The buttons of the actions have just the icon, with the text as their title. */
  iconOnly?: boolean
  loading?: boolean
  disabled?: boolean
}

defineEmits(['click'])
withDefaults(defineProps<Props>(), { iconOnly: true })
</script>
<template>
  <div class="flex items-center gap-2">
    <AppButtons
      v-if="prependActions?.length"
      :actions="prependActions"
      :loading="loading"
      :disabled="disabled"
      :icon-only="iconOnly"
      size="small"
      severity="secondary"
      text
      @click="$emit('click', $event)"
    />
    <div class="flex-1" v-text="title" />
    <slot />
    <AppButtons
      v-if="actions?.length"
      :actions="actions"
      :loading="loading"
      :disabled="disabled"
      :icon-only="iconOnly"
      size="small"
      @click="$emit('click', $event)"
    />
  </div>
</template>
