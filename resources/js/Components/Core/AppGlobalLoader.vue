<script setup>
import { useLoader } from '@/stores/loader.js'
import { storeToRefs } from 'pinia'
import ProgressBar from 'primevue/progressbar'

const { loading, progress, i18nLoaded } = storeToRefs(useLoader())

useLoader().registerListeners()
</script>
<template>
  <div v-if="loading || !i18nLoaded" class="relative h-0">
    <ProgressBar
      v-show="loading || !i18nLoaded"
      :value="progress"
      style="height: 6px"
    />
  </div>
</template>
