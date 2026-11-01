<script setup lang="ts">
import Chart from 'primevue/chart'
import Skeleton from 'primevue/skeleton'
import { computed } from 'vue'
import useChartTheme from '@/composables/useChartTheme'

type Props = {
  /** The label of each point. While labels or data are undefined, a skeleton takes the place of the chart. */
  labels?: string[]
  data?: number[]
  /** What the data is, as the tooltip and the screen readers say it. */
  label: string
}

const props = defineProps<Props>()

const theme = useChartTheme()

const loading = computed(() => !props.labels || !props.data)

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      label: props.label,
      data: props.data,
      borderColor: theme.value.primary,
      backgroundColor: theme.value.primaryFill,
      pointBackgroundColor: theme.value.primary,
      fill: true,
      tension: 0.35,
      pointRadius: 3,
      pointHoverRadius: 5,
    },
  ],
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: {
      ticks: { color: theme.value.muted },
      grid: { display: false },
    },
    y: {
      beginAtZero: true,
      ticks: { color: theme.value.muted, precision: 0 }, // Users are not split in halves
      grid: { color: theme.value.grid },
    },
  },
}))

// The canvas says nothing to a screen reader, so it gets the values as a text
const description = computed(() =>
  [
    props.label,
    ...(props.labels ?? []).map(
      (label, index) => `${label}: ${props.data?.[index]}`
    ),
  ].join(', ')
)
</script>
<template>
  <Skeleton v-if="loading" height="18rem" />
  <Chart
    v-else
    type="line"
    :data="chartData"
    :options="chartOptions"
    :canvas-props="{ role: 'img', 'aria-label': description }"
    class="h-72"
  />
</template>
