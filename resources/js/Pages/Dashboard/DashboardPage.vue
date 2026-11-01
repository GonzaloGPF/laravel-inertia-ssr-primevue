<script lang="ts" setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppChart from '@/Components/Core/AppChart.vue'
import AppStatCard from '@/Components/Core/AppStatCard.vue'
import useAuth from '@/composables/useAuth'
import { Formatter } from '@/objects/Formatter'
import { Translator } from '@/objects/Translator'
import type { ChartSeries } from '@/types/chart'
import type { DashboardStats, MonthlyTotal } from '@/types/dashboard'

/** Both are deferred: they are undefined until the backend gives them, and they are never given to the users that are not admins. */
type Props = {
  stats?: DashboardStats
  usersByMonth?: MonthlyTotal[]
}

const PLACEHOLDERS = 3

const props = defineProps<Props>()

const { isAdmin } = useAuth()

// The placeholder cards are there, loading, before their numbers arrive
const placeholders = computed<(number | undefined)[]>(
  () => props.stats?.placeholders ?? new Array(PLACEHOLDERS).fill(undefined)
)

const monthLabels = computed(() =>
  props.usersByMonth?.map(({ month }) => Formatter.month(month))
)
const newUsers = computed<ChartSeries[] | undefined>(
  () =>
    props.usersByMonth && [
      {
        label: Translator.tl('new_users'),
        data: props.usersByMonth.map(({ total }) => total),
      },
    ]
)
</script>
<template>
  <div>
    <Head :title="Translator.tl('dashboard')" />
    <div v-if="isAdmin" class="flex flex-col gap-6 pb-6">
      <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <AppStatCard
          :label="Translator.tl('total_users')"
          :value="stats?.users"
          icon="group"
        />
        <AppStatCard
          v-for="(value, index) in placeholders"
          :key="index"
          :label="`${Translator.tl('label')} ${index + 1}`"
          :value="value"
          icon="analytics"
        />
      </section>
      <AppChart
        type="line"
        :title="Translator.tl('new_users_per_month')"
        :description="Translator.tl('last_12_months')"
        :labels="monthLabels"
        :series="newUsers"
        icon="show_chart"
      />
    </div>
    <p v-else>This is the dashboard</p>
  </div>
</template>
