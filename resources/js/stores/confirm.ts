import { defineStore } from 'pinia'
import { Translator } from '@/objects/Translator'
import EventBus from '@/objects/EventBus'
import { Events } from '@/config/events'
import { ref, shallowRef } from 'vue'

type ConfirmParams = {
  title?: string
  message?: string
  okText?: string
  cancelText?: string
  component?: unknown
}

export const useConfirm = defineStore('confirm', () => {
  const show = ref(false)
  const title = ref('')
  const message = ref('')
  const okText = ref('')
  const cancelText = ref('')
  const component = shallowRef()

  function showConfirm(confirmMessage = '', params: ConfirmParams = {}) {
    if (!params.component) {
      component.value = undefined
    }

    const parsed = parseParams(confirmMessage, params)

    title.value = parsed.title
    message.value = parsed.message
    okText.value = parsed.okText
    cancelText.value = parsed.cancelText

    return new Promise((resolve) => {
      show.value = true

      // Subscribe to EventBus.confirmed to detect when to close dialog
      EventBus.on(Events.confirmed, (confirmed: unknown) => {
        show.value = false
        resolve(confirmed)
      })
    })
    // .finally(() => show.value = false);
  }

  function showPrompt(confirmMessage?: string, params: ConfirmParams = {}) {
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

function parseParams(
  message: string | ConfirmParams = '',
  params: ConfirmParams = {}
) {
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
