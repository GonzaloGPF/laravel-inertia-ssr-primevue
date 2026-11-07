<script lang="ts" setup>
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppButton from '@/Components/Core/AppButton.vue'
import AppCard from '@/Components/Core/AppCard.vue'
import { Translator } from '@/objects/Translator'
import type { Severity } from '@/types/design'

/** The status of the response of the error: the page tells what it is, and gives a way out. */
type Props = {
  status: number
}

type Presentation = {
  icon: string
  severity: Severity
}

// What a status looks like. A status that is not here looks like a server error.
const PRESENTATIONS: Record<number, Presentation> = {
  403: { icon: 'lock', severity: 'warn' },
  404: { icon: 'search_off', severity: 'info' },
  429: { icon: 'hourglass_top', severity: 'warn' },
  500: { icon: 'error', severity: 'danger' },
  503: { icon: 'construction', severity: 'danger' },
}

const props = defineProps<Props>()

const presentation = computed(
  () => PRESENTATIONS[props.status] ?? PRESENTATIONS[500]
)
const title = computed(() => Translator.t(`http-statuses.${props.status}`))
const description = computed(() =>
  Translator.t(
    Translator.te(`errors.${props.status}`)
      ? `errors.${props.status}`
      : 'errors.default'
  )
)

// A page that was opened straight from a link has nothing to go back to
const goBack = () =>
  window.history.length > 1
    ? window.history.back()
    : router.visit(route('root'))
</script>
<template>
  <div class="mx-auto flex max-w-xl flex-col gap-6 pb-6">
    <Head :title="`${status} ${title}`" />
    <AppCard
      :title="title"
      :icon="presentation.icon"
      :severity="presentation.severity"
      data-error-page
    >
      <div class="flex flex-col items-center gap-4 py-4 text-center">
        <p class="text-6xl font-bold" v-text="status" />
        <p class="text-muted-color" v-text="description" />
        <div class="flex flex-wrap justify-center gap-2">
          <AppButton
            :label="Translator.t('actions.back')"
            icon="arrow_back"
            severity="secondary"
            outlined
            @click="goBack"
          />
          <AppButton
            :label="Translator.t('actions.home')"
            icon="home"
            :href="route('root')"
          />
        </div>
      </div>
    </AppCard>
  </div>
</template>
