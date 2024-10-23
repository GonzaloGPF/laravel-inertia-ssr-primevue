<script setup lang="ts">
import TopMenu from '@/Components/Menu/TopMenu.vue'
import EventBus from '@/objects/EventBus'
import events from '@/config/events'
import { useFlashMessages } from '@/stores/flashMessages'
import AppFooter from '@/Components/Core/AppFooter.vue'
import AppFlashMessages from '@/Components/Core/AppFlashMessages.vue'
import AppConfirm from '@/Components/Core/AppConfirm.vue'
import AppGlobalLoader from '@/Components/Core/AppGlobalLoader.vue'
import { storeToRefs } from 'pinia'
import { useLoader } from '@/stores/loader.js'

const { pushFlashMessage } = useFlashMessages()
const { i18nLoaded } = storeToRefs(useLoader())

EventBus.on(events.flash_message, pushFlashMessage)
EventBus.on(events.i18n_loaded, () => {
  i18nLoaded.value = true
})
</script>
<template>
  <div class="flex flex-col h-screen">
    <TopMenu />
    <div class="flex-1">
      <AppGlobalLoader />
      <AppFlashMessages />
      <AppConfirm />
      <Transition
        v-show="i18nLoaded"
        name="page"
        mode="out-in"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
        class="transition ease-in-out duration-150"
        appear
      >
        <main
          :key="$page.component"
          class="container mx-auto px-0 pb-0 md:px-6 lg:px-8 mb-auto mt-2"
        >
          <slot />
        </main>
      </Transition>
    </div>

    <AppFooter />
  </div>
</template>
