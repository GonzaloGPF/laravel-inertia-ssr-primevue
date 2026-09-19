import { EventName } from '@/types/event'

export const Events: Record<EventName, EventName> = {
  new_notification: 'new_notification',

  /* Frontend Event */
  confirmed: 'confirmed',
  flash_message: 'flash_message',
  errors: 'errors',
  i18n_loaded: 'i18n_loaded',
}
