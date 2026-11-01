<script setup lang="ts">
import Skeleton from 'primevue/skeleton'
import { computed } from 'vue'
import AppCard from '@/Components/Core/AppCard.vue'
import { Formatter } from '@/objects/Formatter'

type Props = {
  label: string
  icon?: string
  /** A pulsing skeleton takes its place while it is undefined (the statistic is still loading). */
  value?: number
}

const props = defineProps<Props>()

const formatted = computed(() => Formatter.number(props.value))
</script>
<template>
  <AppCard :title="label" :icon="icon" data-stat-card>
    <Skeleton v-if="value === undefined" width="5rem" height="2.5rem" />
    <p v-else class="text-4xl font-semibold" v-text="formatted" />
  </AppCard>
</template>
