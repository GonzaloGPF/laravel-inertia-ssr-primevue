import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export const useNavigation = defineStore('navigation', () => {
  const currentLocation = ref(window.location.href)

  router.on('navigate', (event) => {
    currentLocation.value = event.detail.page.props.ziggy.location
  })

  return {
    currentLocation,
  }
})
