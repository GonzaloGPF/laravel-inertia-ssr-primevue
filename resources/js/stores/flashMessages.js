import { defineStore } from 'pinia'
import { ref } from 'vue'
import Translator from '@/objects/Translator.js'

const MESSAGE_TTL = 3000

export const useFlashMessages = defineStore('flashMessages', () => {
  const flashMessages = ref([])

  /**
   * {
   *   title: String
   *   message: String - Required,
   *   icon: String,
   *   border: String,
   *   closable: Boolean,
   *   type: String ('success', 'info', 'warning', or 'error')
   *   duration: Number,
   * }
   * @param message {
   *      title: string|null,
   *      message: string|null,
   *      icon: string|null,
   *      border: string|null,
   *      type: string,
   *      duration: int|null
   * }
   * @param params
   */
  const pushFlashMessage = (message, params = {}) => {
    if (!message) return

    const flashMessage = addMessage(parseFlashMessage(message, params))

    if (!flashMessage) return

    flashMessage.timer = setTimeout(
      () => destroy(flashMessage),
      flashMessage.duration
    )
  }

  const pushFlashMessageAction = (
    action,
    model = null,
    female = false,
    plural = false
  ) => {
    pushFlashMessage({
      type: 'success',
      message: Translator.actionTitle(action, model, female, plural),
    })
  }

  const addMessage = (flashMessage) => {
    if (!flashMessage || isDuplicated(flashMessage)) {
      return
    }

    flashMessages.value.push(flashMessage)

    return flashMessage
  }

  const isDuplicated = (flashMessage) =>
    flashMessages.value.find(
      (iMessage) => iMessage.message === flashMessage.message
    )

  const destroy = (flashMessage) => {
    clearTimeout(flashMessage.timer)

    flashMessage.destroyed = true

    clean()
  }

  const clean = () => {
    flashMessages.value = flashMessages.value.filter(
      (flashMessage) => !flashMessage.destroyed
    )
  }

  const parseFlashMessage = (message, params = {}) => {
    if (typeof message === 'string') {
      params.message = message
    } else {
      params = message || {}
    }

    if (!params.message) {
      return null
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
