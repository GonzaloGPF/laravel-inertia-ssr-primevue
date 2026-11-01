import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

let timeout: ReturnType<typeof setTimeout> | null = null

// The visits that are running: a page can ask for several at once (i.e. its deferred props, in groups)
let visits = 0

export const useLoader = defineStore('loader', () => {
  const loading = ref(false)
  const progress = ref(0)
  const i18nLoaded = ref(false)

  const start = () => {
    loading.value = true
  }

  const finish = () => {
    loading.value = false
  }

  const registerListeners = () => {
    router.on('start', () => {
      visits++

      // Only one timer, whatever the visits: one that is lost could not be cleared, and would show the bar for ever
      timeout ??= setTimeout(() => {
        loading.value = true
        timeout = null
      }, 250)
    })

    router.on('progress', (event) => {
      const percentage = event.detail.progress?.percentage

      if (loading.value && percentage) {
        progress.value = (percentage / 100) * 0.9
      }
    })

    router.on('finish', (event) => {
      visits = Math.max(0, visits - 1)

      // The bar goes on until the last visit ends
      if (visits > 0) return

      if (timeout) {
        clearTimeout(timeout)
        timeout = null
      }

      if (event.detail.visit.completed) {
        loading.value = false
      } else if (event.detail.visit.interrupted) {
        progress.value = 0
      } else if (event.detail.visit.cancelled) {
        loading.value = false
      }
    })
  }

  return {
    loading,
    i18nLoaded,
    start,
    finish,
    registerListeners,
  }
})
