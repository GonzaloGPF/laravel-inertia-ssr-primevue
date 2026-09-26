import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

let timeout: ReturnType<typeof setTimeout> | null = null

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
      timeout = setTimeout(() => {
        loading.value = true
      }, 250)
    })

    router.on('progress', (event) => {
      const percentage = event.detail.progress?.percentage

      if (loading.value && percentage) {
        progress.value = (percentage / 100) * 0.9
      }
    })

    router.on('finish', (event) => {
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
