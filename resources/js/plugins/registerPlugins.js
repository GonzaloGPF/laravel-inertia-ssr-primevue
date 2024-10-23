import { createPinia } from 'pinia'
import i18n, { options, optionsSSR } from './i18n.js'
import { ZiggyVue } from '../../../vendor/tightenco/ziggy/dist'
import PrimeVue from 'primevue/config'
import { primeVueConfig } from './primevue.js'

export function registerPlugins(app, props = {}, isSSR = false) {
  app
    .use(ZiggyVue, isSSR ? getSSRZiggyOptions(props) : {})
    .use(createPinia())
    .use(i18n, isSSR ? optionsSSR : options)
    .use(PrimeVue, primeVueConfig)

  app.config.globalProperties.$route = route
}

function getSSRZiggyOptions(props = {}) {
  if (!props.ziggy) {
    return {}
  }
  return {
    ...props.ziggy,
    location: new URL(props.ziggy.location),
  }
}
