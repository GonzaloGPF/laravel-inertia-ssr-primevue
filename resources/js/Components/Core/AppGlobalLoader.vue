<script setup>
import { useLoader } from '@/stores/loader.ts'
import { storeToRefs } from 'pinia'
import ProgressBar from 'primevue/progressbar'
import { computed } from 'vue'

const { loading, progress, i18nLoaded } = storeToRefs(useLoader())

useLoader().registerListeners()

const active = computed(() => loading.value || !i18nLoaded.value)
</script>
<template>
  <!-- It keeps its space when it is not shown, so the page does not move when it shows up -->
  <div class="h-1.5">
    <ProgressBar
      :class="{ invisible: !active }"
      :value="progress"
      style="height: 6px"
    />
  </div>
</template>
