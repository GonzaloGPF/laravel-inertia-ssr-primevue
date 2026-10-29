<script setup lang="ts">
import Card from 'primevue/card'
import { computed } from 'vue'
import AppIcon from '@/Components/Core/AppIcon.vue'
import type { Severity } from '@/types/design'

type Props = {
  title?: string
  description?: string
  icon?: string
  /** Colors the outline and the title of the card. Without it, the card is the plain one. */
  severity?: Severity
}

type CardStyle = {
  card: string
  title: string
}

// Complete class names, so Tailwind can find them
const SEVERITY_STYLES: Record<Severity, CardStyle> = {
  secondary: {
    card: 'border border-surface-400',
    title: 'text-surface-600 dark:text-surface-300',
  },
  success: {
    card: 'border border-green-500',
    title: 'text-green-600 dark:text-green-400',
  },
  info: {
    card: 'border border-sky-500',
    title: 'text-sky-600 dark:text-sky-400',
  },
  warn: {
    card: 'border border-orange-500',
    title: 'text-orange-600 dark:text-orange-400',
  },
  help: {
    card: 'border border-purple-500',
    title: 'text-purple-600 dark:text-purple-400',
  },
  danger: {
    card: 'border border-red-500',
    title: 'text-red-600 dark:text-red-400',
  },
  contrast: {
    card: 'border border-surface-900 dark:border-surface-0',
    title: 'text-surface-900 dark:text-surface-0',
  },
}

const props = defineProps<Props>()

const styles = computed(() =>
  props.severity ? SEVERITY_STYLES[props.severity] : undefined
)
</script>
<template>
  <Card :class="['[--p-card-body-gap:1rem]', styles?.card]">
    <template v-if="title || icon" #title>
      <div :class="['flex items-center gap-2', styles?.title]">
        <AppIcon v-if="icon" :icon="icon" />
        <span v-if="title" v-text="title" />
      </div>
    </template>
    <template v-if="description" #subtitle>{{ description }}</template>
    <template #content>
      <slot />
    </template>
    <template v-if="$slots.footer" #footer>
      <slot name="footer" />
    </template>
  </Card>
</template>
