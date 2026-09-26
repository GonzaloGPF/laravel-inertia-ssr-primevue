import Tooltip from 'primevue/tooltip'
import type { App } from 'vue'

export function registerDirectives(app: App) {
  app.directive('tooltip', Tooltip)
}
