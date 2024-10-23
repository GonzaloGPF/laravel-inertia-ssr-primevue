import { defineStore } from 'pinia'
import Translator from '@/objects/Translator.js'
import EventBus from '@/objects/EventBus.js'
import events from '@/config/events.js'
import { ref, shallowRef } from 'vue'

export const useConfirm = defineStore('confirm', () => {
  const show = ref(false)
  const title = ref('')
  const message = ref('')
  const okText = ref('')
  const cancelText = ref('')
  const component = shallowRef(null)

  function showConfirm(confirmMessage = '', params = {}) {
    if (!params.component) {
      component.value = null
    }

    params = parseParams(confirmMessage, params)

    title.value = params.title
    message.value = params.message
    okText.value = params.okText
    cancelText.value = params.cancelText

    return new Promise((resolve) => {
      show.value = true

      // Subscribe to EventBus.confirmed to detect when to close dialog
      EventBus.on(events.confirmed, (confirmed) => {
        show.value = false
        resolve(confirmed)
      })
    })
    // .finally(() => show.value = false);
  }

  function showPrompt(confirmMessage, params = {}) {
    component.value = params.component

    return showConfirm(confirmMessage, params)
  }

  return {
    show,
    title,
    message,
    okText,
    cancelText,
    showConfirm,
    showPrompt,
    component,
  }
})

function parseParams(message = '', params = {}) {
  if (typeof message === 'string') {
    params.message = message
  } else {
    params = message || {}
  }

  return {
    title: params.title || Translator.tl('warning'),
    message: params.message || Translator.t('help.confirm'),
    okText: params.okText || Translator.actionTitle('ok'),
    cancelText: params.cancelText || Translator.actionTitle('cancel'),
  }
}
