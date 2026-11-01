<script lang="ts" setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppCard from '@/Components/Core/AppCard.vue'
import AppLineChart from '@/Components/Core/AppLineChart.vue'
import AppStatCard from '@/Components/Core/AppStatCard.vue'
import useAuth from '@/composables/useAuth'
import { Translator } from '@/objects/Translator'
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

/** Y-m as the short month and year, in the language of the user: Nov 26 */
const monthLabel = (month: string) => {
  const [year, number] = month.split('-').map(Number)

  return new Intl.DateTimeFormat(Translator.getLocale(), {
    month: 'short',
    year: '2-digit',
  }).format(new Date(year, number - 1, 1))
}

const monthLabels = computed(() =>
  props.usersByMonth?.map(({ month }) => monthLabel(month))
)
const monthTotals = computed(() =>
  props.usersByMonth?.map(({ total }) => total)
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
      <AppCard
        :title="Translator.tl('new_users_per_month')"
        :description="Translator.tl('last_12_months')"
        icon="show_chart"
      >
        <AppLineChart
          :labels="monthLabels"
          :data="monthTotals"
          :label="Translator.tl('new_users')"
        />
      </AppCard>
    </div>
    <p v-else>This is the dashboard</p>
  </div>
</template>
