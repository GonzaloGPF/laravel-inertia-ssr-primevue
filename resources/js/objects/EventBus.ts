import mitt, { type Handler } from 'mitt'
import { EventName } from '@/types/event'

/**
 * We will use a Mitt as an event bus ❤
 * It will let components messaging each other using a simple object.
 * For example: EventBus.emit() or EventBus.on()
 */
const emitter = mitt<Record<EventName, unknown>>()

const EventBus = {
  emit(event: EventName, data?: unknown) {
    return emitter.emit(event, data)
  },

  on(event?: EventName, callback: Handler = () => {}) {
    if (!event) return
    if (Array.isArray(event)) {
      event.forEach((e) => emitter.on(e, callback))
    } else {
      emitter.on(event, callback)
    }
  },

  /**
   * Stops listening to an event.
   * It removes the callback. If no callback is provided, every event handler associated to the event will be removed
   *
   * @param {string} event
   * @param {function|null} callback
   * @returns void
   */
  off(event?: EventName, callback: Handler = () => {}) {
    if (!event) return
    return emitter.off(event, callback)
  },
}

export default EventBus
