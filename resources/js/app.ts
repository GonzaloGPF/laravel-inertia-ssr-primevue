import '../css/app.css'

import { createSSRApp, DefineComponent, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import app from '@/config/app'
import BaseLayout from '@/Layouts/BaseLayout.vue'
import { registerDirectives } from '@/directives/primevue'
import { registerPlugins } from '@/plugins/registerPlugins'

createInertiaApp({
  title: (title) => app.getAppTitle(title),
  resolve: (name) => {
    const page = resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue')
    )

    page.then((module) => {
      module.default.layout = module.default.layout || BaseLayout
    })

    return page
  },
  setup({ el, App, props, plugin }) {
    const app = createSSRApp({ render: () => h(App, props) }).use(plugin)

    registerPlugins(app, props)
    registerDirectives(app)

    app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
