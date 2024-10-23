import Mitt from 'mitt'

/**
 * We will use a Mitt as an event bus ❤
 * It will let components messaging each other using a simple object.
 * For example: EventBus.emit() or EventBus.on()
 */
const emitter = new Mitt()

const EventBus = {
    /**
     * Emits an event
     *
     * @param {string} event
     * @param {object|null} data
     * @returns void
     */
    emit (event, data = null) {
        return emitter.emit(event, data)
    },

    /**
     * Listen to an event
     *
     * @param {string} event
     * @param {function} callback
     * @returns void
     */
    on (event, callback) {
        if (Array.isArray(event)) {
            event.forEach(e => emitter.on(e, callback))
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
    off (event, callback = null) {
        return emitter.off(event, callback)
    },
}

export default EventBus
