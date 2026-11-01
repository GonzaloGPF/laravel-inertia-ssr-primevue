<script setup lang="ts">
import Chart from 'primevue/chart'
import Skeleton from 'primevue/skeleton'
import { computed } from 'vue'
import AppCard from '@/Components/Core/AppCard.vue'
import useChartTheme from '@/composables/useChartTheme'
import { Charts } from '@/objects/Charts'
import { Translator } from '@/objects/Translator'
import type { ChartSeries, ChartType } from '@/types/chart'

/**
 * The way to show a statistic as a chart: lines, bars or a pie, always in a card with the same structure.
 * A pie takes the first series only: its values are the slices.
 */
type Props = {
  type: ChartType
  title: string
  description?: string
  icon?: string
  /** What each value is. While labels or series are undefined (they are still loading), a skeleton takes the place of the chart. */
  labels?: string[]
  series?: ChartSeries[]
}

const props = defineProps<Props>()

const theme = useChartTheme()

const loading = computed(() => !props.labels || !props.series)
const empty = computed(
  () =>
    !loading.value &&
    Charts.isEmpty(props.type, props.labels ?? [], props.series ?? [])
)

const chartData = computed(() =>
  Charts.data(props.type, props.labels ?? [], props.series ?? [], theme.value)
)
const chartOptions = computed(() =>
  Charts.options(props.type, props.series ?? [], theme.value)
)
const text = computed(() =>
  Charts.description(
    props.type,
    props.title,
    props.labels ?? [],
    props.series ?? []
  )
)
</script>
<template>
  <AppCard :title="title" :description="description" :icon="icon" data-chart>
    <Skeleton v-if="loading" height="18rem" />
    <p
      v-else-if="empty"
      class="text-muted-color flex h-72 items-center justify-center"
      v-text="Translator.tl('empty')"
    />
    <Chart
      v-else
      :type="type"
      :data="chartData"
      :options="chartOptions"
      :canvas-props="{ role: 'img', 'aria-label': text }"
      class="h-72"
    />
  </AppCard>
</template>
