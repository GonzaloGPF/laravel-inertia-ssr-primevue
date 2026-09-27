import Aura from '@primevue/themes/aura'

export const primeVueConfig = {
  theme: {
    preset: Aura,
    options: {
      cssLayer: {
        name: 'primevue',
        order: 'theme, base, primevue',
      },
    },
  },
}
