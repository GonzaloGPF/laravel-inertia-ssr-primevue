import { defineStore } from 'pinia'
import { ref } from 'vue'
import { Translator } from '@/objects/Translator'
import { FlashMessage } from '@/types/flash-messages'

const MESSAGE_TTL = 3000

export const useFlashMessages = defineStore('flashMessages', () => {
  const flashMessages = ref<FlashMessage[]>([])

  const pushFlashMessage = (
    message: FlashMessage,
    params: Partial<FlashMessage> = {}
  ) => {
    if (!message) return

    const flashMessage = addMessage(parseFlashMessage(message, params))

    if (!flashMessage) return

    flashMessage.timer = setTimeout(
      () => destroy(flashMessage),
      flashMessage.duration
    )
  }

  const pushFlashMessageAction = (
    action: string,
    model = undefined,
    female = false,
    plural = false
  ) => {
    pushFlashMessage({
      type: 'success',
      message: Translator.actionTitle(action, model, female, plural),
    })
  }

  const addMessage = (flashMessage?: FlashMessage) => {
    if (!flashMessage || isDuplicated(flashMessage)) {
      return
    }

    flashMessages.value.push(flashMessage)

    return flashMessage
  }

  const isDuplicated = (flashMessage: FlashMessage) =>
    flashMessages.value.find(
      (iMessage) => iMessage.message === flashMessage.message
    )

  const destroy = (flashMessage: FlashMessage) => {
    clearTimeout(flashMessage.timer)

    flashMessage.destroyed = true

    clean()
  }

  const clean = () => {
    flashMessages.value = flashMessages.value.filter(
      (flashMessage) => !flashMessage.destroyed
    )
  }

  const parseFlashMessage = (message: string | FlashMessage, params: FlashMessage = {}) => {
    if (typeof message === 'string') {
      params.message = message
    } else {
      params = message || {}
    }

    if (!params.message) {
      return
    }

    return {
      title: params.title,
      message: params.message,
      icon: params.icon,
      closable: params.closable,
      type: params.type,
      duration: params.duration || MESSAGE_TTL,
    }
  }

  return {
    flashMessages,
    pushFlashMessage,
    pushFlashMessageAction,
  }
})
